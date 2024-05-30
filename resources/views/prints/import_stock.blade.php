<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Đơn nhập hàng {{ $import_stock->code }} - IKITECH.VN</title>
    <style>
        body{
            margin: 0;
        }
        .print{
            margin: 20px 0;
            border: 1px solid #000000;
        }
        .print_order_code{
            padding: 5px 10px;
        }
        .print_info_payment{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding: 5px 10px;
            border-top: 1px dotted #000000;
            border-bottom: 1px dotted #000000;
            column-gap: 10px;
        }
        .print_info_payment > div:first-child{
            border-right: 1px dotted #000000;
        }
        .print_current_date{
            display: flex;
            flex-direction: column;
            align-items: center;
            row-gap: 8px;
            padding: 10px 0;
            border-bottom: 1px dotted #000000;
        }
        .print_current_date > div:first-child{
            font-size: 18px;
        }
        .print_current_date > div:last-child{
            font-size: 14px;
        }
        .print_product{
            padding: 10px;
            border-bottom: 1px dotted #000000;
        }
        .print_product > div{
            font-size: 18px;
            margin-bottom: 10px;
        }
        .print_total{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding: 10px;
            column-gap: 20px;
            
        }
        .print_total > div:first-child{
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            justify-content: space-between;
        }
        .print_total > div:last-child{
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px dotted #000000;
        }
        .print_total > div:last-child > p:first-child{
            margin-bottom: 2px;
            font-size: 18px;
            font-weight: 500;
        }
        .print_total > div:last-child > p:last-child{
            margin: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table td, table th {
            border: 1px dotted #000;
            padding: 10px;
        }
        p{
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="print">
        <div class="print_order_code">Mã đơn nhập hàng: {{ $import_stock->code }}</div>
        <div class="print_info_payment">
            <div>
                <p>
                    <span>Tên nhà cung cấp: </span>
                    <span>
                        {{ $import_stock->supplier ? $import_stock->supplier->name : null }}
                    </span>
                </p> 
                <p>
                    <span>Số điện thoại: </span>
                    <span>
                        {{ $import_stock->supplier ? $import_stock->supplier->phone : null }}
                    </span>
                </p> 
                <p>
                    <span>Địa chỉ: </span>
                    <span>
                        {{ $import_stock->supplier && $import_stock->supplier->wards_name ? $import_stock->supplier->wards_name : null }}
                        {{ $import_stock->supplier && $import_stock->supplier->district_name ? ', ' . $import_stock->supplier->district_name : null }}
                        {{ $import_stock->supplier && $import_stock->supplier->province_name ? ', ' . $import_stock->supplier->province_name : null }}
                    </span>
                </p> 
                <p>
                    <span>Người nhập hàng: </span>
                    <span>
                        {{ $import_stock->user ? $import_stock->user->name : null }}
                    </span>
                </p> 
                <p>
                    <span>Chi nhánh: </span>
                    <span>
                        {{ $import_stock->branch ? $import_stock->branch->name : null }}
                    </span>
                </p> 
            </div>
            <div>
                <p>
                    <span>Tiền hàng: </span>
                    <span>
                        {{ $import_stock->total_amount ? number_format($import_stock->total_amount, 0, ',', '.') . '₫': '0₫' }}
                    </span>
                </p>                 
                <p>
                    <span>Chiết khấu: </span>
                    <span>
                        {{ $import_stock->discount ? number_format($import_stock->discount, 0, ',', '.') . '₫' : '0₫' }}
                    </span>
                </p>                 
                <p>
                    <span>Chi phí nhập hàng: </span>
                    <span>
                        {{ $import_stock->cost ? number_format($import_stock->cost, 0, ',', '.') . '₫' : '0₫' }}
                    </span>
                </p>                 
                <p>
                    <span>Tổng tiền: </span>
                    <span>
                        {{ $import_stock->total_final ? number_format($import_stock->total_final, 0, ',', '.') . '₫' : '0₫' }}
                    </span>
                </p>                 
                <p>
                    <span>Thanh toán: </span>
                    <span>
                        {{ $import_stock->total_payment ? number_format($import_stock->total_payment, 0, ',', '.') . '₫' : '0₫' }}
                    </span>
                </p> 
            </div>
        </div>
        <div class="print_current_date">
            <div>Ngày đặt hàng</div>
            <div>{{ $import_stock->created_at }}</div>
        </div>
        <div class="print_product">
            <div>
                Ghi chú: {{ $import_stock->note }} (Tổng số sản phẩm: {{ $import_stock->import_stock_items ? count($import_stock->import_stock_items) : '' }})
            </div>
            <table>
                <thead>
                  <tr>
                    <th style="width: 40px">STT</th>
                    <th>Tên sản phẩm</th>
                    <th style="width: 15%">Đ.Giá</th>
                    <th style="width: 10%">Số lượng</th>
                    <th style="width: 15%">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($import_stock->import_stock_items as $item)
                        <tr>
                            <td style="text-align: center">{{ $loop->index }}</td>
                            <td>{{ $item['product']['name']}}{{ $item['element_distribute_name'] ? ' - ' . $item['element_distribute_name'] : ''}}{{ $item['element_distribute_name'] ? ' - ' . $item['sub_element_distribute_name'] : ''}}</td>
                            <td style="text-align: center">{{ number_format($item->import_price, 0, ',', '.') }}₫</td>
                            <td style="text-align: center">{{ $item->quantity }}</td>
                            <td style="text-align: center">{{ number_format($item->import_price * $item->quantity, 0, ',', '.') }}₫</td>
                        </tr>
                    @endforeach
                </tbody>
              </table>
        </div>
        <div class="print_total">
            <div>
                <div>
                    <div style="font-size: 20px">
                        Tổng tiền:
                    </div>
                    <h4 style="margin: 8px 0; font-size: 18px">
                        {{ $import_stock->total_final ? number_format($import_stock->total_final, 0, ',', '.') . '₫' : '0₫' }}
                    </h4>
                </div>
                <div style="font-style: italic">
                    Quý khách vui lòng kiểm tra đơn trước khi thanh toán.
                    Cảm ơn quý khách đã tin tưởng sử dụng sản phẩm!
                </div>
            </div>
            <div>
                <p>Chữ ký người nhận:</p>
                <p style="text-align: center">Xác nhận hàng nguyên vẹn không bóp/méo, bể vỡ</p>
                <br>
                <br>
                <br>
            </div>
        </div>   
    </div>

    <script type="text/javascript">
        window.onload = function () {
            window.print();
        };
        window.addEventListener("afterprint", (event) => {
            window.history.back();
        });
    </script>
</body>
</html>