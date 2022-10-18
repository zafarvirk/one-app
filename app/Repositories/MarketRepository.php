<?php

namespace App\Repositories;

use App\Models\Business;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class MarketRepository
 * @package App\Repositories
 * @version August 29, 2019, 9:38 pm UTC
 *
 * @method Market findWithoutFail($id, $columns = ['*'])
 * @method Market find($id, $columns = ['*'])
 * @method Market first($columns = ['*'])
 */
class MarketRepository extends BaseRepository implements CacheableInterface
{

    use CacheableRepository;
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone',
        'mobile',
        'information',
        'delivery_fee',
        'default_tax',
        'delivery_range',
        'available_for_delivery',
        'closed',
        'admin_commission',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Business::class;
    }

    /**
     * get my markets
     */

    public function myMarkets()
    {
        return Business::join("business_users", "business_id", "=", "businesses.id")
            ->where('business_users.user_id', auth()->id())->get();
    }

    public function myActiveMarkets()
    {
        return Business::join("business_users", "business_id", "=", "businesses.id")
            ->where('business_users.user_id', auth()->id())
            ->where('businesses.active','=','1')->get();
    }

}
