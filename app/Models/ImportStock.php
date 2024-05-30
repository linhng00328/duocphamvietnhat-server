<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
class ImportStock extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'code',
        ],
    ];

    protected $casts = [
        'has_refunded' => 'boolean',
    ];
    

    protected $appends = ['change_status_history', 'supplier', 'branch', 'staff', 'user'];


    public function getChangeStatusHistoryAttribute()
    {
        $histories = ImportTimeHistory::where('import_stock_id', $this->id)
        ->orderBy('status', 'asc')
        ->where('store_id', $this->store_id)->get();
        return $histories;
    }


    public function getSupplierAttribute()
    {
        $supplier =    Supplier::where('id', $this->supplier_id)->first();
        return  $supplier;
    }

    public function getBranchAttribute()
    {
        $branch =    Branch::select("name")->where('id', $this->branch_id)->first();
        return  $branch;
    }

    public function getStaffAttribute()
    {
        $staff =    Staff::select("name")->where('id', $this->staff_id)->first();
        return  $staff;
    }

    public function getUserAttribute()
    {
        $user =    User::select("name")->where('id', $this->user_id)->first();
        return  $user;
    }
}
