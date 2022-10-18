<?php
/*
 * File name: BookingAPIController.php
 * Last modified: 2022.02.16 at 21:07:04
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers\API;


use App\Criteria\Bookings\BookingsOfUserCriteria;
use App\Criteria\Coupons\ValidCriteria;
use App\Events\BookingChangedEvent;
use App\Events\TransactionStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateBooking;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Plan;
use App\Notifications\NewBooking;
use App\Notifications\StatusChangedBooking;
use App\Repositories\AddressRepository;
use App\Repositories\BookingRepository;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleScheduleRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OptionRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentStatusRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use function Illuminate\Support\Facades\Log;

/**
 * Class BookingController
 * @package App\Http\Controllers\API
 */
class BookingAPIController extends Controller
{
    /** @var  BookingRepository */
    private $bookingRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var TransactionStatusRepository
     */
    private $TransactionStatusRepository;
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var NotificationRepository
     */
    private $notificationRepository;
    /**
     * @var AddressRepository
     */
    private $addressRepository;
    /**
     * @var TaxRepository
     */
    private $taxRepository;
    /**
     * @var ArticleRepository
     */
    private $articleRepository;
    /**
     * @var BusinessRepository
     */
    private $businessRepository;
    /**
     * @var CouponRepository
     */
    private $couponRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    /**
     * @var PaymentStatusRepository
     */
    private $paymentStatusRepository;
    /**
     * @var ArticleScheduleRepository
     */
    private $articleScheduleRepository;

    public function __construct(
        BookingRepository $bookingRepo, 
        CustomFieldRepository $customFieldRepo, 
        UserRepository $userRepo, 
        TransactionStatusRepository $TransactionStatusRepo, 
        NotificationRepository $notificationRepo, 
        PaymentRepository $paymentRepo, 
        AddressRepository $addressRepository, 
        TaxRepository $taxRepository, 
        ArticleRepository $articleRepository, 
        BusinessRepository $businessRepository, 
        CouponRepository $couponRepository, 
        OptionRepository $optionRepository, 
        PaymentStatusRepository $paymentStatusRepository,
        ArticleScheduleRepository $articleScheduleRepository)
    {
        parent::__construct();
        $this->bookingRepository = $bookingRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->TransactionStatusRepository = $TransactionStatusRepo;
        $this->notificationRepository = $notificationRepo;
        $this->paymentRepository = $paymentRepo;
        $this->addressRepository = $addressRepository;
        $this->taxRepository = $taxRepository;
        $this->articleRepository = $articleRepository;
        $this->businessRepository = $businessRepository;
        $this->couponRepository = $couponRepository;
        $this->optionRepository = $optionRepository;
        $this->paymentStatusRepository = $paymentStatusRepository;
        $this->articleScheduleRepository = $articleScheduleRepository;
    }


    /**
     * Display a listing of the Booking.
     * GET|HEAD /bookings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->bookingRepository->pushCriteria(new RequestCriteria($request));
            $this->bookingRepository->pushCriteria(new BookingsOfUserCriteria(auth()->id()));
            $this->bookingRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $bookings = $this->bookingRepository->all();
        $this->filterCollection($request, $bookings);
        return $this->sendResponse($bookings->toArray(), 'Bookings retrieved successfully');
    }

    /**
     * Display the specified Booking.
     * GET|HEAD /bookings/{id}
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show($id, Request $request)
    {
        try {
            $this->bookingRepository->pushCriteria(new RequestCriteria($request));
            $this->bookingRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $booking = $this->bookingRepository->findWithoutFail($id);
        if (empty($booking)) {
            return $this->sendError('Booking not found');
        }
        $this->filterModel($request, $booking);
        return $this->sendResponse($booking->toArray(), 'Booking retrieved successfully');


    }

    /**
     * Store a newly created Booking in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $business = $this->businessRepository->find($input['business']);
            if (isset($input['address'])) {
                $this->validate($request, [
                    'address.address' => Address::$rules['address'],
                    'address.longitude' => Address::$rules['longitude'],
                    'address.latitude' => Address::$rules['latitude'],
                ]);
                // $input['address']['user_id'] = auth()->user()->id;
                $address = $this->addressRepository->updateOrCreate(['address' => $input['address']['address']], $input['address']);
                if (empty($address)) {
                    return $this->sendError(__('lang.not_found', ['operator', __('lang.address')]));
                } else {
                    $input['address'] = $address;
                }
            } else {
                $input['address'] = $business->address;
            }
            if (isset($input['article'])) {
                $input['article'] = $this->articleRepository->findWhereIn('id', $input['article']);
                // coupon code
                if (isset($input['code'])) {
                    $this->couponRepository->pushCriteria(new ValidCriteria($request));
                    $coupon = $this->couponRepository->first();
                    $input['coupon'] = $coupon->getValue($input['article']);
                }
            }
            $taxes = $business->taxes;
            $input['business'] = $business;
            $input['taxes'] = $taxes;
            // $input['user_id'] = auth()->user()->id;
            if (isset($input['options'])) {
                $input['options'] = $this->optionRepository->findWhereIn('id', $input['options']);
            }
            $input['transaction_status_id'] = $this->TransactionStatusRepository->find(1)->id;

            $booking = $this->bookingRepository->create($input);
            Notification::send($business->users, new NewBooking($booking));
            $notification = [
                'title' => trans('lang.Notifications_NewBooking'),
                'body' => trans('lang.Notifications_NewBooking', ['booking_id' => $booking->id, 'transaction_status' => $booking->transactionStatus->status]),
                'icon' => $booking->business->hasMedia('image')?$booking->business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                'id' => 'App\\Notifications\\StatusChangedBooking',
                'status' => 'done',
            ];
            $data = $notification;
            $data['bookingId'] = $booking->id;
            foreach($business->users as $owner){
                notify($data , $owner->id , trans('lang.Notifications_NewBooking'));
            }
            

        } catch (ValidationException $e) {
            return $this->sendError(array_values($e->errors()));
        } catch (ValidatorException | ModelNotFoundException | Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($booking->toArray(), __('lang.saved_successfully', ['operator' => __('lang.booking')]));
    }

    /**
     * Update the specified Booking in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $oldBooking = $this->bookingRepository->findWithoutFail($id);
        if (empty($oldBooking)) {
            return $this->sendError('Booking not found');
        }
        $input = $request->all();
        try {
            if (isset($input['cancel']) && $input['cancel'] == '1') {
                $input['payment_status_id'] = 3;
                $input['transaction_status_id'] = 7;
            }
            $booking = $this->bookingRepository->update($input, $id);
            if (isset($input['payment_status_id'])) {
                $this->paymentRepository->update(
                    ['payment_status_id' => $input['payment_status_id']],
                    $booking->payment_id
                );
                event(new BookingChangedEvent($booking));
            }
            if (isset($input['transaction_status_id']) && $input['transaction_status_id'] != $oldBooking->transaction_status_id) {
                event(new TransactionStatusChangedEvent($booking));
                $notification = [
                    'title' => trans('lang.notification_status_changed_booking'),
                    'body' => trans('lang.notification_your_booking', ['booking_id' => $booking->id, 'transaction_status' => $booking->transactionStatus->status]),
                    'icon' => $booking->business->hasMedia('image')?$booking->business->getFirstMediaUrl('image', 'thumb'):asset('images/image_default.png'),
                    'click_action' => "FLUTTER_NOTIFICATION_CLICK",
                    'id' => 'App\\Notifications\\StatusChangedBooking',
                    'status' => 'done',
                ];
                $data = $notification;
                $data['bookingId'] = $booking->id;
                notify($data , $booking->user_id , trans('lang.notification_status_changed_booking'));
            }

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($booking->toArray(), __('lang.saved_successfully', ['operator' => __('lang.booking')]));
    }

    public function createClassBooking(CreateBooking $request)
    {
        try {
            $date = (new Carbon($request->date));
            $business = $this->businessRepository->where('id', $request->business_id)->first();
            if (!$business) {
                return $this->sendError('Invalid business id!');
            }

            $article = $this->articleRepository->where('id', $request->article_id)->first();
            if (!$article) {
                return $this->sendError('Invalid article id!');
            }

            $schedule = $this->articleScheduleRepository->where([
                    ['id', $request->schedule_id],
                    ['article_id', $request->article_id]
                ])
                ->whereRaw('"' . $date->format('Y-m-d') . '" BETWEEN start_date AND end_date
                and (article_schedule.repeat="Never" or json_unquote(json_extract(`days`, \'$."' . $date->dayName . '"\')) = 1)')
                ->first();

            if (!$schedule) {
                return $this->sendError('Invalid Schedule id');
            }

            $requestDateTime = (new Carbon($date->format('Y-m-d') . ' ' . $schedule->start_time))->format('Y-m-d H:i:s');
            $scheduleBookings = $this->bookingRepository->where('article_schedule_id', $schedule->id)
                ->whereRaw('"' . $requestDateTime . '" BETWEEN start_at AND ends_at')
                ->get();

            if ($article->max_appoiintments != 0 && $scheduleBookings->count() >= $article->max_appoiintments) {
                return $this->sendError('Class reached its max limit!');
            }

            if ($request->plan_id) {
                $plan = Plan::with('plans_article')->where('id', $request->plan_id)->first();

                if (!$plan) {
                    return $this->sendError('Invalid plan id!');
                }

                if (!$plan->plans_article->where('id', $article->id)->first()) {
                    return $this->sendError('This plan does not have this article!');
                }
            }

            if ($request->payment_id) {
                $payment = $this->paymentRepository->with('paymentStatus')->where('id', $request->payment_id)->first();
                if (!$payment) {
                    return $this->sendError('Invalid payment id!');
                }

                if ($payment->paymentStatus->id != 2) {
                    return $this->sendError('Invalid payment status!');
                }
            }
            
            if (isset($plan)) {
                $plan_id = $plan->id;
                $payment_id = null;
            }
            elseif (isset($payment)) {
                $plan_id = null;
                $payment_id = $payment->id;
            }

            $data = [
                'business' => $business,
                'article' => collect([$article]),
                'quantity' => $request->quantity,
                'user_id' => auth()->id(),
                'employee_id' => null,
                'transaction_status_id' => $this->TransactionStatusRepository->find(1)->id,
                'address' => null,
                'article_schedule_id' => $schedule->id,
                'plan_id' => $plan_id,
                'payment_id' => $payment_id,
                'coupon' => null,
                'taxes' => $business->taxes,
                'booking_at' => now()->toDateTimeString(),
                'start_at' => $requestDateTime,
                'ends_at' => (new Carbon($date->format('Y-m-d') . ' ' . $schedule->end_time))->format('Y-m-d H:i:s'),
                'hint' => null,
                'cancel' => null,
            ];

            $booking = $this->bookingRepository->create($data);
        } catch (\Throwable $th) {
            throw $th;
            return $this->sendError($th->getMessage());
        }

        return $this->sendResponse($booking->toArray(), __('lang.saved_successfully', ['operator' => __('lang.booking')]));
    }

}
