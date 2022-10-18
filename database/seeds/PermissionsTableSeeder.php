<?php
/*
 * File name: PermissionsTableSeeder.php
 * Last modified: 2022.02.15 at 16:47:17
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsTableSeeder extends Seeder
{

    private $exceptNames = [];

    private $exceptControllers = [
        'LoginController',
        'ForgotPasswordController',
        'ResetPasswordController',
        'RegisterController',
        'PayPalController'
    ];


    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        try{
            $role = Role::findByName('admin');
            if (!$role) {
                throw new RoleDoesNotExist();
            }

            $permissions = Permission::all();

            $projectInitialPermissions =  array(
                "dashboard",
                "medias.create",
                "users.profile",
                "users.index",
                "users.create",
                "users.store",
                "users.show",
                "users.edit",
                "users.update",
                "users.destroy",
                "medias.delete",
                "medias",
                "permissions.index",
                "permissions.create",
                "permissions.store",
                "permissions.show",
                "permissions.edit",
                "permissions.update",
                "permissions.destroy",
                "roles.index",
                "roles.create",
                "roles.store",
                "roles.show",
                "roles.edit",
                "roles.update",
                "roles.destroy",
                "customFields.index",
                "customFields.create",
                "customFields.store",
                "customFields.show",
                "customFields.edit",
                "customFields.update",
                "customFields.destroy",
                "currencies.index",
                "currencies.create",
                "currencies.store",
                "currencies.edit",
                "currencies.update",
                "currencies.destroy",
                "users.login-as-user",
                "app-settings",
                "faqCategories.index",
                "faqCategories.create",
                "faqCategories.store",
                "faqCategories.edit",
                "faqCategories.update",
                "faqCategories.destroy",
                "faqs.index",
                "faqs.create",
                "faqs.store",
                "faqs.edit",
                "faqs.update",
                "faqs.destroy",
                "payments.index",
                "payments.show",
                "payments.update",
                "paymentMethods.index",
                "paymentMethods.create",
                "paymentMethods.store",
                "paymentMethods.edit",
                "paymentMethods.update",
                "paymentMethods.destroy",
                "paymentStatuses.index",
                "paymentStatuses.create",
                "paymentStatuses.store",
                "paymentStatuses.edit",
                "paymentStatuses.update",
                "paymentStatuses.destroy",
                "verification.notice",
                "verification.verify",
                "verification.resend",
                "awards.index",
                "awards.create",
                "awards.store",
                "awards.show",
                "awards.edit",
                "awards.update",
                "awards.destroy",
                "experiences.index",
                "experiences.create",
                "experiences.store",
                "experiences.show",
                "experiences.edit",
                "experiences.update",
                "experiences.destroy",
                "businessCategories.index",
                "businessCategories.create",
                "businessCategories.store",
                "businessCategories.edit",
                "businessCategories.update",
                "businessCategories.destroy",
                "salons.index",
                "salons.create",
                "salons.store",
                "salons.edit",
                "salons.update",
                "salons.destroy",
                "addresses.index",
                "addresses.create",
                "addresses.store",
                "addresses.edit",
                "addresses.update",
                "addresses.destroy",
                "taxes.index",
                "taxes.create",
                "taxes.store",
                "taxes.edit",
                "taxes.update",
                "taxes.destroy",
                "availabilityHours.index",
                "availabilityHours.create",
                "availabilityHours.store",
                "availabilityHours.edit",
                "availabilityHours.update",
                "availabilityHours.destroy",
                "article.index",
                "article.create",
                "article.store",
                "article.edit",
                "article.update",
                "article.destroy",
                "article_categories.index",
                "article_categories.create",
                "article_categories.store",
                "article_categories.edit",
                "article_categories.update",
                "article_categories.destroy",
                "optionGroups.index",
                "optionGroups.create",
                "optionGroups.store",
                "optionGroups.edit",
                "optionGroups.update",
                "optionGroups.destroy",
                "options.index",
                "options.create",
                "options.store",
                "options.edit",
                "options.update",
                "options.destroy",
                "favorites.index",
                "favorites.create",
                "favorites.store",
                "favorites.edit",
                "favorites.update",
                "favorites.destroy",
                "businessReviews.index",
                "businessReviews.create",
                "businessReviews.store",
                "businessReviews.edit",
                "businessReviews.update",
                "businessReviews.destroy",
                "galleries.index",
                "galleries.create",
                "galleries.store",
                "galleries.edit",
                "galleries.update",
                "galleries.destroy",
                "requestedSalons.index",
                "slides.index",
                "slides.create",
                "slides.store",
                "slides.edit",
                "slides.update",
                "slides.destroy",
                "notifications.index",
                "notifications.show",
                "notifications.destroy",
                "coupons.index",
                "coupons.create",
                "coupons.store",
                "coupons.edit",
                "coupons.update",
                "coupons.destroy",
                "transactionStatuses.index",
                "transactionStatuses.create",
                "transactionStatuses.store",
                "transactionStatuses.edit",
                "transactionStatuses.update",
                "transactionStatuses.destroy",
                "bookings.index",
                "bookings.create",
                "bookings.store",
                "bookings.show",
                "bookings.edit",
                "bookings.update",
                "bookings.destroy",
                "salonPayouts.index",
                "salonPayouts.create",
                "salonPayouts.store",
                "salonPayouts.destroy",
                "earnings.index",
                "earnings.create",
                "earnings.store",
                "earnings.destroy",
                "customPages.index",
                "customPages.create",
                "customPages.store",
                "customPages.show",
                "customPages.edit",
                "customPages.update",
                "customPages.destroy",
                "wallets.index",
                "wallets.create",
                "wallets.store",
                "wallets.update",
                "wallets.edit",
                "wallets.destroy",
                "walletTransactions.index",
                "walletTransactions.create",
                "walletTransactions.store",
                "eProviderTypes.index",
                "eProviderTypes.create",
                "eProviderTypes.store",
                "eProviderTypes.edit",
                "eProviderTypes.update",
                "eProviderTypes.destroy",
                "eProviders.index",
                "eProviders.create",
                "eProviders.store",
                "eProviders.edit",
                "eProviders.update",
                "eProviders.destroy",
                "eServiceReviews.index",
                "eServiceReviews.create",
                "eServiceReviews.store",
                "eServiceReviews.edit",
                "eServiceReviews.update",
                "eServiceReviews.destroy",
                "eProviderPayouts.index",
                "eProviderPayouts.create",
                "eProviderPayouts.store",
                "eProviderPayouts.destroy",
                "markets.index",
                "markets.create",
                "markets.store",
                "markets.destroy",
                "markets.edit",
                "markets.update",
                "markets.show",
                "products.index",
                "products.create",
                "products.store",
                "products.destroy",
                "products.edit",
                "products.update",
                "products.show",
                "orders.index",
                "orders.create",
                "orders.store",
                "orders.destroy",
                "orders.edit",
                "orders.update",
                "orders.show",
            );

            $newPermissions = [];
            foreach ($projectInitialPermissions as $permission) {
                if ($permissions->where('name', $permission)->count()) {
                    continue;
                }

                $newPermission = array(
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => '2021-01-07 13:17:36',
                    'updated_at' => '2021-01-07 13:17:36',
                );

                $newPermissions[] = $newPermission;
            }

            DB::table('permissions')->insert($newPermissions);

            $routeCollection = Route::getRoutes();
            
            foreach ($routeCollection as $route) {
                if ($this->match($route)) {
                    // PermissionDoesNotExist
                    try{
                        if(!$role->hasPermissionTo($route->getName())){
                            $role->givePermissionTo($route->getName());
                        }
                    }catch (Exception $e){
                        if($e instanceof PermissionDoesNotExist){
                            $permission = Permission::create(['name' => $route->getName()]);
                            $role->givePermissionTo($permission);
                        }
                    }
                }
            }
        }catch (Exception $e){
            die($e->getMessage());
        }
    }

    private function match(Illuminate\Routing\Route $route)
    {
        if ($route->getName() === null) {
            return false;
        } else {
            // if(preg_match('/API/',class_basename($route->getController()))){
            //     return false;
            // }
            // if (in_array(class_basename($route->getController()), $this->exceptControllers)) {
            //     return false;
            // }
            foreach ($this->exceptNames as $except) {
                if (str_is($except, $route->getName())) {
                    return false;
                }
            }
        }
        return true;
    }
}
