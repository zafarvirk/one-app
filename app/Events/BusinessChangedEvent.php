<?php
/*
 * File name: SalonChangedEvent.php
 * Last modified: 2022.02.02 at 21:20:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2022
 */

namespace App\Events;

use App\Models\Business;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BusinessChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $newBusiness;

    public $oldBusiness;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Business $newBusiness, Business $oldBusiness)
    {
        //
        $this->newBusiness = $newBusiness;
        $this->oldBusiness = $oldBusiness;
    }

}
