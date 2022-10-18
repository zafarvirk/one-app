<?php
/*
 * File name: api.php
 * Last modified: 2022.03.10 at 18:43:42
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('image-upload', 'API\ImageUploadController@imageUploadPost')->name('image.upload.post');
Route::prefix('salon_owner')->group(function () {
    Route::post('login', 'API\SalonOwner\UserAPIController@login');
    Route::post('register', 'API\SalonOwner\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\SalonOwner\UserAPIController@user');
    Route::get('logout', 'API\SalonOwner\UserAPIController@logout');
    Route::get('settings', 'API\SalonOwner\UserAPIController@settings');
    Route::get('translations', 'API\TranslationAPIController@translations');
    Route::get('supported_locales', 'API\TranslationAPIController@supportedLocales');
});
Route::middleware('auth:api')->group(function () {
    Route::group(['middleware' => ['role:driver']], function () {
        Route::prefix('driver')->group(function () {
            Route::resource('orders', 'API\OrderAPIController');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::post('users/{id}', 'API\UserAPIController@update');
            Route::resource('faq_categories', 'API\FaqCategoryAPIController');
            Route::resource('faqs', 'API\FaqAPIController');
        });
    });
});

Route::post('login', 'API\Driver\UserAPIController@login');

Route::post('login', 'API\UserAPIController@login');
Route::post('register', 'API\UserAPIController@register');
Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
Route::get('user', 'API\UserAPIController@user');
Route::get('logout', 'API\UserAPIController@logout');
Route::get('settings', 'API\UserAPIController@settings');
Route::get('translations', 'API\TranslationAPIController@translations');
Route::get('supported_locales', 'API\TranslationAPIController@supportedLocales');
Route::post('isUserExist', 'API\UserAPIController@isUserExist');


Route::resource('business_category', 'API\BusinessCategoryAPIController');
Route::resource('businesses', 'API\BusinessAPIController')->only([
    'index' , 'show'
]);
Route::resource('availability_hours', 'API\AvailabilityHourAPIController');
Route::resource('awards', 'API\AwardAPIController');
Route::resource('experiences', 'API\ExperienceAPIController');

Route::resource('faq_categories', 'API\FaqCategoryAPIController');
Route::resource('faqs', 'API\FaqAPIController');
Route::resource('custom_pages', 'API\CustomPageAPIController');

Route::resource('article_categories', 'API\ArticleCategoryAPIController');

Route::resource('modules', 'API\ModuleAPIController');

Route::resource('articles', 'API\ArticleAPIController');
Route::resource('galleries', 'API\GalleryAPIController');
Route::get('business_reviews/{id}', 'API\BusinessReviewAPIController@show')->name('business_reviews.show');
Route::get('business_reviews', 'API\BusinessReviewAPIController@index')->name('business_reviews.index');

Route::resource('currencies', 'API\CurrencyAPIController');
Route::resource('slides', 'API\SlideAPIController')->except([
    'show'
]);
Route::resource('transaction_statuses', 'API\TransactionStatusAPIController')->except([
    'show'
]);
Route::resource('option_groups', 'API\OptionGroupAPIController');
Route::resource('options', 'API\OptionAPIController');
Route::resource('plans', 'API\PlanAPIController');

Route::middleware('auth:api')->group(function () {
    Route::group(['middleware' => ['role:salon owner']], function () {
        Route::prefix('salon_owner')->group(function () {
            Route::post('users/{user}', 'API\UserAPIController@update');
            Route::get('dashboard', 'API\DashboardAPIController@provider');
            Route::resource('businesses', 'API\SalonOwner\BusinessAPIController');
            Route::resource('business_aminities', 'API\SalonOwner\BusinessAminitiesApiController');
            Route::get('getAminities', 'API\SalonOwner\BusinessAminitiesApiController@getBusinessAminities');
            Route::post('assignAminities', 'API\SalonOwner\BusinessAminitiesApiController@assignAminitiesToBusiness');
            Route::resource('business_features', 'API\SalonOwner\BusinessFeatureApiController');
            Route::post('businesses/modules/{id}', 'API\SalonOwner\BusinessAPIController@modulesUpdate');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::get('business_reviews', 'API\BusinessReviewAPIController@index')->name('business_reviews.index');
            Route::get('articles', 'API\ArticleAPIController@index')->name('e_services.index');
            Route::put('payments/{id}', 'API\PaymentAPIController@update')->name('payments.update');
            Route::get('orders', 'API\OrderAPIController@index');
            Route::get('bookings', 'API\BookingAPIController@index');
            Route::get('requests', 'API\ArticleRequestApiController@index');
            Route::get('ownerGalleries', 'API\GalleryAPIController@ownerGalleries');
        });
    });
    Route::post('uploads/store', 'API\UploadAPIController@store');
    Route::post('uploads/clear', 'API\UploadAPIController@clear'); 
    Route::post('users/{user}', 'API\UserAPIController@update');

    Route::get('payments/byMonth', 'API\PaymentAPIController@byMonth')->name('payments.byMonth');
    Route::post('payments/wallets/{id}', 'API\PaymentAPIController@wallets')->name('payments.wallets');
    Route::post('payments/cash', 'API\PaymentAPIController@cash')->name('payments.cash');
    Route::resource('payment_methods', 'API\PaymentMethodAPIController')->only([
        'index'
    ]);
    Route::post('business_reviews', 'API\BusinessReviewAPIController@store')->name('business_reviews.store');


    Route::resource('favorites', 'API\FavoriteAPIController');
    Route::resource('addresses', 'API\AddressAPIController');

    Route::get('notifications/count', 'API\NotificationAPIController@count');
    Route::resource('notifications', 'API\NotificationAPIController');
    Route::resource('bookings', 'API\BookingAPIController');

    Route::resource('earnings', 'API\EarningAPIController');

    Route::resource('business_payouts', 'API\BusinessPayoutAPIController');

    Route::resource('coupons', 'API\CouponAPIController')->except([
        'show'
    ]);
    Route::resource('wallets', 'API\WalletAPIController')->except([
        'show', 'create', 'edit'
    ]);
    Route::get('wallet_transactions', 'API\WalletTransactionAPIController@index')->name('wallet_transactions.index');

    Route::resource('orders', 'API\OrderAPIController');

    Route::resource('article_orders', 'API\ArticleOrderAPIController');

    Route::get('carts/count', 'API\CartAPIController@count')->name('carts.count');
    Route::resource('carts', 'API\CartAPIController');

    Route::resource('drivers', 'API\DriverAPIController');

    Route::resource('driversPayouts', 'API\DriversPayoutAPIController');

    Route::resource('posts', 'API\PostAPIController');
    Route::resource('postComments', 'API\PostCommentAPIController');
    Route::resource('postReactions', 'API\PostReactionAPIController');

    Route::resource('requests', 'API\ArticleRequestApiController');

    Route::resource('request_offer', 'API\RequestOfferAPIController');
    Route::post('accept_offer', 'API\RequestOfferAPIController@acceptOffer');

    Route::resource('subscription', 'API\SubscriptionAPIController');

    Route::get('get-business-articles-with-schedules', 'API\ArticleScheduleController@getBusinessArticleSchedule');
    Route::get('get-article-schedules/{article_id}', 'API\ArticleScheduleController@getArticleSchedules');
    Route::post('create-class-booking', 'API\BookingAPIController@createClassBooking');
});
