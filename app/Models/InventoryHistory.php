<?php

namespace App\Models;

use App\Helper\InventoryUtils;
use App\Services\BalanceCustomerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Base\BaseModel;

class InventoryHistory extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['branch', 'type_name', 'element_distribute', 'sub_element_distribute', 'product'];


    public function getBranchAttribute()
    {
        $branch =    Branch::select("name")->where('id', $this->branch_id)->first();
        return  $branch;
    }


    public function getProductAttribute()
    {
        $product =   DB::table('products')->select("name")->where('id', $this->product_id)->first();
        return  $product;
    }

    public function getElementDistributeAttribute()
    {

        $ele =   DB::table('element_distributes')->select("name")->where('id', $this->element_distribute_id)->first();
        return  $ele;
    }

    public function getSubElementDistributeAttribute()
    {
        $sub =   DB::table('sub_element_distributes')->select("name")->where('id', $this->sub_element_distribute_id)->first();
        return  $sub;
    }

    public function getTypeNameAttribute()
    {
        if ($this->type == InventoryUtils::TYPE_INIT_STOCK) {
            return "Khởi tạo kho ";
        }

        if ($this->type == InventoryUtils::TYPE_IMPORT_STOCK) {
            return "Nhập kho ".($this->references_value);
        }

        if ($this->type == InventoryUtils::TYPE_EDIT_STOCK) {
            return "Sửa kho và cân bằng ".($this->references_value);
        }
        if ($this->type == InventoryUtils::TYPE_EDIT_COST_OF_CAPITAL) {
            return "Sửa giá vốn ".($this->references_value);
        }

        if ($this->type == InventoryUtils::TYPE_REFUND_ORDER) {
            return "Hoàn trả đơn hàng ".($this->references_value);
        }
        if ($this->type == InventoryUtils::TYPE_TALLY_SHEET_STOCK) {
            return "Kiểm kho ".($this->references_value);
        }
        if ($this->type == InventoryUtils::TYPE_EXPORT_ORDER_STOCK) {
            return "Xuất đơn hàng ".($this->references_value);
        }
        if ($this->type == InventoryUtils::TYPE_IMPORT_AUTO_CHANGE_COST_OF_CAPITAL) {
            return "Tự động cập nhật giá vốn sau nhập hàng ";
        }
        if ($this->type == InventoryUtils::TYPE_REFUND_IMPORT_STOCK) {
            return "Hoàn trả đơn nhập hàng ".($this->references_value);
        }
        if ($this->type == InventoryUtils::TYPE_TRANSFER_STOCK_RECEIVER) {
            return "Nhận hàng từ chi nhánh khác ".($this->references_value);
        }
        if ($this->type == InventoryUtils::TYPE_TRANSFER_STOCK_SENDER) {
            return "Chuyển hàng đến chi nhánh khác ".($this->references_value);
        }

        return "";
    }
}
