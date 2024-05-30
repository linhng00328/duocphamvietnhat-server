<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\BaseModel;

class MsgCode extends BaseModel
{
    use HasFactory;

    const CANNOT_POST_PICTURES = [
        "CANNOT_POST_PICTURES",
        "Không thể đăng ảnh"
    ];
    const CANNOT_POST_VIDEOS = [
        "CANNOT_POST_VIDEOS",
        "Không thể đăng video"
    ];

    const ATTRIBUTE_EXISTS = ["ATTRIBUTE_EXISTS", "Thuộc tính đã tồn tại"];
    const APP_HAS_BEEN_LOCKED = ["APP_HAS_BEEN_LOCKED", "Ứng dụng đã bị khóa"];
    const BARCODE_IS_REQUIRED = ["BARCODE_IS_REQUIRED", "Barcode không được trống"];
    const DATA_IS_REQUIRED = ["DATA_IS_REQUIRED", "Dữ liệu không được trống"];
    const CANNOT_GET_FEE = ["CANNOT_GET_FEE", "Không thể lấy chi phí giao hàng"];
    const CANNOT_DELETE_DEFAULT_CART = ["CANNOT_DELETE_DEFAULT_CART", "Không thể xóa đơn hàng mặc định"];
    const UNREGISTERED_PARTNER = ["UNREGISTERED_PARTNER", "Chưa đăng ký đối tác giao hàng"];
    const BRANCH_ALREADY_EXISTS = ["BRANCH_ALREADY_EXISTS",  "Chi nhánh đã tồn tại"];
    const BALANCED_VOTES = ["BALANCED_VOTES",  "Phiếu đã cân bằng"];
    const BRANCHES_RECEIVING_ONLINE_ORDERS_CANNOT_BE_DELETED = ['BRANCHES_RECEIVING_ONLINE_ORDERS_CANNOT_BE_DELETED', 'Chi nhánh mặc định nhận đơn hàng online không thể xóa'];
    const CODE_ALREADY_EXISTS = ["CODE_ALREADY_EXISTS", "Mã cửa hàng đã tồn tại"];
    const CODE_VOUCHER_ALREADY_EXISTS = ["CODE_VOUCHER_ALREADY_EXISTS", "Mã voucher đã tồn tại"];
    const CODE_IS_REQUIRED = ["CODE_IS_REQUIRED", "Mã giảm giá không được để trống"];
    const CANT_SEND_OTP = ["CANT_SEND_OTP", "Không thể gửi mã"];
    const CHECKED_IN_TODAY = ["CHECKED_IN_TODAY", "Bạn đã điểm danh hôm nay"];
    const CONTENT_IS_REQUIRED = ["CONTENT_IS_REQUIRED", "Nội dung không được để trống"];
    const CATEGORY_EXISTS = ["CATEGORY_EXISTS", "Danh mục đã tồn tại"];
    const CHECKIN_LOCATION_EXISTS = ["CHECKIN_LOCATION_EXISTS", "Vị trí chấm công đã tồn tại"];
    const CATEGORY_NAME_EXISTS = ["CATEGORY_NAME_EXISTS", "Danh mục đã tồn tại"];
    const CATEGORY_CHILD_NAME_EXISTS = ["CATEGORY_CHILD_NAME_EXISTS", "Danh mục con tồn tại"];
    const CANNOT_CHANGE_OLD_STATUS = ["CANNOT_CHANGE_OLD_STATUS", "Không thể chuyển đến trạng thái cũ"];
    const DOES_NOT_EXIST = ["DOES_NOT_EXIST", "Không tồn tại"];
    const DISTRIBUTE_NAME_IS_REQUIRED = ["DISTRIBUTE_NAME_IS_REQUIRED", "Tên phân loại chính không được để trống"];
    const ADD_DISTRIBUTE_NAME_IS_REQUIRED = ["ADD_DISTRIBUTE_NAME_IS_REQUIRED", "Tên phân loại bổ sung không được để trống"];
    const DUPLICATE_PRODUCT = ["DUPLICATE_PRODUCT",  "Trùng sản phẩm"];
    const DUPLICATE_SHIFT = ["DUPLICATE_SHIFT",  "Thời gian một số ca trùng nhau"];
    const DUPLICATE_DOMAIN = ["DUPLICATE_DOMAIN",  "Tên miền đã tồn tại"];
    const DEVICE_ALREADY_EXISTS = ["DEVICE_ALREADY_EXISTS",  "Device id đã tồn tại"];
    const DEVICE_IS_REQUIRED = ["DEVICE_IS_REQUIRED", "Device ID không được trống"];
    const DESCRIPTION_CANT_IMAGE_BAGE64 = ["DESCRIPTION_CANT_IMAGE_BAGE64",  "Mô tả không được chứa ảnh mã hóa, mời chọn ảnh khác"];
    const EMAIL_ALREADY_EXISTS = ["EMAIL_ALREADY_EXISTS", "Email đã tồn tại"];
    const BONUS_EXISTS = ["BONUS_EXISTS", "Phần thưởng đã tồn tại"];
    const ERROR = ["ERROR", "Có lỗi xảy ra"];
    const EXPIRED_PIN_CODE = ["EXPIRED_PIN_CODE", "Mã pin đã hết hạn hãy yêu cầu mã mới"];
    const GREAT_TIME = ["GREAT_TIME", "Thời gian quá lớn"];
    const HAVE_AN_UNPAID_REQUEST = ["HAVE_AN_UNPAID_REQUEST", "Đã có yêu cầu thanh toán trước đó"];
    const INVALID_CODE_STORE = ["INVALID_CODE_STORE", "Mã cửa hàng không thể có ký tự đặc biệt và tối thiểu 2 ký tự"];
    const CAN_NOT_USE = ["CAN_NOT_USE", "Không thể sử dụng mã code cửa hàng này"];
    const INVALID_PHOTO = ["INVALID_PHOTO", "Ảnh không hợp lệ"];
    const INVALID_VALUE = ["INVALID_VALUE", "Giá trị không hợp lệ"];
    const INVALID_KEY = ["INVALID_KEY", "Key không hợp lệ"];
    const INVALID_USERNAME = ["INVALID_USERNAME", "Tài khoản không hợp lệ"];
    const INVALID_DOMAIN = ["INVALID_DOMAIN", "Tên miền không hợp lệ"];
    const INVALID_QUANTITY = ["INVALID_QUANTITY", "Số lượng không hợp lệ"];
    const INVALID_VOUCHER_DISCOUNT_TYPE = ["INVALID_VOUCHER_DISCOUNT_TYPE", "Kiểu giảm giá không hợp lệ giá trị"];
    const INVALID_COMBO_DISCOUNT_TYPE = ["INVALID_COMBO_DISCOUNT_TYPE", "Kiểu giảm giá không hợp lệ giá trị"];
    const INVALID_PARTNER = ["INVALID_PARTNER", "Đối tác giao hàng không hợp lệ"];
    const INVALID_PAYMENT_METHOD = ["INVALID_PAYMENT_METHOD", "Phương thức thanh toán không hợp lệ"];
    const INVALID_PRODUCT_RETAIL_STEP  = ["INVALID_PRODUCT_RETAIL_STEP", "Sản phẩm bán lẻ theo bậc thang không hợp lệ"];
    const KEY_IS_REQUIRED = ["KEY_IS_REQUIRED", "Key không được để trống"];
    const LIMIT_NOT_REACHED = ["LIMIT_NOT_REACHED", "Chưa đạt hạn mức thanh toán"];
    const LINK_IS_REQUIRED = ["LINK_IS_REQUIRED", "Link không được trống"];
    const VALUE_MUST_BE_GREATER_THAN_0 = ["VALUE_MUST_BE_GREATER_THAN_0", "Giá trị phiếu phải lớn hơn 0"];
    const MAC_WIFI_EXISTS = ["MAC_WIFI_EXISTS", "Mac wifi đã tồn tại"];
    const NUMBER_OF_COMBOS_MUST_BE_MORE_THAN_2  = ["NUMBER_OF_COMBOS_MUST_BE_MORE_THAN_2", "Số lượng sản phẩm trong combo phải lớn hơn 2"];
    const NOT_HAVE_ACCESS = ["NOT_HAVE_ACCESS", "Không có quyền truy cập"];
    const NO_TOKEN = ["NO_TOKEN", "Chưa đăng nhập bạn không có quyền truy cập"];
    const NO_DEVICE_TOKEN = ["NO_DEVICE_TOKEN", "Không có device token"];
    const NO_STORE_EXISTS = ["NO_STORE_EXISTS", "Store không tồn tại"];
    const NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS = ["NO_PLATFORM_ECOMMERCE_HAS_CONNECTED_NOT_EXISTS", "Nền tảng thương mại điện tử đã kết nối không tồn tại"];
    const NO_TALLY_SHEET_EXISTS = ["NO_TALLY_SHEET_EXISTS", "Phiếu kiểm không tồn tại"];
    const NO_TRANSFER_EXISTS = ["NO_TRANSFER_EXISTS", "Phiếu chuyển kho không tồn tại"];
    const NO_REVENUE_EXPENDITURE_EXISTS = ["NO_REVENUE_EXPENDITURE_EXISTS", "Phiếu thu chi không tồn tại"];
    const NO_IMPORT_STOCK_EXISTS = ["NO_IMPORT_STOCK_EXISTS", "Phiếu nhập hàng không tồn tại"];
    const NO_SHIPPER_SET_YET = ["NO_SHIPPER_SET_YET", "Chưa cài đặt đơn vị vận chuyển này"];
    const NO_BRANCH_EXISTS = ["NO_BRANCH_EXISTS", "Chi nhánh không tồn tại"];
    const NO_SUPPLIER_EXISTS = ["NO_SUPPLIER_EXISTS", "Nhà cung cấp không tồn tại"];
    const NO_USER_EXISTS = ["NO_USER_EXISTS", "User không tồn tại"];
    const NOT_ELIGIBLE_REWARD = ["NOT_ELIGIBLE_REWARD", "Chưa đủ điều kiện nhận thưởng"];
    const NO_PLACE_TYPE = ["NO_PLACE_TYPE", "Kiểu vùng không tồn tại"];
    const NO_GROUP_CUSTOMER = ["NO_GROUP_CUSTOMER", "Nhóm khách hàng không tồn tại"];
    const NO_STORE_CODE_EXISTS = ["NO_STORE_CODE_EXISTS", "Store không tồn tại"];
    const NO_PRODUCT_EXISTS = ["NO_PRODUCT_EXISTS", "Sản phẩm không tồn tại"];
    const NO_LIST_AGENCY_PRICE_EXISTS = ["NO_LIST_AGENCY_PRICE_EXISTS", "Không tìm thấy danh sách giá sản phẩm cho đại lý"];
    const NO_SUB_ELEMENT_DISTRIBUTE_EXISTS = ["NO_SUB_ELEMENT_DISTRIBUTE_EXISTS", "Phân loại con không tồn tại"];
    const NO_ELEMENT_DISTRIBUTE_EXISTS = ["NO_ELEMENT_DISTRIBUTE_EXISTS", "Phân loại không tồn tại"];
    const NO_STATUS_EXISTS = ["NO_STATUS_EXISTS", "Trạng thái không tồn tại"];
    const NO_AGENCY_TYPE_EXISTS = ["NO_AGENCY_TYPE_EXISTS", "Kiểu tầng đại lý không tồn tại"];
    const NO_STAFF_EXISTS = ["NO_STAFF_EXISTS", "Nhân viên không tồn tại"];
    const NO_ADVICE_USER_EXISTS = ["NO_ADVICE_USER_EXISTS", "Tư vấn khách hàng không tồn tại"];
    const NO_DECENTRALIZATION_EXISTS = ["NO_DECENTRALIZATION_EXISTS", "Phân quyền không tồn tại"];
    const NO_PRODUCT_EXISTS_IN_ORDER = ["NO_PRODUCT_EXISTS_IN_ORDER", "Không có sản phẩm nào trong giỏ hàng"];
    const NO_DISCOUNT_EXISTS = ["NO_DISCOUNT_EXISTS", "Giảm giá không tồn tại"];
    const NO_CUSTOMER_DEBT_EXISTS = ["NO_CUSTOMER_DEBT_EXISTS", "Không có khách hàng để cho nợ"];
    const NO_VOUCHER_EXISTS = ["NO_VOUCHER_EXISTS", "Voucher không tồn tại"];
    const NO_COMBO_EXISTS = ["NO_COMBO_EXISTS", "Combo không tồn tại"];
    const NO_PRODUCT_BONUS_EXISTS = ["NO_PRODUCT_BONUS_EXISTS", "Chương trình thưởng sản phẩm không tồn tại"];
    const NO_MOBILE_EXISTS = ["NO_MOBILE_EXISTS", "Điện thoại chấm công không tồn tại"];
    const NO_PHONE_NUMBER_EXISTS = ["NO_PHONE_NUMBER_EXISTS",  "Số điện thoại không tồn tại trên hệ thống"];
    const NO_ACCOUNT_EXISTS = ["NO_ACCOUNT_EXISTS", "Tài khoản không tồn tại"];
    const NO_EMAIL_EXISTS = ["NO_PHONE_NUMBER_EXISTS",  "Số điện thoại không tồn tại trên hệ thống"];
    const NO_ORDER_EXISTS = ["NO_ORDER_EXISTS", "Đơn hàng không tồn tại"];
    const NO_COMMENT_EXISTS = ["NO_COMMENT_EXISTS", "Bình luận không tồn tại"];
    const NO_FAVORITE_EXISTS = ["NO_FAVORITE_EXISTS", "Yêu thích không tồn tại"];
    const NO_POST_EXISTS = ["NO_POST_EXISTS", "Bài viết không tồn tại"];
    const NO_RECIPIENT_GROUP_EXISTS = ["NO_RECIPIENT_GROUP_EXISTS", "Nhóm khách hàng không tồn tại"];
    const NO_PAYMENT_METHOD_EXISTS = ["NO_PAYMENT_METHOD_EXISTS", "Phương thức thanh toán không tồn tại"];
    const NO_ATTRIBUTE_FIELD_EXISTS = ["NO_ATTRIBUTE_FIELD_EXISTS", "Thuộc tính không tồn tại"];
    const NO_STORE_ADDRESS_EXISTS = ["NO_STORE_ADDRESS_EXISTS", "Địa chỉ store không tồn tại"];
    const NO_ADDRESS_EXISTS = ["NO_ADDRESS_EXISTS", "Địa chỉ không tồn tại"];
    const NO_CART_EXISTS = ["NO_CART_EXISTS",  "Giỏ hàng không tồn tại"];
    const NO_LINE_ITEMS = ["NO_LINE_ITEMS",  "Không có sản phẩm này trong đơn"];
    const NO_LINE_ITEMS_REFUND = ["NO_LINE_ITEMS_REFUND",  "Không có sản phẩm này trong đơn hoàn"];
    const NO_CATEGORY_ID_EXISTS = ["NO_CATEGORY_ID_EXISTS",  "Danh mục không tồn tại"];
    const NO_ATTRIBUTE_ID_EXISTS = ["NO_ATTRIBUTE_ID_EXISTS",  "Thuộc tính không tồn tại"];
    const NO_CHECKIN_LOCATION_EXISTS = ["NO_CHECKIN_LOCATION_EXISTS",  "Vị trí làm việc không tồn tại"];
    const NO_CATEGORY_PARENT_ID_EXISTS = ["NO_CATEGORY_PARENT_ID_EXISTS",  "Danh mục cha không tồn tại"];
    const NO_CATEGORY_CHILD_ID_EXISTS = ["NO_CATEGORY_CHILD_ID_EXISTS",  "Danh mục con không tồn tại"];
    const NO_BANNER_EXISTS = ["NO_BANNER_EXISTS",  "Banner không tồn tại"];
    const NO_REVIEW_EXISTS = ["NO_REVIEW_EXISTS",  "Đánh giá không tồn tại"];
    const NO_HISTORY_SALE_VISIT_AGENCY_EXISTS = ["NO_HISTORY_SALE_VISIT_AGENCY_EXISTS",  "Lịch sử sale tới đại lý không tồn tại"];
    const NO_ORDERS_IN_TIME = ["NO_ORDERS_IN_TIME",  "Không có đơn hàng nào trong tháng này"];
    const NO_BONUS_INSTALLED = ["NO_BONUS_INSTALLED",  "Shop chưa cài đặt phần thưởng"];
    const NO_COLLABORATOR_EXISTS = ["NO_COLLABORATOR_EXISTS",  "Cộng tác viên không tồn tại"];
    const NO_AGENCY_EXISTS = ["NO_AGENCY_EXISTS",  "Đại lý không tồn tại"];
    const NOTHING_TO_COPY = ["NOTHING_TO_COPY",  "Không có gì để copy"];
    const NO_BRANCH_TO = ["NO_BRANCH_TO",  "Không tìm thấy chi nhánh chuyển đến"];
    const NO_LESSON = ["NO_LESSON",  "Bài học không tồn tại"];
    const NO_COURSE = ["NO_COURSE",  "Khóa học không tồn tại"];
    const NO_QUIZ = ["NO_QUIZ",  "Bài thi không tồn tại"];
    const NO_PARENT_CATEGORY_ECOMMERCE = ["NO_PARENT_CATEGORY_ECOMMERCE",  "Danh mục cha không tồn tại"];
    const NO_QUESTION = ["NO_QUESTION",  "Câu hỏi không tồn tại"];
    const NO_TRAIN_CHAPTER = ["NO_TRAIN_CHAPTER",  "Chương học không tồn tại"];
    const NO_SHIFT_EXISTS = ["NO_SHIFT_EXISTS",  "Ca làm việc không tồn tại"];
    const NO_WAREHOUSE_EXISTS = ["NO_WAREHOUSE_EXISTS",  "Kho không tồn tại"];
    const NO_CHECKIN_CHECKOUT_HISTORY_EXISTS = ["NO_CHECKIN_CHECKOUT_HISTORY_EXISTS",  "Lịch sử chấm công không tồn tại"];
    const NO_CUSTOMER_EXISTS = ["NO_CUSTOMER_EXISTS",  "Khách hàng không tồn tại"];
    const NO_CUSTOMER_SALE_EXISTS = ["NO_CUSTOMER_SALE_EXISTS",  "Nhân viên bán hàng không tồn tại"];
    const NO_FRIEND_EXISTS = ["NO_FRIEND_EXISTS",  "Không có bạn bè này"];
    const NO_REQUEST_FRIEND_EXISTS = ["NO_REQUEST_FRIEND_EXISTS",  "Yêu cầu kết bạn không tồn tại"];
    const NO_REQUEST_EXISTS = ["NO_REQUEST_EXISTS",  "Yêu cầu  không tồn tại"];
    const NO_SPIN_WHEEL_EXISTS = ["NO_SPIN_WHEEL_EXISTS",  "Mini game vòng quay không tồn tại"];
    const NO_GUESS_NUMBER_EXISTS = ["NO_GUESS_NUMBER_EXISTS",  "Mini game đoán số không tồn tại"];
    const NO_GUESS_NUMBER_RESULT_EXISTS = ["NO_GUESS_NUMBER_RESULT_EXISTS",  "Kết quả mini game đoán số không tồn tại"];
    const NO_GIFT_SPIN_WHEEL_EXISTS = ["NO_GIFT_SPIN_WHEEL_EXISTS",  "Quà mini game vòng quay không tồn tại"];
    const NO_SHIFT_TODAY = ["NO_SHIFT_TODAY",  "Không có ca làm việc nào hôm nay"];
    const NOT_CONFIGURED_KEY_CAN_NOT_SEND_NOTIFICATION = ["NOT_CONFIGURED_KEY_CAN_NOT_SEND_NOTIFICATION", "Chưa cấu hình key không thể gửi thông báo"];
    const NOT_ENOUGH_USE_VOUCHER = ["NOT_ENOUGH_USE_VOUCHER", "Chưa đủ điều kiện dùng voucher"];
    const NO_ADDRESS_SELECTED = ["NO_ADDRESS_SELECTED", "Chưa chọn địa chỉ"];
    const NOT_FULLY_PAID_CAN_NOT_BE_COMPLETED = ["NOT_FULLY_PAID_CAN_NOT_BE_COMPLETED", "Chưa thanh toán đầy đủ không thể hoàn thành đơn"];
    const NOT_REGISTERED_COLLABORATOR = ["NOT_REGISTERED_COLLABORATOR", "Chưa đăng ký cộng tác viên"];
    const NOT_REGISTERED_AGENCY = ["NOT_REGISTERED_AGENCY", "Chưa đăng ký cộng tác viên"];
    const NAME_IS_REQUIRED = ["NAME_IS_REQUIRED", "Tên không được trống"];
    const ONLY_EMPLOYEE = ["ONLY_EMPLOYEE", "Chỉ nhân viên sale mới làm được điều này"];
    const ONLY_EMPLOYEES_CAN_ACCESS = ["ONLY_EMPLOYEES_CAN_ACCESS", "Chỉ nhân viên mới được truy cập"];
    const USERNAME_IS_REQUIRED = ["USERNAME_IS_REQUIRED", "Tên tài khoản không được trống"];
    const USERNAME_ALREADY_EXISTS = ["USERNAME_ALREADY_EXISTS",  "Tên tài khoản đã tồn tại"];
    const PASSWORD_IS_REQUIRED = ["PASSWORD_IS_REQUIRED", "Mật khẩu không được trống"];
    const NAME_ALREADY_EXISTS = ["NAME_ALREADY_EXISTS", "Tên đã tồn tại"];
    const NO_RESTOCKING_IS_NON_REFUNDABLE = ["NO_RESTOCKING_IS_NON_REFUNDABLE", "Chưa nhập kho không thể hoàn trả"];
    const ORDER_HAS_NOT_SELECTED_PARTNER = ["ORDER_HAS_NOT_SELECTED_PARTNER", "Đơn hàng chưa chọn phương thức giao vận"];
    const ORDER_HAS_BEEN_CANCELED_BEFORE = ["ORDER_HAS_BEEN_CANCELED_BEFORE", "Đơn hàng đã được hủy trước đó"];
    const ORDER_STATUS_IS_REQUIRED = ["ORDER_STATUS_IS_REQUIRED", "Trạng thái đơn hàng không được trống"];
    const ORDER_CODE_IS_REQUIRED = ["ORDER_CODE_IS_REQUIRED", "Mã đơn hàng không được trống"];
    const ORDER_HAS_PAID = ['ORDER_HAS_PAID', "Đơn hàng đã thanh toán"];
    const PAYMENT_STATUS_IS_REQUIRED = ["PAYMENT_STATUS_IS_REQUIRED", "Trạng thái thanh toán không được trống"];
    const PAYMENT_METHOD_ID_IS_REQUIRED = ["PAYMENT_METHOD_ID_IS_REQUIRED", "Phương thức thanh toán không được trống"];
    const PASSWORD_NOT_LESS_THAN_6_CHARACTERS = ["PASSWORD_NOT_LESS_THAN_6_CHARACTERS", "Mật khẩu phải lớn hơn 6 ký tự"];
    const PAYMENT_AMOUNT_CANNOT_BE_GREATER_THAN_THE_REMAINING_AMOUNT = ["PAYMENT_AMOUNT_CANNOT_BE_GREATER_THAN_THE_REMAINING_AMOUNT", 'Số tiền thanh toán không thể lớn hơn số tiền còn lại'];
    const PAYMENT_AMOUNT_CANNOT_GREATER_THAN_AMOUNT_PAID = ["PAYMENT_AMOUNT_CANNOT_GREATER_THAN_AMOUNT_PAID", "Số tiền thanh toán không thể lớn hơn số tiền đã thanh toán"];
    const START_TIME_IS_REQUIRED = ["START_TIME_IS_REQUIRED", "Thời gian bắt đầu là bắt buộc"];
    const START_WITH = ["START_WITH", "Tên tài khoản phải bắt đầu bằng"];
    const STAFF_CANNOT_CREATE_STORE = ["STAFF_CANNOT_CREATE_STORE", "Nhân viên không thể tạo cửa hàng"];
    const STORE_HAS_NOT_SET_PICKUP_ADDRESS = ["STORE_HAS_NOT_SET_PICKUP_ADDRESS", "Cửa hàng chưa cài đặt địa chỉ lấy hàng"];
    const STORE_HAS_NOT_CONFIGURED = ["STORE_HAS_NOT_CONFIGURED", "Cửa hàng chưa thiết lập cộng tác viên"];
    const STORE_HAS_NOT_AGENCY_CONFIGURED = ["STORE_HAS_NOT_AGENCY_CONFIGURED", "Cửa hàng chưa thiết lập đại lý"];
    const STORE_NOT_ALLOW_YOU_TO_MAKE_PAYMENTS = ["STORE_NOT_ALLOW_YOU_TO_MAKE_PAYMENTS", "Cửa hàng không cho phép gửi yêu cầu thanh toán"];
    const SUB_DISTRIBUTE_NAME_IS_REQUIRED = ["SUB_DISTRIBUTE_NAME_IS_REQUIRED", "Tên phân loại phụ không được để trống"];
    const END_TIME_IS_REQUIRED = ["END_TIME_IS_REQUIRED", "Thời gian kết thúc là bắt buộc"];
    const END_TIME_GREATE_START_TIME_IS_REQUIRED = ["END_TIME_GREATE_START_TIME_IS_REQUIRED", "Thời gian kết thúc phải lớn hơn thời gian bắt đầu"];
    const ELEMENT_DISTRIBUTE_EXISTS = ["ELEMENT_DISTRIBUTE_EXISTS", "Phân loại đã tồn tại"];
    const EXCEED_THE_QUANTITY_IN_STOCK = ["EXCEED_THE_QUANTITY_IN_STOCK", "Vượt quá số lượng trong kho"];
    const VALUE_IS_REQUIRED = ["VALUE_IS_REQUIRED", "Giá trị giảm là bắt buộc"];
    const VALUE_PREDICT_IS_REQUIRED = ["VALUE_PREDICT_IS_REQUIRED", "Giá trị dự đoán là bắt buộc"];
    const UNABLE_TO_CONNECT_THE_CARRIER = ["UNABLE_TO_CONNECT_THE_CARRIER", "Chưa thể kết nối giao vận này"];
    const UNABLE_TO_FIND_THE_UPLOAD_IMAGE = ["UNABLE_TO_FIND_THE_UPLOAD_IMAGE", "Không tìm thấy ảnh up lên"];
    const UNABLE_TO_FIND_THE_UPLOAD_VIDEO = ["UNABLE_TO_FIND_THE_UPLOAD_VIDEO", "Không tìm thấy video up lên"];
    const UNFINISHED_OR_UNPAID_ORDER = ["UNFINISHED_OR_UNPAID_ORDER", "Đơn hàng chưa hoàn thành hoặc chưa thanh toán"];
    const INVALID_LIST_PRODUCT_ID  = ["INVALID_LIST_PRODUCT_ID",  "Danh sách mã sản phẩm không hợp lệ"];
    const INVALID_LIST_GIFT_SPIN_WHEEL  = ["INVALID_LIST_GIFT_SPIN_WHEEL",  "Danh sách quà không hợp lệ"];
    const INVALID_LIST_GUESS_NUMBER_RESULT  = ["INVALID_LIST_GUESS_NUMBER_RESULT",  "Danh sách kết quả game không hợp lệ"];
    const INVALID_STATUS_MINI_GAME  = ["INVALID_STATUS_MINI_GAME",  "Mã trạng thái mini game không hợp lệ"];
    const INVALID_LIST_GUESS_NUMBER_GIFT  = ["INVALID_LIST_GUESS_NUMBER_GIFT",  "Danh sách phần thưởng mini game không hợp lệ"];
    const INVALID_LIST_ID_GROUP_CUSTOMER  = ["INVALID_LIST_ID_GROUP_CUSTOMER",  "Danh sách mã nhóm khách hàng không hợp lệ"];
    const INVALID_GROUP_CUSTOMER_ID  = ["INVALID_GROUP_CUSTOMER_ID",  "Mã nhóm khách hàng không hợp lệ"];
    const INVALID_TYPE_APPLY_FOR  = ["INVALID_TYPE_APPLY_FOR",  "Loại áp dụng không hợp lệ"];
    const INVALID_OPTION_GAME  = ["INVALID_OPTION_GAME",  "Tùy chọn không hợp lệ"];
    const INVALID_LIST_IMAGE  = ["INVALID_LIST_IMAGE",  "Danh sách ảnh không hợp lệ"];
    const INVALID_PRODUCT_ID  = ["INVALID_PRODUCT_ID",  "Sản phẩm không hợp lệ  "];
    const INVALID_LINE_ITEM  = ["INVALID_LINE_ITEM", "Một số sản phẩm trong hóa đơn không hợp lệ"];
    const INVALID_LINE_ITEM_QUANTITY  = ["INVALID_LINE_ITEM_QUANTITY", "Một số sản phẩm số lượng không hợp lệ"];
    const INVALID_PRODUCT_ITEM  = ["INVALID_PRODUCT_ITEM", "Một số sản phẩm không hợp lệ"];
    const INVALID_TIME  = ["INVALID_TIME", "Thời gian không hợp lệ"];
    const INVALID_TIME_START_AND_END  = ["INVALID_TIME_START_AND_END", "Thời gian bắt đầu và kết thúc không hợp lệ"];
    const INVALID_DATA_BOOLEAN  = ["INVALID_DATA_BOOLEAN", "Dữ liệu boolean không hợp lệ"];
    const INVALID_DATA  = ["INVALID_DATA", "Dữ liệu không hợp lệ"];
    const INVALID_OLD_PASSWORD  = ["INVALID_OLD_PASSWORD", "Mật khẩu cũ không đúng"];
    const INVALID_ORDER_STATUS_CODE  = ["INVALID_ORDER_STATUS_CODE", "Trạng thái đơn hàng không hợp lệ"];
    const INVALID_PAYMENT_STATUS_CODE  = ["INVALID_PAYMENT_STATUS_CODE", "Trạng thái thanh toán không hợp lệ"];
    const INVALID_TOKEN  = ["INVALID_TOKEN", "Token không hợp lệ"];
    const INVALID_EMAIL  = ["INVALID_EMAIL", "Email không hợp lệ"];
    const INVALID_OTP  = ["INVALID_OTP ", "Mã OTP không hợp lệ"];
    const INVALID_PERCENT  = ["INVALID_PERCENT ", "Phần trăm không hợp lệ"];
    const INVALID_REFERRAL_PHONE_NUMBER  = ["INVALID_REFERRAL_PHONE_NUMBER", "Số điện thoại giới thiệu không hợp lệ"];
    const INVALID_PHONE_NUMBER  = ["INVALID_PHONE_NUMBER", "Số điện thoại không hợp lệ"];
    const INVALID_FIELD  = ["INVALID_FIELD", "Một số trường bị để trống"];
    const INVALID_ADDRESS  = ["INVALID_ADDRESS", "Địa chỉ không hợp lệ"];
    const INVALID_LOCATION_CHECKIN  = ["INVALID_LOCATION_CHECKIN", "Địa chỉ làm việc không đúng hãy chọn làm việc từ xa"];
    const INVALID_MOBILE_CHECKIN  = ["INVALID_MOBILE_CHECKIN", "Điện thoại chấm công chưa đúng"];
    const INVALID_PROVINCE  = ["INVALID_PROVINCE", "Tỉnh/TP không hợp lệ"];
    const INVALID_PRICE  = ["INVALID_PRICE", "Giá không hợp lệ"];
    const INVALID_COD  = ["INVALID_COD", "COD không hợp lệ"];
    const INVALID_DISTRICT  = ["INVALID_DISTRICT", "Quận/Huyện không hợp lệ"];
    const INVALID_WARDS  = ["INVALID_WARDS", "Phường/Xã không hợp lệ"];
    const INVALID_YEAR  = ["INVALID_YEAR", "Năm không hợp lệ"];
    const INVALID_LIST  = ["INVALID_LIST", "Danh sách không hợp lệ"];
    const INVALID_MONTH  = ["INVALID_MONTH", "Tháng không hợp lệ"];
    const INVALID_DAY  = ["INVALID_DAY", "Ngày không hợp lệ"];
    const INVALID_ATTRIBUTE_FIELDS  = ["INVALID_ATTRIBUTE_FIELD", "Danh sách thuộc tính không hợp lệ"];
    const INVALID_PERCENT_GIFT_OF_MINI_GAME = ["INVALID_PERCENT_GIFT_OF_MINI_GAME", "Phần trăm trúng thưởng món quà không hợp lệ"];
    const INVALID_TYPE_FROM_TURN_MINI_GAME = ["INVALID_TYPE_FROM_TURN_MINI_GAME", "Loại lượt chơi mini game không hợp lệ"];
    const IMAGE_URL_IS_REQUIRED = ["IMAGE_URL_IS_REQUIRED", "Ảnh không được để trống"];
    const INCOMPLETE_ORDERS_NON_REFUNDABLE = ["INCOMPLETE_ORDERS_NON_REFUNDABLE", "Đơn chưa hoàn thành không thể hoàn trả"];
    const TITLE_IS_REQUIRED = ["TITLE_IS_REQUIRED", "Tiêu đề không được để trống"];
    const TITLE_ALREADY_EXISTS = ["TITLE_ALREADY_EXISTS", "Tiêu đề đã tồn tại"];
    const PRODUCT_NAME_ALREADY_EXISTS = ["PRODUCT_NAME_ALREADY_EXISTS", "Tên sản phẩm đã tồn tại"];
    const PROCESSED_ORDERS_CANNOT_BE_CANCELED = ["PROCESSED_ORDERS_CANNOT_BE_CANCELED", "Đơn hàng đã được xử lý không thể hủy"];
    const PRODUCT_EXIS_IN_DISCOUNT = ["PRODUCT_EXIS_IN_DISCOUNT", "Một số sản phẩm đã tồn tại trong chương trình giảm giá khác"];
    const PRODUCT_EXIS_IN_VOUCHER = ["PRODUCT_EXIS_IN_VOUCHER", "Một số sản phẩm đã tồn tại trong mã giảm giá khác"];
    const PRODUCT_EXIS_IN_COMBO = ["PRODUCT_EXIS_IN_COMBO", "Các sản phẩm đã tồn tại trong combo khác"];
    const PRODUCT_EXIS_IN_BONUS_PRODUCT = ["PRODUCT_EXIS_IN_BONUS_PRODUCT", "Một số sản phẩm đã tồn tại trong tặng thưởng khác"];
    const PRODUCT_NOT_EXIST_IN_ORDER = ["PRODUCT_NOT_EXIST_IN_ORDER", "Sản phẩm không tồn tại trong hóa đơn"];
    const PHONE_NUMBER_ALREADY_EXISTS = ["PHONE_NUMBER_ALREADY_EXISTS",  "Số điện thoại đã tồn tại"];
    const PAYMENT_AMOUNT_CANNOT_GREATER_THAN_DEBT = ["PAYMENT_AMOUNT_CANNOT_GREATER_THAN_DEBT", "Số tiền thanh toán không thể lớn hơn nợ hiện tại của khách hàng"];
    const PAYMENT_AMOUNT_CANNOT_GREATER_THAN_ORDER_DEBT = ["PAYMENT_AMOUNT_CANNOT_GREATER_THAN_ORDER_DEBT", "Số tiền thanh toán không thể lớn hơn nợ tất cả đơn hàng hiện tại"];
    const TOKEN_IS_REQUIRED = ["TOKEN_IS_REQUIRED",  "Bạn chưa nhập token"];
    const TEXT_IS_REQUIRED = ["TEXT_IS_REQUIRED", "Nội dung tìm kiếm là bắt buộc"];
    const THIS_IS_THE_ONLY_PAYMENT_METHOD = ["THIS_IS_THE_ONLY_PAYMENT_METHOD", "Đây là phương thức thanh toán duy nhất không thể thay đổi trạng thái"];
    const VERSION_NOT_FOUND_TO_UPDATE =  ["VERSION_NOT_FOUND_TO_UPDATE", "Không tìm thấy phiên bản để cập nhật"];
    const VERSION_NOT_FOUND_TALLY_SHEET =  ["VERSION_NOT_FOUND_TALLY_SHEET", "Không tìm thấy phiên bản để kiểm kho"];
    const VOUCHERS_ARE_SOLD_OUT = ["VOUCHERS_ARE_SOLD_OUT", "Số lượng sử dụng voucher đã hết"];
    const VISITORS_CANNOT_EDIT_OR_DELETE =  ["VISITORS_CANNOT_EDIT_OR_DELETE", "Khách vãng lai không thể xóa hoặc chỉnh sửa"];
    const VERSION_NOT_FOUND_IMPORT_STOCK =  ["VERSION_NOT_FOUND_IMPORT_STOCK", "Không tìm thấy phân loại sản phẩm để nhập kho"];
    const VERSION_NOT_FOUND_TRANSFER_STOCK =  ["VERSION_NOT_FOUND_TRANSFER_STOCK", "Không tìm thấy phân loại sản phẩm để chuyển kho"];
    const VERSION_NOT_FOUND_BONUS_PRODUCT =  ["VERSION_NOT_FOUND_BONUS_PRODUCT", "Không tìm thấy phân loại sản phẩm để tặng thưởng"];
    const VERSION_NOT_FOUND_PRODUCT =  ["VERSION_NOT_FOUND_PRODUCT", "Không tìm thấy phân loại sản phẩm"];
    const RECEIVED_MONTH_BONUS = ["RECEIVED_MONTH_BONUS", "Bạn đã nhận thưởng tháng này"];
    const REFUND_AMOUNT_CANNOT_GREATER_THAN_CURRENT_QUANTITY = ["REFUND_AMOUNT_CANNOT_GREATER_THAN_CURRENT_QUANTITY", "Số lượng hoàn trả không thể lớn hơn số lượng hiện tại"];
    const REASON_IS_REQUIRED = ['REASON_IS_REQUIRED', "Chưa có lý do làm từ xa"];
    const SUCCESS = ["SUCCESS", "THÀNH CÔNG"];
    const SUB_ELEMENT_DISTRIBUTE_EXISTS = ["SUB_ELEMENT_DISTRIBUTE_EXISTS", "Phân loại phụ đã tồn tại"];
    const PRODUCT_SKU_ALREADY_EXISTS = ["PRODUCT_SKU_ALREADY_EXISTS", "Mã SKU sản phẩm đã tồn tại"];
    const PRODUCT_BARCODE_ALREADY_EXISTS = ["PRODUCT_BARCODE_ALREADY_EXISTS", "Mã barcode đã tồn tại ở sản phẩm khác"];
    const REFUNDED = ["REFUNDED", "Đơn này đã hoàn tiền"];
    const YOU_RATED = ["YOU_RATED", "Bạn đã đánh giá"];
    const WRONG_ACCOUNT_OR_PASSWORD = ["WRONG_ACCOUNT_OR_PASSWORD",  "Sai tài khoản hoặc mật khẩu"];
    const YOU_HAVE_JOINED_THIS_GAME = ["YOU_HAVE_JOINED_THIS_GAME",  "Bạn đã tham gia trò chơi này"];
    const YOU_UNABLE_JOIN_THIS_GAME = ["YOU_UNABLE_JOIN_THIS_GAME",  "Bạn không thể tham gia trò chơi này"];
    const YOU_HAVE_NOT_JOIN_THIS_GAME = ["YOU_HAVE_NOT_JOIN_THIS_GAME",  "Bạn chưa tham gia trò chơi này"];
    const YOU_HAVE_GOT_TURN_PER_DAY_OF_TODAY = ["YOU_HAVE_GOT_TURN_PER_DAY_OF_TODAY",  "Bạn đã lấy lượt chơi của ngày hôm nay"];
    const STAFF_HAVE_CHECKIN_AT_THIS_AGENCY = ["STAFF_HAVE_CHECKIN_AT_THIS_AGENCY", "Nhân viên đã checkin ở đại lý"];
    const YOU_HAVE_NO_TURN_PLAY_GAME = ["YOU_HAVE_NO_TURN_PLAY_GAME",  "Bạn đã hết lượt chơi"];
    const GIFT_HAS_RUN_OUT = ["GIFT_HAS_RUN_OUT",  "Quà tặng đã hết"];
    const DOES_NOT_EXIST_OTP_UNIT = ["DOES_NOT_EXIST_OTP_UNIT", "Không tồn tại đơn vị mặc định"];
    const DOES_NOT_CHANGE_OTP_UNIT = ["DOES_NOT_CHANGE_OTP_UNIT", "Không thay đổi được đơn vị mặc định"];
    const DATA_TYPE_INVALID = ["DATA_TYPE_INVALID", "Kiểu dữ liệu không hợp lệ"];
    const DATA_TYPE_OTP_UNIT_INVALID = ["DATA_TYPE_OTP_UNIT_INVALID", "Nhập đầy đủ đơn vị gửi, token và nội dung"];
    const DOES_NOT_EXIST_OTP_CONFIG = ["DOES_NOT_EXIST_OTP_CONFIG", "Không tồn tại cài đặt otp"];
    const VOUCHER_ONLY_APPLIED_ONCE = ["VOUCHER_ONLY_APPLIED_ONCE", "Voucher chỉ được áp dụng một lần"];
    const PRODUCT_BONUS_END_EXISTS = ["PRODUCT_BONUS_END_EXISTS", "Chương trình thưởng sản phẩm đã kết thúc"];
    const NO_GROUP_PRODUCT_EXISTS = ["NO_GROUP_PRODUCT_EXISTS", "Không tìm thấy nhóm sản phẩm thưởng"];
    const DELETE_GROUP_PRODUCT_EXISTS = ["DELETE_GROUP_PRODUCT_EXISTS", "Xóa nhóm sản phẩm thưởng thành công"];
    const AMOUNT_VOUCHER_CAN_USE_REQUIRED = ["AMOUNT_VOUCHER_CAN_USE_REQUIRED", "Nhập số lượng voucher có thể sử dụng"];
    const VOUCHER_CODE_LENGTH_REQUIRED = ["VOUCHER_CODE_LENGTH_REQUIRED", "Nhập độ dài mã voucher"];
    const STARTING_CHARACTER__REQUIRED = ["STARTING_CHARACTER__REQUIRED", "Nhập ký tự bắt đầu mã voucher"];
    const VOUCHER_CODE_LENGTH_INVALID = ["VOUCHER_CODE_LENGTH_INVALID", "Độ dài mã voucher không hợp lệ"];
    const STARTING_CHARACTER__EXISTS = ["STARTING_CHARACTER__EXISTS", "ký tự bắt đầu mã voucher đã tồn tại"];
    const NUMBER_VOUCHER_CODE_CAN_GENERATE_NOT_ENOUGH = ["NUMBER_VOUCHER_CODE_CAN_GENERATE_NOT_ENOUGH", "Số mã voucher có thể tạo ra không đủ với độ dài mã đó"];
    const NUMBER_VOUCHER_CODE_INVALID = ["NUMBER_VOUCHER_CODE_INVALID", "Mã voucher không hợp lệ"];
}
