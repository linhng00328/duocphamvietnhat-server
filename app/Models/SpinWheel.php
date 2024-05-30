<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpinWheel extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'list_gift',
        'group_customer',
        'agency_type',
        'final_result_announced'
    ];

    protected $casts = [
        'is_shake' => 'boolean',
        'apply_fors' => 'array',
        'agency_types' => 'array',
        'group_types' => 'array',
    ];

    public function getGroupCustomerAttribute()
    {
        if (!empty($this->group_customer_id) && $this->group_customer_id != null) {
            $groupCustomerExists = GroupCustomer::where([
                ['store_id', $this->store_id],
                ['id', $this->group_customer_id]
            ])
                ->select('store_id', 'name', 'note', 'group_type')
                ->first();

            return $groupCustomerExists ?? json_decode('{}');
        }
        return json_decode('{}');
    }

    public function getAgencyTypeAttribute()
    {
        if (!empty($this->agency_type_id) && $this->agency_type_id != null) {
            $agencyExists = AgencyType::where([
                ['store_id', $this->store_id],
                ['id', $this->agency_type_id]
            ])
                ->first();

            return $agencyExists ?? json_decode('{}');
        }
        return json_decode('{}');
    }

    public function getListGiftAttribute()
    {
        return GiftSpinWheel::where([
            ['store_id', $this->store_id],
            ['user_id', $this->user_id],
            ['spin_wheel_id', $this->id]
        ])->get();
    }

    public function getImagesAttribute($value)
    {
        if ($value == null) {
            return [];
        }

        return json_decode($value);
    }

    public function getFinalResultAnnouncedAttribute()
    {
        try {
            $history_gift_spin_wheels = DB::table('history_gift_spin_wheels')
                ->join('player_spin_wheels', 'history_gift_spin_wheels.player_spin_wheel_id', '=', 'player_spin_wheels.id')
                ->join('customers', 'player_spin_wheels.customer_id', '=', 'customers.id')
                ->where([
                    ['history_gift_spin_wheels.store_id', $this->store_id],
                    ['player_spin_wheels.spin_wheel_id', $this->id]
                ])
                ->select('history_gift_spin_wheels.*', 'customers.name as customer_name', 'customers.phone_number as customer_phone_number')
                ->orderBy("history_gift_spin_wheels.created_at", 'desc')
                ->get();

            if ($history_gift_spin_wheels) {
                foreach ($history_gift_spin_wheels as $history) {
                    $product = null;

                    if ($history->type_gift == 1 && $history->value_gift) {
                        $product = DB::table('products')
                            ->where('store_id', $this->store_id)
                            ->where('id', $history->value_gift)
                            ->first();
                    }

                    $history->product = $product;
                }
            }

            return $history_gift_spin_wheels;
        } catch (Exception $ex) {
            return null;
        }
    }
}
