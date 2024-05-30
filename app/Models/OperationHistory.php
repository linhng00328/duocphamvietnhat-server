<?php

namespace App\Models;

use App\Events\RedisRealtimeOperationHistoryEvent;
use App\Helper\TypeAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class OperationHistory extends Model
{


    use SearchableTrait;
    use HasFactory;
    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'content' => 1,
            'ip' => 2,
            'references_value' => 3,
        ],
    ];


    protected $appends = ['function_type_name'];

    public function getFunctionTypeNameAttribute()
    {
        if ($this->function_type == TypeAction::FUNCTION_TYPE_PRODUCT) {
            return 'Sản phẩm';
        }
        if ($this->function_type == TypeAction::FUNCTION_TYPE_INVENTORY) {
            return 'Kho';
        }
        if ($this->function_type == TypeAction::FUNCTION_POINT) {
            return 'Cấu hình xu';
        }
        if ($this->function_type == TypeAction::FUNCTION_TYPE_CATEGORY_PRODUCT) {
            return 'Danh mục sản phẩm';
        }
        if ($this->function_type == TypeAction::FUNCTION_TYPE_CATEGORY_POST) {
            return 'Danh mục bài viết';
        }
        if ($this->function_type == TypeAction::FUNCTION_TYPE_ORDER) {
            return 'Đơn hàng';
        }
        if ($this->function_type == TypeAction::FUNCTION_TYPE_THEME) {
            return 'Giao diện';
        }
        if ($this->function_type == TypeAction::FUNCTION_TYPE_PROMOTION) {
            return 'Khuyến mãi';
        }

        
        return null;
    }
}
