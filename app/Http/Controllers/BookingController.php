<?php
/*
 * File name: BookingController.php
 * Last modified: 2022.02.16 at 21:07:14
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Http\Controllers;

use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\Criteria\Bookings\BookingsOfUserCriteria;
use App\DataTables\BookingDataTable;
use App\Events\BookingChangedEvent;
use App\Events\TransactionStatusChangedEvent;
use App\Http\Requests\UpdateBookingRequest;
use App\Notifications\StatusChangedBooking;
use App\Repositories\AddressRepository;
use App\Repositories\BookingRepository;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OptionRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentStatusRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class BookingController extends Controller
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
    private $transactionStatusRepository;
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

    public function __construct(BookingRepository $bookingRepo, CustomFieldRepository $customFieldRepo, UserRepository $userRepo
        , TransactionStatusRepository                 $transactionStatusRepo, NotificationRepository $notificationRepo, PaymentRepository $paymentRepo, AddressRepository $addressRepository, TaxRepository $taxRepository, ArticleRepository $articleRepository, BusinessRepository $businessRepository, CouponRepository $couponRepository, OptionRepository $optionRepository, PaymentStatusRepository $paymentStatusRepository)
    {
        parent::__construct();
        $this->bookingRepository = $bookingRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->transactionStatusRepository = $transactionStatusRepo;
        $this->notificationRepository = $notificationRepo;
        $this->paymentRepository = $paymentRepo;
        $this->addressRepository = $addressRepository;
        $this->taxRepository = $taxRepository;
        $this->articleRepository = $articleRepository;
        $this->businessRepository = $businessRepository;
        $this->couponRepository = $couponRepository;
        $this->optionRepository = $optionRepository;
        $this->paymentStatusRepository = $paymentStatusRepository;
    }

    /**
     * Display a listing of the Booking.
     *
     * @param BookingDataTable $bookingDataTable
     * @return Response
     */
    public function index(BookingDataTable $bookingDataTable)
    {
        return $bookingDataTable->render('bookings.index');
    }

    /**
     * Display the specified Booking.
     *
     * @param int $id
     *
     * @return Application|Factory|Response|View
     * @throws RepositoryException
     */
    public function show(int $id)
    {
        $this->bookingRepository->pushCriteria(new BookingsOfUserCriteria(auth()->id()));
        $booking = $this->bookingRepository->findWithoutFail($id);
        if (empty($booking)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.booking')]));
            return redirect(route('bookings.index'));
        }
        $transactionStatuses = $this->transactionStatusRepository->orderBy('order')->all();
        return view('bookings.show')->with('booking', $booking)->with('transactionStatuses', $transactionStatuses);
    }

    /**
     * Show the form for editing the specified Booking.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     * @throws RepositoryException
     */
    public function edit(int $id)
    {
        $this->bookingRepository->pushCriteria(new BookingsOfUserCriteria(auth()->id()));
        $booking = $this->bookingRepository->findWithoutFail($id);
        if (empty($booking)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.booking')]));
            return redirect(route('bookings.index'));
        }
        array_push($booking->fillable, ['address_id', 'payment_status_id']);
        $booking->address_id = $booking->address->id;
        $transactionStatus = $this->transactionStatusRepository->orderBy('order')->pluck('status', 'id');
        if (!empty($booking->payment_id)) {
            $booking->payment_status_id = $booking->payment->payment_status_id;
            $paymentStatuses = $this->paymentStatusRepository->pluck('status', 'id');
        } else {
            $paymentStatuses = null;
        }
        $addresses = $this->addressRepository->getByCriteria(new AddressesOfUserCriteria($booking->user_id))->pluck('address', 'id');

        $customFieldsValues = $booking->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->bookingRepository->model());
        $hasCustomField = in_array($this->bookingRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('bookings.edit')->with('booking', $booking)->with("customFields", isset($html) ? $html : false)->with("transactionStatus", $transactionStatus)->with("addresses", $addresses)->with("paymentStatuses", $paymentStatuses);
    }

    /**
     * Update the specified Booking in storage.
     *
     * @param int $id
     * @param UpdateBookingRequest $request
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function update(int $id, UpdateBookingRequest $request)
    {
        $oldBooking = $this->bookingRepository->findWithoutFail($id);
        if (empty($oldBooking)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.booking')]));
            return redirect(route('bookings.index'));
        }
        $input = $request->all();
        $address = $this->addressRepository->findWithoutFail($input['address_id']);
        $input['address'] = $address;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->bookingRepository->model());
        try {
            if (isset($input['cancel']) && $input['cancel'] == '1') {
                $input['payment_status_id'] = 3; // failed
                $input['booking_status_id'] = 7; // failed
            }
            $booking = $this->bookingRepository->update($input, $id);
            if (isset($input['payment_status_id'])) {
                $this->paymentRepository->update(
                    ['payment_status_id' => $input['payment_status_id']],
                    $booking->payment_id
                );
                event(new BookingChangedEvent($booking));
            }
            if (isset($input['booking_status_id']) && $input['booking_status_id'] != $oldBooking->booking_status_id) {
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

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $booking->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }
        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.booking')]));
        return redirect(route('bookings.index'));
    }

    /**
     * Remove the specified Booking from storage.
     *
     * @param int $id
     *
     * @return Application|RedirectResponse|Redirector|Response
     */
    public function destroy($id)
    {
        if (!config('installer.demo_app')) {
            $this->bookingRepository->pushCriteria(new BookingsOfUserCriteria(auth()->id()));
            $booking = $this->bookingRepository->findWithoutFail($id);

            if (empty($booking)) {
                Flash::error(__('lang.not_found', ['operator' => __('lang.booking')]));

                return redirect(route('bookings.index'));
            }

            $this->bookingRepository->delete($id);

            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.booking')]));

        } else {
            Flash::warning('This is only demo app you can\'t change this section ');
        }
        return redirect(route('bookings.index'));
    }

}
