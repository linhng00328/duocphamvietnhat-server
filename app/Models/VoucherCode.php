<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;

class VoucherCode extends Model
{
    use HasFactory, Searchable;

    protected $hidden = [
        'voucher_id',
        'customer_id',
        'store_id',
    ];

    protected $searchable = [
        'columns' => [
            'code',
        ],
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)
            ->select('id', 'name', 'phone_number', 'is_collaborator', 'is_agency', 'is_sale');
    }
}
