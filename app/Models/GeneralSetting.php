<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class GeneralSetting extends BaseModel
{

    const  noti_near_out_stock = ["noti_near_out_stock", false];
    const  noti_stock_count_near =  ["noti_stock_count_near", 10];
    const allow_semi_negative = ["allow_semi_negative", true];
    const enable_vat = ["enable_vat", false];
    const percent_vat = ["percent_vat", 10];
    const allow_branch_payment_order = ["allow_branch_payment_order", false];
    const auto_choose_default_branch_payment_order = ["auto_choose_default_branch_payment_order", true];
    const required_agency_ctv_has_referral_code = ["required_agency_ctv_has_referral_code", false];
    const is_default_terms_agency_collaborator = ["is_default_terms_agency_collaborator", true];
    const terms_agency = ["terms_agency", ''];
    const terms_collaborator = ["terms_collaborator", ''];

    use HasFactory;
    protected $guarded = [];
    protected $hidden = [
        'updated_at',
        'created_at',
        'id'
    ];
    protected $casts = [
        'noti_stock_count_near' => "integer",
        'noti_near_out_stock' => 'boolean',
        'allow_semi_negative' => 'boolean',
        'enable_vat' => 'boolean',
        'allow_branch_payment_order' => 'boolean',
        'auto_choose_default_branch_payment_order' => 'boolean',
        'required_agency_ctv_has_referral_code' => 'boolean',
        'is_default_terms_agency_collaborator' => 'boolean',
    ];


    static public function defaultSetting()
    {
        return [
            GeneralSetting::noti_near_out_stock[0] => GeneralSetting::noti_near_out_stock[1],
            GeneralSetting::noti_stock_count_near[0] => GeneralSetting::noti_stock_count_near[1],
            GeneralSetting::allow_semi_negative[0] => GeneralSetting::allow_semi_negative[1],
            GeneralSetting::enable_vat[0] => GeneralSetting::enable_vat[1],
            GeneralSetting::percent_vat[0] => GeneralSetting::percent_vat[1],
            GeneralSetting::allow_branch_payment_order[0] => GeneralSetting::allow_branch_payment_order[1],
            GeneralSetting::auto_choose_default_branch_payment_order[0] => GeneralSetting::auto_choose_default_branch_payment_order[1],
            GeneralSetting::required_agency_ctv_has_referral_code[0] => GeneralSetting::required_agency_ctv_has_referral_code[1],
            GeneralSetting::is_default_terms_agency_collaborator[0] => GeneralSetting::is_default_terms_agency_collaborator[1],
            GeneralSetting::terms_agency[0] => GeneralSetting::terms_agency[1],
            GeneralSetting::terms_collaborator[0] => GeneralSetting::terms_collaborator[1],
        ];
    }
}
