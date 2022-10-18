<?php
/*
 * File name: web.php
 * Last modified: 2022.02.02 at 23:01:35
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'role:admin'])->group(function () {
    if (!defined('STDIN')) {
        define('STDIN', fopen('php://stdin', 'r'));
    }

    /*
    *   URL Artisan Command
    *   
    *   use @ for space
    *
    *   Example usage, parameters with value:
    *   /artisan/make:factory@CycleFactory@--model=Cycle
    *   php artisan make:factory CycleFactory --model=Cycle
    *
    *   Example usage, parameters without value:
    *   /artisan/make:model@Cycle@--migration@--controller@--resource
    *   php artisan make:model Cycle --migration --controller --resource
    */
    
    Route::get('/artisan/{command}', function(Request $request) {
        if (env('APP_ENV') == 'production') {
            die("This command could not run in production environment!");
        }

        $query = explode('@', $request->command);
        $command = $query[0];
        $parameters = array_slice($query, 1);
    
        $optionsArray = [];
        foreach ($parameters as $index => $param) {
            if ($index == 0 && substr($param, 0, 1) != "-") {
                $param = explode('=', $param);
                if (count($param) > 1) {
                    $key = $param[0];
                    $value = $param[1];
                }else {
                    $key = 'name';
                    $value = $param[0];
                }
            }
            else {
                $param = explode('=', $param);
                $key = $param[0];
        
                if (count($param) > 1) {
                    $value = $param[1];
                }else {
                    $value = true;
                }
            }
    
            $optionsArray[$key] = $value;
        }
    
        try {
            Artisan::call($command, $optionsArray);
            echo Artisan::output();
        } catch (\Throwable $th) {
            $error = $th->getMessage(). ' on line ' .$th->getLine(). ' in file ' .$th->getFile();
            Log::error($error);
            echo $error;
        }
    });

    Route::get('clear-app', function(){
        try {
            Artisan::call('optimize:clear');
            echo Artisan::output();
        } catch (\Throwable $th) {
            $error = $th->getMessage(). ' on line ' .$th->getLine(). ' in file ' .$th->getFile();
            Log::error($error);
            echo $error;
        }
    });

    Route::get('run-migrations', function(){
        try {
            Artisan::call('migrate', [
                '--force' => true,
            ]);
            echo Artisan::output();
        } catch (\Throwable $th) {
            $error = $th->getMessage(). ' on line ' .$th->getLine(). ' in file ' .$th->getFile();
            Log::error($error);
            echo $error;
        }
    });
    // Route::get('reservation', function(){
    //     try {
    //         event(new App\Events\ResevationEarningEvent(15));
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //     }
    // });
    Route::get('run-seeder/{seeder_file_name}', function($seeder_file_name){
        if(!file_exists(database_path('/seeds/'.$seeder_file_name.'.php'))) {
            echo "No \"$seeder_file_name\" file exists. <br>make sure file exists and not to use extention \".php\" with the file name.";
            die;
        }

        try {
            Artisan::call('db:seed', [
                '--class' => $seeder_file_name,
                '--force' => true,
            ]);
            echo Artisan::output();
        } catch (\Throwable $th) {
            $error = $th->getMessage(). ' on line ' .$th->getLine(). ' in file ' .$th->getFile();
            Log::error($error);
            echo $error;
        }
    });
});

Route::get('login/{service}', 'Auth\LoginController@redirectToProvider');
Route::get('login/{service}/callback', 'Auth\LoginController@handleProviderCallback');
Auth::routes();

Route::get('payments/failed', 'PayPalController@index')->name('payments.failed');
Route::get('payments/razorpay/checkout', 'RazorPayController@checkout');
Route::post('payments/razorpay/pay-success/{bookingId}', 'RazorPayController@paySuccess');
Route::get('payments/razorpay', 'RazorPayController@index');

Route::get('payments/stripe/checkout', 'StripeController@checkout');
Route::get('payments/stripe/pay-success/{bookingId}/{paymentMethodId}', 'StripeController@paySuccess');
Route::get('payments/stripe', 'StripeController@index');

Route::get('payments/paymongo/checkout', 'PayMongoController@checkout');
Route::get('payments/paymongo/processing/{bookingId}/{paymentMethodId}', 'PayMongoController@processing');
Route::get('payments/paymongo/success/{bookingId}/{paymentIntentId}', 'PayMongoController@success');
Route::get('payments/paymongo', 'PayMongoController@index');

Route::get('payments/stripe-fpx/checkout', 'StripeFPXController@checkout');
Route::get('payments/stripe-fpx/pay-success/{bookingId}', 'StripeFPXController@paySuccess');
Route::get('payments/stripe-fpx', 'StripeFPXController@index');

Route::get('payments/flutterwave/checkout', 'FlutterWaveController@checkout');
Route::get('payments/flutterwave/pay-success/{bookingId}/{transactionId}', 'FlutterWaveController@paySuccess');
Route::get('payments/flutterwave', 'FlutterWaveController@index');

Route::get('payments/paystack/checkout', 'PayStackController@checkout');
Route::get('payments/paystack/pay-success/{bookingId}/{reference}', 'PayStackController@paySuccess');
Route::get('payments/paystack', 'PayStackController@index');

Route::get('payments/paypal/express-checkout', 'PayPalController@getExpressCheckout')->name('paypal.express-checkout');
Route::get('payments/paypal/express-checkout-success', 'PayPalController@getExpressCheckoutSuccess');
Route::get('payments/paypal', 'PayPalController@index')->name('paypal.index');

Route::get('firebase/sw-js', 'AppSettingController@initFirebase');


Route::get('storage/app/public/{id}/{conversion}/{filename?}', 'UploadController@storage');
Route::middleware('auth')->group(function () {
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::post('uploads/store', 'UploadController@store')->name('medias.create');
    Route::get('users/profile', 'UserController@profile')->name('users.profile');
    Route::post('users/remove-media', 'UserController@removeMedia');
    Route::resource('users', 'UserController');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('classManager/dashboard', 'DashboardController@classManager')->name('classManager.dashboard');

    Route::group(['middleware' => ['permission:medias']], function () {
        Route::get('uploads/all/{collection?}', 'UploadController@all');
        Route::get('uploads/collectionsNames', 'UploadController@collectionsNames');
        Route::post('uploads/clear', 'UploadController@clear')->name('medias.delete');
        Route::get('medias', 'UploadController@index')->name('medias');
        Route::get('uploads/clear-all', 'UploadController@clearAll');
    });

    Route::group(['middleware' => ['permission:permissions.index']], function () {
        Route::get('permissions/role-has-permission', 'PermissionController@roleHasPermission');
        Route::get('permissions/refresh-permissions', 'PermissionController@refreshPermissions');
    });
    Route::group(['middleware' => ['permission:permissions.index']], function () {
        Route::post('permissions/give-permission-to-role', 'PermissionController@givePermissionToRole');
        Route::post('permissions/revoke-permission-to-role', 'PermissionController@revokePermissionToRole');
    });

    Route::group(['middleware' => ['permission:app-settings']], function () {
        Route::prefix('settings')->group(function () {
            Route::resource('permissions', 'PermissionController');
            Route::resource('roles', 'RoleController');
            Route::resource('customFields', 'CustomFieldController');
            Route::resource('currencies', 'CurrencyController')->except([
                'show'
            ]);
            Route::resource('taxes', 'TaxController')->except([
                'show'
            ]);
            Route::get('users/login-as-user/{id}', 'UserController@loginAsUser')->name('users.login-as-user');
            Route::patch('update', 'AppSettingController@update');
            Route::patch('translate', 'AppSettingController@translate');
            Route::get('sync-translation', 'AppSettingController@syncTranslation');
            Route::get('clear-cache', 'AppSettingController@clearCache');
            Route::get('check-update', 'AppSettingController@checkForUpdates');
            // disable special character and number in route params
            Route::get('/{type?}/{tab?}', 'AppSettingController@index')
                ->where('type', '[A-Za-z]*')->where('tab', '[A-Za-z]*')->name('app-settings');
        });
    });
    Route::post('businessCategories/remove-media', 'BusinessCategoryController@removeMedia');
    Route::resource('businessCategories', 'BusinessCategoryController')->except([
        'show'
    ]);
    Route::post('salons/remove-media', 'SalonController@removeMedia');
    Route::resource('salons', 'SalonController')->except([
        'show'
    ]);

    Route::get('requestedSalons', 'SalonController@requestedSalons')->name('requestedSalons.index');
    // Start market routes
    Route::post('markets/remove-media', 'MarketController@removeMedia');
    Route::get('requestedMarkets', 'MarketController@requestedMarkets')->name('requestedMarkets.index'); //adeed
    Route::resource('markets', 'MarketController')->except([
        'show'
    ]);
    // End market routes
    
    // Start gym routes
    Route::post('gyms/remove-media', 'GymController@removeMedia');
    Route::resource('gyms', 'GymController')->except([
        'show'
    ]);
    // End gym routes

    Route::resource('addresses', 'AddressController')->except([
        'show'
    ]);
    // Aminities Route
    Route::post('aminities/remove-media', 'AminitiesController@removeMedia');
    Route::resource('aminities','AminitiesController');
    // Highlight Route
    Route::post('highlight/remove-media', 'HighlightController@removeMedia');
    Route::resource('highlights','HighlightController');
    // Feature Route
    Route::post('features/remove-media', 'FeatureController@removeMedia');
    Route::resource('features','FeatureController');
    // Plan Route
    Route::resource('plans','PlanController');
    // Modules Route
    Route::resource('modules','ModuleController');
    // Award Route
    Route::resource('awards', 'AwardController');
    Route::resource('experiences', 'ExperienceController');

    Route::resource('availabilityHours', 'AvailabilityHourController')->except([
        'show'
    ]);
    Route::post('article/remove-media', 'ArticleController@removeMedia');
    Route::resource('article', 'ArticleController')->except([
        'show'
    ]);

    Route::post('products/remove-media', 'ProductController@removeMedia');
    Route::resource('products', 'ProductController')->except([
        'show'
    ]);

    Route::post('class_article/remove-media', 'ClassArticleController@removeMedia');
    Route::resource('class_article', 'ClassArticleController')->except([
        'show'
    ]);

    Route::resource('article_schedule', 'ArticleScheduleController');

    Route::resource('faqCategories', 'FaqCategoryController')->except([
        'show'
    ]);
    Route::post('article_categories/remove-media', 'ArticleCategoryController@removeMedia');
    Route::resource('article_categories', 'ArticleCategoryController')->except([
        'show'
    ]);
    Route::resource('transactionStatuses', 'TransactionStatusController')->except([
        'show',
    ]);
    Route::post('galleries/remove-media', 'GalleryController@removeMedia');
    Route::resource('galleries', 'GalleryController')->except([
        'show'
    ]);


    Route::resource('businessReviews', 'BusinessReviewController')->except([
        'show'
    ]);
    Route::resource('payments', 'PaymentController')->except([
        'create', 'store', 'edit', 'update', 'destroy'
    ]);
    Route::post('paymentMethods/remove-media', 'PaymentMethodController@removeMedia');
    Route::resource('paymentMethods', 'PaymentMethodController')->except([
        'show'
    ]);
    Route::resource('paymentStatuses', 'PaymentStatusController')->except([
        'show'
    ]);
    Route::resource('faqs', 'FaqController')->except([
        'show'
    ]);
    Route::resource('favorites', 'FavoriteController')->except([
        'show'
    ]);
    Route::resource('notifications', 'NotificationController')->except([
        'create', 'store', 'update', 'edit',
    ]);
    Route::resource('bookings', 'BookingController');

    Route::resource('orders', 'OrderController');

    Route::resource('earnings', 'EarningController')->except([
        'show', 'edit', 'update'
    ]);

    Route::get('salonPayouts/create/{id}', 'BusinessPayoutController@create')->name('salonPayouts.create');
    Route::resource('salonPayouts', 'BusinessPayoutController')->except([
        'show', 'edit', 'update', 'create'
    ]);
    Route::resource('optionGroups', 'OptionGroupController')->except([
        'show'
    ]);
    Route::post('options/remove-media', 'OptionController@removeMedia');
    Route::resource('options', 'OptionController')->except([
        'show'
    ]);
    Route::resource('coupons', 'CouponController')->except([
        'show'
    ]);
    Route::post('slides/remove-media', 'SlideController@removeMedia');
    Route::resource('slides', 'SlideController')->except([
        'show'
    ]);
    Route::resource('customPages', 'CustomPageController');

    Route::resource('wallets', 'WalletController')->except([
        'show'
    ]);
    Route::resource('walletTransactions', 'WalletTransactionController')->except([
        'show', 'edit', 'update', 'destroy'
    ]);
    Route::post('requests/remove-media', 'ArticleRequestController@removeMedia');
    Route::resource('requests', 'ArticleRequestController');
    Route::get('businesses/{business_id}/users', 'BusinessController@getBusinessUsers');
});
