<?php

namespace App\Helper;

use App\Models\Customer;

class CustomerUtils
{

    const GROUP_CUSTOMER_ALL = 0;
    const GROUP_CUSTOMER_CTV = 1;
    const GROUP_CUSTOMER_AGENCY = 2;
    const GROUP_CUSTOMER_BY_CONDITION = 4;
    const GROUP_CUSTOMER_NORMAL_GUEST = 5;
    const GROUP_CUSTOMER_NOT_LOGGED_IN = 6;


    static function getCustomerPassersby($request)
    {
        $customerPasserBy = Customer::where('store_id', $request->store->id)
            ->where('is_passersby', true)->first();

        if ($customerPasserBy == null) {
            $customerPasserBy = Customer::create(
                [
                    'area_code' => '+84',
                    'name' => "Khách vãng lai",
                    'name_str_filter' => StringUtils::convert_name_lowcase("Khách vãng lai"),
                    'phone_number' => "----------",
                    'email' => "",
                    'store_id' => $request->store->id,
                    'password' => bcrypt('DOAPP_BCRYPT_PASS'),
                    'official' => true,
                    "is_passersby" => true
                ]
            );
        }

        return  $customerPasserBy;
    }

    static function isRetailCustomer($customer, $store_id)
    {
        if ($customer) {
            return CollaboratorUtils::isCollaborator($customer->id,  $store_id) || AgencyUtils::isAgency() ? false : true;
        }

        return true;
    }
}
