<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use AjCastro\Searchable\Searchable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use Searchable;

    protected $searchable = [
        'columns' => [
            'phone_number',
            // 'name',
            'email',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "phone_number",
        "email",
        "password",
        'area_code',
        "name",
        "date_of_birth",
        "avatar_image",
        "sex",
        "is_vip",
        'functions_json',
    ];

    protected $appends = ['functions'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'functions_json'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_vip' => 'boolean',
    ];

    public function stores()
    {
        return $this->hasMany('App\Models\Store');
    }

    public function getAddedUserAdviceAttribute()
    {
        $u = UserAdvice::where('phone_number', $this->phone_number)->get()->first();
        return $u != null;
    }


    public function getFunctionsAttribute()
    {
        if ($this->functions_json == null) return [];
        return json_decode($this->functions_json);
    }
}
