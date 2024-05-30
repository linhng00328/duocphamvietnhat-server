<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AjCastro\Searchable\Searchable;

class UserAdvice extends Model
{
    use Searchable;
    use HasFactory;
    protected $guarded = [];


    protected $searchable = [
        'columns' => [
            'username',
            'email',
            'phone_number',
            'name',
            'note'
        ],
    ];

    protected $appends = ['employee_help', 'list_store', 'history_consultant'];

    public function getHistoryConsultantAttribute()
    {
        return HistoryConsultant::where('user_advice_id', $this->id)->orderBy('time_consultant', 'desc')->take(20)->get();
        // return $this->hasMany('App\HistoryConsultant', 'id', 'user_advice_id');
    }

    public function getEmployeeHelpAttribute()
    {
        return Employee::where('id', $this->id_employee_help)->first();
    }

    public function getListStoreAttribute()
    {

        $user = User::where('phone_number', $this->phone_number)->first();
        if ($user != null) {
            $stores = Store::where('user_id', $user->id)->pluck('store_code');
            return $stores;
        }
        return [];
    }
}
