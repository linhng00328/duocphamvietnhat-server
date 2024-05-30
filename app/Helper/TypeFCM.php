<?php

namespace App\Helper;

class TypeFCM
{
    const NEW_ORDER = "NEW_ORDER"; //Đơn hàng mới
    const REFUND_ORDER = "REFUND_ORDER"; //Đơn hàng mới
    const NEW_PERIODIC_SETTLEMENT = "NEW_PERIODIC_SETTLEMENT"; //Quyết toán định kỳ
    const NEW_POST = "NEW_POST"; //Bai viet mới
    const NEW_POST_COMMUNITY = "NEW_POST_COMMUNITY"; //Bài viết cộng đồng mới
    const SEND_ALL = "SEND_ALL";
    const CTV_SUCCESS = "CTV_SUCCESS"; //duyệt ctv
    const ORDER_STATUS = "ORDER_STATUS"; //Thay doi order
    const NEW_MESSAGE = "NEW_MESSAGE"; //Tin nhắn mới
    const GET_CUSTOMER = "GET_CUSTOMER"; //Khách hàng
    const GET_CTV = "GET_CTV"; //Yêu cầu CTV
    const GET_COMMISSION = "GET_COMMISSION"; //Nhận hoa hồng
    const REFUND_POINT = "REFUND_POINT"; //Hoàn xu
    const REFUND_COMMISSION = "REFUND_COMMISSION"; //Hoàn hoa hồng
    const GET_AGENCY = "GET_AGENCY"; //Yêu cầu CTV
    const REQUEST_PAY_AGENCY = "REQUEST_PAY_AGENCY"; //Yêu cầu thanh toán Đại lý
    const REQUEST_PAY_CTV = "REQUEST_PAY_CTV"; //Yêu cầu  thanh toán CTV
    const CANCEL_CTV = "CANCEL_CTV"; //hủy yêu cầu CTV
    const CANCEL_AGENCY = "CANCEL_AGENCY"; //hủy yêu cầu Đại lý
    const CANCEL_CTV_PAY = "CANCEL_CTV_PAY"; //hủy yêu cầu thanh toán CTV
    const NEAR_OUT_STOCK = "NEAR_OUT_STOCK"; //Sản phẩm sắp hết hàng
    const COUNT_ORDER_END_DAY = "COUNT_ORDER_END_DAY"; //Tính số lượng đơn rồi thông báo cuối ngày
    const GOOD_NIGHT_USER = "GOOD_NIGHT_USER"; //Chúc user
    const CUSTOMER_CANCELLED_ORDER = "CUSTOMER_CANCELLED_ORDER"; //Khách đã hủy đơn hàng
    const CUSTOMER_PAID = "CUSTOMER_PAID"; //Khách đã thanh toán
    const TO_ADMIN = "TO_ADMIN"; //gửi đến admin

    const NEW_COMMENT_BUY = "NEW_COMMENT_BUY"; //Comment mua
    const NEW_COMMENT_SELL = "NEW_COMMENT_SELL"; //Comment bán
    const NEW_COMMENT_POST = "NEW_COMMENT_POST"; //Comment bài viết

    const NEW_COMMUNITY_USER = "NEW_COMMUNITY_USER"; //Comment bán
    const NEW_COMMUNITY_BUY = "NEW_COMMUNITY_BUY"; //Bán đăng mua
    const NEW_COMMUNITY_SELL = "NEW_COMMUNITY_SELL"; //Bán đăng bán
    const NEW_REVIEW_PRODUCT = "NEW_REVIEW_PRODUCT"; //Comment bán
    const NEW_CUSTOMER_SALE = "NEW_CUSTOMER_SALE"; //Thêm khách hàng mới cho sale

    const TYPE_RESULT_MINI_GAME_GUESS_NUMBER = "TYPE_RESULT_MINI_GAME_GUESS_NUMBER"; //nhập kết quả mini game đoán số
    const CUSTOMER_WIN_MINI_GAME_GUESS_NUMBER = "CUSTOMER_WIN_MINI_GAME_GUESS_NUMBER"; // gửi tới khách hàng trúng thưởng
}
