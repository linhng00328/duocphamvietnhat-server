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
            <th>Người sử dụng</th>
            <th>Ngày phát hành</th>
            <th>Ngày hết hạn</th>
            <th>Ngày sử dụng</th>
            <th>Trạng thái</th>
        </tr>
        </thead>
        <tbody>
        @foreach($voucher_codes as $voucher_code)
            <tr>
                <td>{{ $voucher_code->code }}</td>
                <td>{{ $voucher_code->customer ? $voucher_code->customer->name : ''}}</td>
                <td>{{ $voucher_code->start_time }}</td>
                <td>{{ $voucher_code->end_time }}</td>
                <td>{{ $voucher_code->use_time }}</td>
                <td>
                    @if($voucher_code->status === 0)
                        Đã phát hành
                    @elseif($voucher_code->status === 1)
                        Đã sử dụng
                    @elseif($voucher_code->status === 2)
                        Kết thúc
                    @else
                        Đã phát hành
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>

