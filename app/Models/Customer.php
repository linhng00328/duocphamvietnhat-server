<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use AjCastro\Searchable\Searchable;
use App\Helper\StatusDefineCode;
use App\Models\Base\BaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Customer extends BaseModel
{
    use HasFactory, Notifiable;
    use Searchable;
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'store_id'
    ];

    protected $appends = [
        'total_referrals',
        'total_final_without_refund',
        'total_final_all_status',
        'total_after_discount_no_bonus'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_collaborator' => 'boolean',
        'is_agency' => 'boolean',
        'is_sale' => 'boolean',
        'official' => 'boolean',
        'is_passersby' => 'boolean',
        'is_from_json' => 'boolean',

    ];

    protected $searchable = [
        'columns' => [
            'username',
            'email',
            'phone_number',
            'name'
        ],
    ];

    public function agency_type()
    {
        return $this->belongsToMany(
            'App\Models\AgencyType',
            'customer_agency_types',
            'customer_id',
            'agency_type_id'
        );
    }

    public function last_submit_quizzes()
    {
        return $this->hasMany('App\Models\LastSubmitQuiz');
    }


    public function getTotalReferralsAttribute()
    {
        return DB::table('customers')->select('id')->where('store_id', $this->store_id)->where('referral_phone_number', $this->phone_number)->count();
    }

    public function getTotalFinalWithoutRefundAttribute()
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);

        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

        return DB::table('orders')->where('store_id', $this->store_id)
            ->where('phone_number', $this->phone_number)
            ->where('payment_status', StatusDefineCode::PAID)
            ->where('order_status', StatusDefineCode::COMPLETED)
            ->when(request('date_from') && request('date_to'), function ($query) use ($dateFrom, $dateTo) {
                $query->where('created_at', '>=', $dateFrom)
                    ->where('created_at', '<=', $dateTo);
            })
            ->sum('total_final');
    }

    public function getTotalFinalAllStatusAttribute()
    {
        return DB::table('orders')->where('store_id', $this->store_id)->where('phone_number', $this->phone_number)->sum('total_final');
    }

    public function getTotalAfterDiscountNoBonusAttribute()
    {
        return DB::table('orders')->where('store_id', $this->store_id)->where('phone_number', $this->phone_number)->where('order_status', StatusDefineCode::COMPLETED)->sum(DB::raw('total_before_discount - combo_discount_amount - product_discount_amount - voucher_discount_amount'));
    }
}
