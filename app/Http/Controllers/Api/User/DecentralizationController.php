<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Decentralization;
use App\Models\MsgCode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * @group  User/Danh sách phân quyền
 */
class DecentralizationController extends Controller
{
    /**
     * Danh sách phân quyền
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function getAll(Request $request)
    {

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Decentralization::where('store_id', $request->store->id)->get()
        ], 200);
    }

    /**
     * Thêm phân quyền
     * 
     * @urlParam  store_code required Store code. Example: kds
     * @bodyParam name string required Tên phân quyền 
     * @bodyParam description string required Mô tả phân quyền 
     * @bodyParam product_list boolean required Xem sản phẩm
     * @bodyParam product_add boolean required  Thêm sản phẩm
     * @bodyParam product_update boolean required Cập nhật sản phẩm
     * @bodyParam product_remove_hide boolean required Xóa/Ẩn sản phẩm
     * @bodyParam product_category_list boolean required  Xem danh mục sản phẩm
     * @bodyParam product_category_add boolean required  Thêm danh mục sản phẩm
     * @bodyParam product_category_update boolean required Cập nhật danh mục sản phẩm
     * @bodyParam product_category_remove boolean required Xóa danh mục sản phẩm
     * @bodyParam product_attribute_list boolean required Xem danh sách thuộc tính sản phẩm
     * @bodyParam product_attribute_add boolean required  Thêm thuộc tính sản phẩm
     * @bodyParam product_attribute_update boolean required Cập nhật thuộc tính sản phẩm
     * @bodyParam product_attribute_remove boolean required Xóa thuộc tính sản phẩm
     * @bodyParam product_ecommerce boolean required Sản phẩm từ sàn thương mại điện tử
     * @bodyParam product_import_from_excel boolean required Thêm sản phẩm từ file exel
     * @bodyParam product_export_to_excel boolean required Xuất danh sách sản phẩm ra file exel
     * @bodyParam customer_list boolean required Xem danh sách khách hàng
     * @bodyParam customer_config_point boolean required Cấu hình điểm thưởng
     * @bodyParam customer_review_list boolean required Danh sách đánh giá
     * @bodyParam customer_review_censorship boolean required Kiểm duyệt đánh giá
     * @bodyParam customer_role_edit boolean required Edit vai trò
     * @bodyParam promotion boolean required Chương trình khuyến mãi
     * @bodyParam promotion_discount_list boolean required Xem danh sách giảm giá sản phẩm
     * @bodyParam promotion_discount_add boolean required Thêm sản phẩm giảm giá
     * @bodyParam promotion_discount_update boolean required Cập nhật sản phẩm giảm giá
     * @bodyParam promotion_discount_end boolean required Kết thúc sản phẩm giảm giá
     * @bodyParam promotion_voucher_list boolean required Xem danh sách voucher
     * @bodyParam promotion_voucher_add boolean required Thêm voucher
     * @bodyParam promotion_voucher_update boolean required Cập nhật voucher
     * @bodyParam promotion_voucher_end boolean required Kết thúc voucher
     * @bodyParam promotion_combo_list boolean required Danh sách combo
     * @bodyParam promotion_combo_add boolean required Thêm combo
     * @bodyParam promotion_combo_update boolean required Cập nhật combo
     * @bodyParam promotion_combo_end boolean required Kết thúc combo
     * @bodyParam post_list boolean required Danh sách bài viết
     * @bodyParam post_add boolean required Thêm bài viết
     * @bodyParam post_update boolean required Cập nhật bài viết
     * @bodyParam post_remove_hide boolean required Xóa/Ẩn bài viết
     * @bodyParam post_category_list boolean required Xem danh mục bài viết
     * @bodyParam post_category_add boolean required Thêm bài mục bài viết
     * @bodyParam post_category_update boolean required Cập nhật danh mục bài viết
     * @bodyParam post_category_remove boolean required Xóa danh mục bài viết
     * @bodyParam app_theme_edit boolean required Truy cập chỉnh sửa app
     * @bodyParam app_theme_main_config boolean required Chỉnh sửa cấu hình
     * @bodyParam app_theme_button_contact boolean required Nút liên hệ
     * @bodyParam app_theme_home_screen boolean required Màn hình trang chủ
     * @bodyParam app_theme_main_component boolean required Thành phần chính
     * @bodyParam app_theme_category_product boolean required Màn hình danh mục sản phẩm
     * @bodyParam app_theme_product_screen boolean required Màn hình sản phẩm
     * @bodyParam app_theme_contact_screen boolean required Màn hình liên hệ
     * @bodyParam web_theme_edit boolean required Truy cập chỉnh sửa web
     * @bodyParam web_theme_overview boolean required Tổng quan
     * @bodyParam web_theme_contact boolean required Liên hệ
     * @bodyParam web_theme_help boolean required Hỗ trợ
     * @bodyParam web_theme_footer boolean required Dưới trang
     * @bodyParam web_theme_banner boolean required Banner
     * @bodyParam delivery_pick_address_list boolean required Danh sách địa chỉ lấy hàng
     * @bodyParam delivery_pick_address_update boolean required Chỉnh sửa địa chỉ
     * @bodyParam delivery_provider_update boolean required Chỉnh sửa bên cung cấp giao vận
     * @bodyParam payment_list boolean required Xem danh sách bên thanh toán
     * @bodyParam payment_on_off boolean required Bật tắt nhà thanh toán
     * @bodyParam notification_schedule_list boolean required Danh sách lịch thông báo
     * @bodyParam notification_schedule_add boolean required Thêm lịch thông báo
     * @bodyParam notification_schedule_remove_pause boolean required Xóa/Tạm dừng thông báo
     * @bodyParam notification_schedule_update boolean required Cập nhật lịch thông báo
     * @bodyParam order_list boolean required Danh sách đơn hàng
     * @bodyParam order_allow_change_status boolean required Cho phép thay đổi trạng thái
     * @bodyParam popup_list boolean required Danh sách popup quảng cáo
     * @bodyParam popup_add boolean required Thêm popup
     * @bodyParam popup_update boolean required Cập nhật popup
     * @bodyParam popup_remove boolean required Xóa popup
     * @bodyParam collaborator_config boolean required Cấu hình cộng tác viên
     * @bodyParam collaborator_list boolean required Xem danh sách cộng tác viên
     * @bodyParam collaborator_payment_request_list boolean required Xem danh sách yêu cầu thanh toán
     * @bodyParam collaborator_payment_request_solve boolean required Cho phép hủy hoặc thanh toán
     * @bodyParam collaborator_payment_request_history boolean required Xem lịch sử yêu cầu thanh toán
     * @bodyParam collaborator_add_sub_balance boolean required Cộng trừ số dư
     * @bodyParam agency_config boolean required Cấu hình cộng tác viên
     * @bodyParam agency_payment_request_list boolean required Xem danh sách yêu cầu thanh toán
     * @bodyParam agency_payment_request_solve boolean required Cho phép hủy hoặc thanh toán
     * @bodyParam agency_payment_request_history boolean required Xem lịch sử yêu cầu thanh toán
     * @bodyParam agency_add_sub_balance boolean required Cộng trừ số dư
     * @bodyParam notification_to_stote boolean required Cho phép xem danh sách thông báo
     * @bodyParam chat_list boolean required Xem danh sách chat
     * @bodyParam chat_allow boolean required Cho phép chat
     * @bodyParam report_view boolean required Xem báo cáo
     * @bodyParam report_overview boolean required Xem báo cáo tổng quan
     * @bodyParam report_product boolean required Xem báo cáo sản phẩm
     * @bodyParam report_order boolean required Xem báo cáo đơn hàng
     * @bodyParam report_inventory boolean required Xem báo cáo kho
     * @bodyParam report_finance boolean required Xem báo cáo tài chính
     * @bodyParam decentralization_list boolean required Danh sách phân quyền
     * @bodyParam decentralization_update boolean required Cập nhật phân quyền
     * @bodyParam decentralization_add boolean required Thêm phân quyền
     * @bodyParam decentralization_remove boolean required Xóa phân quyền
     * @bodyParam agency_list boolean required Dánh sách đại lý
     * @bodyParam staff_list boolean required Danh sách nhân viên
     * @bodyParam staff_update boolean required Cập nhật nhân viên
     * @bodyParam staff_add boolean required Thêm nhân viên
     * @bodyParam staff_remove boolean required Xóa nhân viên
     * @bodyParam staff_delegating boolean required Ủy quyền cho nhân viên
     * @bodyParam inventory_list boolean danh sách kho
     * @bodyParam inventory_import boolean truy cập nhập kho
     * @bodyParam inventory_tally_sheet boolean truy cập kiểm kho
     * @bodyParam revenue_expenditure boolean Truy cập thu chi
     * @bodyParam add_revenue boolean Tạo khoản thu
     * @bodyParam add_expenditure boolean Tạo khoản chi
     * @bodyParam setting_print boolean Cài đặt máy in
     * @bodyParam store_info boolean Thông tin cửa hàng
     * @bodyParam branch_list boolean Quản lý chi nhánh
     * @bodyParam config_setting boolean Cấu hình chung
     * @bodyParam create_order_pos boolean Tạo đơn
     * @bodyParam supplier boolean Tạo đơn
     * @bodyParam barcode_print boolean In mã vạch
     * @bodyParam timekeeping boolean Chấm công
     * @bodyParam transfer_stock boolean Chuyển kho
     * @bodyParam onsale boolean Truy cập onsale
     * @bodyParam train boolean Truy cập đào tạo
     * @bodyParam gamification boolean Truy cập Game
     * @bodyParam config_sms boolean Cài đặt SMS
     * 
     * 
     */
    public function create(Request $request)
    {

        if ($request->name == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_IS_REQUIRED[0],
                'msg' => MsgCode::NAME_IS_REQUIRED[1],
            ], 400);
        }

        $decentralizationExists = Decentralization::where(
            'name',
            $request->name
        )
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($decentralizationExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }

        $columns = Schema::getColumnListing('decentralizations');

        $array_add = [];
        foreach ($columns as $column) {
            $array_add[$column] = filter_var($request->$column, FILTER_VALIDATE_BOOLEAN);
        }

        $array_add['name'] = $request->name;
        $array_add['description'] = $request->description;


        $array_add['id'] = null;
        $array_add['updated_at'] = null;
        $array_add['created_at'] = null;

        $array_add['store_id'] = $request->store->id;

        $created = Decentralization::create(
            Helper::sahaRemoveItemArrayIfNullValue(
                $array_add
            )
        );

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Decentralization::where('store_id', $request->store->id)->where('id',  $created->id)->first()
        ], 200);
    }

    /**
     * Cập nhật phân quyền
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function update(Request $request)
    {
        $decentralization_id = request("decentralization_id");
        $decentralizationExists = Decentralization::where(
            'id',
            $decentralization_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($decentralizationExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DECENTRALIZATION_EXISTS[0],
                'msg' => MsgCode::NO_DECENTRALIZATION_EXISTS[1],
            ], 404);
        }


        $decentralizationDupExists = Decentralization::where(
            'name',
            $request->name
        )->where(
            'id',
            '!=',
            $decentralization_id
        )
            ->where(
                'store_id',
                $request->store->id
            )->first();

        if ($decentralizationDupExists != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NAME_ALREADY_EXISTS[0],
                'msg' => MsgCode::NAME_ALREADY_EXISTS[1],
            ], 400);
        }


        $columns = Schema::getColumnListing('decentralizations');

        $array_add = [];
        foreach ($columns as $column) {
            $array_add[$column] = filter_var($request->$column, FILTER_VALIDATE_BOOLEAN);
        }

        $array_add['name'] = $request->name;
        $array_add['description'] = $request->description;


        $array_add['id'] = null;
        $array_add['updated_at'] = null;
        $array_add['created_at'] = null;


        $array_add['store_id'] = $request->store->id;

        $decentralizationExists->update(
            Helper::sahaRemoveItemArrayIfNullValue(
                $array_add
            )
        );


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Decentralization::where('store_id', $request->store->id)->where('id',   $decentralizationExists->id)->first()
        ], 200);
    }

    /**
     * Xóa phân quyền
     * 
     * @urlParam  store_code required Store code. Example: kds
     */
    public function delete(Request $request)
    {

        $decentralization_id = request("decentralization_id");
        $decentralizationExists = Decentralization::where(
            'id',
            $decentralization_id
        )
            ->where(
                'store_id',
                $request->store->id
            )
            ->first();

        if ($decentralizationExists == null) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_DECENTRALIZATION_EXISTS[0],
                'msg' => MsgCode::NO_DECENTRALIZATION_EXISTS[1],
            ], 404);
        }

        $idDeleted = $decentralizationExists->id;
        $decentralizationExists->delete();



        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $idDeleted],
        ], 200);
    }
}
