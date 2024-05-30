<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;
use AjCastro\Searchable\Searchable;

class CommunityPost extends BaseModel
{

    use Searchable;
    use HasFactory;

    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'name',
            'content',
        ],
    ];

    protected $casts = [
        'is_pin' => 'boolean'
    ];
    protected $hidden = ['images_json'];
    protected $appends = [
        'is_like',
        'total_like',
        'total_comment',
        'staff',
        'user',
        'customer',
        'images'
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
        return Customer::select('id', 'name', 'avatar_image')->where('id', $this->customer_id)->first();
    }
    public function getStaffAttribute()
    {
        return Staff::select('id', 'name')->where('id', $this->staff_id)->first();
    }

    public function getTotalLikeAttribute()
    {
        return  CommunityLike::where(
            'community_post_id',
            $this->id
        )
            ->count();
    }

    public function getTotalCommentAttribute()
    {
        return  CommunityComment::where(
            'community_post_id',
            $this->id
        )
            ->count();
    }

    public function getIsLikeAttribute()
    {
        $request = request();
        $customer = request('customer', $default = null);


        if ($customer   != null) {

            $fv = CommunityLike::where(
                'customer_id',
                $customer->id
            )->where(
                'community_post_id',
                $this->id
            )
                ->first();
            if ($fv != null) return true;
        }

        return false;
    }
}
