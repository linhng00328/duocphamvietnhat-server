<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table>
        <thead>
        <tr>
            <th>Mã</th>
            <th>Loại phiếu</th>
            <th>Số tiền thay đổi</th>
            <th>Số nợ hiện tại</th>
            <th>Hình thức thanh toán</th>
            <th>Loại phiếu thu</th>
            <th>Nhóm người nộp</th>
            <th>Người nộp</th>
            <th>Cho phép tính toán vào báo cáo tài chính lỗ lãi</th>
            <th>Mô tả</th>
            <th>Nhân viên</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
        </tr>
        </thead>
        <tbody>
        @foreach($revenue_expenditures as $revenue_expenditure)
            <tr>
                <td>{{ $revenue_expenditure->code }}</td>
                <td>{{ $revenue_expenditure->is_revenue === true ? 'Phiếu thu' : 'Phiếu chi' }}</td>
                <td>{{ $revenue_expenditure->change_money }}</td>
                <td>{{ $revenue_expenditure->current_money }}</td>
                <td>
                    @if ($revenue_expenditure->payment_method === 1)
                        Quẹt thẻ
                    @elseif ($revenue_expenditure->payment_method === 2)
                        Cod
                    @elseif ($revenue_expenditure->payment_method === 3)
                        Chuyển khoản
                    @else
                        Tiền mặt
                    @endif
                </td>
                <td>
                    @if ($revenue_expenditure->type === 0)
                        Thanh toán cho đơn hàng
                    @elseif ($revenue_expenditure->type === 1)
                        Thu nhập khác
                    @elseif ($revenue_expenditure->type === 2)
                        Tiền thưởng
                    @elseif ($revenue_expenditure->type === 3)
                        Khởi tạo kho
                    @elseif ($revenue_expenditure->type === 4)
                        Cho thuê tài sản
                    @elseif ($revenue_expenditure->type === 5)
                        Nhượng bán thanh lý tài sản
                    @elseif ($revenue_expenditure->type === 6)
                        Thu nợ khách hàng
                    @elseif ($revenue_expenditure->type === 10)
                        Chi phí khác
                    @elseif ($revenue_expenditure->type === 17)
                        Thanh toán cho đơn nhập hàng
                    @elseif ($revenue_expenditure->type === 11)
                        Chi phí sản phẩm
                    @elseif ($revenue_expenditure->type === 12)
                        Chi phí nguyên vật liệu
                    @elseif ($revenue_expenditure->type === 13)
                        Chi phí sinh hoạt
                    @elseif ($revenue_expenditure->type === 14)
                        Chi phí nhân công
                    @elseif ($revenue_expenditure->type === 15)
                        Chi phí bán hàng
                    @elseif ($revenue_expenditure->type === 16)
                        Chi phí quản lý cửa hàng
                    @endif
                </td>
                <td>
                    @if($revenue_expenditure->recipient_group === 0)
                        Nhóm khách hàng
                    @elseif($revenue_expenditure->recipient_group === 1)
                        Nhóm nhà cung cấp
                    @elseif($revenue_expenditure->recipient_group === 2)
                        Nhóm nhân viên
                    @elseif($revenue_expenditure->recipient_group === 3)
                        Đối tượng khác
                    @else
                        Nhóm khách hàng
                    @endif
                </td>
                <td>
                    @if($revenue_expenditure->recipient_group === 0)
                        {{ $revenue_expenditure->customer ? $revenue_expenditure->customer->name : '' }}
                    @elseif($revenue_expenditure->recipient_group === 1)
                        {{ $revenue_expenditure->supplier ? $revenue_expenditure->supplier->name : '' }}
                    @elseif($revenue_expenditure->recipient_group === 2)
                        {{ $revenue_expenditure->staff ? $revenue_expenditure->staff->name : '' }}
                    @elseif($revenue_expenditure->recipient_group === 3)      
                    @else
                        {{ $revenue_expenditure->customer ? $revenue_expenditure->customer->name : '' }}
                    @endif
                </td>
                <td>{{ $revenue_expenditure->allow_accounting === true ? 'Cho phép' : 'Không cho phép' }}</td>
                <td>{{ $revenue_expenditure->description }}</td>
                <td>{{ $revenue_expenditure->user ? $revenue_expenditure->user->name : ''}}</td>
                <td>{{ $revenue_expenditure->created_at }}</td>
                <td>{{ $revenue_expenditure->type_action_name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>

