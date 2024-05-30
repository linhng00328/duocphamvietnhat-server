<?php

namespace App\Traits;

trait CcartItemTrait
{

    public static function scopeAllItem($query, $list_cart_id, $request)
    {

        $device_id = request()->header('device_id');
        $list_cart_id = $list_cart_id == null ? null : $list_cart_id;
        $user_id = $list_cart_id == null && $request->user != null ? $request->user->id : null;
        $staff_id = $list_cart_id == null  && $request->user == null  && $request->staff != null ? $request->staff->id : null;
        $customer_id = $list_cart_id == null && $request->user == null &&  $request->staff == null &&  $request->customer != null ? $request->customer->id : null;


        return static::where(function ($query) use ($request, $list_cart_id, $customer_id, $user_id, $staff_id, $device_id) {

            if ($list_cart_id != null) {
                $query->where(
                    'store_id',
                    $request->store->id
                )
                    ->where('list_cart_id', $list_cart_id)
                    ->where('user_id',  $user_id)
                    ->where('staff_id',  $staff_id);
            }
            if ($list_cart_id == null &&  $device_id != null) {
                $query->where(
                    'store_id',
                    $request->store->id
                )
                    ->when($device_id != null, function ($query) use ($customer_id, $device_id) {
                        $query->where('device_id',  $device_id);
                    });
            } else if ($list_cart_id == null &&  $customer_id != null) {
                $query->where(
                    'store_id',
                    $request->store->id
                )
                    ->when($customer_id != null, function ($query) use ($customer_id, $device_id) {
                        $query->where('customer_id',  $customer_id);
                    });
            } else if ($list_cart_id == null &&  $customer_id != null  &&  $device_id != null) {
                $query->where(
                    'store_id',
                    $request->store->id
                )->where('customer_id',  $customer_id)->where('device_id',  $device_id);
            } else {
                $query->where(
                    'store_id',
                    $request->store->id
                )
                    ->when($device_id != null, function ($query) use ($customer_id, $device_id) {
                        $query->where('device_id',  $device_id);
                    })
                    ->when($customer_id  != null &&  $device_id == null, function ($query) use ($customer_id) {
                        $query->where('customer_id',  $customer_id);
                    })

                    ->where('user_id',  null)
                    ->where('staff_id', null);
            }
        });
    }
}
