<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeSearch extends Model
{
    use HasFactory;
    protected $guarded = [];
    


    protected $appends = ['product_attribute_search_children'];
    
    public function getProductAttributeSearchChildrenAttribute()
    {
        return AttributeSearchChild::where('attribute_search_id', $this->id)->orderBy('name')->get();
    }
}
