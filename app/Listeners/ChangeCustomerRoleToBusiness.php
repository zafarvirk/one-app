<?php
/*
 * File name: ChangeCustomerRoleToBusiness.php
 * Last modified: 2022.02.02 at 21:51:53
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Listeners;

/**
 * Class ChangeCustomerRoleToBusiness
 * @package App\Listeners
 */
class ChangeCustomerRoleToBusiness
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->newBusiness->accepted) {
            foreach ($event->newBusiness->users as $user) {
                $user->syncRoles(['salon owner']);
            }
        }
    }
}
