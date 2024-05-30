<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán không thành công</title>
    <link rel="stylesheet" href="/style/main.css">

</head>

<body>

    <div class="bg">

        <div class="card">


            <div class="pageWrap">
                <div class="checkmark-circle">
                    <div class="background" style="background:red"></div>
                    <div class="checkmark draw2"></div>
                </div>
            </div>
            <h1 class="msg">Thanh toán không thành công</h1>
            <h2 class="submsg">Liên hệ với shop hoặc thử lại!</h2>

            @if($link_back != null)
            <div class="tags">
                <span class="tag" style="font-weight:bold; color:#dd3909"><a href="{{$link_back}}">Nhấn ở đây để quay
                        lại đơn hàng</a></span>
            </div>
            @else
            <div class="tags">
                <span class="tag" style="font-weight:bold; color:#dd3909">Hãy quay trở lại đơn hàng</span>
            </div>
            @endif

        </div>

    </div>


    <script src="/js/main.js"></script>
</body>

</html>