<?php


namespace App\Http\Controllers\API;

use App\Models\Article;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\GetBusinessArticleSchedule;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use App\Repositories\AddressRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleScheduleRepository;
use App\Repositories\BookingRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OptionRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentStatusRepository;
use App\Repositories\TaxRepository;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ArticleScheduleController extends Controller
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

    public function __construct(BookingRepository $bookingRepo, CustomFieldRepository $customFieldRepo, UserRepository $userRepo, TransactionStatusRepository $TransactionStatusRepo, NotificationRepository $notificationRepo, PaymentRepository $paymentRepo, AddressRepository $addressRepository, TaxRepository $taxRepository, ArticleRepository $articleRepository, BusinessRepository $businessRepository, CouponRepository $couponRepository, OptionRepository $optionRepository, PaymentStatusRepository $paymentStatusRepository, ArticleScheduleRepository $articleScheduleRepository)
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

    public function getBusinessArticleSchedule(GetBusinessArticleSchedule $request)
    {
        $user = auth()->user();
        $date = (new Carbon($request->date));
        $articles = DB::table('article')
            ->leftJoin('article_schedule', 'article.id', '=', 'article_schedule.article_id')
            ->where('article.business_id', $request->business_id)
            ->whereRaw('"'.$date->format('Y-m-d').'" BETWEEN start_date AND end_date
                and (article_schedule.repeat="Never" or json_unquote(json_extract(`days`, \'$."'.$date->dayName .'"\')) = 1)')
            ->select(
                'article.*',
                'article_schedule.id as schedule_id',
                'article_schedule.article_id',
                'article_schedule.start_date',
                'article_schedule.end_date',
                'article_schedule.start_time',
                'article_schedule.end_time',
                'article_schedule.duration',
                'article_schedule.repeat',
                'article_schedule.days',
                'article_schedule.recurrence_rules'
            )
            ->get();

        $userPlanIds = Subscription::where('user_id', $user->id)->where('is_active', 1)->where('available_sessions', '>', 0)->whereRaw("DATE(expiry_date) >= '".date('Y-m-d')."'")->pluck('plan_id')->toArray();

        foreach ($articles as $article) {
            $articleWithStaffAndPlans = Article::with('article_staff', 'plans')->where('id', $article->id)->first();
            $users = $articleWithStaffAndPlans->article_staff->map(function ($user) {
                return collect($user->toArray())
                    ->only(['id', 'name', 'email'])
                    ->all();
            });

            $user_plan_id = null;
            if (count($userPlanIds)) {
                $articlePlanIds = $articleWithStaffAndPlans->plans->pluck('id')->toArray();
                $useablePlanIds = array_intersect($userPlanIds, $articlePlanIds);
                $user_plan_id = count($useablePlanIds) ? $useablePlanIds[0] : null;
            }

            $article->name = json_decode($article->name);
            $article->description = json_decode($article->description);
            $article->days = json_decode($article->days);
            $article->article_staff = $users;
            $article->user_plan_id = $user_plan_id;
            $article->total_bookings = rand(0, 10);
        }

        if (!$articles->count()) {
            return $this->sendError('No article found!');
        }

        return $this->sendResponse($articles, 'Articles found!');
    }

    public function getArticleSchedules($article_id)
    {
        $article = Article::with('schedule')->where('id', $article_id)->first();

        if (!$article->schedule->count()) {
            return $this->sendError('No schedule found!');
        }

        return $this->sendResponse($article->schedule, 'Schedules found!');
    }
}
