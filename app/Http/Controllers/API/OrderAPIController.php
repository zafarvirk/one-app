<?php
/**
 * File name: OrderAPIController.php
 * Last modified: 2020.05.31 at 19:34:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Criteria\Orders\OrdersOfStatusesCriteria;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Events\OrderChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\AssignedOrder;
use App\Notifications\NewOrder;
use App\Notifications\StatusChangedOrder;
use App\Repositories\CartRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ArticleOrderRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Repositories\WalletTransactionRepository;
use App\Repositories\BusinessRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Stripe\Token;
use function GuzzleHttp\Promise\iter_for;
use function MongoDB\BSON\toJSON;

/**
 * Class OrderController
 * @package App\Http\Controllers\API
 */
class OrderAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;
    /** @var  ArticleOrderRepository */
    private $productOrderRepository;
    /** @var  CartRepository */
    private $cartRepository;
    /** @var  UserRepository */
    private $userRepository;
    /** @var  PaymentRepository */
    private $paymentRepository;
    /** @var  NotificationRepository */
    private $notificationRepository;
    /** @var  ArticleRepository */
    private $articleRepository;

    /**
     * @var WalletTransactionRepository
     */
    private $walletTransactionRepository;
    /**
     * @var WalletRepository
     */
    private $walletRepository;
    /**
     * @var BusinessRepository
     */
    private $businessRepository;

    /**
     * OrderAPIController constructor.
     * @param OrderRepository $orderRepo
     * @param ProductOrderRepository $productOrderRepository
     * @param CartRepository $cartRepo
     * @param PaymentRepository $paymentRepo
     * @param NotificationRepository $notificationRepo
     * @param UserRepository $userRepository
     */
    public function __construct(OrderRepository $orderRepo, ArticleOrderRepository $productOrderRepository, CartRepository $cartRepo, PaymentRepository $paymentRepo, NotificationRepository $notificationRepo,
     UserRepository $userRepository , ArticleRepository $articleRepo, WalletTransactionRepository $walletTransactionRepository, WalletRepository $walletRepository, BusinessRepository $businessRepository)
    {
        $this->orderRepository = $orderRepo;
        $this->productOrderRepository = $productOrderRepository;
        $this->cartRepository = $cartRepo;
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepo;
        $this->notificationRepository = $notificationRepo;
        $this->articleRepository = $articleRepo;
        $this->walletTransactionRepository = $walletTransactionRepository;
        $this->walletRepository = $walletRepository;
        $this->businessRepository = $businessRepository;
    }

    /**
     * Display a listing of the Order.
     * GET|HEAD /orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->orderRepository->pushCriteria(new RequestCriteria($request));
            $this->orderRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->orderRepository->pushCriteria(new OrdersOfStatusesCriteria($request));
            $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $orders = $this->orderRepository->all();

        return $this->sendResponse($orders->toArray(), 'Orders retrieved successfully');
    }

    /**
     * Display the specified Order.
     * GET|HEAD /orders/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        /** @var Order $order */
        if (!empty($this->orderRepository)) {
            try {
                $this->orderRepository->pushCriteria(new RequestCriteria($request));
                $this->orderRepository->pushCriteria(new LimitOffsetCriteria($request));
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }
            $order = $this->orderRepository->findWithoutFail($id);
        }

        if (empty($order)) {
            return $this->sendError('Order not found');
        }

        return $this->sendResponse($order->toArray(), 'Order retrieved successfully');


    }

    /**
     * Store a newly created Order in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $payment = $request->only('payment');
        if (isset($payment['payment']) && $payment['payment']['method']) {
            if ($payment['payment']['method'] == 7) {
                return $this->stripPayment($request);
            }
            else if ($payment['payment']['method'] == 11) {
                return $this->walletPayment($request);
            } else {
                return $this->cashPayment($request);

            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    private function stripPayment(Request $request)
    {
        $input = $request->all();
        $amount = 0;
        try {
            $user = $this->userRepository->findWithoutFail($input['user_id']);
            if (empty($user)) {
                return $this->sendError('User not found');
            }
            $stripeToken = Token::create(array(
                "card" => array(
                    "number" => $input['stripe_number'],
                    "exp_month" => $input['stripe_exp_month'],
                    "exp_year" => $input['stripe_exp_year'],
                    "cvc" => $input['stripe_cvc'],
                    "name" => $user->name,
                )
            ));
            if ($stripeToken->created > 0) {
                $products = $this->cartRepository->where('user_id' , auth()->user()->id)->get();
                if($products->count() == 0){
                    return $this->sendResponse($products->toArray(), 'Cart is empty add atleast one product to place order');
                }
                $order = $this->orderRepository->create(
                    ['user_id' => auth()->user()->id,'transaction_status_id' => $input['transaction_status_id'], 'tax' => $input['tax'], 'address_id' => $input['address_id'], 'delivery_fee' => $input['delivery_fee'], 'hint' => $input['hint']]
                );
                Log::info($order);
    
                $user = $this->userRepository->find($order->user_id);
                foreach ($products as $productOrder) {
                    $price = 0;
                    if($this->articleRepository->findWithoutFail($productOrder['article_id'])->discount_price != null){
                        $price = $this->articleRepository->findWithoutFail($productOrder['article_id'])->discount_price;
                    }
                    else {
                        $price = $this->articleRepository->findWithoutFail($productOrder['article_id'])->price;
                    }
                    $amount += $price * $productOrder['quantity'];
                    $this->productOrderRepository->create(['price' => $price , 'quantity' => $productOrder['quantity'] , 'article_id' => $productOrder['article_id'] , 'order_id' => $order->id]);
                }
                $amount += $order->delivery_fee;
                $amountWithTax = $amount + ($amount * $order->tax / 100);
                $charge = $user->charge((int)($amountWithTax * 100), ['source' => $stripeToken]);
                $payment = $this->paymentRepository->create([
                    "user_id" => $input['user_id'],
                    "description" => trans("lang.payment_order_done"),
                    "price" => $amountWithTax,
                    "status" => $charge->status, // $charge->status
                    "method" => $input['payment']['method'],
                ]);
                $this->orderRepository->update(['payment_id' => $payment->id], $order->id);

                $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

                // Notification::send($order->productOrders[0]->product->market->users, new NewOrder($order));

                $business = $this->businessRepository->find($this->articleRepository->findWithoutFail($productOrder['article_id'])->business_id);
                $notification = [
                    'title' => trans('lang.Notifications_NewBooking'),
                    'body' => trans('lang.Notifications_NewBooking', ['order_id' => $order->id, 'transaction_status' => $order->transactionStatus->status]),
                    'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                    'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                    'id' => 'App\\Notifications\\CreatedNewOrder',
                    'status' => 'done',
                ];
                $data = $notification;
                $data['orderId'] = $order->id;
                foreach($business->users as $owner){
                    notify($data , $owner->id , trans('lang.Notifications_NewBooking'));
                }

            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    private function cashPayment(Request $request){
        $input = $request->all();
        $amount = 0;
        try {
            $products = $this->cartRepository->where('user_id' , auth()->user()->id)->get();
            if($products->count() == 0){
                return $this->sendResponse($products->toArray(), 'Cart is empty add atleast one product to place order');
            }
            $order = $this->orderRepository->create(
                ['user_id' => auth()->user()->id,'transaction_status_id' => $input['transaction_status_id'], 'tax' => $input['tax'], 'address_id' => $input['address_id'], 'delivery_fee' => $input['delivery_fee'], 'hint' => $input['hint']]
            );
            Log::info($order);

            $user = $this->userRepository->find($order->user_id);
            foreach ($products as $productOrder) {
                $price = 0;
                if($this->articleRepository->findWithoutFail($productOrder['article_id'])->discount_price != null){
                    $price = $this->articleRepository->findWithoutFail($productOrder['article_id'])->discount_price;
                }
                else {
                    $price = $this->articleRepository->findWithoutFail($productOrder['article_id'])->price;
                }
                $amount += $price * $productOrder['quantity'];
                $this->productOrderRepository->create(['price' => $price , 'quantity' => $productOrder['quantity'] , 'article_id' => $productOrder['article_id'] , 'order_id' => $order->id]);
            }
            $amount += $order->delivery_fee;
            $amountWithTax = $amount + ($amount * $order->tax / 100);
            $payment = $this->paymentRepository->create([
                "user_id" => auth()->user()->id,
                "description" => trans("lang.payment_order_waiting"),
                "amount" => $amountWithTax,
                "payment_method_id" => 6,
                "payment_status_id" => 1,
            ]);

            $this->orderRepository->update(['payment_id' => $payment->id], $order->id);

            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

            // Notification::send($order->productOrders[0]->product->business->users, new NewOrder($order));
            $business = $this->businessRepository->find($this->articleRepository->findWithoutFail($productOrder['article_id'])->business_id);
            $notification = [
                'title' => trans('lang.Notifications_NewBooking'),
                'body' => trans('lang.Notifications_NewBooking', ['order_id' => $order->id, 'transaction_status' => $order->transactionStatus->status]),
                'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                'id' => 'App\\Notifications\\CreatedNewOrder',
                'status' => 'done',
            ];
            $data = $notification;
            $data['orderId'] = $order->id;
            foreach($business->users as $owner){
                notify($data , $owner->id , trans('lang.Notifications_NewBooking'));
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    private function walletPayment(Request $request) 
    {
        $input = $request->all();
        $amount = 0;
        try {
            $products = $this->cartRepository->where('user_id' , auth()->user()->id)->get();
            if($products->count() == 0){
                return $this->sendResponse($products->toArray(), 'Cart is empty add atleast one product to place order');
            }
            $order = $this->orderRepository->create(
                ['user_id' => auth()->user()->id,'transaction_status_id' => $input['transaction_status_id'], 'tax' => $input['tax'], 'address_id' => $input['address_id'], 'delivery_fee' => $input['delivery_fee'], 'hint' => $input['hint']]
            );
            Log::info($order);

            $user = $this->userRepository->find($order->user_id);
            foreach ($products as $productOrder) {
                $price = 0;
                if($this->articleRepository->findWithoutFail($productOrder['article_id'])->discount_price != null){
                    $price = $this->articleRepository->findWithoutFail($productOrder['article_id'])->discount_price;
                }
                else {
                    $price = $this->articleRepository->findWithoutFail($productOrder['article_id'])->price;
                }
                $amount += $price * $productOrder['quantity'];
                $this->productOrderRepository->create(['price' => $price , 'quantity' => $productOrder['quantity'] , 'article_id' => $productOrder['article_id'] , 'order_id' => $order->id]);
            }
            $amount += $order->delivery_fee;
            $amountWithTax = $amount + ($amount * $order->tax / 100);
            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);
            $transaction = [];
            $wallet = $this->walletRepository->find($input['walletId']);
            if ($wallet->currency->code == setting('default_currency_code')) {
                $input['payment']['amount'] = $amountWithTax;
                $input['payment']['description'] = __('lang.payment_booking_id') . $order->id;
                $input['payment']['payment_status_id'] = 2; // done
                $input['payment']['payment_method_id'] = 11; 
                $input['payment']['user_id'] = auth()->id();
                $transaction['wallet_id'] = $input['walletId'];
                $transaction['user_id'] = $input['payment']['user_id'];
                $transaction['amount'] = $input['payment']['amount'];
                $transaction['description'] = __('lang.payment_booking_id') . $order->id;
                $transaction['action'] = 'debit';
                $this->walletTransactionRepository->create($transaction);
                
                $payment = $this->paymentRepository->create($input['payment']);
                $this->orderRepository->update(['payment_id' => $payment->id], $order->id);
                // Notification::send($order->business->users, new StatusChangedPayment($order));
                
                $business = $this->businessRepository->find($this->articleRepository->findWithoutFail($productOrder['article_id'])->business_id);
                $notification = [
                    'title' => trans('lang.Notifications_NewBooking'),
                    'body' => trans('lang.Notifications_NewBooking', ['order_id' => $order->id, 'transaction_status' => $order->transactionStatus->status]),
                    'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                    'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                    'id' => 'App\\Notifications\\CreatedNewOrder',
                    'status' => 'done',
                ];
                $data = $notification;
                $data['orderId'] = $order->id;
                foreach($business->users as $owner){
                    notify($data , $owner->id , trans('lang.Notifications_NewBooking'));
                }

            } else {
                return $this->sendError(__('lang.not_found', ['operator' => __('lang.wallet')]));
            }
        } catch (ValidatorException | ModelNotFoundException $e) {
            return $this->sendError(__('lang.not_found', ['operator' => __('lang.payment')]));
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    /**
     * Update the specified Order in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $oldOrder = $this->orderRepository->findWithoutFail($id);
        if (empty($oldOrder)) {
            return $this->sendError('Order not found');
        }
        $oldStatus = $oldOrder->payment->status;
        $input = $request->all();

        try {
            $order = $this->orderRepository->update($input, $id);
            if (isset($input['transaction_status_id']) && $input['transaction_status_id'] == 5 && !empty($order)) {
                $this->paymentRepository->update(['status' => 'Paid'], $order['payment_id']);
            }
            event(new OrderChangedEvent($oldStatus, $order));

            if (setting('enable_notifications', false)) {
                if (isset($input['transaction_status_id']) && $input['transaction_status_id'] != $oldOrder->transaction_status_id) {
                    Notification::send([$order->user], new StatusChangedOrder($order));
                    $business = $this->businessRepository->find($this->articleRepository->findWithoutFail($oldOrder->products[0]->id)->business_id);
                    $notification = [
                        'title' => trans('lang.notification_status_changed_booking'),
                        'body' => trans('lang.notification_your_booking', ['order_id' => $order->id, 'transaction_status' => $order->transactionStatus->status]),
                        'icon' => $business->hasMedia('image')?$business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                        'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                        'id' => 'App\\Notifications\\StatusChangedOrder',
                        'status' => 'done',
                    ];
                    $data = $notification;
                    $data['orderId'] = $order->id;
                    foreach($business->users as $owner){
                        notify($data , $owner->id , trans('lang.notification_status_changed_booking'));
                    }

                }

                if (isset($input['driver_id']) && ($input['driver_id'] != $oldOrder['driver_id'])) {
                    $driver = $this->userRepository->findWithoutFail($input['driver_id']);
                    if (!empty($driver)) {
                        Notification::send([$driver], new AssignedOrder($order));
                    }
                }
            }

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

}
