<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseModel;
use AjCastro\Searchable\Searchable;

class CommunityComment extends BaseModel
{
    use HasFactory;
    use Searchable;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'content',
        ],
    ];

    protected $appends = [
        'staff',
        'user',
        'customer',
        'images',
    ];

    public function getImagesAttribute()
    {
        return json_decode($this->images_json);
    }
    
    public function getUserAttribute()
    {
        return User::select('id', 'name')->where('id', $this->user_id)->first();
    }
    public function getCustomerAttribute()
    {
        return Customer::select('id','name','avatar_image')->where('id', $this->customer_id)->first();
    }
    public function getStaffAttribute()
    {
        return Staff::select('id', 'name')->where('id', $this->staff_id)->first();
    }
}
