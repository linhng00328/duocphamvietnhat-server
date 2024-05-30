<?php

namespace App\Helper;

use App\Models\Agency;
use Illuminate\Support\Facades\Cache;

class AgencyUtils
{


    static function isAgencyByIdsAndLever($customer_id, $agency_type_ids)
    {

        return Cache::remember(json_encode(["isAgencyByIdsAndLever", $customer_id, $agency_type_ids]), 6, function ()  use ($customer_id, $agency_type_ids) {

            $agency = AgencyUtils::getAgencyByCustomerId($customer_id);

            if ($agency == null) return false;

            if ($agency->status != 1) {
                return false;
            }

            if (in_array(0, $agency_type_ids)) {
                return true;
            }

            if (in_array($agency->agency_type_id, $agency_type_ids)) {
                return true;
            }
            return false;
        });
    }

    static function isAgencyByIdAndLever($customer_id, $agency_type_id)
    {

        return Cache::remember(json_encode(["isAgencyByIdAndLever", $customer_id, $agency_type_id]), 6, function ()  use ($customer_id, $agency_type_id) {

            $agency = AgencyUtils::getAgencyByCustomerId($customer_id);

            if ($agency == null) return false;

            if ($agency->status != 1) {
                return false;
            }

            if (0 == $agency_type_id) {
                return true;
            }
            if ($agency->agency_type_id == $agency_type_id) {
                return true;
            }
            return false;
        });
    }

    static function isAgency()
    {
        $customer = request('customer', $default = null);

        return Cache::remember(json_encode(["isAgencyCacHeader",  $customer == null ? null :  $customer->id]), 6, function ()  use ($customer) {

            if ($customer != null) {
                $agency = AgencyUtils::getAgencyByCustomerId($customer->id);


                if ($agency == null) return false;

                if ($agency->status != 1) {
                    return false;
                }
                return true;
            }
            return false;
        });
    }

    static function isAgencyByCustomerId($customer_id)
    {
        return Cache::remember(json_encode(["isAgencyByCustomerId", $customer_id]), 6, function ()  use ($customer_id) {
            $agency = AgencyUtils::getAgencyByCustomerId($customer_id);
            if ($agency == null) return false;
            if ($agency->status != 1) {
                return false;
            }
            return true;
        });
    }

    static function getAgencyByCustomerId($customer_id)
    {
        return Cache::remember(json_encode(["getAgencyByCustomerId", $customer_id]), 6, function ()  use ($customer_id) {

            $agency = Agency::where('customer_id',   $customer_id)->first();

            if ($agency == null) return null;

            if ($agency->status != 1) {
                return null;
            }
            return $agency;
        });
    }
}
