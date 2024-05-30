
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thanh toán chuyển hoàn</title>
    <style>
      .main {
        display: flex;
        justify-content: center;
        font-family: "Inter", sans-serif;
      }
      h3 {
        text-align: center;
        font-size: 28px;
        color: #3b73cb;
      }
      .title {
        color: #535454;
        font-size: 20px;
        width: 500px;
        text-align: center;
        margin: 10px auto;
      }
      .title b {
        color: #b6202a;
        background-color: #fceeef;
      }
      .account_bank_tabs {
        margin: 30px 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        row-gap: 10px;
      }
      .account_bank_tab_item {
        cursor: pointer;
        padding: 5px 20px;
        font-size: 18px;
        color: #7f8c8d;
        border-bottom: 1px solid transparent;
      }
      .account_bank_item_show {
        display: flex;
        flex-direction: column;
        column-gap: 20px;
      }
      .account_bank_img {
        margin: 30px auto;
        display: flex;
        justify-content: center;
      }
      .account_bank_img img {
        width: 300px;
        height: 300px;
      }
      .account_bank_left {
        display: inline-block;
        color: #8d7837;
      }
      .account_bank_left + span {
        color: #9c5e0c;
        font-weight: 600;
      }
      .account_bank_info {
        display: flex;
        flex-direction: column;
        row-gap: 5px;
        font-weight: 500;
        margin-top: 20px;
        background-color: #feefb2;
        justify-content: center;
        align-items: center;
        padding: 10px 20px;
        font-size: 14px;
      }
      .account_bank_name {
        font-weight: 700;
        font-size: 18px;
        margin-bottom: 5px;
      }
      .table,
      .table-all,
      table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        display: table;
        font-size: 18px;
      }

      .table-all,
      table {
        border: 1px solid #ccc;
      }

      .table-all tr,
      .table-bordered tr,
      table tr {
        border-bottom: 1px solid #ddd;
      }

      .table-striped tbody tr:nth-child(even) {
        background-color: #f1f1f1;
      }

      .table-all tr:nth-child(odd) {
        background-color: #fff;
      }

      .table-all tr:nth-child(even) {
        background-color: #f1f1f1;
      }

      .hoverable tbody tr:hover {
        background-color: #ccc;
      }

      .table-centered tr td,
      .table-centered tr th {
        text-align: center;
      }

      .table td,
      .table th,
      .table-all td,
      .table-all th,
      tabke table td,
      table th {
        padding: 8px 8px;
        display: table-cell;
        text-align: left;
        vertical-align: top;
        border: 1px solid #e1e1e1;
      }

      .table td:first-child,
      .table th:first-child,
      .table-all td:first-child,
      .table-all th:first-child,
      table td:first-child,
      table th:first-child {
        padding-left: 16px;
      }

      .table-responsive {
        display: block;
        overflow-x: auto;
        margin: 20px auto;
        width: 600px;
      }
      tr td span {
        font-weight: 600;
        color: #3b73cb;
      }
      @media only screen and (max-width: 600px) {
        .table-responsive {
          width: 100%;
        }
        .table,
        .table-all,
        table {
          font-size: 16px;
        }
      }

      @media only screen and (max-width: 450px) {
        .table-responsive {
          margin: 20px 50px;
        }
      }
    </style>
  </head>

  <body>
    <div class="container main">
      <div>
        <h3>Thanh toán</h3>
        <div class="account_bank_hide" style="display: none">
          @foreach($payment_guide as $bank_item)
          <div class="account_bank_item">
            <div class="account_bank_number">
              {{$bank_item->account_number ?? "" }}
            </div>
            <div class="account_bank_username">
              {{$bank_item->account_name ?? ""}}
            </div>
            <div class="account_bank_name">{{$bank_item->bank ?? "" }}</div>
            @if($bank_item->branch != null)
            <div class="account_bank_branch">{{$bank_item->branch ?? "" }}</div>
            @endif
            <div class="account_bank_img">
              {{$bank_item->qr_code_image_url ?? ""}}
            </div>
          </div>
          @endforeach
        </div>
        <div class="account_bank_show"></div>
      </div>
    </div>

    <script>
      const accountBankData = document.querySelectorAll(
        ".account_bank_hide .account_bank_item"
      );
      const showAccountBank = document.querySelector(".account_bank_show");
      var tabs = [];
      var accounts = [];
      var accountShow = {};

      handleTab = (tab) => {
        const newAccount = accounts.filter((account) => account.name === tab);
        accountShow = newAccount[0];
        handleRender();
      };
      accountBankData.forEach((element) => {
        const bankNumber = element.querySelector(".account_bank_number");
        const bankUsername = element.querySelector(".account_bank_username");
        const bankName = element.querySelector(".account_bank_name");
        const bankBranch = element.querySelector(".account_bank_branch");
        const bankImg = element.querySelector(".account_bank_img");

        const account = {
          number: bankNumber?.innerHTML ? bankNumber.innerHTML : "",
          username: bankUsername?.innerHTML ? bankUsername.innerHTML : "",
          name: bankName?.innerHTML ? bankName.innerHTML?.trim() : "",
          branch: bankBranch?.innerHTML ? bankBranch.innerHTML : "",
          qrcode: (bankImg?.innerText ? bankImg.innerText : "")?.trim(),
        };
        tabs.push(bankName?.innerHTML ? bankName.innerHTML?.trim() : "");
        accounts.push(account);
      });

      if (accounts.length > 0) {
        accountShow = accounts[0];
      }

      handleRender = () => {
        if (accounts.length > 0) {
          const tabsRender = `<div class="account_bank_tabs">
            ${
              tabs.length > 0 &&
              tabs.map(
                (item) =>
                  `<div class="account_bank_tab_item" onclick="handleTab('${item}')" style="border-color:${
                    accountShow.name === item ? "#b4bcbd" : "transparent"
                  };color:${
                    accountShow.name === item ? "#050505" : "#8d8e8e"
                  };background:${
                    accountShow.name === item ? "#3b73cb36" : "transparent"
                  };">${item}</div>`
              )
            }
          </div>`.replace(/<\/div>,/g, "</div>");
          const result = `${tabsRender}        <p class="title">Hãy chuyển số tiền <b> {{$total_final}}</b></p>
          <p class="title">với nội dung <b>{{ $order->order_code }}</b></p>
       <div class="account_bank_item_show">
        ${
          accountShow.qrcode
            ? `<div class="account_bank_img"><img src=${accountShow.qrcode} alt="" /> </div>`
            : ""
        }
            
         
          
          <div class="table-responsive">
            <table class="table mt-table">
                <colgroup>
                    <col width="30%">
                    <col width="70%">
                </colgroup>
                <tbody>  
                    <tr>
                        <td>Tên Ngân Hàng </td>
                        <td><span>${accountShow.name}</span></td>
                    </tr>
                    <tr>
                        <td>Chủ Tài Khoản </td>
                        <td><span>${accountShow.username}</span></td>
                    </tr>
                                    <tr>
                        <td>Số Tài Khoản </td>
                        <td><span>${accountShow.number}</span></td>
                    </tr>
                    <tr>
                        <td>Chi Nhánh </td>
                        <td>${accountShow.branch}</td>
                    </tr>
                   
                </tbody>
            </table>
            </div>
         `;
          const accountBankItemExist = document.querySelector(
            ".account_bank_show .account_bank_item"
          );
          if (accountBankItemExist) {
            accountBankItemExist.remove();
          }
          const divTag = document.createElement("div");
          divTag.classList.add("account_bank_item");
          divTag.innerHTML = result;
          showAccountBank.appendChild(divTag);
        }
      };
      handleRender();
    </script>
  </body>
</html>
