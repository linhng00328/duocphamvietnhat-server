---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://localhost/docs/collection.json)

<!-- END_INFO -->

#Admin/Badges


<!-- START_db6e8e1ead53d153af4b87daf52e6937 -->
## Lấy chỉ số

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/badges" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/badges"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/badges`


<!-- END_db6e8e1ead53d153af4b87daf52e6937 -->

#Admin/Banner


<!-- START_1342712ccbf02c9364b98eb3b9ac9427 -->
## Danh sách banner

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/banners" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/banners"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/banners`


<!-- END_1342712ccbf02c9364b98eb3b9ac9427 -->

#Admin/Cấu hình data ví dụ


<!-- START_15520b614046b9aca3a462d0bad4d567 -->
## Cấu hình data ví dụ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/setup_data_example" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"data_setup":"qui"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/setup_data_example"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "data_setup": "qui"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/setup_data_example`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `data_setup` | List |  optional  | data dạng [  {"type_id": 11, "store_code": "sy"},  ]
    
<!-- END_15520b614046b9aca3a462d0bad4d567 -->

<!-- START_5ae8b4834f2dfdd9d44fb1c1a0924f15 -->
## Lấy thông tin cấu hình

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/setup_data_example" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/setup_data_example"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/setup_data_example`


<!-- END_5ae8b4834f2dfdd9d44fb1c1a0924f15 -->

#Admin/Cấu hình thông báo


<!-- START_416f893b5a330f413cdbfd56e56c2a5c -->
## Cấu hình thông báo

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/notification/user/config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/notification/user/config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/notification/user/config`


<!-- END_416f893b5a330f413cdbfd56e56c2a5c -->

<!-- START_ee10bbf2930b6c7e0de864b9c29a59a5 -->
## Thông tin cài đặt

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/notification/user/config?store_code=magnam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/notification/user/config"
);

let params = {
    "store_code": "magnam",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/notification/user/config`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `store_code` |  optional  | Code stop

<!-- END_ee10bbf2930b6c7e0de864b9c29a59a5 -->

#Admin/Device token


<!-- START_f3f617b7d248ec64fc6c6d61a5ef579a -->
## Đăng ký device token

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/device_token" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"device_id":"reiciendis","device_type":1,"device_token":"veniam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/device_token"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "device_id": "reiciendis",
    "device_type": 1,
    "device_token": "veniam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/device_token`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `device_id` | string |  required  | device_id
        `device_type` | integer |  required  | 0 android | 1 ios
        `device_token` | string |  required  | device_token
    
<!-- END_f3f617b7d248ec64fc6c6d61a5ef579a -->

#Admin/Khách hàng cần tư vấn


<!-- START_9485452f8c89896cc93a0a86c9dbdf95 -->
## Thêm 1 user cần tư vấn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/user_advices" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":16,"username":"magnam","phone_number":"earum","email":"neque","name":"numquam","salary":"reprehenderit","id_employee_help":"et","sex":9,"id_decentralization":12,"consultation_1":"et","consultation_2":"autem","consultation_3":"laudantium"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/user_advices"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 16,
    "username": "magnam",
    "phone_number": "earum",
    "email": "neque",
    "name": "numquam",
    "salary": "reprehenderit",
    "id_employee_help": "et",
    "sex": 9,
    "id_decentralization": 12,
    "consultation_1": "et",
    "consultation_2": "autem",
    "consultation_3": "laudantium"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/user_advices`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | 0 chưa xử lý, 1 đang hỗ trợ, 2 thành công, 3 thất bại
        `username` | string |  optional  | tên gợi nhớ
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `salary` | string |  optional  | Lương
        `id_employee_help` | Id |  optional  | nhân viên hõ trọ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `id_decentralization` | integer |  optional  | id phân quyền (ủy quyền cho user cần tư vấn)
        `consultation_1` | string |  optional  | Lần tư vấn 1
        `consultation_2` | string |  optional  | Lần tư vấn 2
        `consultation_3` | string |  optional  | Lần tư vấn 3
    
<!-- END_9485452f8c89896cc93a0a86c9dbdf95 -->

<!-- START_056f090dbfa4f0be3af1ead91cc13162 -->
## Khách hàng cần tư vấn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/user_advices" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/user_advices"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/user_advices`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `search` |  optional  | string search
    `status` |  optional  | int status
    `limit` |  optional  | int số user mỗi trang
    `id_employee_help` |  optional  | Id nhân viên hõ trọ

<!-- END_056f090dbfa4f0be3af1ead91cc13162 -->

<!-- START_0a6286ed32d7eb936ae51731a8a242ca -->
## Xóa 1 user cần tư vấn

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/user_advices/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/user_advices/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/admin/user_advices/{userAdvice_id}`


<!-- END_0a6286ed32d7eb936ae51731a8a242ca -->

<!-- START_ceab589feef02cf5cddfd791db470e56 -->
## Cập nhật thông tin user cần tư vấn

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/user_advices/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"id_employee_help":14,"username":"velit","phone_number":"deserunt","email":"ipsa","name":"laudantium","sex":1,"consultation_1":"esse","consultation_2":"dolores","consultation_3":"ipsam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/user_advices/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "id_employee_help": 14,
    "username": "velit",
    "phone_number": "deserunt",
    "email": "ipsa",
    "name": "laudantium",
    "sex": 1,
    "consultation_1": "esse",
    "consultation_2": "dolores",
    "consultation_3": "ipsam"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/user_advices/{userAdvice_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `id_employee_help` | integer |  optional  | id nhân viên sale hỗ trợ
        `username` | string |  optional  | tên gợi nhớ
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `consultation_1` | string |  optional  | Lần tư vấn 1
        `consultation_2` | string |  optional  | Lần tư vấn 2
        `consultation_3` | string |  optional  | Lần tư vấn 3
    
<!-- END_ceab589feef02cf5cddfd791db470e56 -->

<!-- START_a2ba5f31da917b51d95a405eb345e25a -->
## Thêm nhiều user tu van

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/user_advices/all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_skip_same_name":false,"list":"quod","item":"velit"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/user_advices/all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_skip_same_name": false,
    "list": "quod",
    "item": "velit"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/user_advices/all`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_skip_same_name` | boolean |  required  | Có bỏ qua khách hàng tư vấn trùng tên không (Không bỏ qua sẽ replace khách hàng tư vấn trùng tên)
        `list` | List |  required  | List danh sách khách hàng tư vấn  (item json như thêm 1 userAdvice)
        `item` | userAdvice |  optional  | thêm {category_name}
    
<!-- END_a2ba5f31da917b51d95a405eb345e25a -->

<!-- START_7cd3b03a515bae583fbc499116731c0a -->
## Cập nhật nhiều user thông tin user cần tư vấn

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/user_advices" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"id_employee_help":2,"username":"quo","phone_number":"officiis","email":"qui","name":"est","sex":9,"consultation_1":"quia","consultation_2":"dolores","consultation_3":"perferendis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/user_advices"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "id_employee_help": 2,
    "username": "quo",
    "phone_number": "officiis",
    "email": "qui",
    "name": "est",
    "sex": 9,
    "consultation_1": "quia",
    "consultation_2": "dolores",
    "consultation_3": "perferendis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/user_advices`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `id_employee_help` | integer |  optional  | id nhân viên sale hỗ trợ
        `username` | string |  optional  | tên gợi nhớ
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `consultation_1` | string |  optional  | Lần tư vấn 1
        `consultation_2` | string |  optional  | Lần tư vấn 2
        `consultation_3` | string |  optional  | Lần tư vấn 3
    
<!-- END_7cd3b03a515bae583fbc499116731c0a -->

<!-- START_aff68c6a260ecd0c433dd16d5850f097 -->
## Khách hàng cần tư vấn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/otp" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/otp"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/otp`


<!-- END_aff68c6a260ecd0c433dd16d5850f097 -->

#Admin/Log


<!-- START_6c20324bd5f8039a854f7d84f739f3c0 -->
## Thông tin server

> Example request:

```bash
curl -X POST \
    "http://localhost/api/logger_fail" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/logger_fail"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/logger_fail`


<!-- END_6c20324bd5f8039a854f7d84f739f3c0 -->

#Admin/Lịch sử liên hệ user tư vấn


<!-- START_73a18902304dbcc32735fd9041a257c2 -->
## Thêm vào lịch sử

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/history_contact_user_advice/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"eaque","status":9}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/history_contact_user_advice/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "eaque",
    "status": 9
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/history_contact_user_advice/{user_advice_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | string |  required  | nội dung tư vấn
        `status` | integer |  required  | trạng thái tư vấn 0 chưa liên hệ dc, 1 khách đang bận, 2 khách chần chừ, 3 hẹn bữa sau, 4 đã ok, 5 khách hết quan tâm
    
<!-- END_73a18902304dbcc32735fd9041a257c2 -->

<!-- START_8d661406ffe29ce5970a72ec6854f178 -->
## Danh sách lịch sử của 1 user

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/history_contact_user_advice/et" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/history_contact_user_advice/et"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/history_contact_user_advice/{user_advice_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `user_advice_id` |  required  | id user cần hỗ trợ

<!-- END_8d661406ffe29ce5970a72ec6854f178 -->

#Admin/Migrate


Đưa khách qua IKItech
<!-- START_18869745ad5518493c606963e8360979 -->
## Đưa khách qua IKItech

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/migrate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/migrate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/migrate`


<!-- END_18869745ad5518493c606963e8360979 -->

#Admin/Nhân viên


<!-- START_5e83f020eda2632f3f81fd9293343804 -->
## Thêm 1 nhân viên

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/employee" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"quia","phone_number":"temporibus","email":"vitae","name":"temporibus","salary":"magnam","sex":10,"id_decentralization":12}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/employee"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "username": "quia",
    "phone_number": "temporibus",
    "email": "vitae",
    "name": "temporibus",
    "salary": "magnam",
    "sex": 10,
    "id_decentralization": 12
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/employee`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `username` | string |  optional  | Tên tài khoản
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `salary` | string |  optional  | Lương
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `id_decentralization` | integer |  optional  | 1 NV Sale,  0 QL SALE
    
<!-- END_5e83f020eda2632f3f81fd9293343804 -->

<!-- START_5a5d147d8c1507aa1e0a4c4126ab5b5e -->
## Danh cách nhân viên

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/employee" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/employee"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/employee`


<!-- END_5a5d147d8c1507aa1e0a4c4126ab5b5e -->

<!-- START_932f24e626691c1049e252df57dd11a2 -->
## Xóa 1 nhân viên

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/employee/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/employee/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/admin/employee/{employee_id}`


<!-- END_932f24e626691c1049e252df57dd11a2 -->

<!-- START_18c1af23c1b2f07929f0c97d92c3f2a5 -->
## Cập nhật thông tin nhân viên

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/employee/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"animi","phone_number":"voluptatem","email":"vero","name":"molestiae","salary":"explicabo","sex":9,"id_decentralization":15}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/employee/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "username": "animi",
    "phone_number": "voluptatem",
    "email": "vero",
    "name": "molestiae",
    "salary": "explicabo",
    "sex": 9,
    "id_decentralization": 15
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/employee/{employee_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `username` | string |  optional  | Tên tài khoản
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `salary` | string |  optional  | Lương
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `id_decentralization` | integer |  optional  | 1 NV Sale,  0 QL SALE
    
<!-- END_18c1af23c1b2f07929f0c97d92c3f2a5 -->

#Admin/Quản lý Store


APIs Quản lý Store
<!-- START_298dd9f84e97941ffdf7f631a45ec25b -->
## Danh sách store
/stores?page=1&amp;search=name&amp;sort_by=id&amp;descending=false&amp;store_ids=1,2,3

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/manage/stores?page=16&search=consequatur&sort_by=quidem&descending=maiores&type_compare_date_expried=aut&date_expried=tenetur" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/stores"
);

let params = {
    "page": "16",
    "search": "consequatur",
    "sort_by": "quidem",
    "descending": "maiores",
    "type_compare_date_expried": "aut",
    "date_expried": "tenetur",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/manage/stores`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price
    `descending` |  optional  | Giảm dần không VD: false
    `type_compare_date_expried` |  optional  | kiểu so sánh(>,<,==, default: <)
    `date_expried` |  optional  | Giá trị so sánh với time hết hạn

<!-- END_298dd9f84e97941ffdf7f631a45ec25b -->

<!-- START_3370ca6317cdb4376118f11b98ac88ff -->
## Thông tin một Store

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/manage/stores/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/stores/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/manage/stores/{store_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `id` |  required  | ID store cần lấy thông tin.

<!-- END_3370ca6317cdb4376118f11b98ac88ff -->

<!-- START_cd7f26ee13f21fd36eff686a3afe3bd1 -->
## update một Store
Gửi một trong các trường sau các trường null sẽ ko nhận và lấy giá trị cũ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/manage/stores/est" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"store_code":"consequatur","date_expried":"libero","address":"voluptatum","logo_url":"et","has_upload_store":"nisi","link_google_play":"quaerat","link_apple_store":"est","store_code_fake_for_ios":"labore"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/stores/est"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "store_code": "consequatur",
    "date_expried": "libero",
    "address": "voluptatum",
    "logo_url": "et",
    "has_upload_store": "nisi",
    "link_google_play": "quaerat",
    "link_apple_store": "est",
    "store_code_fake_for_ios": "labore"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/manage/stores/{store_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_id` |  required  | Store id cần update
    `name` |  required  | Name Store
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `store_code` | string |  required  | store_code
        `date_expried` | required |  optional  | Ngày hết hạn "2012-1-1"
        `address` | string |  required  | Địa chỉ
        `logo_url` | string |  required  | Logo url
        `has_upload_store` | required |  optional  | Đã up lên store hay chưa
        `link_google_play` | required |  optional  | Link tải google play
        `link_apple_store` | required |  optional  | Link tải apple store
        `store_code_fake_for_ios` | required |  optional  | store_code_fake_for_ios Chọn store để fake data
    
<!-- END_cd7f26ee13f21fd36eff686a3afe3bd1 -->

<!-- START_440f9a865d917cb03777546a90894d69 -->
## xóa một Store

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/manage/stores/nulla" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/stores/nulla"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/admin/manage/stores/{store_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_id` |  required  | Store id cần delete

<!-- END_440f9a865d917cb03777546a90894d69 -->

#Admin/Quản lý User


APIs Quản lý User
<!-- START_2af56f4c386f42c1b9e946f56be56eab -->
## Danh sách user
/stores?page=1&amp;search=name&amp;sort_by=id&amp;descending=false

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/manage/users?page=2&search=et&sort_by=deleniti&descending=corrupti" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/users"
);

let params = {
    "page": "2",
    "search": "et",
    "sort_by": "deleniti",
    "descending": "corrupti",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/manage/users`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price
    `descending` |  optional  | Giảm dần không VD: false

<!-- END_2af56f4c386f42c1b9e946f56be56eab -->

<!-- START_73d1975634b55a1b3a758fcc5aeea25d -->
## Thông tin một sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/manage/users/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/users/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/manage/users/{user_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `id` |  required  | ID User cần lấy thông tin.

<!-- END_73d1975634b55a1b3a758fcc5aeea25d -->

<!-- START_11e35510d791faaca3c8a442c88e42f5 -->
## Thông tin một sản phẩm

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/manage/users/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"functions":"harum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/users/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "functions": "harum"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/manage/users/{user_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `id` |  required  | ID User cần lấy thông tin.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `functions` | required |  optional  | Danh sách chức năng
    
<!-- END_11e35510d791faaca3c8a442c88e42f5 -->

<!-- START_1450ff9b72383aa34afce4de0b1b8e58 -->
## Thông tin một sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/manage/users/phone_number/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/manage/users/phone_number/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/manage/users/phone_number/{phone_number}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `id` |  required  | ID User cần lấy thông tin.

<!-- END_1450ff9b72383aa34afce4de0b1b8e58 -->

#Admin/Thông tin cá nhân


<!-- START_126b94ce8ba6c33c6c3be8322957c491 -->
## Tạo Lấy thông tin profile

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/profile"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/profile`


<!-- END_126b94ce8ba6c33c6c3be8322957c491 -->

<!-- START_74a9949e782ab1dc74706b1a7318f784 -->
## Cập nhật thông tin profile

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"possimus","date_of_birth":"culpa","avatar_image":"dolorum","sex":11}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/profile"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "possimus",
    "date_of_birth": "culpa",
    "avatar_image": "dolorum",
    "sex": 11
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/profile`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | String |  optional  | Họ và tên
        `date_of_birth` | Date |  optional  | Ngày sinh
        `avatar_image` | String |  optional  | Link ảnh avater
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
    
<!-- END_74a9949e782ab1dc74706b1a7318f784 -->

#Admin/Thông tin server


<!-- START_20724bef30a524519623305c02ff0c44 -->
## Thông tin server

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/info_server" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/info_server"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/info_server`


<!-- END_20724bef30a524519623305c02ff0c44 -->

#Admin/UserVip


<!-- START_3c436966cf1c77777a32d2fc9ba58bc9 -->
## Bật tắt vip

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/vip_user/1/on_off" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_vip":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/vip_user/1/on_off"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_vip": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/vip_user/{user_id}/on_off`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_vip` | boolean |  optional  | vip hay không
    
<!-- END_3c436966cf1c77777a32d2fc9ba58bc9 -->

<!-- START_b03b0768714b2e00f4bfa8f4371df3fa -->
## Cập nhật cấu hình user vip

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/vip_user/officia/config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"trader_mark_name":"illo","url_logo_image":"debitis","url_logo_small_image":"sed","url_login_image":"consequuntur","user_copyright":"autem","customer_copyright":"reiciendis","url_customer_copyright":"ut","list_json_id_theme_vip":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/vip_user/officia/config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "trader_mark_name": "illo",
    "url_logo_image": "debitis",
    "url_logo_small_image": "sed",
    "url_login_image": "consequuntur",
    "user_copyright": "autem",
    "customer_copyright": "reiciendis",
    "url_customer_copyright": "ut",
    "list_json_id_theme_vip": "et"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/vip_user/{user_id}/config`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `user_id` |  optional  | int user id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `trader_mark_name` | string |  optional  | Tên nhãn hiệu
        `url_logo_image` | string |  optional  | Url logo
        `url_logo_small_image` | string |  optional  | url logo nhỏ khi thu nhỏ thanh công cụ
        `url_login_image` | string |  optional  | user login image
        `user_copyright` | string |  optional  | thương hiệu dưới trang user quản lý
        `customer_copyright` | string |  optional  | thương hiệu dưới trang customer
        `url_customer_copyright` | string |  optional  | đường link trỏ đi của thương hiệu customer
        `list_json_id_theme_vip` | string |  optional  | list theme vip
    
<!-- END_b03b0768714b2e00f4bfa8f4371df3fa -->

<!-- START_f3e557dfeac72f5a0c593e72c141b52b -->
## Lấy cấu hình hình

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/vip_user/1/config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/vip_user/1/config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/vip_user/{user_id}/config`


<!-- END_f3e557dfeac72f5a0c593e72c141b52b -->

#Admin/Xử lý exel của khách hàng


<!-- START_4be74c48dc282b44d3d9eea9596ff40c -->
## Cấu hình data ví dụ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/handle_excel" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/handle_excel"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/handle_excel`


<!-- END_4be74c48dc282b44d3d9eea9596ff40c -->

#Admin/Đăng nhập


<!-- START_e9aa8e9cecac4d07efa45f1b2d470efb -->
## Login

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"voluptatem","password":"minima"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/login"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "voluptatem",
    "password": "minima"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/login`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `password` | string |  required  | Password
    
<!-- END_e9aa8e9cecac4d07efa45f1b2d470efb -->

<!-- START_128af210d94ad99dbd7f563d43d70282 -->
## Lấy lại mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/reset_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"ad","password":"animi","otp":"rem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/reset_password"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "ad",
    "password": "animi",
    "otp": "rem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/reset_password`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `password` | string |  required  | Mật khẩu mới
        `otp` | string |  optional  | gửi tin nhắn (DV SAHA gửi tới 8085)
    
<!-- END_128af210d94ad99dbd7f563d43d70282 -->

<!-- START_63677cf6435a36c3d33d88c7cea95e66 -->
## Thay đổi mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/change_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"password":"repellendus"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/change_password"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "password": "repellendus"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/change_password`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `password` | string |  required  | Mật khẩu mới
    
<!-- END_63677cf6435a36c3d33d88c7cea95e66 -->

#Chat


<!-- START_38028dc38af883b39926ed400ee51ce4 -->
## Chat đến khách hàng
Khách nhận tin nhắn khai báo io socket port 6441 nhận
var socket = io(&quot;http://localhost:6441&quot;)
socket.on(&quot;chat:message_from_user:1&quot;, function(data) {
  console.log(data)
  })
chat:message:1   với 1 là customer_id

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/message_customers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/message_customers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/message_customers/{customer_id}`


<!-- END_38028dc38af883b39926ed400ee51ce4 -->

<!-- START_b03fc06e778fb79a23d4dd9dc7dadb27 -->
## Danh sách tổng quan tin nhắn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/message_customers?page=20" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/message_customers"
);

let params = {
    "page": "20",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/message_customers`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)

<!-- END_b03fc06e778fb79a23d4dd9dc7dadb27 -->

<!-- START_836b92b10520caf79d026f42c789a933 -->
## Danh sách tin nhắn với 1 khách

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/message_customers/1?page=17" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/message_customers/1"
);

let params = {
    "page": "17",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/message_customers/{customer_id}`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)

<!-- END_836b92b10520caf79d026f42c789a933 -->

<!-- START_f0d70b879505b86977e27617b3503815 -->
## Khách hàng chat cho user
Khách nhận tin nhắn reatime khai báo io socket port 6441 nhận
var socket = io(&quot;http://localhost:6441&quot;)
socket.on(&quot;chat:message_from:customer:1&quot;, function(data) {
  console.log(data)
  })
chat:message:1   với 1 là customer_id
Lấy tin nhắn chưa đọc realtime
 socket.on(&quot;chat:message_from_customer&quot;,

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/messages" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/messages"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/messages`


<!-- END_f0d70b879505b86977e27617b3503815 -->

<!-- START_cc39bdb8b9237a6efe5ed01ce17865bf -->
## Danh sách tin nhắn với user

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/messages?page=9" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/messages"
);

let params = {
    "page": "9",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/messages`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)

<!-- END_cc39bdb8b9237a6efe5ed01ce17865bf -->

#Customer/AppWebTheme


<!-- START_5a5c2b512972b1757473e7532845b394 -->
## Theme App

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/blanditiis/app-theme" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/blanditiis/app-theme"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/app-theme`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_5a5c2b512972b1757473e7532845b394 -->

<!-- START_757a83ba420eb05e4355fa3f807f501e -->
## Theme Web

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/iusto/web-theme" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/iusto/web-theme"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/web-theme`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_757a83ba420eb05e4355fa3f807f501e -->

#Customer/BOnus product


<!-- START_451f0b50498cececb90dd3312b16dd34 -->
## Lấy danh sách bonus đang phát hành

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/suscipit/bonus_products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/suscipit/bonus_products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/bonus_products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_451f0b50498cececb90dd3312b16dd34 -->

#Customer/Bài viết


<!-- START_f30abc08f91c06b222f5a49653564322 -->
## Danh sách bài viết
customer/{{store_code}}/posts?page=1&amp;search=name&amp;sort_by=id&amp;descending=false&amp;category_ids=1,2,3

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/eligendi/posts?page=9&search=omnis&sort_by=molestias&descending=mollitia&category_ids=sit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/eligendi/posts"
);

let params = {
    "page": "9",
    "search": "omnis",
    "sort_by": "molestias",
    "descending": "mollitia",
    "category_ids": "sit",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/posts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price
    `descending` |  optional  | Giảm dần không VD: false
    `category_ids` |  optional  | Thuộc category id nào VD: 1,2,3

<!-- END_f30abc08f91c06b222f5a49653564322 -->

<!-- START_def9515b1ff55750b220fab6691f000b -->
## Thông tin bài viết

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/voluptas/posts/illum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/voluptas/posts/illum"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/posts/{id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
    `id` |  required  | ID post cần lấy thông tin.

<!-- END_def9515b1ff55750b220fab6691f000b -->

#Customer/CTV


<!-- START_fa2ee1b51d7a50f0bbb6a29623d32564 -->
## Đăng ký ctv

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/collaborator/reg" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_collaborator":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/reg"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_collaborator": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/collaborator/reg`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_collaborator` | boolean |  optional  | đăng ký hay không hay không (true false)
    
<!-- END_fa2ee1b51d7a50f0bbb6a29623d32564 -->

<!-- START_74f3778c87ce8c59b05e16b4bbce2280 -->
## Thông tin cộng tác viên

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/collaborator/account" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_collaborator":false,"payment_auto":true,"first_and_last_name":"et","cmnd":"esse","date_range":"architecto","issued_by":"aut","front_card":"nihil","back_card":"vel","bank":"odit","account_number":"temporibus","account_name":"nihil","branch":"nostrum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/account"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_collaborator": false,
    "payment_auto": true,
    "first_and_last_name": "et",
    "cmnd": "esse",
    "date_range": "architecto",
    "issued_by": "aut",
    "front_card": "nihil",
    "back_card": "vel",
    "bank": "odit",
    "account_number": "temporibus",
    "account_name": "nihil",
    "branch": "nostrum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/collaborator/account`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_collaborator` | boolean |  optional  | đăng ký hay không hay không (true false)
        `payment_auto` | boolean |  optional  | Bật tự động để user quyết toán
        `first_and_last_name` | Họ |  optional  | và tên
        `cmnd` | CMND |  optional  | 
        `date_range` | ngày |  optional  | cấp
        `issued_by` | Nơi |  optional  | cấp
        `front_card` | Mặt |  optional  | trước link
        `back_card` | Mật |  optional  | sau link
        `bank` | Tên |  optional  | ngân hàng
        `account_number` | Số |  optional  | tài khoản
        `account_name` | Tên |  optional  | tài khoản
        `branch` | Chi |  optional  | nhánh
    
<!-- END_74f3778c87ce8c59b05e16b4bbce2280 -->

<!-- START_6279422b182ffa7004a9792693ead60d -->
## api/customer/{store_code}/collaborator/account
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/collaborator/account" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/account"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/collaborator/account`


<!-- END_6279422b182ffa7004a9792693ead60d -->

<!-- START_285a049bfcc6426422166db7e67720e0 -->
## Báo cáo thưởng  bậc thang

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/collaborator/bonus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/bonus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/collaborator/bonus`


<!-- END_285a049bfcc6426422166db7e67720e0 -->

<!-- START_65aa466882eb728a3938903d14a95668 -->
## Nhận thưởng tháng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/collaborator/bonus/take" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"month":"et","year":"cupiditate"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/bonus/take"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "month": "et",
    "year": "cupiditate"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/collaborator/bonus/take`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `month` | Tháng |  optional  | muốn nhận VD: 2
        `year` | Năm |  optional  | muốn nhận VD: 2012
    
<!-- END_65aa466882eb728a3938903d14a95668 -->

<!-- START_2f364ad3c111768f4488edfa95944818 -->
## Thông tin tổng quan

Doanh thu hiện tại

type_rose 0 doanh sô - 1 hoa hồng

total_final daonh số tháng này

share_collaborator tổng tiền hoa đồng chia sẻ

received_month_bonus Đã nhận thưởng tháng hay chưa

number_order số lượng đơn hàng tháng này

allow_payment_request cho phép yêu cầu thanh toán

payment_1_of_month định kỳ thanh toán 1

payment_16_of_month định kỳ thanh toán 15

payment_limit Giới hạn yêu cầu thanh toán

has_payment_request có yêu cầu thanh toán hay không

money_payment_request Số tiền yêu cầu hiện tại

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/collaborator/info" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/info"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/collaborator/info`


<!-- END_2f364ad3c111768f4488edfa95944818 -->

#Customer/Chat


<!-- START_b00570cdfb1d45295e40e888968b66b8 -->
## Danh sách người chat với customer

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/person_chat" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/person_chat"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/person_chat`


<!-- END_b00570cdfb1d45295e40e888968b66b8 -->

<!-- START_190149a85ab91e1c94e7a8b2d1776c28 -->
## Danh sách tin nhắn với 1 người

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/person_chat/1/messages" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"content":"et","images":"omnis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/person_chat/1/messages"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "content": "et",
    "images": "omnis"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/person_chat/{to_customer_id}/messages`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `content` | required |  optional  | Nội dung
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
    
<!-- END_190149a85ab91e1c94e7a8b2d1776c28 -->

<!-- START_a992ad02e7caf5f77fdbac59e167c2c8 -->
## Gửi tin nhắn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/person_chat/1/messages" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"content":"voluptatem","images":"est"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/person_chat/1/messages"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "content": "voluptatem",
    "images": "est"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/person_chat/{to_customer_id}/messages`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `content` | required |  optional  | Nội dung
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
    
<!-- END_a992ad02e7caf5f77fdbac59e167c2c8 -->

#Customer/Chỉ số đếm


<!-- START_7089066a62d61d9d5db005c5808c09ad -->
## Lấy tất cả chỉ số đếm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/badges" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/badges"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/badges`


<!-- END_7089066a62d61d9d5db005c5808c09ad -->

#Customer/Combo


<!-- START_81e21079d9c858e67d98caf6701edc40 -->
## Lấy danh sách combo đang phát hành

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/et/combos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/et/combos"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/combos`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_81e21079d9c858e67d98caf6701edc40 -->

#Customer/Danh mục bài viết


<!-- START_23f1a3a9b8f769b5b4698d811c767547 -->
## Danh sách danh mục bài viết

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/quidem/post_categories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/quidem/post_categories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/post_categories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_23f1a3a9b8f769b5b4698d811c767547 -->

#Customer/Danh mục sản phẩm


<!-- START_cb6f4c56606fe56c0103e49810bfd1e2 -->
## Danh sách danh mục sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/veritatis/categories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/veritatis/categories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/categories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_cb6f4c56606fe56c0103e49810bfd1e2 -->

#Customer/Device token


<!-- START_e8e44105e1bc8b24de07714f0461f1e4 -->
## Đăng ký device token

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/device_token_customer" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"device_id":"reprehenderit","device_type":"quisquam","device_token":"suscipit"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/device_token_customer"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "device_id": "reprehenderit",
    "device_type": "quisquam",
    "device_token": "suscipit"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/device_token_customer`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `device_id` | string |  required  | device_id
        `device_type` | string |  required  | 0 android | 1 ios
        `device_token` | string |  required  | device_token
    
<!-- END_e8e44105e1bc8b24de07714f0461f1e4 -->

#Customer/Giỏ hàng


<!-- START_92129c1fcff2eab279e067d71c4e24d1 -->
## Danh sách sản phẩm trong giỏ hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/debitis/pos/carts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"\"code_voucher\":\"SUPER\"":"at","is_use_points":"maiores","is_use_balance_collaborator":"voluptatem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/debitis/pos/carts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "\"code_voucher\":\"SUPER\"": "at",
    "is_use_points": "maiores",
    "is_use_balance_collaborator": "voluptatem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/pos/carts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `&quot;code_voucher&quot;:&quot;SUPER&quot;` | gửi |  optional  | code voucher
        `is_use_points` | có |  optional  | sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | su |  optional  | dung diem CTV
    
<!-- END_92129c1fcff2eab279e067d71c4e24d1 -->

<!-- START_e236a65933e0e2d6c2ed3ca1057a5163 -->
## Danh sách sản phẩm trong giỏ hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/et/pos/carts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/et/pos/carts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/pos/carts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_e236a65933e0e2d6c2ed3ca1057a5163 -->

<!-- START_3e205a170e94ab8aeb181db9b893a6ae -->
## Thêm sản phẩm vào giỏ hàng

header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/sed/pos/carts/items" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":12,"distributes":"expedita","\"code_voucher\":\"SUPER\"":"accusamus","is_use_points":"ut","is_use_balance_collaborator":"voluptatibus"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/sed/pos/carts/items"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": 12,
    "distributes": "expedita",
    "\"code_voucher\":\"SUPER\"": "accusamus",
    "is_use_points": "ut",
    "is_use_balance_collaborator": "voluptatibus"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/pos/carts/items`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | integer |  required  | Product id
        `distributes` | List |  optional  | danh sách phân loại đã chọn vd: [  {name:"màu", value:"đỏ", sub_element_distributes:"XL"} ]
        `&quot;code_voucher&quot;:&quot;SUPER&quot;` | gửi |  optional  | code voucher
        `is_use_points` | có |  optional  | sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | su |  optional  | dung diem CTV
    
<!-- END_3e205a170e94ab8aeb181db9b893a6ae -->

<!-- START_b5712adc6498fcd43f91565bf3d996e2 -->
## Cập nhật 1 sản phẩm trong giỏ hàng

header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/eum/pos/carts/items" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"quantity":8,"product_id":11,"line_item_id":1,"distributes":"ut","code_vouche":"odio","is_use_points":false,"is_use_balance_collaborator":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/eum/pos/carts/items"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "quantity": 8,
    "product_id": 11,
    "line_item_id": 1,
    "distributes": "ut",
    "code_vouche": "odio",
    "is_use_points": false,
    "is_use_balance_collaborator": false
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/pos/carts/items`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `quantity` | integer |  required  | Số lượng (Nếu == 0 xóa luôn sản phẩm khỏi giỏ)
        `product_id` | integer |  required  | Product id (bat buoc phai co)
        `line_item_id` | integer |  required  | Trường hợp cần cập nhật phân loại mới gửi sp mới thì để null
        `distributes` | List |  optional  | danh sách phân loại đã chọn vd: [  {name:"màu", value:"đỏ"} ]
        `code_vouche` | string |  optional  | ":"SUPER" gửi code voucher
        `is_use_points` | boolean |  optional  | có sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | boolean |  optional  | su dung diem CTV
    
<!-- END_b5712adc6498fcd43f91565bf3d996e2 -->

<!-- START_e0e2a93a5ca464f3ea2f05081e54e6c1 -->
## Danh sách sản phẩm trong giỏ hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/at/carts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"\"code_voucher\":\"SUPER\"":"et","is_use_points":"ea","is_use_balance_collaborator":"nulla"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/at/carts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "\"code_voucher\":\"SUPER\"": "et",
    "is_use_points": "ea",
    "is_use_balance_collaborator": "nulla"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/carts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `&quot;code_voucher&quot;:&quot;SUPER&quot;` | gửi |  optional  | code voucher
        `is_use_points` | có |  optional  | sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | su |  optional  | dung diem CTV
    
<!-- END_e0e2a93a5ca464f3ea2f05081e54e6c1 -->

<!-- START_82f8fd98a0554d4df9df7a88758fc170 -->
## Thêm sản phẩm vào giỏ hàng

header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/itaque/carts/items" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":8,"distributes":"at","\"code_voucher\":\"SUPER\"":"eos","is_use_points":"voluptatum","is_use_balance_collaborator":"id"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/itaque/carts/items"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": 8,
    "distributes": "at",
    "\"code_voucher\":\"SUPER\"": "eos",
    "is_use_points": "voluptatum",
    "is_use_balance_collaborator": "id"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/carts/items`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | integer |  required  | Product id
        `distributes` | List |  optional  | danh sách phân loại đã chọn vd: [  {name:"màu", value:"đỏ", sub_element_distributes:"XL"} ]
        `&quot;code_voucher&quot;:&quot;SUPER&quot;` | gửi |  optional  | code voucher
        `is_use_points` | có |  optional  | sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | su |  optional  | dung diem CTV
    
<!-- END_82f8fd98a0554d4df9df7a88758fc170 -->

<!-- START_ee5bc9763cf021a13a423dd8f0ec7884 -->
## Cập nhật 1 sản phẩm trong giỏ hàng

header co them device_id (truyền lên khi ko cần đăng nhập vẫn có giỏ hàng)

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/aut/carts/items" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"quantity":5,"product_id":10,"line_item_id":13,"distributes":"quo","code_vouche":"facilis","is_use_points":true,"is_use_balance_collaborator":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/aut/carts/items"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "quantity": 5,
    "product_id": 10,
    "line_item_id": 13,
    "distributes": "quo",
    "code_vouche": "facilis",
    "is_use_points": true,
    "is_use_balance_collaborator": false
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/carts/items`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `quantity` | integer |  required  | Số lượng (Nếu == 0 xóa luôn sản phẩm khỏi giỏ)
        `product_id` | integer |  required  | Product id (bat buoc phai co)
        `line_item_id` | integer |  required  | Trường hợp cần cập nhật phân loại mới gửi sp mới thì để null
        `distributes` | List |  optional  | danh sách phân loại đã chọn vd: [  {name:"màu", value:"đỏ"} ]
        `code_vouche` | string |  optional  | ":"SUPER" gửi code voucher
        `is_use_points` | boolean |  optional  | có sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | boolean |  optional  | su dung diem CTV
    
<!-- END_ee5bc9763cf021a13a423dd8f0ec7884 -->

#Customer/Gửi otp email


<!-- START_e74afaff9603d17912838f8ba2048d0a -->
## Gửi otp qua email

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/veniam/send_email_otp" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/veniam/send_email_otp"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/send_email_otp`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_e74afaff9603d17912838f8ba2048d0a -->

#Customer/HomeApp


<!-- START_5cce076463809bf7d76d8a11b4066098 -->
## Lấy giao diện home

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_app?from=ullam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_app"
);

let params = {
    "from": "ullam",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_app`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `from` |  optional  | string home từ đâu (FROM_APP,FROM_WEB)

<!-- END_5cce076463809bf7d76d8a11b4066098 -->

<!-- START_54dae80240a9db2a3a324aec8c70170c -->
## Lấy Danh sách banner

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/banners?from=placeat" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/banners"
);

let params = {
    "from": "placeat",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/banners`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `from` |  optional  | string home từ đâu (FROM_APP,FROM_WEB)

<!-- END_54dae80240a9db2a3a324aec8c70170c -->

<!-- START_9ab7fc2b1437f6d50a9857d2284b0e59 -->
## Lấy Danh sách product discount

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/product_discounts?from=quisquam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/product_discounts"
);

let params = {
    "from": "quisquam",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/product_discounts`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `from` |  optional  | string home từ đâu (FROM_APP,FROM_WEB)

<!-- END_9ab7fc2b1437f6d50a9857d2284b0e59 -->

<!-- START_fe514e9be960b580d22011e563f54a60 -->
## Lấy Danh sách product discount

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/product_top_sales?from=delectus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/product_top_sales"
);

let params = {
    "from": "delectus",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/product_top_sales`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `from` |  optional  | string home từ đâu (FROM_APP,FROM_WEB)

<!-- END_fe514e9be960b580d22011e563f54a60 -->

<!-- START_5c1a0d76aa2c096b47a2085bc8c92cf9 -->
## Lấy Danh sách product discount

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/product_news?from=officiis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/product_news"
);

let params = {
    "from": "officiis",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/product_news`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `from` |  optional  | string home từ đâu (FROM_APP,FROM_WEB)

<!-- END_5c1a0d76aa2c096b47a2085bc8c92cf9 -->

<!-- START_100ffca2626622f21ea9c18cb5e1c195 -->
## api/customer/{store_code}/home_web/product_by_category
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/product_by_category" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/product_by_category"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/product_by_category`


<!-- END_100ffca2626622f21ea9c18cb5e1c195 -->

<!-- START_5872425f6350d727272087a5b056fe63 -->
## Lấy Danh sách post new

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/posts_new?from=consequatur" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/posts_new"
);

let params = {
    "from": "consequatur",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/posts_new`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `from` |  optional  | string home từ đâu (FROM_APP,FROM_WEB)

<!-- END_5872425f6350d727272087a5b056fe63 -->

<!-- START_85dca50f0cdad8131d04af190d8d8fe6 -->
## api/customer/{store_code}/home_web/posts_with_category
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/posts_with_category" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/posts_with_category"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/posts_with_category`


<!-- END_85dca50f0cdad8131d04af190d8d8fe6 -->

<!-- START_e7bf8ba5c35b5d333355d48d33fada51 -->
## api/customer/{store_code}/home_web/ads
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/home_web/ads" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/home_web/ads"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/home_web/ads`


<!-- END_e7bf8ba5c35b5d333355d48d33fada51 -->

#Customer/Like cộng đồng


<!-- START_d061a40b71496c840213eb8b73df8f81 -->
## Like bài đăng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/community_post_like" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"community_post_id":4,"is_like":"est"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_post_like"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "community_post_id": 4,
    "is_like": "est"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/community_post_like`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `community_post_id` | integer |  required  | id bài viết
        `is_like` | required |  optional  | boolean
    
<!-- END_d061a40b71496c840213eb8b73df8f81 -->

#Customer/Lịch sử thay đổi số dư


<!-- START_ac316a321a14527e6fc0d04c6adcd0f5 -->
## Lịch sử thay đổi số dư

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/nihil/collaborator/history_balace" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/nihil/collaborator/history_balace"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/collaborator/history_balace`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_ac316a321a14527e6fc0d04c6adcd0f5 -->

<!-- START_feb19f71b1ba0cf91fa75d87730add0a -->
## Lịch sử thay đổi số dư

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/omnis/agency/history_balace" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/omnis/agency/history_balace"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/agency/history_balace`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_feb19f71b1ba0cf91fa75d87730add0a -->

#Customer/Lịch sử tìm kiếm sản phẩm


<!-- START_8c5633f101604746b3a2fe8d93b8bd3c -->
## Xóa lịch sử tìm kiếm

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/customer/1/search_histories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/search_histories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/customer/{store_code}/search_histories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `device_id` |  optional  | trường hợp chưa đăng nhập (có vẫn ưu tiên đã đăng nhập)

<!-- END_8c5633f101604746b3a2fe8d93b8bd3c -->

<!-- START_f4a232f86aa1a6cdb3268fd88892bda6 -->
## Danh sách lịch sử tìm kiếm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/search_histories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/search_histories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/search_histories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `device_id` |  optional  | trường hợp chưa đăng nhập (có vẫn ưu tiên đã đăng nhập)

<!-- END_f4a232f86aa1a6cdb3268fd88892bda6 -->

#Customer/Phương thức thanh toán


<!-- START_ace62e42c9a01108ac46855484730b1d -->
## Danh sách phương thức thanh toán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/payment_methods" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/payment_methods"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/payment_methods`


<!-- END_ace62e42c9a01108ac46855484730b1d -->

#Customer/Scan Qr Barcode


<!-- START_e33be718b648c23d7f5dc32f7521143c -->
## Tìm sản phẩm theo mã barcode

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/scan_product" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"barcode":"non"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/scan_product"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "barcode": "non"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/scan_product`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `barcode` | code |  optional  | barcode
    
<!-- END_e33be718b648c23d7f5dc32f7521143c -->

#Customer/Sản phẩm


<!-- START_23f919a4bbb1125bd5f2a040477dc9fa -->
## Danh sách sản phẩm

thêm trường: is_favorite, is_top_sale, is_new

customer/{{store_code}}/products?page=1&search=name&sort_by=id&descending=false&category_ids=1,2,3&details=Màu:Đỏ|Size:XL

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/eius/products?page=5&search=numquam&sort_by=optio&descending=sit&category_ids=perspiciatis&category_children_ids=neque&details=officiis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/eius/products"
);

let params = {
    "page": "5",
    "search": "numquam",
    "sort_by": "optio",
    "descending": "sit",
    "category_ids": "perspiciatis",
    "category_children_ids": "neque",
    "details": "officiis",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: (sales theo luot mua,views theo luot xem, created_at)
    `descending` |  optional  | Giảm dần không VD: false  (chỉ áp dụng cho giá tiền)
    `category_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `category_children_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `details` |  optional  | Filter theo thuộc tính VD: Màu:Đỏ|Size:XL

<!-- END_23f919a4bbb1125bd5f2a040477dc9fa -->

<!-- START_228655261b45e3d5018aa81a901bc5f8 -->
## Thông tin một sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/dolorem/products/qui" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/dolorem/products/qui"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/products/{id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
    `id` |  required  | ID product cần lấy thông tin.

<!-- END_228655261b45e3d5018aa81a901bc5f8 -->

<!-- START_8123fa6ef4d7f4ff8e65fcaa48d6db04 -->
## Danh sách sản phẩm tương tự

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/eos/products/tenetur/similar_products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/eos/products/tenetur/similar_products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/products/{id}/similar_products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
    `id` |  required  | ID product cần lấy danh sách

<!-- END_8123fa6ef4d7f4ff8e65fcaa48d6db04 -->

<!-- START_80590ef154046daef963ad9f761ea8f1 -->
## DS sản phẩm đã mua

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/id/purchased_products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/id/purchased_products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchased_products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_80590ef154046daef963ad9f761ea8f1 -->

<!-- START_c3c6d3dbfc4849fb67aaba3beff62662 -->
## DS sản phẩm vừa xem

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/molestiae/watched_products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/molestiae/watched_products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/watched_products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_c3c6d3dbfc4849fb67aaba3beff62662 -->

<!-- START_6de9aa080110be988a0a412b1d925732 -->
## Danh sách sản phẩm yêu thích

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/favorites" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_favorite":"quae"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/favorites"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_favorite": "quae"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/favorites`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `product_id` |  optional  | string required product_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_favorite` | yêu |  optional  | thích hay không
    
<!-- END_6de9aa080110be988a0a412b1d925732 -->

#Customer/Thanh toán tiền hoa hồng


<!-- START_b2fe7252ce0ba46da5e3388b245795eb -->
## Yêu cầu thanh toán

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/collaborator/request_payment" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/collaborator/request_payment"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/collaborator/request_payment`


<!-- END_b2fe7252ce0ba46da5e3388b245795eb -->

<!-- START_aeeef00a7671749cb86ee486295faa4d -->
## Yêu cầu thanh toán

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/agency/request_payment" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/request_payment"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/agency/request_payment`


<!-- END_aeeef00a7671749cb86ee486295faa4d -->

#Customer/Thông báo


<!-- START_8b35198bd23094610afee349a7522220 -->
## Danh sách thông báo

total_unread số chưa đọc

page số trang

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/notifications_history" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/notifications_history"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/notifications_history`


<!-- END_8b35198bd23094610afee349a7522220 -->

<!-- START_8aeda937ce20fb05a169e7155fb1b2ee -->
## Đã đọc tất cả

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/notifications_history/read_all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/notifications_history/read_all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/notifications_history/read_all`


<!-- END_8aeda937ce20fb05a169e7155fb1b2ee -->

#Customer/Thông tin 1 người trong cộng đồng


<!-- START_ed437ef3d1e00d78a2e386c8c201fbac -->
## Thông tin tổng quan

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/community_customer_profile/repudiandae" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_customer_profile/repudiandae"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/community_customer_profile/{customer_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `customer_id` |  required  | Nếu là customer id

<!-- END_ed437ef3d1e00d78a2e386c8c201fbac -->

#Customer/Thông tin cá nhân


<!-- START_fa459d76209ee5bb2f80933d77f59c86 -->
## Tạo Lấy thông tin profile

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/assumenda/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/assumenda/profile"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/profile`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_fa459d76209ee5bb2f80933d77f59c86 -->

<!-- START_75939d135a8bb7c4dff61eb8d6a6322c -->
## Cập nhật thông tin profile

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/ratione/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"est","avatar_image":"sit","date_of_birth":"aperiam","sex":4}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/ratione/profile"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "est",
    "avatar_image": "sit",
    "date_of_birth": "aperiam",
    "sex": 4
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/profile`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | String |  optional  | Họ và tên
        `avatar_image` | String |  optional  | Link ảnh avater
        `date_of_birth` | Date |  optional  | Ngày sinh
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
    
<!-- END_75939d135a8bb7c4dff61eb8d6a6322c -->

#Customer/Thông tin store


<!-- START_4538f7aac1c1bdfa98e8229cdfd17d14 -->
## Lấy thông tin store

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/explicabo" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/explicabo"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_4538f7aac1c1bdfa98e8229cdfd17d14 -->

#Customer/Tích điểm


<!-- START_f814514162c492390039e2983f6f8977 -->
## Lịch sử tích điểm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/point_history" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/point_history"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/point_history`


<!-- END_f814514162c492390039e2983f6f8977 -->

#Customer/Voucher


<!-- START_8bfa20af52b533b1b98cc9fdf7bd4313 -->
## Lấy danh sách voucher đang phát hành

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/magnam/vouchers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/magnam/vouchers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/vouchers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.

<!-- END_8bfa20af52b533b1b98cc9fdf7bd4313 -->

#Customer/Vận chuyển


<!-- START_c70ca3b925bd9f10dd2167c06222cd4d -->
## Tính phí vận chuyển

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/shipment/fee" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"id_address_customer":13}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/shipment/fee"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "id_address_customer": 13
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/shipment/fee`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `id_address_customer` | integer |  required  | Id địa chỉ giao hàng
    
<!-- END_c70ca3b925bd9f10dd2167c06222cd4d -->

#Customer/Yêu thích sản phẩm


<!-- START_39b7b400f0d75b1278f8c167921510da -->
## Yêu thích sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/products/sed/favorites" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_favorite":"rerum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/products/sed/favorites"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_favorite": "rerum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/products/{product_id}/favorites`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `product_id` |  optional  | string required product_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_favorite` | yêu |  optional  | thích hay không
    
<!-- END_39b7b400f0d75b1278f8c167921510da -->

#Customer/thanh toán


<!-- START_8dccaadca9fbe2831d209cfae59984f8 -->
## Danh sách bài viết

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/repellendus/purchase/pay/culpa" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/repellendus/purchase/pay/culpa"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/pay/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
    `order_code` |  required  | Mã đơn hàng

<!-- END_8dccaadca9fbe2831d209cfae59984f8 -->

<!-- START_97b71c5e7e7997de5bd56ec1b85d62dd -->
## api/customer/{store_code}/purchase/pay/{order_code}/bank
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/purchase/pay/1/bank" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/purchase/pay/1/bank"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/pay/{order_code}/bank`


<!-- END_97b71c5e7e7997de5bd56ec1b85d62dd -->

<!-- START_424349f4ef1b66f99ebf0b4c63ee8b2e -->
## api/customer/{store_code}/purchase/pay/{order_code}/vn_pay
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/purchase/pay/1/vn_pay" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/purchase/pay/1/vn_pay"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/pay/{order_code}/vn_pay`


<!-- END_424349f4ef1b66f99ebf0b4c63ee8b2e -->

<!-- START_84e1ff752d72273e37e17508f4627271 -->
## api/customer/{store_code}/purchase/return/vn_pay
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/purchase/return/vn_pay" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/purchase/return/vn_pay"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/return/vn_pay`


<!-- END_84e1ff752d72273e37e17508f4627271 -->

<!-- START_f865262f77c45d1b2114cf669571ef84 -->
## HÀM TẠO ĐƯỜNG LINK THANH TOÁN QUA NGÂNLƯỢNG.VN VỚI THAM SỐ MỞ RỘNG

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/purchase/pay/1/ngan_luong" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/purchase/pay/1/ngan_luong"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/pay/{order_code}/ngan_luong`


<!-- END_f865262f77c45d1b2114cf669571ef84 -->

#Customer/thanh toán onpay


<!-- START_ee17673f85271fe08680cd2b14d2a865 -->
## api/customer/{store_code}/purchase/pay/{order_code}/one_pay
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/purchase/pay/1/one_pay" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/purchase/pay/1/one_pay"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/pay/{order_code}/one_pay`


<!-- END_ee17673f85271fe08680cd2b14d2a865 -->

<!-- START_2c13462242b81ab22cb6d6d83f90ee09 -->
## api/customer/{store_code}/purchase/return/one_pay
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/purchase/return/one_pay" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/purchase/return/one_pay"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/purchase/return/one_pay`


<!-- END_2c13462242b81ab22cb6d6d83f90ee09 -->

#Customer/Đánh giá sản phẩm


<!-- START_06dd044b77185aacb288c85780d2e0a9 -->
## Danh sách đánh giá của sản phẩm
averaged_stars trung bình sao

filter_by  (theo số sao stars hoặc status )
filter_by_value (giá trị muốn lấy)
has_image có ảnh hay không

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/products/1/reviews" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/products/1/reviews"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/products/{product_id}/reviews`


<!-- END_06dd044b77185aacb288c85780d2e0a9 -->

<!-- START_82a2a3769313ffa9ff9a910e80c71c05 -->
## Tất cả đánh giá của tôi

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/reviews" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/reviews"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/reviews`


<!-- END_82a2a3769313ffa9ff9a910e80c71c05 -->

<!-- START_faba9d83a691ba852c3be1a9dd569a5e -->
## Line item Sản phẩm chưa đánh giá

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/reviews/not_rated" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/reviews/not_rated"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/reviews/not_rated`


<!-- END_faba9d83a691ba852c3be1a9dd569a5e -->

#Customer/Đăng ký


<!-- START_82a3f96b6868e275c58e53dae3e48895 -->
## Register

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/register" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"et","email":"ut","password":"omnis","name":"accusantium","sex":1,"referral_phone_number":"cum","otp":"illum","otp_from":"sunt"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/register"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "et",
    "email": "ut",
    "password": "omnis",
    "name": "accusantium",
    "sex": 1,
    "referral_phone_number": "cum",
    "otp": "illum",
    "otp_from": "sunt"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/register`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `email` | string |  required  | Email
        `password` | string |  required  | Password
        `name` | string |  required  | Họ và tên
        `sex` | integer |  required  | (0 ko xác định - 1 nam - 2 nữ)
        `referral_phone_number` | string |  optional  | số điện thoại giới thiệu
        `otp` | string |  optional  | gửi tin nhắn (DV SAHA gửi tới 8085)
        `otp_from` | string |  optional  | phone(từ sdt)  email(từ email) mặc định là phone
    
<!-- END_82a3f96b6868e275c58e53dae3e48895 -->

#Customer/Đăng nhập


<!-- START_fd2a06790d4739596ae788573bc3ee83 -->
## Login

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"in","password":"ex"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/login"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "in",
    "password": "ex"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/login`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `password` | string |  required  | Password
    
<!-- END_fd2a06790d4739596ae788573bc3ee83 -->

<!-- START_2cd07714b2a63d03bdb7832031b7e938 -->
## Lấy lại mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/reset_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"eum","password":"quo","otp":"aliquam","otp_from":"dolorem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/reset_password"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "eum",
    "password": "quo",
    "otp": "aliquam",
    "otp_from": "dolorem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/reset_password`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `password` | string |  required  | Mật khẩu mới
        `otp` | string |  optional  | gửi tin nhắn (DV SAHA gửi tới 8085)
        `otp_from` | string |  optional  | phone(từ sdt)  email(từ email) mặc định là phone
    
<!-- END_2cd07714b2a63d03bdb7832031b7e938 -->

<!-- START_33eb1bd769b2422c17095880d417f639 -->
## Kiểm tra email,phone_number đã tồn tại
Sẽ ưu tiên kiểm tra phone_number (kết quả true tồn tại, false không tồn tại)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/login/check_exists" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"fugit","email":"quia"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/login/check_exists"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "fugit",
    "email": "quia"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/login/check_exists`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | required |  optional  | phone_number
        `email` | string |  required  | email
    
<!-- END_33eb1bd769b2422c17095880d417f639 -->

<!-- START_d97ab30712e358f90b3cc808108b92ba -->
## Thay đổi mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/change_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"password":"rerum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/change_password"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "password": "rerum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/change_password`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `password` | string |  required  | Mật khẩu mới
    
<!-- END_d97ab30712e358f90b3cc808108b92ba -->

#Customer/Đơn hàng


<!-- START_fd2cd636d5883001c4481d01280cd5d9 -->
## Đặt hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/perferendis/pos/carts/orders" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"payment_method_id":7,"payment_partner_id":17,"partner_shipper_id":16,"shipper_type":16,"total_shipping_fee":7,"customer_address_id":3,"customer_note":"quos","collaborator_by_customer_id":14,"agency_by_customer_id":11,"phone":"sunt","name":"eius","amount_money":13417.12244,"email":"rem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/perferendis/pos/carts/orders"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "payment_method_id": 7,
    "payment_partner_id": 17,
    "partner_shipper_id": 16,
    "shipper_type": 16,
    "total_shipping_fee": 7,
    "customer_address_id": 3,
    "customer_note": "quos",
    "collaborator_by_customer_id": 14,
    "agency_by_customer_id": 11,
    "phone": "sunt",
    "name": "eius",
    "amount_money": 13417.12244,
    "email": "rem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/pos/carts/orders`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  optional  | string required Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `payment_method_id` | integer |  optional  | ID phương thức thanh toán là param payment_method_id ở API payment_methods
        `payment_partner_id` | integer |  optional  | ID Đối tác thanh toán là param id ở API payment_methods
        `partner_shipper_id` | integer |  required  | ID nhà giao hàng
        `shipper_type` | integer |  required  | (partner_shipper_id != null) Kiểu giao (0 tiêu chuẩn - 1 siêu tốc)
        `total_shipping_fee` | integer |  required  | (partner_shipper_id != null) Tổng tiền giao hàng
        `customer_address_id` | integer |  required  | ID địa chỉ khách hàng
        `customer_note` | string |  required  | Ghi chú khách hàng
        `collaborator_by_customer_id` | integer |  optional  | customer  ID CTV
        `agency_by_customer_id` | integer |  optional  | ID customer Đại lý
        `phone` | string |  optional  | Số điện thoại customer
        `name` | string |  optional  | Tên khách hàng
        `amount_money` | float |  optional  | Số tiền thanh toán
        `email` | string |  optional  | email khách hàng
    
<!-- END_fd2cd636d5883001c4481d01280cd5d9 -->

<!-- START_eb68d6de32102f1c2e67e528e830209d -->
## Danh sách Order
Trạng thái đơn hàng saha
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đang chuẩn bị hàng (PACKING)
- Hết hàng (OUT_OF_STOCK)
- Shop huỷ (USER_CANCELLED)
- Khách đã hủy (CUSTOMER_CANCELLED)
- Đang giao hàng (SHIPPING)
- Lỗi giao hàng (DELIVERY_ERROR)
- Đã hoàn thành (COMPLETED)
- Chờ trả hàng (CUSTOMER_RETURNING)
- Đã trả hàng (CUSTOMER_HAS_RETURNS)
############################################################################
Trạng thái thanh toán
- Chưa thanh toán (UNPAID)
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đã thanh toán (PAID)
- Đã thanh toán một phần (PARTIALLY_PAID)
- Đã hủy (CANCELLED)
- Đã hoàn tiền (REFUNDS)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/kds/collaborator/orders?page=15&search=et&sort_by=laborum&descending=iusto&field_by=expedita&field_by_value=consequatur&time_from=animi&time_to=4" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/collaborator/orders"
);

let params = {
    "page": "15",
    "search": "et",
    "sort_by": "laborum",
    "descending": "iusto",
    "field_by": "expedita",
    "field_by_value": "consequatur",
    "time_from": "animi",
    "time_to": "4",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/collaborator/orders`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `field_by` |  optional  | Chọn trường nào để lấy
    `field_by_value` |  optional  | Giá trị trường đó
    `time_from` |  optional  | Từ thời gian nào
    `time_to` |  optional  | Đến thời gian nào.

<!-- END_eb68d6de32102f1c2e67e528e830209d -->

<!-- START_8533f7ba81fb09fdd34f2f57b0c4b865 -->
## Danh sách Order
Trạng thái đơn hàng saha
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đang chuẩn bị hàng (PACKING)
- Hết hàng (OUT_OF_STOCK)
- Shop huỷ (USER_CANCELLED)
- Khách đã hủy (CUSTOMER_CANCELLED)
- Đang giao hàng (SHIPPING)
- Lỗi giao hàng (DELIVERY_ERROR)
- Đã hoàn thành (COMPLETED)
- Chờ trả hàng (CUSTOMER_RETURNING)
- Đã trả hàng (CUSTOMER_HAS_RETURNS)
############################################################################
Trạng thái thanh toán
- Chưa thanh toán (UNPAID)
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đã thanh toán (PAID)
- Đã thanh toán một phần (PARTIALLY_PAID)
- Đã hủy (CANCELLED)
- Đã hoàn tiền (REFUNDS)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/kds/agency/orders?page=2&search=saepe&sort_by=fugiat&descending=quia&field_by=aut&field_by_value=dolores&time_from=quidem&time_to=4" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/agency/orders"
);

let params = {
    "page": "2",
    "search": "saepe",
    "sort_by": "fugiat",
    "descending": "quia",
    "field_by": "aut",
    "field_by_value": "dolores",
    "time_from": "quidem",
    "time_to": "4",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/agency/orders`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `field_by` |  optional  | Chọn trường nào để lấy
    `field_by_value` |  optional  | Giá trị trường đó
    `time_from` |  optional  | Từ thời gian nào
    `time_to` |  optional  | Đến thời gian nào.

<!-- END_8533f7ba81fb09fdd34f2f57b0c4b865 -->

<!-- START_0284969b533f97be419d9cb50e6564ce -->
## Đặt hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/fuga/carts/orders" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"payment_method_id":6,"payment_partner_id":9,"partner_shipper_id":15,"shipper_type":11,"total_shipping_fee":4,"customer_address_id":1,"customer_note":"et","collaborator_by_customer_id":12,"agency_by_customer_id":13,"phone":"ipsum","name":"odio","amount_money":332689.66222,"email":"delectus"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/fuga/carts/orders"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "payment_method_id": 6,
    "payment_partner_id": 9,
    "partner_shipper_id": 15,
    "shipper_type": 11,
    "total_shipping_fee": 4,
    "customer_address_id": 1,
    "customer_note": "et",
    "collaborator_by_customer_id": 12,
    "agency_by_customer_id": 13,
    "phone": "ipsum",
    "name": "odio",
    "amount_money": 332689.66222,
    "email": "delectus"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/carts/orders`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  optional  | string required Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `payment_method_id` | integer |  optional  | ID phương thức thanh toán là param payment_method_id ở API payment_methods
        `payment_partner_id` | integer |  optional  | ID Đối tác thanh toán là param id ở API payment_methods
        `partner_shipper_id` | integer |  required  | ID nhà giao hàng
        `shipper_type` | integer |  required  | (partner_shipper_id != null) Kiểu giao (0 tiêu chuẩn - 1 siêu tốc)
        `total_shipping_fee` | integer |  required  | (partner_shipper_id != null) Tổng tiền giao hàng
        `customer_address_id` | integer |  required  | ID địa chỉ khách hàng
        `customer_note` | string |  required  | Ghi chú khách hàng
        `collaborator_by_customer_id` | integer |  optional  | customer  ID CTV
        `agency_by_customer_id` | integer |  optional  | ID customer Đại lý
        `phone` | string |  optional  | Số điện thoại customer
        `name` | string |  optional  | Tên khách hàng
        `amount_money` | float |  optional  | Số tiền thanh toán
        `email` | string |  optional  | email khách hàng
    
<!-- END_0284969b533f97be419d9cb50e6564ce -->

<!-- START_028a6709b093e190bf8e4fae2df07d1e -->
## Hủy đơn hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/kds/carts/orders/cancel" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"1","note":"nemo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/carts/orders/cancel"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "1",
    "note": "nemo"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/carts/orders/cancel`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | required |  optional  | Mã đơn hàng.
        `note` | string |  required  | Lý do
    
<!-- END_028a6709b093e190bf8e4fae2df07d1e -->

<!-- START_3f1970107abf7ec1dd40d0977895e50f -->
## Danh sách Order
Trạng thái đơn hàng saha
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đang chuẩn bị hàng (PACKING)
- Hết hàng (OUT_OF_STOCK)
- Shop huỷ (USER_CANCELLED)
- Khách đã hủy (CUSTOMER_CANCELLED)
- Đang giao hàng (SHIPPING)
- Lỗi giao hàng (DELIVERY_ERROR)
- Đã hoàn thành (COMPLETED)
- Chờ trả hàng (CUSTOMER_RETURNING)
- Đã trả hàng (CUSTOMER_HAS_RETURNS)
############################################################################
Trạng thái thanh toán
- Chưa thanh toán (UNPAID)
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đã thanh toán (PAID)
- Đã thanh toán một phần (PARTIALLY_PAID)
- Đã hủy (CANCELLED)
- Đã hoàn tiền (REFUNDS)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/kds/carts/orders?page=15&search=recusandae&sort_by=reprehenderit&descending=qui&field_by=delectus&field_by_value=commodi&time_from=sit&time_to=4" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/carts/orders"
);

let params = {
    "page": "15",
    "search": "recusandae",
    "sort_by": "reprehenderit",
    "descending": "qui",
    "field_by": "delectus",
    "field_by_value": "commodi",
    "time_from": "sit",
    "time_to": "4",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/carts/orders`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `field_by` |  optional  | Chọn trường nào để lấy
    `field_by_value` |  optional  | Giá trị trường đó
    `time_from` |  optional  | Từ thời gian nào
    `time_to` |  optional  | Đến thời gian nào.

<!-- END_3f1970107abf7ec1dd40d0977895e50f -->

<!-- START_c657c4b0b5508eed5a717c4ec1135c21 -->
## Lấy thông tin 1 đơn hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/kds/carts/orders/order_code" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/carts/orders/order_code"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/carts/orders/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
    `order_code` |  required  | order_code.

<!-- END_c657c4b0b5508eed5a717c4ec1135c21 -->

<!-- START_46c4fb18d32beb8b3415999f677d89d0 -->
## Lịch sử trạng thái đơn hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/kds/carts/orders/status_records/kds" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/carts/orders/status_records/kds"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/carts/orders/status_records/{order_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
    `order_id` |  required  | order_id.

<!-- END_46c4fb18d32beb8b3415999f677d89d0 -->

<!-- START_f1f9b1657dd42cf1c74806e6b0c7335c -->
## Thay đổi phương thức thanh toán

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/kds/carts/orders/change_payment_method/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"payment_method_id":"adipisci","payment_partner_id":"quam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/kds/carts/orders/change_payment_method/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "payment_method_id": "adipisci",
    "payment_partner_id": "quam"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/carts/orders/change_payment_method/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `payment_method_id` | Id |  optional  | phương thức thanh toán
        `payment_partner_id` | Id |  optional  | hình thức thanh toán
    
<!-- END_f1f9b1657dd42cf1c74806e6b0c7335c -->

#Customer/Đại lý


<!-- START_24aab6c2399383b8908c1b1041d34168 -->
## Đăng ký dai ly

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/agency/reg" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_agency":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/reg"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_agency": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/agency/reg`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_agency` | boolean |  optional  | đăng ký hay không hay không (true false)
    
<!-- END_24aab6c2399383b8908c1b1041d34168 -->

<!-- START_589dd29f692b7008fa265f59f5269431 -->
## Thông tin Đại lý

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/agency/account" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_agency":true,"payment_auto":true,"first_and_last_name":"dolorum","cmnd":"et","date_range":"assumenda","issued_by":"aut","front_card":"suscipit","back_card":"quam","bank":"repudiandae","account_number":"dolore","account_name":"eum","branch":"id"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/account"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_agency": true,
    "payment_auto": true,
    "first_and_last_name": "dolorum",
    "cmnd": "et",
    "date_range": "assumenda",
    "issued_by": "aut",
    "front_card": "suscipit",
    "back_card": "quam",
    "bank": "repudiandae",
    "account_number": "dolore",
    "account_name": "eum",
    "branch": "id"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/agency/account`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_agency` | boolean |  optional  | đăng ký hay không hay không (true false)
        `payment_auto` | boolean |  optional  | Bật tự động để user quyết toán
        `first_and_last_name` | Họ |  optional  | và tên
        `cmnd` | CMND |  optional  | 
        `date_range` | ngày |  optional  | cấp
        `issued_by` | Nơi |  optional  | cấp
        `front_card` | Mặt |  optional  | trước link
        `back_card` | Mật |  optional  | sau link
        `bank` | Tên |  optional  | ngân hàng
        `account_number` | Số |  optional  | tài khoản
        `account_name` | Tên |  optional  | tài khoản
        `branch` | Chi |  optional  | nhánh
    
<!-- END_589dd29f692b7008fa265f59f5269431 -->

<!-- START_c2af26aab30c656df8959114179f0401 -->
## api/customer/{store_code}/agency/account
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/agency/account" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/account"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/agency/account`


<!-- END_c2af26aab30c656df8959114179f0401 -->

<!-- START_85eac81d2c62d945851c0271ca43b89b -->
## Báo cáo thưởng  bậc thang

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/agency/bonus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/bonus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/agency/bonus`


<!-- END_85eac81d2c62d945851c0271ca43b89b -->

<!-- START_2630f32e9553f91c6590f2ef93e00f61 -->
## Nhận thưởng tháng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/agency/bonus/take" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"month":"ducimus","year":"nihil"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/bonus/take"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "month": "ducimus",
    "year": "nihil"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/agency/bonus/take`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `month` | Tháng |  optional  | muốn nhận VD: 2
        `year` | Năm |  optional  | muốn nhận VD: 2012
    
<!-- END_2630f32e9553f91c6590f2ef93e00f61 -->

<!-- START_4edda088e706e68bdd707cbbbc084a19 -->
## Thông tin tổng quan

Doanh thu hiện tại

type_rose 0 doanh sô - 1 hoa hồng

total_final daonh số tháng này

share_agency tổng tiền hoa đồng chia sẻ

received_month_bonus Đã nhận thưởng tháng hay chưa

number_order số lượng đơn hàng tháng này

allow_payment_request cho phép yêu cầu thanh toán

payment_1_of_month định kỳ thanh toán 1

payment_16_of_month định kỳ thanh toán 15

payment_limit Giới hạn yêu cầu thanh toán

has_payment_request có yêu cầu thanh toán hay không

money_payment_request Số tiền yêu cầu hiện tại

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/agency/info" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/agency/info"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/agency/info`


<!-- END_4edda088e706e68bdd707cbbbc084a19 -->

#Customer/Địa chỉ khách hàng


<!-- START_a2dfaae8cd1b632db8385abaf2c78370 -->
## Thêm địa chỉ cho store

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/address" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"aliquid","address_detail":"sit","country":9,"province":6,"district":2,"village":17,"wards":4,"postcode":"necessitatibus","email":"iusto","phone":"quia","is_default":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/address"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "aliquid",
    "address_detail": "sit",
    "country": 9,
    "province": 6,
    "district": 2,
    "village": 17,
    "wards": 4,
    "postcode": "necessitatibus",
    "email": "iusto",
    "phone": "quia",
    "is_default": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/address`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | họ tên
        `address_detail` | string |  optional  | Địa chỉ chi tiết
        `country` | integer |  required  | id country
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `village` | integer |  required  | id village
        `wards` | integer |  required  | id wards
        `postcode` | string |  required  | postcode
        `email` | string |  required  | email
        `phone` | string |  required  | phone
        `is_default` | boolean |  required  | Địa chỉ mặc định hay không
    
<!-- END_a2dfaae8cd1b632db8385abaf2c78370 -->

<!-- START_96d7777ce381d9215d883d588019bc49 -->
## Cập nhật địa chỉ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/1/address/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"quia","address_detail":"exercitationem","country":8,"province":1,"district":3,"village":17,"wards":20,"postcode":"quam","email":"quia","phone":"vel","is_default_pickup":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/address/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "quia",
    "address_detail": "exercitationem",
    "country": 8,
    "province": 1,
    "district": 3,
    "village": 17,
    "wards": 20,
    "postcode": "quam",
    "email": "quia",
    "phone": "vel",
    "is_default_pickup": true
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/address/{customer_address_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_address_id` |  required  | id địa chỉ cần sửa
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | họ tên
        `address_detail` | string |  optional  | Địa chỉ chi tiết
        `country` | integer |  required  | id country
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `village` | integer |  required  | id village
        `wards` | integer |  required  | id wards
        `postcode` | string |  required  | postcode
        `email` | string |  required  | email
        `phone` | string |  required  | phone
        `is_default_pickup` | boolean |  required  | Địa chỉ mặc định hay không
    
<!-- END_96d7777ce381d9215d883d588019bc49 -->

<!-- START_4d22473ba3e7c7f624bf3fdd65a6ecaf -->
## Xem tất cả address

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/address" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/address"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/address`


<!-- END_4d22473ba3e7c7f624bf3fdd65a6ecaf -->

<!-- START_e3aa7831f42a2305ba5b2cf67bd20b59 -->
## xóa một địa chỉ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/customer/est/address/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/est/address/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/customer/{store_code}/address/{customer_address_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID địa chỉ cần xóa

<!-- END_e3aa7831f42a2305ba5b2cf67bd20b59 -->

#Giao hàng/Đơn hàng cho nhà vận chuyển


<!-- START_15b762f8a3484965706dddd3df0a4777 -->
## Gửi đơn hàng cho nhà vận chuyển đăng đơn hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/shipper/send_order" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"quidem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipper/send_order"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "quidem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/shipper/send_order`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | string |  optional  | code đơn hàng
    
<!-- END_15b762f8a3484965706dddd3df0a4777 -->

<!-- START_0480cd6a9b30bc728c34caa6e1777030 -->
## Lịch sử trạng thái đơn hàng từ nhà vận chuyển

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/shipper/history_order_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"natus"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipper/history_order_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "natus"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/shipper/history_order_status`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | string |  optional  | code đơn hàng
    
<!-- END_0480cd6a9b30bc728c34caa6e1777030 -->

<!-- START_900fc2e00a8044e3796bbfa6c3b826cd -->
## Lấy trạng thái đơn hàng và cập nhật

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/shipper/order_and_payment_status/ipsam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_update":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipper/order_and_payment_status/ipsam"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_update": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/shipper/order_and_payment_status/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `order_code` |  optional  | string code hàng
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_update` | boolean |  optional  | cho phép update trạng thái đơn hàng
    
<!-- END_900fc2e00a8044e3796bbfa6c3b826cd -->

#Gui OTP


<!-- START_47292b8e3a04072eeb4c8c60c399c4d1 -->
## Send

> Example request:

```bash
curl -X POST \
    "http://localhost/api/send_otp" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/send_otp"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/send_otp`


<!-- END_47292b8e3a04072eeb4c8c60c399c4d1 -->

#In/in hóa đơn


<!-- START_8b7c372d3c00df508248b1af62a092d2 -->
## In hóa đơn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/print/bill/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/print/bill/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/print/bill/{order_code}`


<!-- END_8b7c372d3c00df508248b1af62a092d2 -->

#Kiểu cửa hàng


<!-- START_9178edc64f285a222463c717e639b5c5 -->
## Danh sách kiểu cửa hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/type_of_store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/type_of_store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/type_of_store`


<!-- END_9178edc64f285a222463c717e639b5c5 -->

#Nơi chốn


<!-- START_254a33c50b7024631f5710c66c10a4ec -->
## Lấy danh sách vùng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/place/vn/commodi/facere" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/place/vn/commodi/facere"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/place/vn/{type}/{parent_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `type` |  required  | mục cần lấy (  province(tỉnh,thành phố) | district(quận,huyện) | wards(phường,xã))
    `parent_id` |  required  | id mục cha, riêng province có thể không cần

<!-- END_254a33c50b7024631f5710c66c10a4ec -->

<!-- START_771993d39791d36e85d775e76660728d -->
## Lấy danh sách vùng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/place/vn/non" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/place/vn/non"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/place/vn/{type}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `type` |  required  | mục cần lấy (  province(tỉnh,thành phố) | district(quận,huyện) | wards(phường,xã))
    `parent_id` |  required  | id mục cha, riêng province có thể không cần

<!-- END_771993d39791d36e85d775e76660728d -->

#Thêm vào lịch sử


<!-- START_50631e6532adf60d356e10a03e6d4d32 -->
## api/customer/{store_code}/search_histories
> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/search_histories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"text":"eveniet","device_id":"sapiente"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/search_histories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "text": "eveniet",
    "device_id": "sapiente"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/search_histories`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `text` | string |  required  | Nội dung tìm kiếm
        `device_id` | trường |  optional  | hợp chưa đăng nhập
    
<!-- END_50631e6532adf60d356e10a03e6d4d32 -->

#Upload video


<!-- START_59ee96c738b1698066925e6b55db1f79 -->
## Upload 1 video

> Example request:

```bash
curl -X POST \
    "http://localhost/api/videos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"video":"ut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/videos"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "video": "ut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/videos`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `video` | file |  required  | File video
    
<!-- END_59ee96c738b1698066925e6b55db1f79 -->

#Upload ảnh


<!-- START_e22775c0644ec2b90c5987a008b0e1fd -->
## Upload 1 ảnh

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/images" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"image":"reiciendis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/images"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "image": "reiciendis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/images`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `image` | file |  required  | File ảnh
    
<!-- END_e22775c0644ec2b90c5987a008b0e1fd -->

<!-- START_cbf2cae98c8b066863480d1d4dfe460f -->
## Danh sách ảnh

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/images" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/images"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/images`


<!-- END_cbf2cae98c8b066863480d1d4dfe460f -->

<!-- START_59fb984cefeed532e6499ec5d6fd6819 -->
## Thông tin 1 ảnh

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/images/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/images/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/images/{image_id}`


<!-- END_59fb984cefeed532e6499ec5d6fd6819 -->

<!-- START_e066bf8e58a7be31f3132fcac6617d87 -->
## Cập nhật 1 ảnh

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/images/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"remi_name":"at"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/images/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "remi_name": "at"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/images/{image_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `remi_name` | string |  required  | Tên ảnh gợi ý
    
<!-- END_e066bf8e58a7be31f3132fcac6617d87 -->

<!-- START_04e5e6be5f914346acf195d4e9476886 -->
## Cập nhật 1 ảnh

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/images/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"remi_name":"omnis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/images/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "remi_name": "omnis"
}

fetch(url, {
    method: "DELETE",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/images/{image_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `remi_name` | string |  required  | Tên ảnh gợi ý
    
<!-- END_04e5e6be5f914346acf195d4e9476886 -->

<!-- START_204613676cab89a55dfdc7d81f16a281 -->
## Upload 1 ảnh

> Example request:

```bash
curl -X POST \
    "http://localhost/api/images" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"image":"voluptatem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/images"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "image": "voluptatem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/images`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `image` | file |  required  | File ảnh
    
<!-- END_204613676cab89a55dfdc7d81f16a281 -->

<!-- START_dbcda95eb6ccba996a406c6b6ff5eafa -->
## Upload 1 ảnh

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/images" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"image":"quia"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/images"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "image": "quia"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/images`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `image` | file |  required  | File ảnh
    
<!-- END_dbcda95eb6ccba996a406c6b6ff5eafa -->

<!-- START_3cf7432209d43de45c19f1ad1063b398 -->
## Upload 1 ảnh

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/images" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"image":"quos"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/images"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "image": "quos"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/images`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `image` | file |  required  | File ảnh
    
<!-- END_3cf7432209d43de45c19f1ad1063b398 -->

#User/AppTheme


APIs AppTheme
<!-- START_606d43d8125ffa9e631fba038bd1639a -->
## Cập nhật AppTheme
Gửi một trong các trường lên để cập nhật

> Example request:

```bash
curl -X POST \
    "http://localhost/api/app-theme/rerum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"carousel_app_images":"similique"}'

```

```javascript
const url = new URL(
    "http://localhost/api/app-theme/rerum"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "carousel_app_images": "similique"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/app-theme/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `carousel_app_images` | List&lt;json&gt; |  optional  | VD: [ {image_url:"link",title:"title"} ]
    
<!-- END_606d43d8125ffa9e631fba038bd1639a -->

<!-- START_89128e30d4bd289c26a5df7c816cdd92 -->
## Thông tin AppTheme

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/app-theme/architecto" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/app-theme/architecto"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/app-theme/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy

<!-- END_89128e30d4bd289c26a5df7c816cdd92 -->

<!-- START_7a8f73491fc7e7ed250d093df7ea7d4c -->
## Cập nhật thứ tự danh sách layout (bố cục)
Định nghĩa type_layout gồm: BUTTONS (model HomeButton),PRODUCTS_DISCOUNT(model Product),PRODUCTS_TOP_SALES(model Product),PRODUCTS_NEW (model Product),POSTS_NEW (model Posy),
Định nghĩa type_action_more:  PRODUCTS_DISCOUNT, PRODUCTS_TOP_SALES, PRODUCTS_NEW, CATEGORY_POST
Truyền đầy đủ danh sách trong đủ item là json gồm title, type_layout, type_action_more, hide (ko truyền sẽ mặc định hiển thị)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/app-theme/1/layout_sort" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"layouts":"rerum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/app-theme/1/layout_sort"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "layouts": "rerum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/app-theme/{store_code}/layout_sort`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `layouts` | List&lt;json&gt; |  optional  | VD: [ {title:"title", type_layout:"PRODUCTS_DISCOUNT",type_action_more:"PRODUCTS_DISCOUNT",} ]
    
<!-- END_7a8f73491fc7e7ed250d093df7ea7d4c -->

<!-- START_d15ba3f3420b9209fb713a956028ed71 -->
## Cập nhật Home Button
Gửi danh sách button lên type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE

> Example request:

```bash
curl -X POST \
    "http://localhost/api/app-theme/1/home_buttons" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"home_buttons":"porro"}'

```

```javascript
const url = new URL(
    "http://localhost/api/app-theme/1/home_buttons"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "home_buttons": "porro"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/app-theme/{store_code}/home_buttons`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `home_buttons` | List&lt;json&gt; |  optional  | VD: [ {image_url:"link",title:"title", type_action:"PRODUCT", value:"gia trị thực thi"} ]
    
<!-- END_d15ba3f3420b9209fb713a956028ed71 -->

<!-- START_6ad51435674550592188a7019f39555d -->
## lấy ds Home Button
Gửi danh sách button lên type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/app-theme/1/home_buttons" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"home_buttons":"ea"}'

```

```javascript
const url = new URL(
    "http://localhost/api/app-theme/1/home_buttons"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "home_buttons": "ea"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/app-theme/{store_code}/home_buttons`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `home_buttons` | List&lt;json&gt; |  optional  | VD: [ {image_url:"link",title:"title", type_action:"PRODUCT", value:"gia trị thực thi"} ]
    
<!-- END_6ad51435674550592188a7019f39555d -->

#User/Banner quảng cáo


<!-- START_592e2ca411f33b1f2f8ad0625fe095ee -->
## Tạo mục 1 banner web

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/totam/banner_ads" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"natus","image_url":"nulla","type":"maxime"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/totam/banner_ads"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "natus",
    "image_url": "nulla",
    "type": "maxime"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/banner_ads`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề quảng cáo
        `image_url` | required |  optional  | link ảnh
        `type` | required |  optional  | ( 0 dưới banner,  1 trên sp nổi bật, 2 trên sp mới, 3 trên sản phẩm khuyến mãi, 4 trên danh sách tin tức, 5 trên footer, 6 dưới danh mục sản phẩm, 7 dưới danh mục tin tức, 8 trên header )
    
<!-- END_592e2ca411f33b1f2f8ad0625fe095ee -->

<!-- START_e51706028aa833e0455f52b21444a70d -->
## xóa một 1 banner web

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/consequatur/banner_ads/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/consequatur/banner_ads/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/banner_ads/{banner_ad_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID BannerAd cần xóa thông tin.

<!-- END_e51706028aa833e0455f52b21444a70d -->

<!-- START_2c29cbad55bde9d25028674d108873d4 -->
## Danh sách banner quảng cáo web

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/asperiores/banner_ads" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/asperiores/banner_ads"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/banner_ads`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_2c29cbad55bde9d25028674d108873d4 -->

<!-- START_1a5713181c531e079e19e80c744c2bff -->
## update một BannerAd web

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/aut/banner_ads/fuga" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"aut","image_url":"reiciendis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/aut/banner_ads/fuga"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "aut",
    "image_url": "reiciendis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/banner_ads/{banner_ad_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `banner_ad_id` |  required  | BannerAd_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tên danh mục
        `image_url` | file |  required  | Ảnh (hoặc truyền lên image_url)
    
<!-- END_1a5713181c531e079e19e80c744c2bff -->

<!-- START_9cdc7dcceed3a5642a64465f4af6cf22 -->
## Tạo mục 1 banner app

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/maxime/banner_ads_app" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"aut","image_url":"provident","type_action":"vero","value":"omnis","position":"eos","c\u00f3":"beatae"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/maxime/banner_ads_app"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "aut",
    "image_url": "provident",
    "type_action": "vero",
    "value": "omnis",
    "position": "eos",
    "c\u00f3": "beatae"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/banner_ads_app`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề quảng cáo
        `image_url` | required |  optional  | link ảnh
        `type_action` | string |  optional  | gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE,BONUS_PRODUCT,COMBO
        `value` | string |  optional  | giá trị thực thi
        `position` | required |  optional  | ( 0 dưới banner,  1 trên sp nổi bật, 2 trên sp mới, 3 trên sản phẩm khuyến mãi, 4 trên danh sách tin tức, 5 trên footer, 6 dưới danh mục sản phẩm, 7 dưới danh mục tin tức, 8 trên header )
        `có` | show |  optional  | hay không
    
<!-- END_9cdc7dcceed3a5642a64465f4af6cf22 -->

<!-- START_5b961aac4ad1ec874410d8489c81fdb2 -->
## xóa một 1 banner app

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/molestias/banner_ads_app/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/molestias/banner_ads_app/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/banner_ads_app/{banner_ad_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID BannerAd cần xóa thông tin.

<!-- END_5b961aac4ad1ec874410d8489c81fdb2 -->

<!-- START_d1f790dc1d8beb40a15dcda8669cedf6 -->
## Danh sách banner quảng cáo app

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/reiciendis/banner_ads_app" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/reiciendis/banner_ads_app"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/banner_ads_app`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_d1f790dc1d8beb40a15dcda8669cedf6 -->

<!-- START_9ae07d7d4fe3dad8aa0d50bcb82c1292 -->
## update một BannerAd app

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/pariatur/banner_ads_app/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"quas","image_url":"optio","type_action":"eius","value":"est","position":"qui","c\u00f3":"consequatur"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/pariatur/banner_ads_app/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "quas",
    "image_url": "optio",
    "type_action": "eius",
    "value": "est",
    "position": "qui",
    "c\u00f3": "consequatur"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/banner_ads_app/{banner_ad_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề quảng cáo
        `image_url` | required |  optional  | link ảnh
        `type_action` | string |  optional  | gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE,BONUS_PRODUCT,COMBO
        `value` | string |  optional  | giá trị thực thi
        `position` | required |  optional  | ( 0 dưới banner,  1 trên sp nổi bật, 2 trên sp mới, 3 trên sản phẩm khuyến mãi, 4 trên danh sách tin tức, 5 trên footer, 6 dưới danh mục sản phẩm, 7 dưới danh mục tin tức, 8 trên header )
        `có` | show |  optional  | hay không
    
<!-- END_9ae07d7d4fe3dad8aa0d50bcb82c1292 -->

#User/Bài viết


<!-- START_b4a718bd0619b1d562c2940447d82c4f -->
## Tạo bài viết

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/quis/posts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"et","image_url":"inventore","summary":"omnis","content":"ipsam","published":false,"category_id":"soluta"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quis/posts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "et",
    "image_url": "inventore",
    "summary": "omnis",
    "content": "ipsam",
    "published": false,
    "category_id": "soluta"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/posts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề bài viết
        `image_url` | string |  required  | Link ảnh hoặc có thể gửi data ảnh bằng bodyParam "image"
        `summary` | string |  required  | Nội dung vắn tắt
        `content` | string |  required  | Nội dung bài viết
        `published` | boolean |  required  | Ẩn hiện bài viết
        `category_id` | id |  optional  | category
    
<!-- END_b4a718bd0619b1d562c2940447d82c4f -->

<!-- START_ae7ddcf885cf08546b71cb36f2f171a7 -->
## Danh sách bài viết

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/nemo/posts?page=9&search=ut&sort_by=maiores&descending=iure&category_ids=qui" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/nemo/posts"
);

let params = {
    "page": "9",
    "search": "ut",
    "sort_by": "maiores",
    "descending": "iure",
    "category_ids": "qui",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/posts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `category_ids` |  optional  | Thuộc post category id nào VD: 1,2,3

<!-- END_ae7ddcf885cf08546b71cb36f2f171a7 -->

<!-- START_65dc889d7b33e933fc4f8e680f7a12aa -->
## Thông tin một bài viết

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/deserunt/posts/velit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/deserunt/posts/velit"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/posts/{post_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
    `post_id` |  required  | ID post cần lấy thông tin.

<!-- END_65dc889d7b33e933fc4f8e680f7a12aa -->

<!-- START_977eeaa96ec0d6454d2271bc8f36430f -->
## xóa một bài viết

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/iure/posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/iure/posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/posts/{post_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID bài viết cần xóa thông tin.

<!-- END_977eeaa96ec0d6454d2271bc8f36430f -->

<!-- START_9a07d5bb5614c374c40ebbd729d53b1b -->
## Update bài viết

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/recusandae/posts/sapiente" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"dolores","image_url":"reiciendis","summary":"mollitia","content":"dolore","published":false,"category_id":"veritatis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/recusandae/posts/sapiente"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "dolores",
    "image_url": "reiciendis",
    "summary": "mollitia",
    "content": "dolore",
    "published": false,
    "category_id": "veritatis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/posts/{post_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `post_id` |  required  | ID post cần cập nhật
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề bài viết
        `image_url` | string |  required  | Link ảnh hoặc có thể gửi data ảnh bằng bodyParam "image"
        `summary` | string |  required  | Nội dung vắn tắt
        `content` | string |  required  | Nội dung bài viết
        `published` | boolean |  required  | Ẩn hiện bài viết
        `category_id` | id |  optional  | category
    
<!-- END_9a07d5bb5614c374c40ebbd729d53b1b -->

#User/Bài đăng cộng đồng


<!-- START_90dff58695d47104b870263010803e00 -->
## Thêm bài đăng cộng đồng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/community_posts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"odit","content":"sint","status":"non","images":"non","time_repost":"non","privacy":"velit"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "odit",
    "content": "sint",
    "status": "non",
    "images": "non",
    "time_repost": "non",
    "privacy": "velit"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/community_posts`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | required |  optional  | tên sản phẩm
        `content` | required |  optional  | nội dung
        `status` | required |  optional  | (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
        `time_repost` | required |  optional  | thời gian đăng lại
        `privacy` | required |  optional  | Quyền riêng tư (0 tất cả/ 1 chỉ mình tôi/ 2 bạn bè)
    
<!-- END_90dff58695d47104b870263010803e00 -->

<!-- START_396f2ac2dda936c3558fb5b085bf8b19 -->
## Danh sách Bài đăng cộng đồng của tôi

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/community_posts?search=facere" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts"
);

let params = {
    "search": "facere",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/community_posts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `status` |  optional  | integer required  trạng thái 1 chờ duyệt, 0 đã duyệt, 2 đã ẩn
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `search` |  required  | Tìm tên bài đăng

<!-- END_396f2ac2dda936c3558fb5b085bf8b19 -->

<!-- START_4c2a0f129365c8c7b77e2ffd4be4e1bf -->
## Danh sách Bài đăng cộng đồng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/community_posts/home?search=consequatur" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"privacy":"culpa"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts/home"
);

let params = {
    "search": "consequatur",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "privacy": "culpa"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/community_posts/home`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `status` |  optional  | integer required  trạng thái 1 chờ duyệt, 0 đã duyệt, 2 đã ẩn
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `search` |  required  | Tìm tên bài đăng
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `privacy` | required |  optional  | Quyền riêng tư (0 tất cả/ 1 chỉ mình tôi/ 2 bạn bè)
    
<!-- END_4c2a0f129365c8c7b77e2ffd4be4e1bf -->

<!-- START_3846312b3adc2aa33542c44439dea2c2 -->
## Danh sách Bài đăng của người khác

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/community_posts/customer/1?search=est" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts/customer/1"
);

let params = {
    "search": "est",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/community_posts/customer/{customer_id}`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `search` |  required  | Tìm tên bài đăng

<!-- END_3846312b3adc2aa33542c44439dea2c2 -->

<!-- START_f0e374ef2f66ac2246274d0c46c4b05e -->
## Đăng lại lên top

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/1/community_posts/1/reup" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts/1/reup"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/community_posts/{community_post_id}/reup`


<!-- END_f0e374ef2f66ac2246274d0c46c4b05e -->

<!-- START_3f6b5e82a9bc03d1f7dcf6c3278b786f -->
## Cập nhật bài đăng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/1/community_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","content":"ut","status":"sapiente","images":"in","is_pin":"nam","time_repost":"voluptatum","privacy":"officia"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "et",
    "content": "ut",
    "status": "sapiente",
    "images": "in",
    "is_pin": "nam",
    "time_repost": "voluptatum",
    "privacy": "officia"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/community_posts/{community_post_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | required |  optional  | tên sản phẩm
        `content` | required |  optional  | Nội dung
        `status` | required |  optional  | (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
        `is_pin` | required |  optional  | ghim hay không
        `time_repost` | required |  optional  | thời gian đăng lại
        `privacy` | required |  optional  | Quyền riêng tư (0 tất cả/ 1 chỉ mình tôi/ 2 bạn bè)
    
<!-- END_3f6b5e82a9bc03d1f7dcf6c3278b786f -->

<!-- START_ab23d275435e9e83af1da9407cfba443 -->
## Thông tin 1 bài

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/community_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/community_posts/{community_post_id}`


<!-- END_ab23d275435e9e83af1da9407cfba443 -->

<!-- START_85a2536e440f8d71891203d61bde69d6 -->
## Xóa Cần mua cần bán

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/customer/1/community_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/customer/{store_code}/community_posts/{community_post_id}`


<!-- END_85a2536e440f8d71891203d61bde69d6 -->

#User/Báo cáo


<!-- START_88b31b860ec773f0a7c57baac58faee6 -->
## Báo cáo doanh thu tổng quan

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/adipisci/1/report/overview?collaborator_by_customer_id=numquam&agency_by_customer_id=et&date_from=sit&date_to=impedit&date_from_compare=minima&date_to_compare=sed&branch_id=placeat" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/adipisci/1/report/overview"
);

let params = {
    "collaborator_by_customer_id": "numquam",
    "agency_by_customer_id": "et",
    "date_from": "sit",
    "date_to": "impedit",
    "date_from_compare": "minima",
    "date_to_compare": "sed",
    "branch_id": "placeat",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/report/overview`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `collaborator_by_customer_id` |  optional  | 
    `agency_by_customer_id` |  optional  | 
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `date_from_compare` |  optional  | 
    `date_to_compare` |  optional  | 
    `branch_id` |  optional  | int Branch_id chi nhánh

<!-- END_88b31b860ec773f0a7c57baac58faee6 -->

<!-- START_4c5ede433411ab9843c711be444851a0 -->
## Báo cáo top sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/iure/1/report/top_ten_products?collaborator_by_customer_id=reiciendis&agency_by_customer_id=voluptate&date_from=voluptas&date_to=delectus&date_from_compare=asperiores&date_to_compare=est&branch_id=maxime" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/iure/1/report/top_ten_products"
);

let params = {
    "collaborator_by_customer_id": "reiciendis",
    "agency_by_customer_id": "voluptate",
    "date_from": "voluptas",
    "date_to": "delectus",
    "date_from_compare": "asperiores",
    "date_to_compare": "est",
    "branch_id": "maxime",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/report/top_ten_products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `collaborator_by_customer_id` |  optional  | 
    `agency_by_customer_id` |  optional  | 
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `date_from_compare` |  optional  | 
    `date_to_compare` |  optional  | 
    `branch_id` |  optional  | int Branch_id chi nhánh

<!-- END_4c5ede433411ab9843c711be444851a0 -->

<!-- START_91d724397d5ed55c169a86c9646d44c5 -->
## Báo cáo doanh thu tổng quan

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/exercitationem/report/overview?collaborator_by_customer_id=iste&agency_by_customer_id=eligendi&date_from=mollitia&date_to=voluptatem&date_from_compare=perspiciatis&date_to_compare=saepe&branch_id=rerum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/exercitationem/report/overview"
);

let params = {
    "collaborator_by_customer_id": "iste",
    "agency_by_customer_id": "eligendi",
    "date_from": "mollitia",
    "date_to": "voluptatem",
    "date_from_compare": "perspiciatis",
    "date_to_compare": "saepe",
    "branch_id": "rerum",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/overview`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `collaborator_by_customer_id` |  optional  | 
    `agency_by_customer_id` |  optional  | 
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `date_from_compare` |  optional  | 
    `date_to_compare` |  optional  | 
    `branch_id` |  optional  | int Branch_id chi nhánh

<!-- END_91d724397d5ed55c169a86c9646d44c5 -->

<!-- START_fd23bc4ff73944a936adfad698367aa4 -->
## Báo cáo top sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/sint/report/top_ten_products?collaborator_by_customer_id=nemo&agency_by_customer_id=et&date_from=vitae&date_to=est&date_from_compare=fugit&date_to_compare=nam&branch_id=eos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/sint/report/top_ten_products"
);

let params = {
    "collaborator_by_customer_id": "nemo",
    "agency_by_customer_id": "et",
    "date_from": "vitae",
    "date_to": "est",
    "date_from_compare": "fugit",
    "date_to_compare": "nam",
    "branch_id": "eos",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/top_ten_products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `collaborator_by_customer_id` |  optional  | 
    `agency_by_customer_id` |  optional  | 
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `date_from_compare` |  optional  | 
    `date_to_compare` |  optional  | 
    `branch_id` |  optional  | int Branch_id chi nhánh

<!-- END_fd23bc4ff73944a936adfad698367aa4 -->

<!-- START_c45b62e58024d21c8fa339b85b9785de -->
## Báo cáo doanh thu tổng quan

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/id/agency/report?collaborator_by_customer_id=ut&agency_by_customer_id=corrupti&date_from=nisi&date_to=esse&date_from_compare=et&date_to_compare=tempore&branch_id=exercitationem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/id/agency/report"
);

let params = {
    "collaborator_by_customer_id": "ut",
    "agency_by_customer_id": "corrupti",
    "date_from": "nisi",
    "date_to": "esse",
    "date_from_compare": "et",
    "date_to_compare": "tempore",
    "branch_id": "exercitationem",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/agency/report`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `collaborator_by_customer_id` |  optional  | 
    `agency_by_customer_id` |  optional  | 
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `date_from_compare` |  optional  | 
    `date_to_compare` |  optional  | 
    `branch_id` |  optional  | int Branch_id chi nhánh

<!-- END_c45b62e58024d21c8fa339b85b9785de -->

<!-- START_616aee2aba7a834076bed8a9857e3dda -->
## Báo cáo doanh thu tổng quan

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/et/collaborator/report?collaborator_by_customer_id=sed&agency_by_customer_id=quidem&date_from=asperiores&date_to=ipsum&date_from_compare=perspiciatis&date_to_compare=rerum&branch_id=voluptatum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/et/collaborator/report"
);

let params = {
    "collaborator_by_customer_id": "sed",
    "agency_by_customer_id": "quidem",
    "date_from": "asperiores",
    "date_to": "ipsum",
    "date_from_compare": "perspiciatis",
    "date_to_compare": "rerum",
    "branch_id": "voluptatum",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/collaborator/report`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `collaborator_by_customer_id` |  optional  | 
    `agency_by_customer_id` |  optional  | 
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `date_from_compare` |  optional  | 
    `date_to_compare` |  optional  | 
    `branch_id` |  optional  | int Branch_id chi nhánh

<!-- END_616aee2aba7a834076bed8a9857e3dda -->

#User/Báo cáo kho


<!-- START_1223f527b1e0dfdc48faffe80d05a1ce -->
## Báo cáo nhập xuất tồn

status": 0 hiển thị - số còn lại là ẩn

import_export nhập xuất tồn


has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)

has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)

total_stoking còn hàng

total_out_of_stock' hết hàng

total_hide' ẩn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/autem/report/stock/1/product_import_export_stock?page=9&search=accusantium&sort_by=quas&descending=qui&category_ids=aut&category_children_ids=necessitatibus&details=qui&status=sed&filter_by=exercitationem&filter_option=natus&filter_by_value=repellendus&is_get_all=omnis&limit=facilis&agency_type_id=ipsam&is_show_description=nihil" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/autem/report/stock/1/product_import_export_stock"
);

let params = {
    "page": "9",
    "search": "accusantium",
    "sort_by": "quas",
    "descending": "qui",
    "category_ids": "aut",
    "category_children_ids": "necessitatibus",
    "details": "qui",
    "status": "sed",
    "filter_by": "exercitationem",
    "filter_option": "natus",
    "filter_by_value": "repellendus",
    "is_get_all": "omnis",
    "limit": "facilis",
    "agency_type_id": "ipsam",
    "is_show_description": "nihil",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/stock/{branch_id}/product_import_export_stock`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price
    `descending` |  optional  | Giảm dần không VD: false
    `category_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `category_children_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `details` |  optional  | Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
    `status` |  optional  | (0 -1) còn hàng hay không! không truyền lấy cả 2
    `filter_by` |  optional  | Chọn trường nào để lấy
    `filter_option` |  optional  | Kiểu filter ( > = <)
    `filter_by_value` |  optional  | Giá trị trường đó
    `is_get_all` |  optional  | boolean Lấy tất cá hay không
    `limit` |  optional  | int Số item 1 trangơ
    `agency_type_id` |  optional  | int id Kiểu đại lý
    `is_show_description` |  optional  | bool Cho phép trả về mô tả

<!-- END_1223f527b1e0dfdc48faffe80d05a1ce -->

<!-- START_e5fff10f296229671e248fabbe8b25f4 -->
## Báo cáo  tồn kho

status": 0 hiển thị - số còn lại là ẩn

import_export nhập xuất tồn

has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)

has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)

total_stoking còn hàng

total_out_of_stock' hết hàng

total_hide' ẩn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/quidem/report/stock/1/product_last_inventory?page=3&search=eveniet&sort_by=nemo&descending=voluptatem&category_ids=suscipit&category_children_ids=adipisci&details=eaque&status=aspernatur&filter_by=quo&filter_option=qui&filter_by_value=ut&is_get_all=tenetur&limit=et&agency_type_id=necessitatibus&is_show_description=architecto&date=eligendi" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/quidem/report/stock/1/product_last_inventory"
);

let params = {
    "page": "3",
    "search": "eveniet",
    "sort_by": "nemo",
    "descending": "voluptatem",
    "category_ids": "suscipit",
    "category_children_ids": "adipisci",
    "details": "eaque",
    "status": "aspernatur",
    "filter_by": "quo",
    "filter_option": "qui",
    "filter_by_value": "ut",
    "is_get_all": "tenetur",
    "limit": "et",
    "agency_type_id": "necessitatibus",
    "is_show_description": "architecto",
    "date": "eligendi",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/stock/{branch_id}/product_last_inventory`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price
    `descending` |  optional  | Giảm dần không VD: false
    `category_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `category_children_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `details` |  optional  | Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
    `status` |  optional  | (0 -1) còn hàng hay không! không truyền lấy cả 2
    `filter_by` |  optional  | Chọn trường nào để lấy
    `filter_option` |  optional  | Kiểu filter ( > = <)
    `filter_by_value` |  optional  | Giá trị trường đó
    `is_get_all` |  optional  | boolean Lấy tất cá hay không
    `limit` |  optional  | int Số item 1 trangơ
    `agency_type_id` |  optional  | int id Kiểu đại lý
    `is_show_description` |  optional  | bool Cho phép trả về mô tả
    `date` |  optional  | Date Ngày xem

<!-- END_e5fff10f296229671e248fabbe8b25f4 -->

<!-- START_9c8769dda2869adce14882e00252e3ca -->
## Báo cáo nhập xuất theo thời gian

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/report/stock/1/inventory_histories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/report/stock/1/inventory_histories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/stock/{branch_id}/inventory_histories`


<!-- END_9c8769dda2869adce14882e00252e3ca -->

<!-- START_03c6f49c39cadadc4d8a9b4568c0ac97 -->
## Danh sách phiếu thu chi

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/vel/report/finance/1/revenue_expenditure?recipient_group=ipsum&recipient_references_id=est&search=magnam&is_revenue=molestiae" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/vel/report/finance/1/revenue_expenditure"
);

let params = {
    "recipient_group": "ipsum",
    "recipient_references_id": "est",
    "search": "magnam",
    "is_revenue": "molestiae",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/finance/{branch_id}/revenue_expenditure`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `recipient_group` |  optional  | int id Nhóm khách hàng
    `recipient_references_id` |  optional  | int id ID chủ thể
    `search` |  optional  | Mã phiếu
    `is_revenue` |  optional  | boolean Phải thu không

<!-- END_03c6f49c39cadadc4d8a9b4568c0ac97 -->

#User/Báo cáo tài chính


<!-- START_8a3663d8d9cbc509021a70d834be23f0 -->
## Báo cáo lãi lỗ

Profit and Loss


 sales_revenue Doanh thu bán hàng (1)

 real_money_for_sale Tiền hàng thực bán

 tax_vat = 10000 thuế vat

 customer_delivery_fee phí giao hàng thu của khách

 total_discount Giảm giá

 product_discount giảm giá sản phẩm

 combo giảm giá combo

 voucher giảm giá voucher

 discount  chiết khấu

 selling_expenses Chi phí bán hàng (2)

 cost_of_sales giá vốn bán hàng

 pay_with_points thanh toán bằng điểm

 partner_delivery_fee phí giao hàng đối tác

 other_income  thu nhập khác (3)

 $revenue_auto_create thu tự tạo

 customer_return  khách trả hàng

 other_costs   Chi phí khác (4)

 profit  Lợi nhuận   (1-2+3-4)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/report/finance/1/profit_and_loss?date=minus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/report/finance/1/profit_and_loss"
);

let params = {
    "date": "minus",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/finance/{branch_id}/profit_and_loss`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `date` |  optional  | Date Ngày xem

<!-- END_8a3663d8d9cbc509021a70d834be23f0 -->

<!-- START_2f14f12a7f607658aaab98aa28751860 -->
## Danh sách nhà cung cấp đang nợi\\

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/report/finance/1/supplier_debt?date=ipsa" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/report/finance/1/supplier_debt"
);

let params = {
    "date": "ipsa",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/finance/{branch_id}/supplier_debt`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `date` |  optional  | Date Ngày xem

<!-- END_2f14f12a7f607658aaab98aa28751860 -->

<!-- START_7411487c23ad929aa1f059d3c840db23 -->
## Danh sách khách hàng đang nợ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/report/finance/1/customer_debt?date=perferendis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/report/finance/1/customer_debt"
);

let params = {
    "date": "perferendis",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/report/finance/{branch_id}/customer_debt`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `date` |  optional  | Date Ngày xem

<!-- END_7411487c23ad929aa1f059d3c840db23 -->

#User/Ca làm việc


<!-- START_44425e20961a1ca35112d68c6354e025 -->
## Tạo một Ca

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/quibusdam/1/shifts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"sunt","code":"ipsam","start_work_hour":15,"start_work_minute":19,"end_work_hour":15,"end_work_minute":2,"start_break_hour":9,"start_break_minute":14,"end_break_hour":4,"end_break_minute":9,"minutes_late_allow":18,"minutes_early_leave_allow":6,"days_of_week":"odio"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/quibusdam/1/shifts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "sunt",
    "code": "ipsam",
    "start_work_hour": 15,
    "start_work_minute": 19,
    "end_work_hour": 15,
    "end_work_minute": 2,
    "start_break_hour": 9,
    "start_break_minute": 14,
    "end_break_hour": 4,
    "end_break_minute": 9,
    "minutes_late_allow": 18,
    "minutes_early_leave_allow": 6,
    "days_of_week": "odio"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/shifts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `shift_id` |  required  | shift_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | String |  optional  | Tên ca
        `code` | String |  optional  | Mã ca
        `start_work_hour` | integer |  optional  | Giờ bắt đầu
        `start_work_minute` | integer |  optional  | Phút bắt đầu
        `end_work_hour` | integer |  optional  | giờ kết thúc
        `end_work_minute` | integer |  optional  | phút kết thúc
        `start_break_hour` | integer |  optional  | giờ nghỉ bắt đầu
        `start_break_minute` | integer |  optional  | phút nghỉ bắt đầu
        `end_break_hour` | integer |  optional  | giờ nghỉ kết thúc
        `end_break_minute` | integer |  optional  | phút nghit bắt đầu
        `minutes_late_allow` | integer |  optional  | phút đi trễ cho phép
        `minutes_early_leave_allow` | integer |  optional  | phút đi về sớm cho
        `days_of_week` | List |  optional  | ngày trong tuần VD: [2,3,4,5,6,7]
    
<!-- END_44425e20961a1ca35112d68c6354e025 -->

<!-- START_7fb9250d8a7accb6c9f3c46af4c9fc33 -->
## Danh sách ca làm việc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/shifts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/shifts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/shifts`


<!-- END_7fb9250d8a7accb6c9f3c46af4c9fc33 -->

<!-- START_e0183452fcdff0c44e06d3ded10ffb6e -->
## update một Ca

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store_v2/id/1/shifts/dolor" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"quas","code":"quibusdam","start_work_hour":20,"start_work_minute":9,"end_work_hour":14,"end_work_minute":6,"start_break_hour":4,"start_break_minute":20,"end_break_hour":8,"end_break_minute":18,"minutes_late_allow":9,"minutes_early_leave_allow":4,"days_of_week":"nemo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/id/1/shifts/dolor"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "quas",
    "code": "quibusdam",
    "start_work_hour": 20,
    "start_work_minute": 9,
    "end_work_hour": 14,
    "end_work_minute": 6,
    "start_break_hour": 4,
    "start_break_minute": 20,
    "end_break_hour": 8,
    "end_break_minute": 18,
    "minutes_late_allow": 9,
    "minutes_early_leave_allow": 4,
    "days_of_week": "nemo"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store_v2/{store_code}/{branch_id}/shifts/{shift_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `shift_id` |  required  | shift_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | String |  optional  | Tên ca
        `code` | String |  optional  | Mã ca
        `start_work_hour` | integer |  optional  | Giờ bắt đầu
        `start_work_minute` | integer |  optional  | Phút bắt đầu
        `end_work_hour` | integer |  optional  | giờ kết thúc
        `end_work_minute` | integer |  optional  | phút kết thúc
        `start_break_hour` | integer |  optional  | giờ nghỉ bắt đầu
        `start_break_minute` | integer |  optional  | phút nghỉ bắt đầu
        `end_break_hour` | integer |  optional  | giờ nghỉ kết thúc
        `end_break_minute` | integer |  optional  | phút nghit bắt đầu
        `minutes_late_allow` | integer |  optional  | phút đi trễ cho phép
        `minutes_early_leave_allow` | integer |  optional  | phút đi về sớm cho
        `days_of_week` | List |  optional  | ngày trong tuần VD: [2,3,4,5,6,7]
    
<!-- END_e0183452fcdff0c44e06d3ded10ffb6e -->

<!-- START_4f005b40ed73314c80eb92a35d2998a1 -->
## xem 1 một ca

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/voluptas/1/shifts/dignissimos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/voluptas/1/shifts/dignissimos"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/shifts/{shift_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `shift_id` |  required  | ID ca cần xóa

<!-- END_4f005b40ed73314c80eb92a35d2998a1 -->

<!-- START_be8b1e0b0a8d23aac6c91052eb99092b -->
## xóa một ca

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store_v2/et/1/shifts/ut" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/et/1/shifts/ut"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store_v2/{store_code}/{branch_id}/shifts/{shift_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `shift_id` |  required  | ID ca cần xóa

<!-- END_be8b1e0b0a8d23aac6c91052eb99092b -->

#User/Chi nhánh


<!-- START_79be128e4a64d780176724d9b5a1afe2 -->
## Tạo chi nhánh mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/ratione/branches" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"eius","phone":"tenetur","email":"accusantium","branch_code":"expedita","province":19,"district":1,"wards":18,"address_detail":"ut","postcode":"tempora","is_default":false,"is_default_order_online":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/ratione/branches"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "eius",
    "phone": "tenetur",
    "email": "accusantium",
    "branch_code": "expedita",
    "province": 19,
    "district": 1,
    "wards": 18,
    "address_detail": "ut",
    "postcode": "tempora",
    "is_default": false,
    "is_default_order_online": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/branches`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên chi nhánh
        `phone` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email chi nhánh
        `branch_code` | string |  optional  | Mã chi nhánh
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `wards` | integer |  required  | id wards
        `address_detail` | Địa |  optional  | chỉ chi tiết
        `postcode` | string |  optional  | Mã bưu điện
        `is_default` | boolean |  optional  | is_default
        `is_default_order_online` | boolean |  optional  | Chi nhánh mặc định nhận đơn hàng online
    
<!-- END_79be128e4a64d780176724d9b5a1afe2 -->

<!-- START_c18b1ba856c7cf32ef6136153dccc928 -->
## Danh sách chi nhánh

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/rerum/branches" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/branches"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/branches`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_c18b1ba856c7cf32ef6136153dccc928 -->

<!-- START_2b9a82bb40296036441377ed1fdbae4a -->
## Xóa chi nhánh

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/commodi/branches/ullam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/commodi/branches/ullam"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/branches/{branch_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | ID chi nhánh

<!-- END_2b9a82bb40296036441377ed1fdbae4a -->

<!-- START_23d9524900d66862328e6229d0dfcec0 -->
## Cập nhật chi nhánh

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/aut/branches/optio" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"aut","phone":"natus","email":"cumque","branch_code":"dolore","province":12,"district":8,"wards":11,"address_detail":"quis","postcode":"in","is_default":false,"is_default_order_online":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/aut/branches/optio"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "aut",
    "phone": "natus",
    "email": "cumque",
    "branch_code": "dolore",
    "province": 12,
    "district": 8,
    "wards": 11,
    "address_detail": "quis",
    "postcode": "in",
    "is_default": false,
    "is_default_order_online": false
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/branches/{branch_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | ID chi nhánh
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên chi nhánh
        `phone` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email chi nhánh
        `branch_code` | string |  optional  | Mã chi nhánh
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `wards` | integer |  required  | id wards
        `address_detail` | Địa |  optional  | chỉ chi tiết
        `postcode` | string |  optional  | Mã bưu điện
        `is_default` | boolean |  optional  | is_default
        `is_default_order_online` | boolean |  optional  | Chi nhánh mặc định nhận đơn hàng online
    
<!-- END_23d9524900d66862328e6229d0dfcec0 -->

#User/Chuyển kho


<!-- START_87b4f269e5381c3be876ed77eda7404c -->
## Tạo phiếu chuyển hàng

//0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/et/1/inventory/transfer_stocks" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"beatae","transfer_stock_items":"quae","to_branch_id":20}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/et/1/inventory/transfer_stocks"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "beatae",
    "transfer_stock_items": "quae",
    "to_branch_id": 20
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/inventory/transfer_stocks`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | String |  optional  | ghi chú
        `transfer_stock_items` | List |  optional  | danh sách chuyển hàng [ {quantity:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
        `to_branch_id` | integer |  optional  | id Chi nhánh chuyển đén
    
<!-- END_87b4f269e5381c3be876ed77eda7404c -->

<!-- START_95c3c56d994b21c3d861e29ef5a2bbbe -->
## Danh sách phiếu chuyển kho bên chuyển

//0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/est/1/inventory/transfer_stocks/sender?search=reprehenderit&status=quia" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/est/1/inventory/transfer_stocks/sender"
);

let params = {
    "search": "reprehenderit",
    "status": "quia",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/transfer_stocks/sender`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `search` |  optional  | Mã phiếu
    `status` |  optional  | //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

<!-- END_95c3c56d994b21c3d861e29ef5a2bbbe -->

<!-- START_1dc66513344d744c326c764364c8cea2 -->
## Danh sách phiếu chuyển kho người nhận

//0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/fugiat/1/inventory/transfer_stocks/receiver?search=ea&status=deleniti" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/fugiat/1/inventory/transfer_stocks/receiver"
);

let params = {
    "search": "ea",
    "status": "deleniti",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/transfer_stocks/receiver`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `search` |  optional  | Mã phiếu
    `status` |  optional  | //0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

<!-- END_1dc66513344d744c326c764364c8cea2 -->

<!-- START_5e84bd1aae0308c114af522583b300f0 -->
## Thông tin phiếu chuyển hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/veniam/1/inventory/transfer_stocks/recusandae" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/veniam/1/inventory/transfer_stocks/recusandae"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/transfer_stocks/{transfer_stock_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `transfer_stock_id` |  required  | Id phiếu nhập hàng

<!-- END_5e84bd1aae0308c114af522583b300f0 -->

<!-- START_9331dcb10bcfc41ae582861e9d1c30ee -->
## Cập nhật phiếu chuyển hàng

//0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/1/inventory/transfer_stocks/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"natus","transfer_stock_items":"fugit","to_branch_id":15}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/1/inventory/transfer_stocks/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "natus",
    "transfer_stock_items": "fugit",
    "to_branch_id": 15
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/transfer_stocks/{transfer_stock_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | String |  optional  | ghi chú
        `transfer_stock_items` | List |  optional  | danh sách chuyển hàng [ {quantity:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
        `to_branch_id` | integer |  optional  | id Chi nhánh chuyển đén
    
<!-- END_9331dcb10bcfc41ae582861e9d1c30ee -->

<!-- START_5a748d59c1dc751ceb55510d7d4e8ef8 -->
## Xử lý phiếu nhập kho

//0 Đang chờ chuyển //1 đã hủy chuyển //2 đã chuyển

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/1/inventory/transfer_stocks/1/status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":4}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/1/inventory/transfer_stocks/1/status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 4
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/transfer_stocks/{transfer_stock_id}/status`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | (1 hủy phiếu chuyển , 2 đồng ý nhập kho (chỉ khi ở chi nhánh nhận kho))
    
<!-- END_5a748d59c1dc751ceb55510d7d4e8ef8 -->

#User/Chương trình khuyến mãi

discount_type // 0 gia co dinh - 1 theo phan tram
set_limit_total khi set giá trị true - yêu cầu khách hàng phải mua đủ sản phẩm
<!-- START_549dfa15a64753f550d2bdfcfcc565bc -->
## Tạo combo mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/nihil/combos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"fugit","description":"doloribus","image_url":"rerum","start_time":"odio","end_time":"cupiditate","discount_type":11,"value_discount":139.54,"set_limit_amount":false,"amount":5,"combo_products":"commodi","group_type_id":2,"group_type_name":14,"agency_type_id":3,"agency_type_name":"sit"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/nihil/combos"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "fugit",
    "description": "doloribus",
    "image_url": "rerum",
    "start_time": "odio",
    "end_time": "cupiditate",
    "discount_type": 11,
    "value_discount": 139.54,
    "set_limit_amount": false,
    "amount": 5,
    "combo_products": "commodi",
    "group_type_id": 2,
    "group_type_name": 14,
    "agency_type_id": 3,
    "agency_type_name": "sit"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/combos`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
        `discount_type` | integer |  required  | 0 giám giá cố định - 1 theo %
        `value_discount` | float |  required  | (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `combo_products` | List&lt;json&gt; |  required  | danh sách sản phẩm kèm số lượng [ {product_id:1, quantity: 10} ]
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_549dfa15a64753f550d2bdfcfcc565bc -->

<!-- START_7e833eb92c343f2509bb3b4747e284e8 -->
## Update combo

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/eveniet/combos/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_end":false,"name":"qui","description":"eos","image_url":"dolorem","start_time":"distinctio","end_time":"aut","discount_type":19,"value_discount":179075,"set_limit_amount":false,"amount":10,"combo_products":"repudiandae","group_type_id":5,"group_type_name":9,"agency_type_id":4,"agency_type_name":"officia"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/eveniet/combos/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_end": false,
    "name": "qui",
    "description": "eos",
    "image_url": "dolorem",
    "start_time": "distinctio",
    "end_time": "aut",
    "discount_type": 19,
    "value_discount": 179075,
    "set_limit_amount": false,
    "amount": 10,
    "combo_products": "repudiandae",
    "group_type_id": 5,
    "group_type_name": 9,
    "agency_type_id": 4,
    "agency_type_name": "officia"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/combos/{combo_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `Combo_id` |  required  | Id Combo
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_end` | boolean |  required  | Chương trình đã kết thúc chưa
        `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thời gian kết thúc
        `discount_type` | integer |  required  | (combo_type == 1) 0 giám giá cố định - 1 theo %
        `value_discount` | float |  required  | (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `combo_products` | List&lt;json&gt; |  required  | danh sách sản phẩm kèm số lượng [ {product_id:1, quantity: 10} ]
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_7e833eb92c343f2509bb3b4747e284e8 -->

<!-- START_bbd0f209502a3e8578ce2c88c97d8a0d -->
## Xem 1 combo

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/assumenda/combos/commodi" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/assumenda/combos/commodi"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/combos/{combo_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `combo_id` |  required  | Id Combo

<!-- END_bbd0f209502a3e8578ce2c88c97d8a0d -->

<!-- START_1115f12c03ca0632fd6e85a0115cae3a -->
## Xem tất cả combo chuẩn vị và đang phát hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/repellendus/combos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/repellendus/combos"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/combos`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_1115f12c03ca0632fd6e85a0115cae3a -->

<!-- START_61b0eb9bcf91e566ddecb5c7f1f31676 -->
## Xem tất cả Combo đã kết thúc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/et/combos_end?page=18" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/et/combos_end"
);

let params = {
    "page": "18",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/combos_end`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)

<!-- END_61b0eb9bcf91e566ddecb5c7f1f31676 -->

<!-- START_5a2f80ef57f23b484a82a89cc7f54f3c -->
## xóa một chương trình Combo

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/impedit/combos/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/impedit/combos/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/combos/{combo_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID combo cần xóa thông tin.

<!-- END_5a2f80ef57f23b484a82a89cc7f54f3c -->

#User/Chương trình khuyến mãi tặng thưởng sản phẩm


<!-- START_c54120b4b61a80d067f83acc41b08eda -->
## Tạo tặng thưởng mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/in/bonus_product" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"dignissimos","description":"iure","image_url":"esse","start_time":"et","end_time":"aut","set_limit_amount":false,"amount":10,"ladder_reward":true,"data_ladder":"velit","select_products":"neque","bonus_products":"ex","allows_choose_distribute":true,"allows_all_distribute":true,"multiply_by_number":false,"group_type_id":6,"group_type_name":5,"agency_type_id":6,"agency_type_name":"dolores"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/in/bonus_product"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "dignissimos",
    "description": "iure",
    "image_url": "esse",
    "start_time": "et",
    "end_time": "aut",
    "set_limit_amount": false,
    "amount": 10,
    "ladder_reward": true,
    "data_ladder": "velit",
    "select_products": "neque",
    "bonus_products": "ex",
    "allows_choose_distribute": true,
    "allows_all_distribute": true,
    "multiply_by_number": false,
    "group_type_id": 6,
    "group_type_name": 5,
    "agency_type_id": 6,
    "agency_type_name": "dolores"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/bonus_product`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | Thời gian kết thúc
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `ladder_reward` | boolean |  required  | có phải khuyến mãi tầng ko
        `data_ladder` | { |  optional  | product_id,distribute_name,element_distribute_name,sub_element_distribute_name, , list:[{from_quantity, bonus_quantity, bo_product_id, bo_element_distribute_name, bo_sub_element_distribute_name}] }
        `select_products` | List&lt;json&gt; |  required  | danh sách sản phẩm mua và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1, quantity: 10} ]
        `bonus_products` | List&lt;json&gt; |  required  | danh sách sản phẩm được tặng và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1,, quantity: 10} ]
        `allows_choose_distribute` | boolean |  required  | thêm cái này vào ds nếu cho phép chọn phân loại sp thưởng
        `allows_all_distribute` | boolean |  required  | thêm cái này vào ds nếu cho phép tất cả phân loại được thưởng
        `multiply_by_number` | boolean |  optional  | nhan theo so luong
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_c54120b4b61a80d067f83acc41b08eda -->

<!-- START_06366d620c483bd75d35fb4e0c136cd7 -->
## Update combo

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/doloribus/bonus_product/facilis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_end":false,"name":"veniam","description":"voluptatem","image_url":"reiciendis","start_time":"ut","end_time":"et","multiply_by_number":false,"set_limit_amount":true,"amount":14,"select_products":"enim","bonus_products":"placeat","allows_choose_distribute":false,"group_type_id":15,"group_type_name":19,"agency_type_id":3,"agency_type_name":"tempore"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/doloribus/bonus_product/facilis"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_end": false,
    "name": "veniam",
    "description": "voluptatem",
    "image_url": "reiciendis",
    "start_time": "ut",
    "end_time": "et",
    "multiply_by_number": false,
    "set_limit_amount": true,
    "amount": 14,
    "select_products": "enim",
    "bonus_products": "placeat",
    "allows_choose_distribute": false,
    "group_type_id": 15,
    "group_type_name": 19,
    "agency_type_id": 3,
    "agency_type_name": "tempore"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/bonus_product/{bonus_product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `bonus_product_id` |  required  | Id bonus_product_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_end` | boolean |  required  | Chương trình đã kết thúc chưa
        `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thời gian kết thúc
        `multiply_by_number` | boolean |  optional  | nhan theo so luong
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `select_products` | List&lt;json&gt; |  required  | danh sách sản phẩm mua và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1, quantity: 10} ]
        `bonus_products` | List&lt;json&gt; |  required  | danh sách sản phẩm được tặng và phân loại kèm số lượng [ {product_id:1,distribute_name:1,element_distribute_name:1,sub_element_distribute_name:1,, quantity: 10} ]
        `allows_choose_distribute` | boolean |  required  | thêm cái này vào ds nếu cho phép chọn phân loại sp thưởng
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_06366d620c483bd75d35fb4e0c136cd7 -->

<!-- START_2a5fe2b91d63f288fb65f6c194a0ca95 -->
## Xem 1 Bonus

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/rerum/bonus_product/accusantium" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/bonus_product/accusantium"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/bonus_product/{bonus_product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `bonus_product_id` |  required  | Id bonus_product_id

<!-- END_2a5fe2b91d63f288fb65f6c194a0ca95 -->

<!-- START_db874df0568c2d8afb5c42c32cf04456 -->
## Xem tất cả combo chuẩn vị và đang phát hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/iste/bonus_product" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/iste/bonus_product"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/bonus_product`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_db874df0568c2d8afb5c42c32cf04456 -->

<!-- START_296c3a668aac56103ad8557951a196ac -->
## Xem tất cả Bonus Product đã kết thúc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/quis/bonus_product_end?page=8" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/quis/bonus_product_end"
);

let params = {
    "page": "8",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/bonus_product_end`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)

<!-- END_296c3a668aac56103ad8557951a196ac -->

<!-- START_174faea3b8fcdc326faf751567c25981 -->
## xóa một chương trình Bonus Product

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/molestiae/bonus_product/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/molestiae/bonus_product/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/bonus_product/{bonus_product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID combo cần xóa thông tin.

<!-- END_174faea3b8fcdc326faf751567c25981 -->

#User/Chỉ số


<!-- START_c32bbe0dabef0e064faa1a52b739c4f5 -->
## Lấy tất cả chỉ số đếm

Khách hàng chat cho user
Nhận badges realtime
var socket = io("http://localhost:6441")
socket.on("badges:badges_user:1", function(data) {
  console.log(data)
  })
 1 là user_id

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/badges" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/badges"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/badges`


<!-- END_c32bbe0dabef0e064faa1a52b739c4f5 -->

<!-- START_c893abe6a2eaca1a070b9ef2bc8e1ae8 -->
## Lấy tất cả chỉ số đếm

Khách hàng chat cho user
Nhận badges realtime
var socket = io("http://localhost:6441")
socket.on("badges:badges_user:1", function(data) {
  console.log(data)
  })
 1 là user_id

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/badges" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/badges"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/badges`


<!-- END_c893abe6a2eaca1a070b9ef2bc8e1ae8 -->

#User/Comment


<!-- START_8106db4c3f56cfd30b572cdf51d920cf -->
## Comment bài đăng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/community_comments" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"community_post_id":12,"images":"magnam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_comments"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "community_post_id": 12,
    "images": "magnam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/community_comments`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `community_post_id` | integer |  required  | id bài viết
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
    
<!-- END_8106db4c3f56cfd30b572cdf51d920cf -->

<!-- START_1dbaaf0bddac21076607c339eaeffc1b -->
## Danh sách comment của 1 bài đăng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/community_comments?community_post_id=ipsa" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_comments"
);

let params = {
    "community_post_id": "ipsa",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/community_comments`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `status` |  optional  | integer required trạng thái  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `community_post_id` |  optional  | int id bài viết cần xem

<!-- END_1dbaaf0bddac21076607c339eaeffc1b -->

<!-- START_045a4ed2b38d9cf625186883cd899cce -->
## Cập nhật Commet cộng đồng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/customer/1/community_comments/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"content":"sit","images":"illo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_comments/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "content": "sit",
    "images": "illo"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/customer/{store_code}/community_comments/{community_comment_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `content` | required |  optional  | Content
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
    
<!-- END_045a4ed2b38d9cf625186883cd899cce -->

<!-- START_8c994a68a87721580955366c59333a55 -->
## Xóa comment

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/customer/1/community_comments/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/community_comments/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/customer/{store_code}/community_comments/{community_comment_id}`


<!-- END_8c994a68a87721580955366c59333a55 -->

#User/Comment cong dong


<!-- START_f7f01e89770372e6b5541484b6672d2d -->
## Comment bài đăng cộng đồng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/community_comments" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"community_post_id":20,"content":"ut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_comments"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "community_post_id": 20,
    "content": "ut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/community_comments`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `community_post_id` | integer |  required  | id bài viết
        `content` | required |  optional  | Nội dung
    
<!-- END_f7f01e89770372e6b5541484b6672d2d -->

<!-- START_03a86e8ec1002340b848a95530d7196c -->
## Danh sách comment của 1 bài đăng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/community_comments?community_post_id=laborum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_comments"
);

let params = {
    "community_post_id": "laborum",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/community_comments`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `status` |  optional  | integer required trạng thái  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `community_post_id` |  optional  | int id bài viết cần xem

<!-- END_03a86e8ec1002340b848a95530d7196c -->

<!-- START_cf27d438c7a0a767d67b4bb4e2bdbf3d -->
## Cập nhật Commet

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/community_comments/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"images":"ut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_comments/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "images": "ut"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/community_comments/{community_comment_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
    
<!-- END_cf27d438c7a0a767d67b4bb4e2bdbf3d -->

<!-- START_619cbdedba49c800c473b9f4588342bd -->
## Xóa comment

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/community_comments/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_comments/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/community_comments/{community_comment_id}`


<!-- END_619cbdedba49c800c473b9f4588342bd -->

#User/Cấu hình chung


<!-- START_44fe30a503d13d51a5581b95da606e96 -->
## Lấy thông số cài đặt

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/non/general_settings" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"noti_near_out_stock":false,"noti_stock_count_near":14,"allow_semi_negative":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/non/general_settings"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "noti_near_out_stock": false,
    "noti_stock_count_near": 14,
    "allow_semi_negative": false
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/general_settings`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `noti_near_out_stock` | boolean |  optional  | Gửi thông báo khi hết kho hàng
        `noti_stock_count_near` | integer |  optional  | Số lượng sản phẩm còn lại báo gần hết hàng
        `allow_semi_negative` | boolean |  optional  | Cho phép bán âm
    
<!-- END_44fe30a503d13d51a5581b95da606e96 -->

<!-- START_ed61e02ec12129db7a0a29d89f4f8228 -->
## update Cấu hình

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/repudiandae/general_settings" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"noti_near_out_stock":true,"noti_stock_count_near":4,"allow_semi_negative":false,"email_send_to_customer":"deserunt"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/repudiandae/general_settings"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "noti_near_out_stock": true,
    "noti_stock_count_near": 4,
    "allow_semi_negative": false,
    "email_send_to_customer": "deserunt"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/general_settings`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `noti_near_out_stock` | boolean |  optional  | Gửi thông báo khi hết kho hàng
        `noti_stock_count_near` | integer |  optional  | Số lượng sản phẩm còn lại báo gần hết hàng
        `allow_semi_negative` | boolean |  optional  | Cho phép bán âm
        `email_send_to_customer` | string |  optional  | email gửi tới khách hàng
    
<!-- END_ed61e02ec12129db7a0a29d89f4f8228 -->

#User/Cấu hình kho


<!-- START_cdd85025a2e84545267429f54d99a15c -->
## Cấu hình kho cho sản phẩm

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/non/1/inventory/update_balance" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":17,"distribute_name":"consequatur","element_distribute_name":"assumenda","sub_element_distribute_name":"et","cost_of_capital":782,"stock":3}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/non/1/inventory/update_balance"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": 17,
    "distribute_name": "consequatur",
    "element_distribute_name": "assumenda",
    "sub_element_distribute_name": "et",
    "cost_of_capital": 782,
    "stock": 3
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/update_balance`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | integer |  required  | ID sản phẩm
        `distribute_name` | Tên |  optional  | phân loại  (có thể để trống thì vào mặc định)
        `element_distribute_name` | giá |  optional  | trị phân loại (có thể để trống thì vào mặc định)
        `sub_element_distribute_name` | Giá |  optional  | trị thuộc tính con của phân loại (có thể để trống thì vào mặc định)
        `cost_of_capital` | float |  optional  | giá vốn
        `stock` | integer |  optional  | Tồn kho hiện tại
    
<!-- END_cdd85025a2e84545267429f54d99a15c -->

<!-- START_dcb08fcfd3edbba4af0661e5438c43ec -->
## Lịch sử kho

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/delectus/1/inventory/history" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":5,"distribute_name":"sunt","element_distribute_name":"quod","sub_element_distribute_name":"magni"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/delectus/1/inventory/history"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": 5,
    "distribute_name": "sunt",
    "element_distribute_name": "quod",
    "sub_element_distribute_name": "magni"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/inventory/history`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | integer |  required  | ID sản phẩm
        `distribute_name` | Tên |  optional  | phân loại  (có thể để trống thì vào mặc định)
        `element_distribute_name` | giá |  optional  | trị phân loại (có thể để trống thì vào mặc định)
        `sub_element_distribute_name` | Giá |  optional  | trị thuộc tính con của phân loại (có thể để trống thì vào mặc định)
    
<!-- END_dcb08fcfd3edbba4af0661e5438c43ec -->

#User/Cấu hình ship


APIs AppTheme
<!-- START_addd00759045c628eff9a17156c2fd23 -->
## Lấy cấu hình ship

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/officiis/config_ship" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/officiis/config_ship"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/config_ship`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_addd00759045c628eff9a17156c2fd23 -->

<!-- START_ab9356372951c99eaa9da82d63957982 -->
## Cập nhật cấu hình ship (shipper_id == -1)

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/nisi/config_ship" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_calculate_ship":true,"use_fee_from_partnership":true,"fee_urban":11.8534592,"fee_suburban":152.54,"urban_list_id_province":"placeat"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/nisi/config_ship"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_calculate_ship": true,
    "use_fee_from_partnership": true,
    "fee_urban": 11.8534592,
    "fee_suburban": 152.54,
    "urban_list_id_province": "placeat"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/config_ship`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_calculate_ship` | boolean |  optional  | Cho phép tính phí ship khi customer mua hàng
        `use_fee_from_partnership` | boolean |  optional  | Sử dụng phí vận chuyển từ nhà vận chuyển hay không
        `fee_urban` | float |  optional  | Phí nội thành (khi use_fee_from_partnership = false)
        `fee_suburban` | float |  optional  | phí ngoại thành (khi use_fee_from_partnership = false)
        `urban_list_id_province` | List |  optional  | id tỉnh nội thành
    
<!-- END_ab9356372951c99eaa9da82d63957982 -->

#User/Cấu hình điểm thưởng


<!-- START_30a8ebb4c45e3101a987c0cb5b7aa188 -->
## Lấy cấu hình điểm
point_review int required Điểm được khi đánh giá \n

point_introduce_customer int required Điểm được khi giới thiệu được 1 khách hàng

percent_refund double required Phần trăm hoàn xu (0-100)

order_max_point int required Số điểm tối đa khi mua hàng

is_set_order_max_point boolean required Có set tối đa điểm mua hàng ko

money_a_point double required Số tiền 1 point

allow_use_point_order boolean required Cho phép sử dụng điểm tại order

percent_use_max_point Phần trăm tối đa điểm có thể sử dụng khi mua mỗi đơn hàng

is_set_order_max_point Có set tối đa điểm có thể sử dụng khi mua hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/reward_points" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/reward_points"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/reward_points`


<!-- END_30a8ebb4c45e3101a987c0cb5b7aa188 -->

<!-- START_c33b6ae2fa7963d3204ec7682d7e98ee -->
## Cập nhật cấu hình điểm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/at/reward_points" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"point_review":5,"point_introduce_customer":1,"percent_refund":821710724.9786,"order_max_point":19,"is_set_order_max_point":true,"percent_use_max_point":3,"is_percent_use_max_point":false,"money_a_point":14.56476,"allow_use_point_order":true,"bonus_point_product_to_agency":true,"bonus_point_bonus_product_to_agency":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/at/reward_points"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "point_review": 5,
    "point_introduce_customer": 1,
    "percent_refund": 821710724.9786,
    "order_max_point": 19,
    "is_set_order_max_point": true,
    "percent_use_max_point": 3,
    "is_percent_use_max_point": false,
    "money_a_point": 14.56476,
    "allow_use_point_order": true,
    "bonus_point_product_to_agency": true,
    "bonus_point_bonus_product_to_agency": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/reward_points`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `point_review` | integer |  required  | Điểm được khi đánh giá
        `point_introduce_customer` | integer |  required  | Điểm được khi giới thiệu được 1 khách hàng
        `percent_refund` | float |  required  | Phần trăm hoàn xu (0-100)
        `order_max_point` | integer |  required  | Số điểm tối đa khi mua hàng
        `is_set_order_max_point` | boolean |  required  | Có set tối đa điểm thưởng mua hàng ko
        `percent_use_max_point` | integer |  required  | Phần trăm tối đa điểm có thể sử dụng khi mua mỗi đơn hàng
        `is_percent_use_max_point` | boolean |  required  | Có set tối đa điểm có thể sử dụng khi mua hàng
        `money_a_point` | float |  required  | Số tiền 1 point
        `allow_use_point_order` | boolean |  required  | Cho phép sử dụng điểm tại order
        `bonus_point_product_to_agency` | boolean |  optional  | cho phép thưởng xu khi đại lý mua hàng ko
        `bonus_point_bonus_product_to_agency` | boolean |  optional  | cho phép cộng xu từ sp thưởng ko
    
<!-- END_c33b6ae2fa7963d3204ec7682d7e98ee -->

<!-- START_b73ea1e91bf8aea1db4c65a412a81659 -->
## Khôi phục mặc định

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/accusantium/reward_points/reset" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/accusantium/reward_points/reset"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/reward_points/reset`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_b73ea1e91bf8aea1db4c65a412a81659 -->

<!-- START_8886f3d94d38199bc7862beb88742eea -->
## Lấy cấu hình điểm
point_review int required Điểm được khi đánh giá \n

point_introduce_customer int required Điểm được khi giới thiệu được 1 khách hàng

percent_refund double required Phần trăm hoàn xu (0-100)

order_max_point int required Số điểm tối đa khi mua hàng

is_set_order_max_point boolean required Có set tối đa điểm mua hàng ko

money_a_point double required Số tiền 1 point

allow_use_point_order boolean required Cho phép sử dụng điểm tại order

percent_use_max_point Phần trăm tối đa điểm có thể sử dụng khi mua mỗi đơn hàng

is_set_order_max_point Có set tối đa điểm có thể sử dụng khi mua hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/reward_points" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/reward_points"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/reward_points`


<!-- END_8886f3d94d38199bc7862beb88742eea -->

#User/Danh mục bài viết


Danh mục bài viết
<!-- START_df008a8ded1cbbd69110a2d59b441bd6 -->
## Tạo danh mục bài viết

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/cum/post_categories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"ex","image":"enim","description":"quae"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/cum/post_categories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "ex",
    "image": "enim",
    "description": "quae"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/post_categories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề danh mục
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
        `description` | string |  required  | Nội dung mô tả danh mục
    
<!-- END_df008a8ded1cbbd69110a2d59b441bd6 -->

<!-- START_a15ccbec4506194ae1374461e7923fc3 -->
## Danh sách danh mục Post

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/provident/post_categories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/provident/post_categories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/post_categories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_a15ccbec4506194ae1374461e7923fc3 -->

<!-- START_efb3531418e7de08e883d24e70f181ae -->
## xóa một danh mục Post

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/quia/post_categories/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/quia/post_categories/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/post_categories/{category_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID category cần xóa thông tin.

<!-- END_efb3531418e7de08e883d24e70f181ae -->

#User/Danh mục sản phẩm


APIs AppTheme
<!-- START_12d4fb53aadb7901dbacce3715a5e785 -->
## Tạo danh mục sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/necessitatibus/categories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"eos","image":"minima"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/necessitatibus/categories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "eos",
    "image": "minima"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/categories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên danh mục
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
    
<!-- END_12d4fb53aadb7901dbacce3715a5e785 -->

<!-- START_fe71c93ee68b79e307909dc9cf66cdf7 -->
## Danh sách danh mục sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/eos/categories" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/eos/categories"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/categories`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_fe71c93ee68b79e307909dc9cf66cdf7 -->

<!-- START_b0baec31828cb0e6d893f0208252546d -->
## xóa một danh mục

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/id/categories/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/id/categories/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/categories/{category_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID category cần xóa thông tin.

<!-- END_b0baec31828cb0e6d893f0208252546d -->

<!-- START_076c81f43c5ad2f7dd3cff60a6ee4b1c -->
## update một Category

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/aut/categories/corrupti" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"ea","image":"recusandae","is_show_home":"rerum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/aut/categories/corrupti"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "ea",
    "image": "recusandae",
    "is_show_home": "rerum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/categories/{category_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `category_id` |  required  | Category_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên danh mục
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
        `is_show_home` | required |  optional  | Có show ở màn hình home không
    
<!-- END_076c81f43c5ad2f7dd3cff60a6ee4b1c -->

<!-- START_578efbda29ad5c2579c26a830c99f946 -->
## Sắp xếp lại thứ tự category

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/aut/category/sort" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"List<ids>":"blanditiis","List<positions>":"neque"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/aut/category/sort"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "List<ids>": "blanditiis",
    "List<positions>": "neque"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/category/sort`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `List&lt;ids&gt;` | required |  optional  | List id cate VD: [4,8,9]
        `List&lt;positions&gt;` | required |  optional  | List vị trí theo danh sách id ở trên [1,2,3]
    
<!-- END_578efbda29ad5c2579c26a830c99f946 -->

<!-- START_06ae27fda3cb03fbb74a98f7bafad8b0 -->
## Sắp xếp lại thứ tự category

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/error/post_categories/sort" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"List<ids>":"at","List<positions>":"aut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/error/post_categories/sort"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "List<ids>": "at",
    "List<positions>": "aut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/post_categories/sort`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `List&lt;ids&gt;` | required |  optional  | List id cate VD: [4,8,9]
        `List&lt;positions&gt;` | required |  optional  | List vị trí theo danh sách id ở trên [1,2,3]
    
<!-- END_06ae27fda3cb03fbb74a98f7bafad8b0 -->

#User/Danh mục sản phẩm con


APIs Danh mục sản phẩm
<!-- START_c4994d7b405ab05a38ec26f01d863e5a -->
## Tạo danh mục sản phẩm con

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/voluptatibus/categories/iste/category_children" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","image":"qui"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptatibus/categories/iste/category_children"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "et",
    "image": "qui"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/categories/{category_id}/category_children`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `category_id` |  required  | category_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên danh mục con
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
    
<!-- END_c4994d7b405ab05a38ec26f01d863e5a -->

<!-- START_7deb52342414621cca2975e2e4ff73d5 -->
## update một Category

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/corporis/categories/amet/category_children/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"molestias","image":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/corporis/categories/amet/category_children/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "molestias",
    "image": "et"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/categories/{category_id}/category_children/{category_children_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `category_id` |  required  | Category_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên danh mục
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
    
<!-- END_7deb52342414621cca2975e2e4ff73d5 -->

<!-- START_a3150e89b470db805cd7442e8577f438 -->
## xóa một danh mục

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/blanditiis/categories/1/category_children/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/blanditiis/categories/1/category_children/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/categories/{category_id}/category_children/{category_children_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID category cần xóa thông tin.

<!-- END_a3150e89b470db805cd7442e8577f438 -->

#User/Danh mục tin tức con


APIs Danh mục tin tức
<!-- START_93036d7239cf5dce6939d35b48379960 -->
## Tạo danh mục  tin tức con

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/distinctio/post_categories/dolor/category_children" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"in","image":"quo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/distinctio/post_categories/dolor/category_children"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "in",
    "image": "quo"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/post_categories/{category_id}/category_children`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `category_id` |  required  | category_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên danh mục con
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
    
<!-- END_93036d7239cf5dce6939d35b48379960 -->

<!-- START_e7f43046c62dc0fedfdf2bfbba0c78f7 -->
## update một Category

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/et/post_categories/est/category_children/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"voluptate","image":"aliquam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/et/post_categories/est/category_children/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "voluptate",
    "image": "aliquam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/post_categories/{category_id}/category_children/{category_children_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `category_id` |  required  | Category_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên danh mục
        `image` | file |  required  | Ảnh (hoặc truyền lên image_url)
    
<!-- END_e7f43046c62dc0fedfdf2bfbba0c78f7 -->

<!-- START_e634c5bd8854f43a5e03018b6192eff7 -->
## xóa một danh mục

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/ad/post_categories/1/category_children/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/ad/post_categories/1/category_children/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/post_categories/{category_id}/category_children/{category_children_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID category cần xóa thông tin.

<!-- END_e634c5bd8854f43a5e03018b6192eff7 -->

#User/Danh sách bạn bè


<!-- START_1d5d8cdd754c090131e9d52be55dd5fa -->
## Danh sách bạn bè

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/friends?customer_id=quo&search=et" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friends"
);

let params = {
    "customer_id": "quo",
    "search": "et",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/friends`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `customer_id` |  required  | Nếu là customer id
    `search` |  required  | Tìm tên sdt

<!-- END_1d5d8cdd754c090131e9d52be55dd5fa -->

<!-- START_4a9470b1c110e412a8f952512e84466c -->
## Danh sách bạn bè của 1 người

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/friends/all/1?customer_id=occaecati&search=quia" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friends/all/1"
);

let params = {
    "customer_id": "occaecati",
    "search": "quia",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/friends/all/{customer_id}`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `customer_id` |  required  | Nếu là customer id
    `search` |  required  | Tìm tên sdt

<!-- END_4a9470b1c110e412a8f952512e84466c -->

<!-- START_f1cf79236274d137e976945600139fd7 -->
## Danh sách bạn bè

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/customer/1/friends/1?customer_id=similique" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friends/1"
);

let params = {
    "customer_id": "similique",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/customer/{store_code}/friends/{customer_id}`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `customer_id` |  required  | Nếu là customer id

<!-- END_f1cf79236274d137e976945600139fd7 -->

<!-- START_fa050102121aed109c844c298c6a9254 -->
## Danh sách yêu cầu kết bạn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/friend_requests" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":13}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friend_requests"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 13
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/friend_requests`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `request_id` |  required  | Request id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | Hành động 0 xóa 1 đồng ý kết bạn
    
<!-- END_fa050102121aed109c844c298c6a9254 -->

<!-- START_c9104cefaa1fef5f37587271356f8d39 -->
## Gửi yêu cầu kết bạn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/friend_requests" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"to_customer_id":"voluptatem","content":"natus"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friend_requests"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "to_customer_id": "voluptatem",
    "content": "natus"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/friend_requests`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `to_customer_id` | required |  optional  | Nếu là customer id
        `content` | required |  optional  | Nội dung yêu cầu
    
<!-- END_c9104cefaa1fef5f37587271356f8d39 -->

<!-- START_49ab675ed91c0e437434fb7a528976c8 -->
## Hủy yêu cầu kết bạn

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/customer/1/friend_requests/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"customer_id":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friend_requests/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "customer_id": "et"
}

fetch(url, {
    method: "DELETE",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/customer/{store_code}/friend_requests/{customer_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `customer_id` | required |  optional  | Nếu là customer id
    
<!-- END_49ab675ed91c0e437434fb7a528976c8 -->

<!-- START_a29e8996016662856292dc03a7ec555b -->
## Xử lý yêu cầu bạn bè

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/friend_requests/sunt/handle" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":3}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/friend_requests/sunt/handle"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 3
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/friend_requests/{request_id}/handle`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `request_id` |  required  | Request id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | Hành động 0 xóa 1 đồng ý kết bạn
    
<!-- END_a29e8996016662856292dc03a7ec555b -->

#User/Danh sách phân quyền


<!-- START_d3c9818ac73d348bd4983985e1928f44 -->
## Thêm phân quyền

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/kds/decentralizations" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","description":"a","product_list":true,"product_add":true,"product_update":true,"product_remove_hide":false,"product_category_list":false,"product_category_add":false,"product_category_update":false,"product_category_remove":false,"product_attribute_list":true,"product_attribute_add":false,"product_attribute_update":true,"product_attribute_remove":true,"product_ecommerce":false,"product_import_from_excel":true,"product_export_to_excel":true,"customer_list":true,"customer_config_point":false,"customer_review_list":false,"customer_review_censorship":false,"promotion":false,"promotion_discount_list":false,"promotion_discount_add":true,"promotion_discount_update":false,"promotion_discount_end":true,"promotion_voucher_list":true,"promotion_voucher_add":true,"promotion_voucher_update":true,"promotion_voucher_end":false,"promotion_combo_list":false,"promotion_combo_add":false,"promotion_combo_update":false,"promotion_combo_end":false,"post_list":false,"post_add":true,"post_update":true,"post_remove_hide":true,"post_category_list":false,"post_category_add":true,"post_category_update":true,"post_category_remove":false,"app_theme_edit":true,"app_theme_main_config":true,"app_theme_button_contact":true,"app_theme_home_screen":false,"app_theme_main_component":false,"app_theme_category_product":true,"app_theme_product_screen":false,"app_theme_contact_screen":true,"web_theme_edit":false,"web_theme_overview":false,"web_theme_contact":true,"web_theme_help":false,"web_theme_footer":false,"web_theme_banner":false,"delivery_pick_address_list":true,"delivery_pick_address_update":false,"delivery_provider_update":true,"payment_list":false,"payment_on_off":false,"notification_schedule_list":false,"notification_schedule_add":false,"notification_schedule_remove_pause":false,"notification_schedule_update":true,"order_list":false,"order_allow_change_status":false,"popup_list":false,"popup_add":false,"popup_update":true,"popup_remove":false,"collaborator_config":true,"collaborator_list":false,"collaborator_payment_request_list":true,"collaborator_payment_request_solve":false,"collaborator_payment_request_history":false,"notification_to_stote":true,"chat_list":true,"chat_allow":false,"report_view":true,"report_overview":true,"report_product":false,"report_order":true,"report_inventory":false,"report_finance":false,"decentralization_list":false,"decentralization_update":true,"decentralization_add":false,"decentralization_remove":false,"agency_list":false,"staff_list":false,"staff_update":false,"staff_add":false,"staff_remove":false,"staff_delegating":true,"inventory_list":false,"inventory_import":true,"inventory_tally_sheet":true,"revenue_expenditure":false,"add_revenue":false,"add_expenditure":true,"setting_print":false,"store_info":false,"branch_list":true,"config_setting":true,"create_order_pos":false,"supplier":true,"barcode_print":true,"timekeeping":false,"transfer_stock":false,"onsale":false,"train":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/decentralizations"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "et",
    "description": "a",
    "product_list": true,
    "product_add": true,
    "product_update": true,
    "product_remove_hide": false,
    "product_category_list": false,
    "product_category_add": false,
    "product_category_update": false,
    "product_category_remove": false,
    "product_attribute_list": true,
    "product_attribute_add": false,
    "product_attribute_update": true,
    "product_attribute_remove": true,
    "product_ecommerce": false,
    "product_import_from_excel": true,
    "product_export_to_excel": true,
    "customer_list": true,
    "customer_config_point": false,
    "customer_review_list": false,
    "customer_review_censorship": false,
    "promotion": false,
    "promotion_discount_list": false,
    "promotion_discount_add": true,
    "promotion_discount_update": false,
    "promotion_discount_end": true,
    "promotion_voucher_list": true,
    "promotion_voucher_add": true,
    "promotion_voucher_update": true,
    "promotion_voucher_end": false,
    "promotion_combo_list": false,
    "promotion_combo_add": false,
    "promotion_combo_update": false,
    "promotion_combo_end": false,
    "post_list": false,
    "post_add": true,
    "post_update": true,
    "post_remove_hide": true,
    "post_category_list": false,
    "post_category_add": true,
    "post_category_update": true,
    "post_category_remove": false,
    "app_theme_edit": true,
    "app_theme_main_config": true,
    "app_theme_button_contact": true,
    "app_theme_home_screen": false,
    "app_theme_main_component": false,
    "app_theme_category_product": true,
    "app_theme_product_screen": false,
    "app_theme_contact_screen": true,
    "web_theme_edit": false,
    "web_theme_overview": false,
    "web_theme_contact": true,
    "web_theme_help": false,
    "web_theme_footer": false,
    "web_theme_banner": false,
    "delivery_pick_address_list": true,
    "delivery_pick_address_update": false,
    "delivery_provider_update": true,
    "payment_list": false,
    "payment_on_off": false,
    "notification_schedule_list": false,
    "notification_schedule_add": false,
    "notification_schedule_remove_pause": false,
    "notification_schedule_update": true,
    "order_list": false,
    "order_allow_change_status": false,
    "popup_list": false,
    "popup_add": false,
    "popup_update": true,
    "popup_remove": false,
    "collaborator_config": true,
    "collaborator_list": false,
    "collaborator_payment_request_list": true,
    "collaborator_payment_request_solve": false,
    "collaborator_payment_request_history": false,
    "notification_to_stote": true,
    "chat_list": true,
    "chat_allow": false,
    "report_view": true,
    "report_overview": true,
    "report_product": false,
    "report_order": true,
    "report_inventory": false,
    "report_finance": false,
    "decentralization_list": false,
    "decentralization_update": true,
    "decentralization_add": false,
    "decentralization_remove": false,
    "agency_list": false,
    "staff_list": false,
    "staff_update": false,
    "staff_add": false,
    "staff_remove": false,
    "staff_delegating": true,
    "inventory_list": false,
    "inventory_import": true,
    "inventory_tally_sheet": true,
    "revenue_expenditure": false,
    "add_revenue": false,
    "add_expenditure": true,
    "setting_print": false,
    "store_info": false,
    "branch_list": true,
    "config_setting": true,
    "create_order_pos": false,
    "supplier": true,
    "barcode_print": true,
    "timekeeping": false,
    "transfer_stock": false,
    "onsale": false,
    "train": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/decentralizations`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên phân quyền
        `description` | string |  required  | Mô tả phân quyền
        `product_list` | boolean |  required  | Xem sản phẩm
        `product_add` | boolean |  required  | Thêm sản phẩm
        `product_update` | boolean |  required  | Cập nhật sản phẩm
        `product_remove_hide` | boolean |  required  | Xóa/Ẩn sản phẩm
        `product_category_list` | boolean |  required  | Xem danh mục sản phẩm
        `product_category_add` | boolean |  required  | Thêm danh mục sản phẩm
        `product_category_update` | boolean |  required  | Cập nhật danh mục sản phẩm
        `product_category_remove` | boolean |  required  | Xóa danh mục sản phẩm
        `product_attribute_list` | boolean |  required  | Xem danh sách thuộc tính sản phẩm
        `product_attribute_add` | boolean |  required  | Thêm thuộc tính sản phẩm
        `product_attribute_update` | boolean |  required  | Cập nhật thuộc tính sản phẩm
        `product_attribute_remove` | boolean |  required  | Xóa thuộc tính sản phẩm
        `product_ecommerce` | boolean |  required  | Sản phẩm từ sàn thương mại điện tử
        `product_import_from_excel` | boolean |  required  | Thêm sản phẩm từ file exel
        `product_export_to_excel` | boolean |  required  | Xuất danh sách sản phẩm ra file exel
        `customer_list` | boolean |  required  | Xem danh sách khách hàng
        `customer_config_point` | boolean |  required  | Cấu hình điểm thưởng
        `customer_review_list` | boolean |  required  | Danh sách đánh giá
        `customer_review_censorship` | boolean |  required  | Kiểm duyệt đánh giá
        `promotion` | boolean |  required  | Chương trình khuyến mãi
        `promotion_discount_list` | boolean |  required  | Xem danh sách giảm giá sản phẩm
        `promotion_discount_add` | boolean |  required  | Thêm sản phẩm giảm giá
        `promotion_discount_update` | boolean |  required  | Cập nhật sản phẩm giảm giá
        `promotion_discount_end` | boolean |  required  | Kết thúc sản phẩm giảm giá
        `promotion_voucher_list` | boolean |  required  | Xem danh sách voucher
        `promotion_voucher_add` | boolean |  required  | Thêm voucher
        `promotion_voucher_update` | boolean |  required  | Cập nhật voucher
        `promotion_voucher_end` | boolean |  required  | Kết thúc voucher
        `promotion_combo_list` | boolean |  required  | Danh sách combo
        `promotion_combo_add` | boolean |  required  | Thêm combo
        `promotion_combo_update` | boolean |  required  | Cập nhật combo
        `promotion_combo_end` | boolean |  required  | Kết thúc combo
        `post_list` | boolean |  required  | Danh sách bài viết
        `post_add` | boolean |  required  | Thêm bài viết
        `post_update` | boolean |  required  | Cập nhật bài viết
        `post_remove_hide` | boolean |  required  | Xóa/Ẩn bài viết
        `post_category_list` | boolean |  required  | Xem danh mục bài viết
        `post_category_add` | boolean |  required  | Thêm bài mục bài viết
        `post_category_update` | boolean |  required  | Cập nhật danh mục bài viết
        `post_category_remove` | boolean |  required  | Xóa danh mục bài viết
        `app_theme_edit` | boolean |  required  | Truy cập chỉnh sửa app
        `app_theme_main_config` | boolean |  required  | Chỉnh sửa cấu hình
        `app_theme_button_contact` | boolean |  required  | Nút liên hệ
        `app_theme_home_screen` | boolean |  required  | Màn hình trang chủ
        `app_theme_main_component` | boolean |  required  | Thành phần chính
        `app_theme_category_product` | boolean |  required  | Màn hình danh mục sản phẩm
        `app_theme_product_screen` | boolean |  required  | Màn hình sản phẩm
        `app_theme_contact_screen` | boolean |  required  | Màn hình liên hệ
        `web_theme_edit` | boolean |  required  | Truy cập chỉnh sửa web
        `web_theme_overview` | boolean |  required  | Tổng quan
        `web_theme_contact` | boolean |  required  | Liên hệ
        `web_theme_help` | boolean |  required  | Hỗ trợ
        `web_theme_footer` | boolean |  required  | Dưới trang
        `web_theme_banner` | boolean |  required  | Banner
        `delivery_pick_address_list` | boolean |  required  | Danh sách địa chỉ lấy hàng
        `delivery_pick_address_update` | boolean |  required  | Chỉnh sửa địa chỉ
        `delivery_provider_update` | boolean |  required  | Chỉnh sửa bên cung cấp giao vận
        `payment_list` | boolean |  required  | Xem danh sách bên thanh toán
        `payment_on_off` | boolean |  required  | Bật tắt nhà thanh toán
        `notification_schedule_list` | boolean |  required  | Danh sách lịch thông báo
        `notification_schedule_add` | boolean |  required  | Thêm lịch thông báo
        `notification_schedule_remove_pause` | boolean |  required  | Xóa/Tạm dừng thông báo
        `notification_schedule_update` | boolean |  required  | Cập nhật lịch thông báo
        `order_list` | boolean |  required  | Danh sách đơn hàng
        `order_allow_change_status` | boolean |  required  | Cho phép thay đổi trạng thái
        `popup_list` | boolean |  required  | Danh sách popup quảng cáo
        `popup_add` | boolean |  required  | Thêm popup
        `popup_update` | boolean |  required  | Cập nhật popup
        `popup_remove` | boolean |  required  | Xóa popup
        `collaborator_config` | boolean |  required  | Cấu hình cộng tác viên
        `collaborator_list` | boolean |  required  | Xem danh sách cộng tác viên
        `collaborator_payment_request_list` | boolean |  required  | Xem danh sách yêu cầu thanh toán
        `collaborator_payment_request_solve` | boolean |  required  | Cho phép hủy hoặc thanh toán
        `collaborator_payment_request_history` | boolean |  required  | Xem lịch sử yêu cầu thanh toán
        `notification_to_stote` | boolean |  required  | Cho phép xem danh sách thông báo
        `chat_list` | boolean |  required  | Xem danh sách chat
        `chat_allow` | boolean |  required  | Cho phép chat
        `report_view` | boolean |  required  | Xem báo cáo
        `report_overview` | boolean |  required  | Xem báo cáo tổng quan
        `report_product` | boolean |  required  | Xem báo cáo sản phẩm
        `report_order` | boolean |  required  | Xem báo cáo đơn hàng
        `report_inventory` | boolean |  required  | Xem báo cáo kho
        `report_finance` | boolean |  required  | Xem báo cáo tài chính
        `decentralization_list` | boolean |  required  | Danh sách phân quyền
        `decentralization_update` | boolean |  required  | Cập nhật phân quyền
        `decentralization_add` | boolean |  required  | Thêm phân quyền
        `decentralization_remove` | boolean |  required  | Xóa phân quyền
        `agency_list` | boolean |  required  | Dánh sách đại lý
        `staff_list` | boolean |  required  | Danh sách nhân viên
        `staff_update` | boolean |  required  | Cập nhật nhân viên
        `staff_add` | boolean |  required  | Thêm nhân viên
        `staff_remove` | boolean |  required  | Xóa nhân viên
        `staff_delegating` | boolean |  required  | Ủy quyền cho nhân viên
        `inventory_list` | boolean |  optional  | danh sách kho
        `inventory_import` | boolean |  optional  | truy cập nhập kho
        `inventory_tally_sheet` | boolean |  optional  | truy cập kiểm kho
        `revenue_expenditure` | boolean |  optional  | Truy cập thu chi
        `add_revenue` | boolean |  optional  | Tạo khoản thu
        `add_expenditure` | boolean |  optional  | Tạo khoản chi
        `setting_print` | boolean |  optional  | Cài đặt máy in
        `store_info` | boolean |  optional  | Thông tin cửa hàng
        `branch_list` | boolean |  optional  | Quản lý chi nhánh
        `config_setting` | boolean |  optional  | Cấu hình chung
        `create_order_pos` | boolean |  optional  | Tạo đơn
        `supplier` | boolean |  optional  | Tạo đơn
        `barcode_print` | boolean |  optional  | In mã vạch
        `timekeeping` | boolean |  optional  | Chấm công
        `transfer_stock` | boolean |  optional  | Chuyển kho
        `onsale` | boolean |  optional  | Truy cập onsale
        `train` | boolean |  optional  | Truy cập đào tạo
    
<!-- END_d3c9818ac73d348bd4983985e1928f44 -->

<!-- START_133b6353e1f5f531270516128b544c4e -->
## Danh sách phân quyền

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/decentralizations" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/decentralizations"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/decentralizations`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.

<!-- END_133b6353e1f5f531270516128b544c4e -->

<!-- START_2e1adbdb88343c440d36d7d65bab1089 -->
## Xóa phân quyền

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/kds/decentralizations/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/decentralizations/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/decentralizations/{decentralization_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.

<!-- END_2e1adbdb88343c440d36d7d65bab1089 -->

<!-- START_f6070bb3f47f38d13b6e741cbb6a321a -->
## Cập nhật phân quyền

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/kds/decentralizations/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/decentralizations/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/decentralizations/{decentralization_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.

<!-- END_f6070bb3f47f38d13b6e741cbb6a321a -->

#User/Device token


<!-- START_882728f8fc1393dc0801ae1affd48950 -->
## Đăng ký device token

> Example request:

```bash
curl -X POST \
    "http://localhost/api/device_token_user" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"device_id":"tempora","device_type":"aut","device_token":"similique"}'

```

```javascript
const url = new URL(
    "http://localhost/api/device_token_user"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "device_id": "tempora",
    "device_type": "aut",
    "device_token": "similique"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/device_token_user`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `device_id` | string |  required  | device_id
        `device_type` | string |  required  | 0 android | 1 ios
        `device_token` | string |  required  | device_token
    
<!-- END_882728f8fc1393dc0801ae1affd48950 -->

#User/Giá sản phẩm cho đại lý


<!-- START_6fa349922ec325e08e933f6ac55b6f85 -->
## Cập nhật giá đại lý 1 sản phẩm

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/quam/products/1/agency_price" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"agency_type_id":"repellendus","main_price":5266449.99,"element_distributes_price":"quo","sub_element_distributes_price":"magni"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quam/products/1/agency_price"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "agency_type_id": "repellendus",
    "main_price": 5266449.99,
    "element_distributes_price": "quo",
    "sub_element_distributes_price": "magni"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/products/{product_id}/agency_price`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `agency_type_id` | required |  optional  | agency_type_id
        `main_price` | float |  required  | Giá đại lý thay đổi khi không có distribute
        `element_distributes_price` | List |  required  | [{distribute_name:"Màu", element_distribute:"Đỏ", price:180000}]
        `sub_element_distributes_price` | List |  required  | [{distribute_name:"Màu", element_distribute:"Đỏ", sub_element_distribute:"vàng", price:180000}]
    
<!-- END_6fa349922ec325e08e933f6ac55b6f85 -->

<!-- START_666081ed4e82151818bee5cc3bd863b1 -->
## Lấy giá đại lý theo tầng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/culpa/products/1/agency_price" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"agency_type_id":448593866.41974}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/culpa/products/1/agency_price"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "agency_type_id": 448593866.41974
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/products/{product_id}/agency_price`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `agency_type_id` | float |  required  | agency_type_id
    
<!-- END_666081ed4e82151818bee5cc3bd863b1 -->

#User/Giảm giá sản phẩm


<!-- START_8c22ab9d7b80f250d62a79e4c384ea64 -->
## Tạo giảm giá sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/ipsam/discounts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"placeat","description":"cum","image_url":"aspernatur","start_time":"suscipit","end_time":"earum","value":90620.22062,"set_limit_amount":false,"amount":18,"product_ids":"error","group_customer":20,"group_type_id":1,"group_type_name":8,"agency_type_id":20,"agency_type_name":"dolor"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/ipsam/discounts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "placeat",
    "description": "cum",
    "image_url": "aspernatur",
    "start_time": "suscipit",
    "end_time": "earum",
    "value": 90620.22062,
    "set_limit_amount": false,
    "amount": 18,
    "product_ids": "error",
    "group_customer": 20,
    "group_type_id": 1,
    "group_type_name": 8,
    "agency_type_id": 20,
    "agency_type_name": "dolor"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/discounts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
        `value` | float |  required  | Giá trị % giảm giá 1 - 99
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `product_ids` | List&lt;int&gt; |  required  | danh sách id sản phẩm kèm số lượng 1,2,...
        `group_customer` | integer |  required  | 0 khách hàng, 1  cộng tác viên, 2 đại lý, 4 nhóm khách hàng
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_8c22ab9d7b80f250d62a79e4c384ea64 -->

<!-- START_1420f2bab5a6c613e98491e40152a999 -->
## Update giảm giá sản phẩm
Muốn kết thúc chương trình chỉ cần truyền is_end = false (Còn lại truyền đầy đủ)

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/voluptas/discounts/5" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_end":false,"name":"atque","description":"enim","image_url":"rem","start_time":"sit","end_time":"non","value":1873077.98509,"set_limit_amount":false,"amount":12,"product_ids":"consequuntur","group_type_id":12,"group_type_name":19,"agency_type_id":19,"agency_type_name":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptas/discounts/5"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_end": false,
    "name": "atque",
    "description": "enim",
    "image_url": "rem",
    "start_time": "sit",
    "end_time": "non",
    "value": 1873077.98509,
    "set_limit_amount": false,
    "amount": 12,
    "product_ids": "consequuntur",
    "group_type_id": 12,
    "group_type_name": 19,
    "agency_type_id": 19,
    "agency_type_name": "et"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/discounts/{discount_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `discount_id` |  required  | Id discount
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_end` | boolean |  required  | Chương trình đã kết thúc chưa
        `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
        `value` | float |  required  | Giá trị % giảm giá 1 - 99
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `product_ids` | List&lt;int&gt; |  required  | danh sách id sản phẩm kèm số lượng 1,2,...
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_1420f2bab5a6c613e98491e40152a999 -->

<!-- START_157a21a47b8fb9dbb331faa6023b79b6 -->
## Xem 1 chương trình giảm giá

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/laborum/discounts/9" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/laborum/discounts/9"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/discounts/{discount_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `discount_id` |  required  | Id discount

<!-- END_157a21a47b8fb9dbb331faa6023b79b6 -->

<!-- START_9e0a7b7ad3fdf92297e2b67c3d25ecbf -->
## Xem tất cả chương trình giảm giá chuẩn vị và đang phát hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/deserunt/discounts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/deserunt/discounts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/discounts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_9e0a7b7ad3fdf92297e2b67c3d25ecbf -->

<!-- START_7ebdfa2df32903142b73edabfd8fc594 -->
## Xem tất cả Discount đã kết thúc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/animi/discounts_end?page=19" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/animi/discounts_end"
);

let params = {
    "page": "19",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/discounts_end`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)

<!-- END_7ebdfa2df32903142b73edabfd8fc594 -->

<!-- START_7e1f58a3096d7a4e0dd731c43c1a08b4 -->
## xóa một chương trình discount

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/voluptatum/discounts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptatum/discounts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/discounts/{discount_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID discount cần xóa thông tin.

<!-- END_7e1f58a3096d7a4e0dd731c43c1a08b4 -->

#User/Giỏ hàng


<!-- START_0b9cbcbae6fe3e138ad1784aae0a2d63 -->
## Danh sách giỏ hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/labore/carts/neque/list?has_cart_default=vel" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/labore/carts/neque/list"
);

let params = {
    "has_cart_default": "vel",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/carts/{branch_id}/list`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `has_cart_default` |  required  | có hiển thị đơn mặc định hay không

<!-- END_0b9cbcbae6fe3e138ad1784aae0a2d63 -->

<!-- START_15b5b15099bdf6f410f22eabef8d2d88 -->
## Tạo giỏ hàng mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/rerum/carts/aut/list" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"veniam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/carts/aut/list"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "veniam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/carts/{branch_id}/list`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên giỏ hàng (Không truyền sẽ tự động đặt lên Hóa đơn x)
    
<!-- END_15b5b15099bdf6f410f22eabef8d2d88 -->

<!-- START_12a57f22908fb8b47c53b9d337539662 -->
## Danh sách sản phẩm trong giỏ hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/sed/carts/quos/list/inventore" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/sed/carts/quos/list/inventore"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/carts/{branch_id}/list/{cart_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
    `cart_id` |  required  | cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)

<!-- END_12a57f22908fb8b47c53b9d337539662 -->

<!-- START_26ccf5ffc39f15d7dd2488a94c4ad71e -->
## Thêm sản phẩm vào giỏ hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/laborum/carts/et/list/aut/items" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":11,"distribute_name":"vitae","element_distribute_name":"necessitatibus","sub_element_distribute_name":"quo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/laborum/carts/et/list/aut/items"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": 11,
    "distribute_name": "vitae",
    "element_distribute_name": "necessitatibus",
    "sub_element_distribute_name": "quo"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/carts/{branch_id}/list/{cart_id}/items`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
    `cart_id` |  required  | cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | integer |  required  | Product id
        `distribute_name` | string |  optional  | Tên kiểu phân loại
        `element_distribute_name` | string |  optional  | Kiểu phân loại
        `sub_element_distribute_name` | string |  optional  | Phân loại con
    
<!-- END_26ccf5ffc39f15d7dd2488a94c4ad71e -->

<!-- START_d610a26314221b7ef9ffb018f47ed785 -->
## Cập nhật sản phẩm trong giỏ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/earum/carts/iusto/list/1/items" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":20,"distribute_name":"rerum","element_distribute_name":"ea","sub_element_distribute_name":"quo","quantity":13,"line_item_id":9}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/earum/carts/iusto/list/1/items"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": 20,
    "distribute_name": "rerum",
    "element_distribute_name": "ea",
    "sub_element_distribute_name": "quo",
    "quantity": 13,
    "line_item_id": 9
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/carts/{branch_id}/list/{cart_id}/items`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | integer |  required  | Product id
        `distribute_name` | string |  optional  | Tên kiểu phân loại
        `element_distribute_name` | string |  optional  | Kiểu phân loại
        `sub_element_distribute_name` | string |  optional  | Phân loại con
        `quantity` | integer |  optional  | so luong  Phân loại con
        `line_item_id` | integer |  optional  | line_item_id
    
<!-- END_d610a26314221b7ef9ffb018f47ed785 -->

<!-- START_fc12687eef3fe3462a7722a46e8c33dd -->
## Cập nhật thông tin giỏ hàng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/tempore/carts/vel/list/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"voluptatem","is_use_points":false,"is_use_balance_collaborator":false,"payment_method_id":3,"partner_shipper_id":20,"shipper_type":9,"total_shipping_fee":19,"collaborator_by_customer_id":4,"agency_by_customer_id":19,"customer_phone":"pariatur","customer_name":"vero","customer_address_id":12,"customer_note":"maxime","customer_email":"aut","customer_sex":"fugiat","customer_date_of_birth":"culpa"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/tempore/carts/vel/list/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "voluptatem",
    "is_use_points": false,
    "is_use_balance_collaborator": false,
    "payment_method_id": 3,
    "partner_shipper_id": 20,
    "shipper_type": 9,
    "total_shipping_fee": 19,
    "collaborator_by_customer_id": 4,
    "agency_by_customer_id": 19,
    "customer_phone": "pariatur",
    "customer_name": "vero",
    "customer_address_id": 12,
    "customer_note": "maxime",
    "customer_email": "aut",
    "customer_sex": "fugiat",
    "customer_date_of_birth": "culpa"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/carts/{branch_id}/list/{cart_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên giỏ hàng
        `is_use_points` | boolean |  optional  | có sử dụng điểm thưởng hay không
        `is_use_balance_collaborator` | boolean |  optional  | su dung diem CTV
        `payment_method_id` | integer |  optional  | ID phương thức thanh toán
        `partner_shipper_id` | integer |  required  | ID nhà giao hàng
        `shipper_type` | integer |  required  | (partner_shipper_id != null) Kiểu giao (0 tiêu chuẩn - 1 siêu tốc)
        `total_shipping_fee` | integer |  required  | (partner_shipper_id != null) Tổng tiền giao hàng
        `collaborator_by_customer_id` | integer |  optional  | customer  ID CTV
        `agency_by_customer_id` | integer |  optional  | ID customer Đại lý
        `customer_phone` | string |  optional  | Số điện thoại customer
        `customer_name` | string |  optional  | Tên khách hàng
        `customer_address_id` | integer |  required  | ID địa chỉ khách hàng
        `customer_note` | string |  required  | Ghi chú khách hàng
        `customer_email` | string |  required  | Email
        `customer_sex` | string |  required  | giới tính
        `customer_date_of_birth` | string |  required  | Ngày sinh
    
<!-- END_fc12687eef3fe3462a7722a46e8c33dd -->

<!-- START_f244cb873fb4613eaed6a67133ee35c3 -->
## Xóa 1 giỏ hàng

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/ut/carts/vel/list/facilis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/ut/carts/vel/list/facilis"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/carts/{branch_id}/list/{cart_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
    `cart_id` |  required  | cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)

<!-- END_f244cb873fb4613eaed6a67133ee35c3 -->

<!-- START_9a82f515a5b44cd17d6d98b6949b9064 -->
## Sử dụng voucher

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/quia/carts/cupiditate/list/eaque/use_voucher" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"debitis","code_voucher":"sunt"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quia/carts/cupiditate/list/eaque/use_voucher"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "debitis",
    "code_voucher": "sunt"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/carts/{branch_id}/list/{cart_id}/use_voucher`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
    `cart_id` |  required  | cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên giỏ hàng
        `code_voucher` | string |  optional  | ":"SUPER" gửi code voucher (không xài thì truyền voucher)
    
<!-- END_9a82f515a5b44cd17d6d98b6949b9064 -->

<!-- START_fd73199354155f048fe18ce1907ccac6 -->
## Thêm combo sản phẩm vào giỏ hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/culpa/carts/voluptatibus/list/nam/use_combo" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"combo_id":10}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/culpa/carts/voluptatibus/list/nam/use_combo"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "combo_id": 10
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/carts/{branch_id}/list/{cart_id}/use_combo`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
    `cart_id` |  required  | cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `combo_id` | integer |  required  | Id của combo cần thêm
    
<!-- END_fd73199354155f048fe18ce1907ccac6 -->

<!-- START_e59b995991b9bf23d63fdb9181261e8b -->
## Tạo giỏ hàng mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/quos/carts/nisi/list/1/create_cart_save" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"qui"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quos/carts/nisi/list/1/create_cart_save"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "qui"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/carts/{branch_id}/list/{cart_id}/create_cart_save`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên giỏ hàng
    
<!-- END_e59b995991b9bf23d63fdb9181261e8b -->

<!-- START_a892760e5471d0266b4af74d4a15cc83 -->
## Lên đơn hàng và thanh toán

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/temporibus/carts/aperiam/list/amet/order" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/temporibus/carts/aperiam/list/amet/order"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/carts/{branch_id}/list/{cart_id}/order`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | Branch id
    `cart_id` |  required  | cart_id (0 là giỏ mặc định auto lấy id thấp nhất backend tự tạo giỏ khi không có giỏ nào)

<!-- END_a892760e5471d0266b4af74d4a15cc83 -->

#User/HomeApp


<!-- START_8bffc503795fe47417bcb9b3e35e6aaf -->
## Lấy giao diện home

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/home_app" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/home_app"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/home_app`


<!-- END_8bffc503795fe47417bcb9b3e35e6aaf -->

#User/Khách hàng onsale


<!-- START_c25f41c5a8afe368a267f2edfcf45331 -->
## Thêm 1 khách hàng cần tư vấn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/customer_sales" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":9,"phone_number":"omnis","email":"ut","name":"id","address":"hic","staff_id":"sapiente","sex":1,"consultation_1":"quisquam","consultation_2":"reiciendis","consultation_3":"modi"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 9,
    "phone_number": "omnis",
    "email": "ut",
    "name": "id",
    "address": "hic",
    "staff_id": "sapiente",
    "sex": 1,
    "consultation_1": "quisquam",
    "consultation_2": "reiciendis",
    "consultation_3": "modi"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/customer_sales`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | 0 chưa xử lý, 1 đang hỗ trợ, 2 thành công, 3 thất bại
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `address` | string |  optional  | Địa chỉ
        `staff_id` | Id |  optional  | nhân viên hõ trọ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `consultation_1` | string |  optional  | Lần tư vấn 1
        `consultation_2` | string |  optional  | Lần tư vấn 2
        `consultation_3` | string |  optional  | Lần tư vấn 3
    
<!-- END_c25f41c5a8afe368a267f2edfcf45331 -->

<!-- START_8e4eeaffd14d151e13a39923a453653e -->
## Khách hàng cần tư vấn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/customer_sales" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/customer_sales`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `search` |  optional  | string search
    `status` |  optional  | int status
    `limit` |  optional  | int số user mỗi trang
    `staff_id` |  optional  | Id nhân viên hõ trọ

<!-- END_8e4eeaffd14d151e13a39923a453653e -->

<!-- START_f9d48a7347a1ea06c363ccc5856aa5d8 -->
## Xem 1 kahch hang

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/customer_sales/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/customer_sales/{customersale_id}`


<!-- END_f9d48a7347a1ea06c363ccc5856aa5d8 -->

<!-- START_f4b4adbe01de6dc10b8e000d4b0086d4 -->
## Xóa 1 user cần tư vấn

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/customer_sales/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/customer_sales/{customersale_id}`


<!-- END_f4b4adbe01de6dc10b8e000d4b0086d4 -->

<!-- START_ab9cdace7a4f67e98cc4bcce2dea23cd -->
## Cập nhật thông tin khách hàng tư vấn

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/customer_sales/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"staff_id":19,"phone_number":"sit","email":"dolorem","name":"magnam","sex":12,"consultation_1":"magni","consultation_2":"nihil","consultation_3":"veritatis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "staff_id": 19,
    "phone_number": "sit",
    "email": "dolorem",
    "name": "magnam",
    "sex": 12,
    "consultation_1": "magni",
    "consultation_2": "nihil",
    "consultation_3": "veritatis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/customer_sales/{customersale_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `staff_id` | integer |  optional  | id nhân viên sale hỗ trợ
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `consultation_1` | string |  optional  | Lần tư vấn 1
        `consultation_2` | string |  optional  | Lần tư vấn 2
        `consultation_3` | string |  optional  | Lần tư vấn 3
    
<!-- END_ab9cdace7a4f67e98cc4bcce2dea23cd -->

<!-- START_126b78416c242f126baf60810a1b05d4 -->
## Thêm nhiều khách hàng tu van

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/customer_sales/all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_skip_same_phone_number":false,"list":"fugit","item":"cum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales/all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_skip_same_phone_number": false,
    "list": "fugit",
    "item": "cum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/customer_sales/all`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_skip_same_phone_number` | boolean |  required  | Có bỏ qua khách hàng tư vấn trùng sdt không (Không bỏ qua sẽ replace khách hàng tư vấn trùng tên)
        `list` | List |  required  | List danh sách khách hàng tư vấn  (item json như thêm 1 CustomerSale)
        `item` | CustomerSale |  optional  | thêm {category_name}
    
<!-- END_126b78416c242f126baf60810a1b05d4 -->

<!-- START_7909f1ed23c0250076fe6449bd9fa83e -->
## Cập nhật nhiều khách hàng thông tin user cần tư vấn

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/customer_sales" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"staff_id":13,"phone_number":"et","email":"eius","name":"reprehenderit","sex":14,"consultation_1":"occaecati","consultation_2":"eos","consultation_3":"rem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customer_sales"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "staff_id": 13,
    "phone_number": "et",
    "email": "eius",
    "name": "reprehenderit",
    "sex": 14,
    "consultation_1": "occaecati",
    "consultation_2": "eos",
    "consultation_3": "rem"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/customer_sales`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `staff_id` | integer |  optional  | id nhân viên sale hỗ trợ
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `consultation_1` | string |  optional  | Lần tư vấn 1
        `consultation_2` | string |  optional  | Lần tư vấn 2
        `consultation_3` | string |  optional  | Lần tư vấn 3
    
<!-- END_7909f1ed23c0250076fe6449bd9fa83e -->

#User/Lên lịch thông báo tới khách hàng


<!-- START_f51f603b0e268e383fbec678ced48caa -->
## Tạo lịch mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/notifications/schedule" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"laboriosam","description":"et","group_customer":17,"time_of_day":"minus","type_schedule":5,"time_run":"esse","day_of_week":9,"day_of_month":7,"time_run_near":"adipisci","status":"voluptatem","reminiscent_name":"molestias","type_action":"consequatur","value_action":"officia","group_type_id":19,"group_type_name":9,"agency_type_id":18,"agency_type_name":"distinctio"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications/schedule"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "laboriosam",
    "description": "et",
    "group_customer": 17,
    "time_of_day": "minus",
    "type_schedule": 5,
    "time_run": "esse",
    "day_of_week": 9,
    "day_of_month": 7,
    "time_run_near": "adipisci",
    "status": "voluptatem",
    "reminiscent_name": "molestias",
    "type_action": "consequatur",
    "value_action": "officia",
    "group_type_id": 19,
    "group_type_name": 9,
    "agency_type_id": 18,
    "agency_type_name": "distinctio"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/notifications/schedule`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề thông báo
        `description` | string |  required  | Mô tả thông báo
        `group_customer` | integer |  required  | Nhóm khách hàng 0 tất cả, 1 ngày sinh nhật, 2 dai ly, 3 ctv
        `time_of_day` | string |  required  | Thời gian thông báo trong ngày
        `type_schedule` | integer |  required  | 0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
        `time_run` | datetime |  required  | Khi chọn chạy đúng 1 lần
        `day_of_week` | integer |  required  | Khi chọn chạy hàng tuần
        `day_of_month` | integer |  required  | Khi chọn chạy hàng tháng
        `time_run_near` | datetime |  required  | Gần nhất
        `status` | datetime |  required  | 0 đang chạy, 1 tạm dừng, 2 đã xong
        `reminiscent_name` | string |  optional  | tên gợi nhớ (ví dụ tên sản phẩm cái này khách không nhập)
        `type_action` | gồm: |  optional  | NONE,PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
        `value_action` | string |  optional  | giá trị thực thi ví dụ  id cate,product hoặc link (string)
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_f51f603b0e268e383fbec678ced48caa -->

<!-- START_4f90776a4c92025702bb09d74161a3c1 -->
## Danh sách lịch gửi noti

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/notifications/schedule" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications/schedule"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/notifications/schedule`


<!-- END_4f90776a4c92025702bb09d74161a3c1 -->

<!-- START_9791c52aefff3ceef76f111c44d3778a -->
## Xóa 1 lịch

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/notifications/schedule/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications/schedule/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/notifications/schedule/{schedule_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `title` |  optional  | int required schedule_id

<!-- END_9791c52aefff3ceef76f111c44d3778a -->

<!-- START_fe9a2b09a40900021a3e459337449bef -->
## Sửa 1 lịch

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/notifications/schedule/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"eveniet","description":"consequatur","group_customer":14,"time_of_day":"sint","type_schedule":10,"time_run":"vel","day_of_week":17,"day_of_month":13,"time_run_near":"nihil","status":"magni","reminiscent_name":"animi","type_action":"velit","value_action":"consequuntur","group_type_id":1,"group_type_name":10,"agency_type_id":1,"agency_type_name":"nesciunt"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications/schedule/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "eveniet",
    "description": "consequatur",
    "group_customer": 14,
    "time_of_day": "sint",
    "type_schedule": 10,
    "time_run": "vel",
    "day_of_week": 17,
    "day_of_month": 13,
    "time_run_near": "nihil",
    "status": "magni",
    "reminiscent_name": "animi",
    "type_action": "velit",
    "value_action": "consequuntur",
    "group_type_id": 1,
    "group_type_name": 10,
    "agency_type_id": 1,
    "agency_type_name": "nesciunt"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/notifications/schedule/{schedule_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `title` |  optional  | int required schedule_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề thông báo
        `description` | string |  required  | Mô tả thông báo
        `group_customer` | integer |  required  | Nhóm khách hàng 0 tất cả, 1 ngày sinh nhật
        `time_of_day` | string |  required  | Thời gian thông báo trong ngày
        `type_schedule` | integer |  required  | , 0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
        `time_run` | datetime |  required  | Khi chọn chạy đúng 1 lần
        `day_of_week` | integer |  required  | Khi chọn chạy hàng tuần
        `day_of_month` | integer |  required  | Khi chọn chạy hàng tháng
        `time_run_near` | datetime |  required  | Gần nhất
        `status` | datetime |  required  | 0 đang chạy, 1 tạm dừng, 2 đã xong
        `reminiscent_name` | string |  optional  | tên gợi nhớ (ví dụ tên sản phẩm cái này khách không nhập)
        `type_action` | gồm: |  optional  | NONE,PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
        `value_action` | string |  optional  | giá trị thực thi ví dụ  id cate,product hoặc link (string)
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_fe9a2b09a40900021a3e459337449bef -->

<!-- START_bdfdcfcf252a523f912059280bce4f11 -->
## Test gửi thông báo

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/notifications/schedule/test" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"et","description":"placeat"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications/schedule/test"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "et",
    "description": "placeat"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/notifications/schedule/test`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề thông báo
        `description` | string |  required  | Mô tả thông báo
    
<!-- END_bdfdcfcf252a523f912059280bce4f11 -->

#User/Lên lịch thông báo tới user


<!-- START_a96b2795ddf667130e24a7eb817d75b6 -->
## Tạo lịch mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/notifications/schedule" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"voluptatem","description":"minima","group_user":1,"time_of_day":"fuga","type_schedule":5,"time_run":"minus","day_of_week":14,"day_of_month":10,"time_run_near":"tempore","status":"rem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/notifications/schedule"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "voluptatem",
    "description": "minima",
    "group_user": 1,
    "time_of_day": "fuga",
    "type_schedule": 5,
    "time_run": "minus",
    "day_of_week": 14,
    "day_of_month": 10,
    "time_run_near": "tempore",
    "status": "rem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/notifications/schedule`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề thông báo
        `description` | string |  required  | Mô tả thông báo
        `group_user` | integer |  required  | Nhóm khách hàng 0 tất cả, 1 ngày sinh nhật
        `time_of_day` | string |  required  | Thời gian thông báo trong ngày
        `type_schedule` | integer |  required  | 0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
        `time_run` | datetime |  required  | Khi chọn chạy đúng 1 lần
        `day_of_week` | integer |  required  | Khi chọn chạy hàng tuần
        `day_of_month` | integer |  required  | Khi chọn chạy hàng tháng
        `time_run_near` | datetime |  required  | Gần nhất
        `status` | datetime |  required  | 0 đang chạy, 1 tạm dừng, 2 đã xong
    
<!-- END_a96b2795ddf667130e24a7eb817d75b6 -->

<!-- START_4b77515c4cf4777c9040328d22fafb2d -->
## Danh sách lịch gửi noti

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/notifications/schedule" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/notifications/schedule"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/admin/notifications/schedule`


<!-- END_4b77515c4cf4777c9040328d22fafb2d -->

<!-- START_f10e331bfab79244b86722895f99cd76 -->
## Xóa 1 lịch

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/notifications/schedule/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/admin/notifications/schedule/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/admin/notifications/schedule/{schedule_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `title` |  optional  | int required schedule_id

<!-- END_f10e331bfab79244b86722895f99cd76 -->

<!-- START_d1b826d7771286f8f1be38b8724da466 -->
## Sửa 1 lịch

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/notifications/schedule/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"et","description":"quam","group_user":20,"time_of_day":"enim","type_schedule":9,"time_run":"delectus","day_of_week":4,"day_of_month":11,"time_run_near":"est","status":"autem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/notifications/schedule/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "et",
    "description": "quam",
    "group_user": 20,
    "time_of_day": "enim",
    "type_schedule": 9,
    "time_run": "delectus",
    "day_of_week": 4,
    "day_of_month": 11,
    "time_run_near": "est",
    "status": "autem"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/admin/notifications/schedule/{schedule_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `title` |  optional  | int required schedule_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề thông báo
        `description` | string |  required  | Mô tả thông báo
        `group_user` | integer |  required  | Nhóm khách hàng 0 tất cả, 1 ngày sinh nhật
        `time_of_day` | string |  required  | Thời gian thông báo trong ngày
        `type_schedule` | integer |  required  | , 0 chạy đúng 1 lần, 1 hàng ngày, 2 hàng tuần, 3 hàng tháng
        `time_run` | datetime |  required  | Khi chọn chạy đúng 1 lần
        `day_of_week` | integer |  required  | Khi chọn chạy hàng tuần
        `day_of_month` | integer |  required  | Khi chọn chạy hàng tháng
        `time_run_near` | datetime |  required  | Gần nhất
        `status` | datetime |  required  | 0 đang chạy, 1 tạm dừng, 2 đã xong
    
<!-- END_d1b826d7771286f8f1be38b8724da466 -->

<!-- START_f9f78112cd900343137b50cd7e3c60be -->
## Test gửi thông báo

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/notifications/schedule/test" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"perferendis","description":"quo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/admin/notifications/schedule/test"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "perferendis",
    "description": "quo"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/admin/notifications/schedule/test`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  required  | Tiêu đề thông báo
        `description` | string |  required  | Mô tả thông báo
    
<!-- END_f9f78112cd900343137b50cd7e3c60be -->

#User/Lịch làm việc


<!-- START_d6a6d54b137ed72e9aa7e6b246dc0be8 -->
## Danh sách lich làm việc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/calendar_shifts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/calendar_shifts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/calendar_shifts`


<!-- END_d6a6d54b137ed72e9aa7e6b246dc0be8 -->

<!-- START_6e64039e207199d8bf12f8fbad5d3d36 -->
## Xếp ca hàng loạt

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/a/molestias/calendar_shifts/put_a_lot" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"list_shift_id":"odit","list_staff_id":"quaerat","start_time":"consequatur","end_time":"qui"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/a/molestias/calendar_shifts/put_a_lot"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "list_shift_id": "odit",
    "list_staff_id": "quaerat",
    "start_time": "consequatur",
    "end_time": "qui"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/calendar_shifts/put_a_lot`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `branch_id` |  required  | branch_id Chi nhánh
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `list_shift_id` | List |  optional  | Danh sách id ca
        `list_staff_id` | List |  optional  | Danh sách nhân viên
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | Thời gian kết thúc
    
<!-- END_6e64039e207199d8bf12f8fbad5d3d36 -->

<!-- START_289b0b7c3561a81fb05fb83768e660a9 -->
## Xếp ca vào 1 ô (ngày và giờ cụ thể)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/eius/qui/calendar_shifts/put_one" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"shift_id":11,"list_staff_ids":"in","date":"ab"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/eius/qui/calendar_shifts/put_one"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "shift_id": 11,
    "list_staff_ids": "in",
    "date": "ab"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/calendar_shifts/put_one`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `branch_id` |  required  | branch_id Chi nhánh
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `shift_id` | integer |  optional  | Id ca
        `list_staff_ids` | List |  optional  | Danh sách nhân viên
        `date` | datetime |  required  | Ngày
    
<!-- END_289b0b7c3561a81fb05fb83768e660a9 -->

<!-- START_6dc8af54b7bcc344852a53981da5a934 -->
## Thông tin ca hôm nay

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/timekeeping/to_day" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/timekeeping/to_day"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/timekeeping/to_day`


<!-- END_6dc8af54b7bcc344852a53981da5a934 -->

<!-- START_8f1a418421193171394330cd5e0bd155 -->
## Checkin checkout

0 ok, 1 chờ duyet, 2 da duyet, 3 huy

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/1/1/timekeeping/checkin_checkout" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_remote":false,"wifi_name":"adipisci","wifi_mac":"adipisci","device_name":"similique","device_id":"modi","reason":"quam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/timekeeping/checkin_checkout"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_remote": false,
    "wifi_name": "adipisci",
    "wifi_mac": "adipisci",
    "device_name": "similique",
    "device_id": "modi",
    "reason": "quam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/timekeeping/checkin_checkout`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_remote` | boolean |  optional  | Có phải chấm công từ xa không
        `wifi_name` | string |  optional  | wifi_name
        `wifi_mac` | string |  optional  | wifi_mac (check)
        `device_name` | string |  optional  | tên máy
        `device_id` | string |  optional  | device id (check)
        `reason` | string |  optional  | lý do (trường hợp check từ xa)
    
<!-- END_8f1a418421193171394330cd5e0bd155 -->

#User/Nhà cung cấp


<!-- START_46231023aa5c9bc9648f55cb9505b32b -->
## Tạo  nhà cung cấp mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/harum/suppliers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"ab","phone":"maiores","email":"fugiat","branch_code":"exercitationem","province":6,"district":19,"wards":1,"address_detail":"veritatis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/harum/suppliers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "ab",
    "phone": "maiores",
    "email": "fugiat",
    "branch_code": "exercitationem",
    "province": 6,
    "district": 19,
    "wards": 1,
    "address_detail": "veritatis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/suppliers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên nhà cung cấp
        `phone` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email nhà cung cấp
        `branch_code` | string |  optional  | Mã nhà cung cấp
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `wards` | integer |  required  | id wards
        `address_detail` | Địa |  optional  | chỉ chi tiết
    
<!-- END_46231023aa5c9bc9648f55cb9505b32b -->

<!-- START_e69a34c96739c608d13feeaf54829ef6 -->
## Xem 1 nhà cung cấp

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/et/suppliers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/et/suppliers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/suppliers/{supplier_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | ID nhà cung cấp

<!-- END_e69a34c96739c608d13feeaf54829ef6 -->

<!-- START_66a1e430f677e6771ee9a445ba855d68 -->
## Danh sách nhà cung cấp

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/amet/suppliers?name=sunt&is_debt=nam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/amet/suppliers"
);

let params = {
    "name": "sunt",
    "is_debt": "nam",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/suppliers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `name` |  optional  | string Tên nhà cung cấp
    `is_debt` |  optional  | Có nợ không

<!-- END_66a1e430f677e6771ee9a445ba855d68 -->

<!-- START_a24b88d1feb426bf618704834998aaf7 -->
## Xóa nhà cung cấp

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/fuga/suppliers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/fuga/suppliers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/suppliers/{supplier_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | ID nhà cung cấp

<!-- END_a24b88d1feb426bf618704834998aaf7 -->

<!-- START_4d0e335c63c34c99e8690f20a19658ca -->
## Cập nhật nhà cung cấp

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/labore/suppliers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"magnam","phone":"tenetur","email":"in","province":18,"district":2,"wards":4,"address_detail":"eveniet"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/labore/suppliers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "magnam",
    "phone": "tenetur",
    "email": "in",
    "province": 18,
    "district": 2,
    "wards": 4,
    "address_detail": "eveniet"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/suppliers/{supplier_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | ID nhà cung cấp
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên nhà cung cấp
        `phone` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email nhà cung cấp
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `wards` | integer |  required  | id wards
        `address_detail` | Địa |  optional  | chỉ chi tiết
    
<!-- END_4d0e335c63c34c99e8690f20a19658ca -->

#User/Nhóm khách hàng


<!-- START_9553334eb1bb7dd74469f2d996a6ba36 -->
## Tạo nhóm khách hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/group_customers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"ut","note":"sunt","list":"vero","type_compare":"doloribus","comparison_expression":"sunt","value_compare":"animi"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/group_customers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "ut",
    "note": "sunt",
    "list": "vero",
    "type_compare": "doloribus",
    "comparison_expression": "sunt",
    "value_compare": "animi"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/group_customers`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên nhóm khách hàng
        `note` | file |  required  | Ghi chú
        `list` | {type_compare,comparison_expression,value_compare) |  optional  | 
        `type_compare` | Kiểu |  optional  | so sánh //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký
        `comparison_expression` | Biểu |  optional  | thức so sánh  (>,>=,=,<,<=)
        `value_compare` | Giá |  optional  | trị so sánh so sánh
    
<!-- END_9553334eb1bb7dd74469f2d996a6ba36 -->

<!-- START_b772ae86edbc3eeba35ebd51b5e8d6f1 -->
## Danh sách nhóm khách hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/nobis/group_customers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/nobis/group_customers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/group_customers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_b772ae86edbc3eeba35ebd51b5e8d6f1 -->

<!-- START_09b98082946f19c62814714efaefe715 -->
## remove một GroupCustomer

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/group_customers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/group_customers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/group_customers/{group_customer_id}`


<!-- END_09b98082946f19c62814714efaefe715 -->

<!-- START_4e684bf19b53e0237ab2ffb9477a11dd -->
## update một GroupCustomer

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/group_customers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","note":"quia","list":"aliquam","type_compare":"expedita","comparison_expression":"quia","value_compare":"qui"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/group_customers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "et",
    "note": "quia",
    "list": "aliquam",
    "type_compare": "expedita",
    "comparison_expression": "quia",
    "value_compare": "qui"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/group_customers/{group_customer_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên nhóm khách hàng
        `note` | file |  required  | Ghi chú
        `list` | {type_compare,comparison_expression,value_compare) |  optional  | 
        `type_compare` | Kiểu |  optional  | so sánh //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký, 9 CTV, 10 AGENCY
        `comparison_expression` | Biểu |  optional  | thức so sánh  (>,>=,=,<,<=)
        `value_compare` | Giá |  optional  | trị so sánh so sánh (đối với ctv vs agency 0 là tất cả)
    
<!-- END_4e684bf19b53e0237ab2ffb9477a11dd -->

<!-- START_a90aeda660ae65d48b5b469a4fc09907 -->
## thông tin 1 nhóm khách hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/group_customers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/group_customers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/group_customers/{group_customer_id}`


<!-- END_a90aeda660ae65d48b5b469a4fc09907 -->

#User/Nhập kho


<!-- START_116b024748094d3f941c0f7b417bef98 -->
## Tạo phiếu nhập hàng

payment_status  Trạng tháng thanh toán (0 chưa thanh toán, 1 thanh toán 1 phần, 2 đã thanh toán)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/accusamus/1/inventory/import_stocks" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"pariatur","import_stock_items":"repellendus","supplier_id":5,"tax":0.911,"cost":3.337221786,"discount":139}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/accusamus/1/inventory/import_stocks"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "pariatur",
    "import_stock_items": "repellendus",
    "supplier_id": 5,
    "tax": 0.911,
    "cost": 3.337221786,
    "discount": 139
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/inventory/import_stocks`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | String |  optional  | ghi chú
        `import_stock_items` | List |  optional  | danh sách nhập hàng [ {reality_exist:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
        `supplier_id` | integer |  optional  | id nhà cung cấp
        `tax` | float |  optional  | thuế
        `cost` | float |  optional  | chi phí
        `discount` | float |  optional  | chi phí
    
<!-- END_116b024748094d3f941c0f7b417bef98 -->

<!-- START_077b102262acfee95b74198b36179bff -->
## Danh sách phiếu nhập

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/rerum/1/inventory/import_stocks?supplier_id=nihil&search=inventore&status_list=nobis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/1/inventory/import_stocks"
);

let params = {
    "supplier_id": "nihil",
    "search": "inventore",
    "status_list": "nobis",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/import_stocks`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `supplier_id` |  optional  | int id nhà cung cấp
    `search` |  optional  | Mã phiếu
    `status_list` |  optional  | List danh sách trạng thái VD: 0,1,2

<!-- END_077b102262acfee95b74198b36179bff -->

<!-- START_b2a1957eb6c326d0055e7259039200a8 -->
## Thông tin phiếu nhập hàng

total_amount tổng số tiền nhập hàng

total_number số lượng sp nhập hàng

status int (0 đặt hàng, 1 duyệt, 2 nhập kho, 3 hoàn thành, 4 đã hủy)

remaining_amount thanh toán còn lại

history_pay_import_stock danh sách lịch sử thanh toán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/sapiente/1/inventory/import_stocks/aliquid" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/sapiente/1/inventory/import_stocks/aliquid"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `import_stock_id` |  required  | Id phiếu nhập hàng

<!-- END_b2a1957eb6c326d0055e7259039200a8 -->

<!-- START_0bba360224f87b753a1d2ede28aea58b -->
## Cập nhật phiếu nhập hàng

Có thể truyền 1 trong số này không bắt buộc truyền lên hết

refund_received_money biến này lưu số tiền đã nhận hoàn nếu đơn đã nhập thì trở thành tổng của các đơn hoàn

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/quia/1/inventory/import_stocks/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"omnis","status":10,"import_stock_items":"labore","tax":2594.58071899,"cost":125.954,"discount":1606.979272}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quia/1/inventory/import_stocks/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "omnis",
    "status": 10,
    "import_stock_items": "labore",
    "tax": 2594.58071899,
    "cost": 125.954,
    "discount": 1606.979272
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | String |  optional  | ghi chú
        `status` | integer |  optional  | 0 đã kiểm kho, 1 đã cân bằng
        `import_stock_items` | List |  optional  | danh sách check kho [ {reality_exist:1, product_id,import_price,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
        `tax` | float |  optional  | thuế
        `cost` | float |  optional  | chi phí
        `discount` | float |  optional  | chi phí
    
<!-- END_0bba360224f87b753a1d2ede28aea58b -->

<!-- START_0e7507f0fe908041a35aae6c7d868cf8 -->
## Thay đổi trạng thái

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/1/inventory/import_stocks/1/status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":17}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/1/inventory/import_stocks/1/status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 17
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/status`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | (0 đặt hàng, 1 duyệt, 2 nhập kho, 3 hoàn thành, 4 đã hủy)
    
<!-- END_0e7507f0fe908041a35aae6c7d868cf8 -->

<!-- START_442e2b2f436f86f97c348d889ac45e40 -->
## Thanh toán đơn nhập hàng

@bodyParam amount_money double số tiền thanh toán  (truyền lên đầy đủ sẽ tự động thanh toán, truyền 1 phần tự động chuyển thanh trạng thái thanh toán 1 phần, truyền 0 chưa thanh toán)

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/1/inventory/import_stocks/1/payment" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"payment_method":7}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/1/inventory/import_stocks/1/payment"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "payment_method": 7
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/payment`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `payment_method` | integer |  optional  | phương thức thanh toán
    
<!-- END_442e2b2f436f86f97c348d889ac45e40 -->

<!-- START_c33ae2e390a34c0acfd43c82c4ca8da0 -->
## Hoàn trả phiếu nhập hàng

refund_received_money biến này lưu số tiền đã nhận hoàn nếu đơn đã nhập thì trở thành tổng của các đơn hoàn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/dignissimos/1/inventory/import_stocks/1/refund" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"refund_line_items":"dolor","refund_money_paid":"labore"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/dignissimos/1/inventory/import_stocks/1/refund"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "refund_line_items": "dolor",
    "refund_money_paid": "labore"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/inventory/import_stocks/{import_stock_id}/refund`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `refund_line_items` | List |  optional  | danh sách trả hàng [ {  "line_item_id":25, "quantity":1 } ]
        `refund_money_paid` | Model |  optional  | Số liệu tiền hoàn trả của NCC (ko trả tiền truyền null) {amount_money, payment_method}
    
<!-- END_c33ae2e390a34c0acfd43c82c4ca8da0 -->

#User/Phiếu kiểm kho


<!-- START_2541dd24c47f6438b99bdd836ef52473 -->
## Tạo phiếu kiểm hàng

status int 0 đã kiểm kho, 1 đã cân bằng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/ea/quo/inventory/tally_sheets" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"fuga","tally_sheet_items":"vitae"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/ea/quo/inventory/tally_sheets"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "fuga",
    "tally_sheet_items": "vitae"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/inventory/tally_sheets`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | branch_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | String |  optional  | ghi chú
        `tally_sheet_items` | List |  optional  | danh sách check kho [ {reality_exist:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
    
<!-- END_2541dd24c47f6438b99bdd836ef52473 -->

<!-- START_6213712f3bc1e3ec8885302adc174dce -->
## Danh sách phiếu kiểm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/velit/eligendi/inventory/tally_sheets?search=eum&status=maiores" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/velit/eligendi/inventory/tally_sheets"
);

let params = {
    "search": "eum",
    "status": "maiores",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/tally_sheets`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | branch_id
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `search` |  optional  | Mã phiếu
    `status` |  optional  | int trạng phiếu kiểm

<!-- END_6213712f3bc1e3ec8885302adc174dce -->

<!-- START_4b205f974aeaa1edc50e353249f90d01 -->
## Thông tin phiếu chi tiết

status int 0 đã kiểm kho, 1 đã cân bằng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/voluptate/autem/inventory/tally_sheets/magnam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptate/autem/inventory/tally_sheets/magnam"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | branch_id
    `tally_sheet_id` |  required  | Id phiếu kiểm hàng

<!-- END_4b205f974aeaa1edc50e353249f90d01 -->

<!-- START_c2836c5ce0cacea578a76f22394754a1 -->
## Xóa phiếu kiểm

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/eaque/aut/inventory/tally_sheets/delectus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/eaque/aut/inventory/tally_sheets/delectus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | branch_id
    `tally_sheet_id` |  required  | Id phiếu kiểm hàng

<!-- END_c2836c5ce0cacea578a76f22394754a1 -->

<!-- START_fe0f5da8e491fbbc2321e1ce22b0647e -->
## Cập nhật phiếu kiểm hàng

Có thể truyền 1 trong số này không bắt buộc truyền lên hết

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/molestiae/sint/inventory/tally_sheets/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"note":"est","tally_sheet_items":"perferendis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/molestiae/sint/inventory/tally_sheets/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "est",
    "tally_sheet_items": "perferendis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `branch_id` |  required  | branch_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `note` | String |  optional  | ghi chú
        `tally_sheet_items` | List |  optional  | (có thể trống) danh sách check kho [ {reality_exist:1, product_id,distribute_name,element_distribute_name,sub_element_distribute_name  } ]
    
<!-- END_fe0f5da8e491fbbc2321e1ce22b0647e -->

<!-- START_316196236fdc1c9f25a06b540af23869 -->
## Cân bằng kho từ phiếu kiểm

@urlParam  branch_id required branch_id

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/1/inventory/tally_sheets/aut/balance" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/1/inventory/tally_sheets/aut/balance"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/inventory/tally_sheets/{tally_sheet_id}/balance`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `tally_sheet_id` |  required  | Id phiếu kiểm hàng

<!-- END_316196236fdc1c9f25a06b540af23869 -->

#User/Phiếu thu chi


<!-- START_139a4734ccafdce32de2ab66c55f1044 -->
## Danh sách phiếu thu chi

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/eligendi/1/revenue_expenditures?recipient_group=dolor&recipient_references_id=in&search=provident&is_revenue=quae" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/eligendi/1/revenue_expenditures"
);

let params = {
    "recipient_group": "dolor",
    "recipient_references_id": "in",
    "search": "provident",
    "is_revenue": "quae",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/revenue_expenditures`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `recipient_group` |  optional  | int id Nhóm khách hàng
    `recipient_references_id` |  optional  | int id ID chủ thể
    `search` |  optional  | Mã phiếu
    `is_revenue` |  optional  | boolean Phải thu không

<!-- END_139a4734ccafdce32de2ab66c55f1044 -->

<!-- START_3b9a9e906d5093dbffb76d846e22d1eb -->
## Tạo phiếu thu chi

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/et/1/revenue_expenditures" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_revenue":false,"change_money":284888,"recipient_group":7,"recipient_references_id":6,"allow_accounting":false,"description":"eaque","code":"voluptas","type":18,"reference_name":"qui","payment_method":15}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/et/1/revenue_expenditures"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_revenue": false,
    "change_money": 284888,
    "recipient_group": 7,
    "recipient_references_id": 6,
    "allow_accounting": false,
    "description": "eaque",
    "code": "voluptas",
    "type": 18,
    "reference_name": "qui",
    "payment_method": 15
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/{branch_id}/revenue_expenditures`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_revenue` | boolean |  optional  | true phiếu thu, false phiếu chi
        `change_money` | float |  optional  | Số tiền
        `recipient_group` | integer |  optional  | nhóm khách hàng 0 khách hàng, 1Nhóm nhà cung cấp, 2nhân viên,  3Đối tượng khác
        `recipient_references_id` | integer |  optional  | ID đại diện chủ thể (khách hàng, Nhóm nhà cung cấp,nhân viên,Đối tượng khác)
        `allow_accounting` | boolean |  optional  | Cho phép hạch toán
        `description` | String |  optional  | Mô tả
        `code` | String |  optional  | Mã phiếu
        `type` | integer |  optional  | Kiểu phiếu
        `reference_name` | String |  optional  | Tham chiếu
        `payment_method` | integer |  optional  | kiểu thanh toán (0 tiền mặt, 1 quẹt thẻ, 2 cod, 3 chuyển khoản)
    
<!-- END_3b9a9e906d5093dbffb76d846e22d1eb -->

<!-- START_30e31383628b39afb446383a65530939 -->
## Thông tin phiếu thu chi

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/fugiat/1/revenue_expenditures/corporis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/fugiat/1/revenue_expenditures/corporis"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/{branch_id}/revenue_expenditures/{revenue_expenditure_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `revenue_expenditure_id` |  required  | Id phiếu thu chi

<!-- END_30e31383628b39afb446383a65530939 -->

#User/Phân loại sản phẩm


<!-- START_996f5436f626cf431ab59a223d667f10 -->
## Thông tin 1 phân loại sp

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/products/1/distribute" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":"vel"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/products/1/distribute"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": "vel"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/products/{product_id}/distribute`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | product |  optional  | 
    
<!-- END_996f5436f626cf431ab59a223d667f10 -->

<!-- START_81ffe76fbb1abcfc8d8f9d07575ae061 -->
## Sửa phân loại

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store_v2/1/1/products/1/distribute?product_id=rerum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"has_distribute":true,"distribute_name":"esse","has_sub":false,"sub_element_distribute_name":"velit","element_distributes":"qui","sub_element_distributes":"commodi","name":"sed","image_url":"dignissimos","price":"voluptatem","import_price":"et","default_price":"odit","barcode":"qui","quantity_in_stock":"eos"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/products/1/distribute"
);

let params = {
    "product_id": "rerum",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "has_distribute": true,
    "distribute_name": "esse",
    "has_sub": false,
    "sub_element_distribute_name": "velit",
    "element_distributes": "qui",
    "sub_element_distributes": "commodi",
    "name": "sed",
    "image_url": "dignissimos",
    "price": "voluptatem",
    "import_price": "et",
    "default_price": "odit",
    "barcode": "qui",
    "quantity_in_stock": "eos"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store_v2/{store_code}/{branch_id}/products/{product_id}/distribute`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `product_id` |  optional  | int id của id của product
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `has_distribute` | boolean |  optional  | Có phân loại không (false đồng nghĩa với xóa)
        `distribute_name` | Tên |  optional  | phân loại chính (VD màu sắc)
        `has_sub` | boolean |  optional  | Có phân loại phụ không
        `sub_element_distribute_name` | Tên |  optional  | kiểu phân loại phụ
        `element_distributes` | List |  optional  | danh sách element [ {is_edit,before_name, name,image_url,price,import_price,default_price,barcode, quantity_in_stock, sub_element_distributes:[json phía dưới] }  ]
        `sub_element_distributes` | List |  optional  | danh sách element [ {is_edit,before_name, name,image_url,price,import_price,default_price,barcode, quantity_in_stock}  ]
        `name` | string |  optional  | tên phân loại
        `image_url` | string |  optional  | ảnh phân loại
        `price` | giá |  optional  | bán
        `import_price` | giá |  optional  | nhập
        `default_price` | giá |  optional  | mặc định
        `barcode` | barcode |  optional  | 
        `quantity_in_stock` | kho |  optional  | 
    
<!-- END_81ffe76fbb1abcfc8d8f9d07575ae061 -->

<!-- START_c5a4db4ba4eb93041a4aa3c324a404ff -->
## Thông tin 1 phân loại sp

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/products/1/distribute" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":"eaque"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/products/1/distribute"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": "eaque"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/products/{product_id}/distribute`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | product |  optional  | 
    
<!-- END_c5a4db4ba4eb93041a4aa3c324a404ff -->

<!-- START_09183969dc4f586d172bd18c3d3e1948 -->
## Sửa phân loại

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/products/1/distribute?product_id=minus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"has_distribute":false,"distribute_name":"non","has_sub":false,"sub_element_distribute_name":"voluptatem","element_distributes":"soluta","sub_element_distributes":"nisi","name":"maiores","image_url":"iusto","price":"natus","import_price":"ea","default_price":"molestiae","barcode":"dolorem","quantity_in_stock":"cum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/products/1/distribute"
);

let params = {
    "product_id": "minus",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "has_distribute": false,
    "distribute_name": "non",
    "has_sub": false,
    "sub_element_distribute_name": "voluptatem",
    "element_distributes": "soluta",
    "sub_element_distributes": "nisi",
    "name": "maiores",
    "image_url": "iusto",
    "price": "natus",
    "import_price": "ea",
    "default_price": "molestiae",
    "barcode": "dolorem",
    "quantity_in_stock": "cum"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/products/{product_id}/distribute`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `product_id` |  optional  | int id của id của product
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `has_distribute` | boolean |  optional  | Có phân loại không (false đồng nghĩa với xóa)
        `distribute_name` | Tên |  optional  | phân loại chính (VD màu sắc)
        `has_sub` | boolean |  optional  | Có phân loại phụ không
        `sub_element_distribute_name` | Tên |  optional  | kiểu phân loại phụ
        `element_distributes` | List |  optional  | danh sách element [ {is_edit,before_name, name,image_url,price,import_price,default_price,barcode, quantity_in_stock, sub_element_distributes:[json phía dưới] }  ]
        `sub_element_distributes` | List |  optional  | danh sách element [ {is_edit,before_name, name,image_url,price,import_price,default_price,barcode, quantity_in_stock}  ]
        `name` | string |  optional  | tên phân loại
        `image_url` | string |  optional  | ảnh phân loại
        `price` | giá |  optional  | bán
        `import_price` | giá |  optional  | nhập
        `default_price` | giá |  optional  | mặc định
        `barcode` | barcode |  optional  | 
        `quantity_in_stock` | kho |  optional  | 
    
<!-- END_09183969dc4f586d172bd18c3d3e1948 -->

#User/Phương thức thanh toán


<!-- START_4494699b13da04d34d923dff32ffdcc2 -->
## Danh cách phương thức thanh toán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/payment_methods" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/payment_methods"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/payment_methods`


<!-- END_4494699b13da04d34d923dff32ffdcc2 -->

<!-- START_5df6634875e464020d3c9ce3e46d2807 -->
## Cập nhật thông tin cho 1 phương thức thanh toán

payment_guide trường hợp này gửi danh sách item gồm

[ {"account_name":"HTS", "account_number":"4445564", "bank":"BIDV", "branch":"gdfg"} ]

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/payment_methods/excepturi" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"use":false}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/payment_methods/excepturi"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "use": false
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/payment_methods/{method_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `method_id` |  required  | id cần sửa
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `use` | boolean |  optional  | Có sử dụng hay không
    
<!-- END_5df6634875e464020d3c9ce3e46d2807 -->

#User/Quản lý Cộng tác viên


Cộng tác viên
<!-- START_3e6baa3c719c2d48c9e22e32ea4a07a5 -->
## Danh sách CTV

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/minus/collaborators?page=15&search=non&sort_by=iure&descending=perspiciatis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/minus/collaborators"
);

let params = {
    "page": "15",
    "search": "non",
    "sort_by": "iure",
    "descending": "perspiciatis",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/collaborators`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false

<!-- END_3e6baa3c719c2d48c9e22e32ea4a07a5 -->

<!-- START_7746fcfed5d41a8164fd140e26e7fe6b -->
## Báo cáo Danh sách CTV theo top

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/rerum/collaborators/report?page=11&search=provident&sort_by=officia&descending=perferendis&date_from=sint&date_to=praesentium" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/collaborators/report"
);

let params = {
    "page": "11",
    "search": "provident",
    "sort_by": "officia",
    "descending": "perferendis",
    "date_from": "sint",
    "date_to": "praesentium",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/collaborators/report`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `date_from` |  optional  | 
    `date_to` |  optional  | 

<!-- END_7746fcfed5d41a8164fd140e26e7fe6b -->

<!-- START_3a2cb3c9038e9c3f7bf131b6721260c4 -->
## Cập nhật 1 số thuộc tính cho collaborators

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/autem/collaborators/id" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":7}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/autem/collaborators/id"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 7
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/collaborators/{collaborator_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `collaborator_id` |  required  | id trong danh sach cong tac vien
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | Trạng thái cộng tác viên 1 (Hoạt động)  0 đã hủy
    
<!-- END_3a2cb3c9038e9c3f7bf131b6721260c4 -->

<!-- START_7b6dd17bf31af1d19789ef897324d7d9 -->
## Lấy thông số chia sẻ cho cộng tác viên

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/ad/collaborator_configs" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_payment_request":"et","type_rose":"rerum","payment_1_of_month":"nostrum","payment_16_of_month":"ipsum","payment_limit":"sapiente"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/ad/collaborator_configs"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_payment_request": "et",
    "type_rose": "rerum",
    "payment_1_of_month": "nostrum",
    "payment_16_of_month": "ipsum",
    "payment_limit": "sapiente"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/collaborator_configs`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_payment_request` | cho |  optional  | phép gửi yêu cầu thanh toán
        `type_rose` | string |  optional  | doanh số, 1 hoa hồng
        `payment_1_of_month` | Quyết |  optional  | toán ngày 1 hàng tháng ko
        `payment_16_of_month` | Quyết |  optional  | toán ngày 15 hàng tháng ko
        `payment_limit` | Số |  optional  | tiền hoa hồng được quyết toán
    
<!-- END_7b6dd17bf31af1d19789ef897324d7d9 -->

<!-- START_fadae6a73c0c82ff70a57a35337f956c -->
## Cập nhật cấu hình cài đặt cho phần CTV

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/qui/collaborator_configs" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"type_rose":15,"allow_payment_request":"officia","payment_1_of_month":"velit","payment_16_of_month":"laborum","payment_limit":"aut","percent_collaborator_t1":506.944843711,"allow_rose_referral_customer":"distinctio"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/qui/collaborator_configs"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "type_rose": 15,
    "allow_payment_request": "officia",
    "payment_1_of_month": "velit",
    "payment_16_of_month": "laborum",
    "payment_limit": "aut",
    "percent_collaborator_t1": 506.944843711,
    "allow_rose_referral_customer": "distinctio"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/collaborator_configs`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `type_rose` | integer |  optional  | 0 (Theo doanh số)  1 Theo hoa hồng giới thiệu
        `allow_payment_request` | cho |  optional  | phép gửi yêu cầu thanh toán
        `payment_1_of_month` | Quyết |  optional  | toán ngày 1 hàng tháng ko
        `payment_16_of_month` | Quyết |  optional  | toán ngày 15 hàng tháng ko
        `payment_limit` | Số |  optional  | tiền hoa hồng được quyết toán
        `percent_collaborator_t1` | float |  optional  | Phăm trăm chia sẻ cho công tác viên T1
        `allow_rose_referral_customer` | cho |  optional  | phép cộng tiền hoa hồng từ khách hàng của CTV giới thiệu
    
<!-- END_fadae6a73c0c82ff70a57a35337f956c -->

<!-- START_0d6dba38f3cb2fe45e9358593a7ebc09 -->
## Danh sách bậc thang thưởng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/maxime/collaborator_configs/bonus_steps" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/maxime/collaborator_configs/bonus_steps"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/collaborator_configs/bonus_steps`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_0d6dba38f3cb2fe45e9358593a7ebc09 -->

<!-- START_776415013455c25f8a24d357a5fe62ed -->
## Thêm 1 bậc tiền thưởng 1 tháng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/et/collaborator_configs/bonus_steps" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":1.176,"bonus":4929.852074495}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/et/collaborator_configs/bonus_steps"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 1.176,
    "bonus": 4929.852074495
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/collaborator_configs/bonus_steps`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn được thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_776415013455c25f8a24d357a5fe62ed -->

<!-- START_638a9b71193a537b3363fef081dfb076 -->
## update một Step

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/pariatur/collaborator_configs/bonus_steps/ab" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":6717.55,"bonus":1604.2282}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/pariatur/collaborator_configs/bonus_steps/ab"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 6717.55,
    "bonus": 1604.2282
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/collaborator_configs/bonus_steps/{step_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `step_id` |  required  | Step_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn đc thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_638a9b71193a537b3363fef081dfb076 -->

<!-- START_998c77b5d6ba4dfb664d7b938d356fcd -->
## xóa một bac thang

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/et/collaborator_configs/bonus_steps/est" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/et/collaborator_configs/bonus_steps/est"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/collaborator_configs/bonus_steps/{step_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `step_id` |  required  | ID Step cần xóa thông tin.

<!-- END_998c77b5d6ba4dfb664d7b938d356fcd -->

#User/Quản lý bài đăng cộng đồng


<!-- START_7377b829b385f9e0df39672584a0cea2 -->
## Thêm bài đăng cộng đồng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/community_posts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"qui","content":"error","status":"est","images":"minima","feeling":"blanditiis","checkin_location":"vel","background_color":"qui"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_posts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "qui",
    "content": "error",
    "status": "est",
    "images": "minima",
    "feeling": "blanditiis",
    "checkin_location": "vel",
    "background_color": "qui"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/community_posts`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | required |  optional  | tên bài đăng
        `content` | required |  optional  | nội dung
        `status` | required |  optional  | (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
        `feeling` | required |  optional  | Cảm xúc
        `checkin_location` | required |  optional  | Vị trí checkin
        `background_color` | required |  optional  | Màu nền
    
<!-- END_7377b829b385f9e0df39672584a0cea2 -->

<!-- START_95b54978c5f48e81e0c95043cc63371b -->
## Danh sách bài đăng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/community_posts?customer_id=aspernatur&search=alias" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_posts"
);

let params = {
    "customer_id": "aspernatur",
    "search": "alias",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/community_posts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `status` |  optional  | integer required trạng thái  (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `customer_id` |  required  | Nếu là user thì tự động lấy ds thuộc customer, còn admin thì truyền lên
    `search` |  required  | Tìm theo tiêu đề

<!-- END_95b54978c5f48e81e0c95043cc63371b -->

<!-- START_d259795638334314417d44b5b52fc3b1 -->
## Cập nhật bài đăng cộng đồng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/community_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"content":"et","status":"alias","images":"architecto","feeling":"earum","checkin_location":"ad","background_color":"sint"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "content": "et",
    "status": "alias",
    "images": "architecto",
    "feeling": "earum",
    "checkin_location": "ad",
    "background_color": "sint"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/community_posts/{community_post_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `content` | required |  optional  | Nội dung
        `status` | required |  optional  | (1 chờ duyệt, 0 đã duyệt, 2 đã ẩn)
        `images` | required |  optional  | List danh sách ảnh sp (VD: ["linl1", "link2"])
        `feeling` | required |  optional  | Cảm xúc
        `checkin_location` | required |  optional  | Vị trí checkin
        `background_color` | required |  optional  | Màu nền
    
<!-- END_d259795638334314417d44b5b52fc3b1 -->

<!-- START_c1db2d0f8215b31c6927de38ef11ee6b -->
## Lấy 1 bài

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/community_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/community_posts/{community_post_id}`


<!-- END_c1db2d0f8215b31c6927de38ef11ee6b -->

<!-- START_54d269d5b44d36bdc3bb89e63baaa2c6 -->
## Xóa Cần mua cần bán

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/community_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_posts/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/community_posts/{community_post_id}`


<!-- END_54d269d5b44d36bdc3bb89e63baaa2c6 -->

<!-- START_345b1467628dde2731c28d2774a3843a -->
## Đăng lại lên top

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/community_posts/1/reup" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_posts/1/reup"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/community_posts/{community_post_id}/reup`


<!-- END_345b1467628dde2731c28d2774a3843a -->

<!-- START_a35362d5604e86943f7479ef7e994131 -->
## Ghim bài

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/community_post_ghim" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"community_post_id":"ut","is_pin":"dolores"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/community_post_ghim"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "community_post_id": "ut",
    "is_pin": "dolores"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/community_post_ghim`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `community_post_id` | required |  optional  | id bài viết
        `is_pin` | required |  optional  | is_pin
    
<!-- END_a35362d5604e86943f7479ef7e994131 -->

#User/Quản lý chấm công


APIs AppTheme
<!-- START_8a30f3bfe08d63cec745437e5802cb95 -->
## Bổ sung - bớt công

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/sapiente/1/bonus_less_checkin_checkout" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_bonus":false,"checkin_time":"eum","checkout_time":"itaque","reason":"quam","staff_id":5}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/sapiente/1/bonus_less_checkin_checkout"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_bonus": false,
    "checkin_time": "eum",
    "checkout_time": "itaque",
    "reason": "quam",
    "staff_id": 5
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/bonus_less_checkin_checkout`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_bonus` | boolean |  optional  | Thêm công (true là thêm, false là bớt)
        `checkin_time` | datetime |  optional  | checkin_time Thời gian bắt đầu
        `checkout_time` | datetime |  optional  | checkout_time Thời gian kết thúc
        `reason` | string |  optional  | Lý do
        `staff_id` | integer |  optional  | Staff id
    
<!-- END_8a30f3bfe08d63cec745437e5802cb95 -->

<!-- START_1da7d27f3466676d8dd8add2e3527fc9 -->
## Danh sách yêu cầu chấm công

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/dolore/1/await_checkin_checkouts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/dolore/1/await_checkin_checkouts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/await_checkin_checkouts`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_1da7d27f3466676d8dd8add2e3527fc9 -->

<!-- START_66a3b0f77c245a04911dff967003bc8f -->
## Thay đổi trạng thái chấm công

Trạng thái    STATUS_AWAIT_CHECK = 1;STATUS_CHECKED = 2; STATUS_CANCEL = 3;

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/qui/1/await_checkin_checkouts/1/change_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":5}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/qui/1/await_checkin_checkouts/1/change_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 5
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/await_checkin_checkouts/{date_timekeeping_history_id}/change_status`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | trạng thái 1 chờ xử lý, 2 đã đồng ý, 3 hủy
    
<!-- END_66a3b0f77c245a04911dff967003bc8f -->

<!-- START_fbe63a30cbd757f7b1e1d102b92dcf79 -->
## Danh sách mobile yêu cầu

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/et/1/await_mobile_checkins" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":8}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/et/1/await_mobile_checkins"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 8
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/await_mobile_checkins`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | trạng thái 0 chờ xử lý, 1 đã đồng ý, 2 hủy
    
<!-- END_fbe63a30cbd757f7b1e1d102b92dcf79 -->

<!-- START_8ebf47f3df4ec05154672c9720dffc7b -->
## Thay đổi trạng thái mobile

Trạng thái    0 cho duyet, 1 da duyet

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/sapiente/1/await_mobile_checkins/1/change_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":7}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/sapiente/1/await_mobile_checkins/1/change_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 7
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/await_mobile_checkins/{mobile_id}/change_status`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | trạng thái 1 chờ xử lý, 1 da duyet
    
<!-- END_8ebf47f3df4ec05154672c9720dffc7b -->

<!-- START_3724340fe313bd14ea5f807f17406776 -->
## Danh sách mobile của nhân viên

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/placeat/1/mobile_checkin/staff/architecto" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/placeat/1/mobile_checkin/staff/architecto"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/mobile_checkin/staff/{staff_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `staff_id` |  required  | staff_id

<!-- END_3724340fe313bd14ea5f807f17406776 -->

#User/Quản lý khách hàng


<!-- START_14412d6a3060f05d13c7e1b8cad93bd3 -->
## Danh sách tất cả khách hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/customers?page=14&search=suscipit&sort_by=ut&descending=eveniet&field_by=quidem&field_by_value=ratione&day_of_birth=atque&month_of_birth=quam&year_of_birth=quidem&referral_phone_number=quibusdam&json_list_filter=voluptatem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"type_compare":"laborum","comparison_expression":"quia","value_compare":"voluptates"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/customers"
);

let params = {
    "page": "14",
    "search": "suscipit",
    "sort_by": "ut",
    "descending": "eveniet",
    "field_by": "quidem",
    "field_by_value": "ratione",
    "day_of_birth": "atque",
    "month_of_birth": "quam",
    "year_of_birth": "quidem",
    "referral_phone_number": "quibusdam",
    "json_list_filter": "voluptatem",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "type_compare": "laborum",
    "comparison_expression": "quia",
    "value_compare": "voluptates"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/customers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên,số điện thoại cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `field_by` |  optional  | Chọn trường nào để lấy
    `field_by_value` |  optional  | Giá trị trường đó
    `day_of_birth` |  optional  | Ngay sinh
    `month_of_birth` |  optional  | Thang sinh
    `year_of_birth` |  optional  | Nam sinh
    `referral_phone_number` |  optional  | Số điện thoại giới thiệu
    `json_list_filter` |  optional  | Chuỗi tìm
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `type_compare` | Kiểu |  optional  | so sánh //0 Tổng mua (Chỉ đơn hoàn thành), 1 tổng bán, 2 Xu hiện tại, 3 Số lần mua hàng 4, tháng sinh nhật 5, tuổi 6, giới tính, 7 tỉnh, 8 ngày đăng ký, 9 CTV, 10 đại lý, 11 nhóm kh
        `comparison_expression` | Biểu |  optional  | thức so sánh  (>,>=,=,<,<=)
        `value_compare` | Giá |  optional  | trị so sánh so sánh
    
<!-- END_14412d6a3060f05d13c7e1b8cad93bd3 -->

<!-- START_cde99daaf0326f26780f49a52eb163c7 -->
## Thông tin 1 khách hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/customers/1?customer_id=a" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/customers/1"
);

let params = {
    "customer_id": "a",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/customers/{customer_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `customer_id` |  optional  | int required  Customer id

<!-- END_cde99daaf0326f26780f49a52eb163c7 -->

<!-- START_5b7448e0db83c1428fa6c0e44ca7bbc0 -->
## Tạo thêm 1 khách hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/kds/customers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","phone_number":"qui","email":"voluptas","address_detail":"asperiores","province":12,"district":"sunt","wards":"aliquam","date_of_birth":"doloribus","sex":19,"is_update":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/customers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "et",
    "phone_number": "qui",
    "email": "voluptas",
    "address_detail": "asperiores",
    "province": 12,
    "district": "sunt",
    "wards": "aliquam",
    "date_of_birth": "doloribus",
    "sex": 19,
    "is_update": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/customers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | tên khách hàng
        `phone_number` | string |  required  | sdt khách hàng
        `email` | string |  required  | email
        `address_detail` | string |  required  | địa chỉ
        `province` | integer |  required  | id tỉnh
        `district` | string |  required  | id quận
        `wards` | string |  required  | id xã
        `date_of_birth` | Date |  optional  | Ngày sinh
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
        `is_update` | boolean |  optional  | truong hop cap nhat chu ko them moi
    
<!-- END_5b7448e0db83c1428fa6c0e44ca7bbc0 -->

<!-- START_99203ad6be6731eb9595e9caf51b7ae5 -->
## Cập nhật 1 khách hàng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/kds/customers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"voluptas","phone_number":"et","email":"quia","address_detail":"cumque","province":10,"district":"harum","wards":"velit","date_of_birth":"rerum","sex":11}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/customers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "voluptas",
    "phone_number": "et",
    "email": "quia",
    "address_detail": "cumque",
    "province": 10,
    "district": "harum",
    "wards": "velit",
    "date_of_birth": "rerum",
    "sex": 11
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/customers/{customer_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | tên khách hàng
        `phone_number` | string |  required  | sdt khách hàng
        `email` | string |  required  | email
        `address_detail` | string |  required  | địa chỉ
        `province` | integer |  required  | id tỉnh
        `district` | string |  required  | id quận
        `wards` | string |  required  | id xã
        `date_of_birth` | Date |  optional  | Ngày sinh
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
    
<!-- END_99203ad6be6731eb9595e9caf51b7ae5 -->

<!-- START_778da3682203e7edfcb7d15e1c3a5c1c -->
## Xóa 1 khách hàng

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/kds/customers/veritatis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/customers/veritatis"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/customers/{customer_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
    `customer_id` |  optional  | string required  id khách hàng cần xóa

<!-- END_778da3682203e7edfcb7d15e1c3a5c1c -->

<!-- START_dd6db82946c100290a19eefb3d3d2cec -->
## Xem địa chỉ của khách

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/address_customer" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"esse"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/address_customer"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "esse"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/address_customer`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  optional  | sdt khách
    
<!-- END_dd6db82946c100290a19eefb3d3d2cec -->

<!-- START_afb3659201eb9bfcb5db7c0607f46a12 -->
## Lịch sử tích điểm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/customers/1/history_points" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/customers/1/history_points"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/customers/{customer_id}/history_points`


<!-- END_afb3659201eb9bfcb5db7c0607f46a12 -->

#User/Quản lý Đại lý


Đại lý
<!-- START_f1ff89487828168656b59ebfafa3d680 -->
## Thêm tầng đại lý

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/quaerat/agency_type" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"itaque","position":"molestiae"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quaerat/agency_type"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "itaque",
    "position": "molestiae"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/agency_type`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | Tên |  optional  | tầng đại lý
        `position` | Vị |  optional  | trí trên danh sách
    
<!-- END_f1ff89487828168656b59ebfafa3d680 -->

<!-- START_d573bf585c15dfd84af573f668734bb8 -->
## Cập nhật tầng đại lý

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/voluptas/agency_type/tempore" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"consequatur","position":"deleniti"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptas/agency_type/tempore"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "consequatur",
    "position": "deleniti"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/agency_type/{agency_type_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
    `agency_type_id` |  required  | agency_type_id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | Tên |  optional  | tầng đại lý
        `position` | Vị |  optional  | trí trên danh sách
    
<!-- END_d573bf585c15dfd84af573f668734bb8 -->

<!-- START_811eecc71d252bb640a9dff9269227d9 -->
## Xóa 1 tầng đại lý

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/dignissimos/agency_type/libero" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/dignissimos/agency_type/libero"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/agency_type/{agency_type_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
    `agency_type_id` |  required  | agency_type_id

<!-- END_811eecc71d252bb640a9dff9269227d9 -->

<!-- START_b76700d3ecf9af7090823a0424b209db -->
## DS tầng đại lý

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/agency_type" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/agency_type"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agency_type`


<!-- END_b76700d3ecf9af7090823a0424b209db -->

<!-- START_b9f1dd155e3fd26c93d0bfc877e4d232 -->
## Danh sách Đại lý

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/dolores/agencies?page=1&search=eum&sort_by=at&descending=dolorem&agency_type_id=necessitatibus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/dolores/agencies"
);

let params = {
    "page": "1",
    "search": "eum",
    "sort_by": "at",
    "descending": "dolorem",
    "agency_type_id": "necessitatibus",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agencies`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `agency_type_id` |  optional  | Id tầng đại lý

<!-- END_b9f1dd155e3fd26c93d0bfc877e4d232 -->

<!-- START_219dc803a8151958b4289fda7a999085 -->
## Báo cáo Danh sách CTV theo top

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/harum/agencies/report?page=11&search=dolorem&sort_by=omnis&descending=aliquid&date_from=quam&date_to=veniam&report_type=a" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/harum/agencies/report"
);

let params = {
    "page": "11",
    "search": "dolorem",
    "sort_by": "omnis",
    "descending": "aliquid",
    "date_from": "quam",
    "date_to": "veniam",
    "report_type": "a",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agencies/report`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `date_from` |  optional  | 
    `date_to` |  optional  | 
    `report_type` |  optional  | báo cáo theo xu, hay theo đơn hàng (order, point)

<!-- END_219dc803a8151958b4289fda7a999085 -->

<!-- START_a303786f1b3538c2df3417c76a425e5b -->
## Cập nhật 1 số thuộc tính cho agencys

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/sapiente/agencies/unde" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":12,"agency_type_id":"veritatis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/sapiente/agencies/unde"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 12,
    "agency_type_id": "veritatis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/agencies/{agency_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `agency_id` |  required  | id trong danh sach cong tac vien
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  optional  | Trạng thái Đại lý 1 (Hoạt động)  0 đã hủy
        `agency_type_id` | Id |  optional  | tầng đại lý
    
<!-- END_a303786f1b3538c2df3417c76a425e5b -->

<!-- START_1587b68116f0e3ddbd6c8418760965f1 -->
## Lấy thông số chia sẻ cho Đại lý

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/eveniet/agency_configs" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_payment_request":"eligendi","type_rose":"sunt","payment_1_of_month":"in","payment_16_of_month":"voluptas","payment_limit":"quia"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/eveniet/agency_configs"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_payment_request": "eligendi",
    "type_rose": "sunt",
    "payment_1_of_month": "in",
    "payment_16_of_month": "voluptas",
    "payment_limit": "quia"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agency_configs`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_payment_request` | cho |  optional  | phép gửi yêu cầu thanh toán
        `type_rose` | string |  optional  | doanh số, 1 hoa hồng
        `payment_1_of_month` | Quyết |  optional  | toán ngày 1 hàng tháng ko
        `payment_16_of_month` | Quyết |  optional  | toán ngày 15 hàng tháng ko
        `payment_limit` | Số |  optional  | tiền hoa hồng được quyết toán
    
<!-- END_1587b68116f0e3ddbd6c8418760965f1 -->

<!-- START_f31657e349d0bfa0f05a55f37e709f72 -->
## Cập nhật cấu hình cài đặt cho phần Đại lý

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/quidem/agency_configs" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_payment_request":"quia","payment_1_of_month":"error","payment_16_of_month":"eaque","payment_limit":"nobis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quidem/agency_configs"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_payment_request": "quia",
    "payment_1_of_month": "error",
    "payment_16_of_month": "eaque",
    "payment_limit": "nobis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/agency_configs`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_payment_request` | cho |  optional  | phép gửi yêu cầu thanh toán
        `payment_1_of_month` | Quyết |  optional  | toán ngày 1 hàng tháng ko
        `payment_16_of_month` | Quyết |  optional  | toán ngày 15 hàng tháng ko
        `payment_limit` | Số |  optional  | tiền hoa hồng được quyết toán
    
<!-- END_f31657e349d0bfa0f05a55f37e709f72 -->

<!-- START_d6038b76275123fe986928d6a5c67be4 -->
## Danh sách bậc thang thưởng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/sequi/agency_configs/bonus_steps" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/sequi/agency_configs/bonus_steps"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agency_configs/bonus_steps`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_d6038b76275123fe986928d6a5c67be4 -->

<!-- START_66e896f1caf143b1376049fcd5d24661 -->
## Thêm 1 bậc tiền thưởng 1 tháng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/magni/agency_configs/bonus_steps" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":1346396.06375,"bonus":22455079.66393}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/magni/agency_configs/bonus_steps"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 1346396.06375,
    "bonus": 22455079.66393
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/agency_configs/bonus_steps`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn được thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_66e896f1caf143b1376049fcd5d24661 -->

<!-- START_e0a0effb3c54fe85bccae01aef520f9e -->
## update một Step

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/rerum/agency_configs/bonus_steps/occaecati" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":886072.305895642,"bonus":593.84913}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/agency_configs/bonus_steps/occaecati"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 886072.305895642,
    "bonus": 593.84913
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/agency_configs/bonus_steps/{step_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `step_id` |  required  | Step_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn đc thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_e0a0effb3c54fe85bccae01aef520f9e -->

<!-- START_b0993c1a763752c65473da3733046b46 -->
## xóa một bac thang

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/facere/agency_configs/bonus_steps/illo" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/facere/agency_configs/bonus_steps/illo"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/agency_configs/bonus_steps/{step_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `step_id` |  required  | ID Step cần xóa thông tin.

<!-- END_b0993c1a763752c65473da3733046b46 -->

#User/Store


<!-- START_4415fe19a34c45a3c980043797ae0623 -->
## api/store/new_data_example
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/new_data_example" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/new_data_example"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/new_data_example`


<!-- END_4415fe19a34c45a3c980043797ae0623 -->

<!-- START_3f8761ce88b9d8916be2cc7506661dc3 -->
## Tạo store

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"commodi","store_code":"hic","address":"voluptatibus","id_type_of_store":"fugit","career":"eum","logo_url":"aut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "commodi",
    "store_code": "hic",
    "address": "voluptatibus",
    "id_type_of_store": "fugit",
    "career": "eum",
    "logo_url": "aut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên store
        `store_code` | string |  required  | Code store (Bắt đầu bằng chữ >= 2 ký tự)
        `address` | string |  required  | Địa chỉ
        `id_type_of_store` | string |  required  | Lĩnh vực
        `career` | Ngành |  optional  | nghề
        `logo_url` | string |  optional  | logo
    
<!-- END_3f8761ce88b9d8916be2cc7506661dc3 -->

<!-- START_3f14b0b8ac965f764ced266bb2e6f85e -->
## Danh sách store

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store`


<!-- END_3f14b0b8ac965f764ced266bb2e6f85e -->

<!-- START_339fefb49286065667c8867e507674aa -->
## get một Store

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/pariatur" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/pariatur"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update

<!-- END_339fefb49286065667c8867e507674aa -->

<!-- START_13c87bbb8b9fe505f6667a53f276a7a4 -->
## xóa một Store

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/doloribus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/doloribus"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.

<!-- END_13c87bbb8b9fe505f6667a53f276a7a4 -->

<!-- START_70f6a5d0b233095cbd127094ed6cab24 -->
## uppdate một Store

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/accusantium" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"unde","store_code":"dicta","address":"ullam","id_type_of_store":"alias","career":"qui","logo_url":"deleniti"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/accusantium"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "unde",
    "store_code": "dicta",
    "address": "ullam",
    "id_type_of_store": "alias",
    "career": "qui",
    "logo_url": "deleniti"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên store
        `store_code` | string |  required  | Code store (Bắt đầu bằng chữ >= 2 ký tự)
        `address` | string |  required  | Địa chỉ
        `id_type_of_store` | string |  required  | Lĩnh vực
        `career` | Ngành |  optional  | nghề
        `logo_url` | string |  optional  | logo
    
<!-- END_70f6a5d0b233095cbd127094ed6cab24 -->

#User/Sản phẩm


<!-- START_b31f649441dd08393bf0047f83d97e5c -->
## Tạo sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/iusto/1/products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"expedita","sku":"provident","description":"aliquid","video_url":"nulla","content_for_collaborator":"iusto","price":76100494.4,"import_price":18.5613,"main_cost_of_capital":1379162.7383454884,"main_stock":1,"status":18,"barcode":"autem","percent_collaborator":7.2,"list_distribute":"voluptas","list_attribute":"enim","images":"molestias","list_promotion":"nulla","categories":"commodi","category_children_ids":"praesentium","seo_title":"aut","seo_description":"quis","check_inventory":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/iusto/1/products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "expedita",
    "sku": "provident",
    "description": "aliquid",
    "video_url": "nulla",
    "content_for_collaborator": "iusto",
    "price": 76100494.4,
    "import_price": 18.5613,
    "main_cost_of_capital": 1379162.7383454884,
    "main_stock": 1,
    "status": 18,
    "barcode": "autem",
    "percent_collaborator": 7.2,
    "list_distribute": "voluptas",
    "list_attribute": "enim",
    "images": "molestias",
    "list_promotion": "nulla",
    "categories": "commodi",
    "category_children_ids": "praesentium",
    "seo_title": "aut",
    "seo_description": "quis",
    "check_inventory": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên sản phẩm
        `sku` | string |  required  | Sku
        `description` | string |  required  | Mô tả sản phẩm
        `video_url` | string |  required  | Video sản phẩm
        `content_for_collaborator` | string |  required  | Nội dung mô tả cho cộng tác viên bán
        `price` | float |  required  | Giá sản phẩm
        `import_price` | float |  required  | Giá nhập
        `main_cost_of_capital` | float |  optional  | giá vốn
        `main_stock` | integer |  optional  | tồn kho ban đầu
        `status` | integer |  optional  | Trạng thái sản phẩm (0 Hiển thị -1 Đang ẩn  1 Đã xóa)
        `barcode` | string |  optional  | Barcode sản phẩm
        `percent_collaborator` | float |  optional  | chia se cho CTV
        `list_distribute` | string |  optional  | List chi tiết [  {name:"ten", "sub_element_distribute_name": "Sub ten",element_distributes:[{name:"ten",image_url:"image",price:1000,"import_price":1,"cost_of_capital": 1, "stock": 1,barcode:"123456","price": 1, "quantity_in_stock": 2,},{name:"ten",image_url:"image",price:1000",barcode:"123456",price": 1, "quantity_in_stock": 2,"sub_element_distributes": [ {"name": "XL", "price": 3,barcode:"123456", "quantity_in_stock": 4,"cost_of_capital": 1,"import_price":1, "stock": 1, }]}]}  ] toi da 1 item
        `list_attribute` | string |  optional  | List chi tiết [  {"name": "Màu","value": "Xanh" }, { "name": "Xuất xứ", "value": "Vàng"}  ]
        `images` | string |  optional  | List chi tiết [ link1 link2 ]
        `list_promotion` | List |  optional  | [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]
        `categories` | List |  optional  | Danh sach danh muc
        `category_children_ids` | List |  optional  | Danh sach danh muc con
        `seo_title` | string |  optional  | tiêu đề cho seo
        `seo_description` | string |  optional  | Mô tả cho seo
        `check_inventory` | boolean |  optional  | Có kiểm kho hay ko (ko gửi mặc định false)
    
<!-- END_b31f649441dd08393bf0047f83d97e5c -->

<!-- START_1f53dbca91dcfa1f84b984d5d2a12cbd -->
## Cập nhật hoa hồng tất cả sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/1/collaborator_products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"percent_collaborator":543121660.205}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/collaborator_products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "percent_collaborator": 543121660.205
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/collaborator_products`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `percent_collaborator` | float |  optional  | phần trăm hoa hồng sản phẩm
    
<!-- END_1f53dbca91dcfa1f84b984d5d2a12cbd -->

<!-- START_ef45d9665383047ae95c4f861307bdcf -->
## Thêm nhiều sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/1/1/products/all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_skip_same_name":true,"list":"odio","item":"consequatur","category_id":"harum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/products/all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_skip_same_name": true,
    "list": "odio",
    "item": "consequatur",
    "category_id": "harum"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/products/all`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_skip_same_name` | boolean |  required  | Có bỏ qua sản phẩm trùng tên không (Không bỏ qua sẽ replace sản phẩm trùng tên)
        `list` | List |  required  | List danh sách sản phẩm  (item json như thêm 1 product)
        `item` | product |  optional  | thêm {category_name}
        `category_id` | danh |  optional  | mục cần thêm vào
    
<!-- END_ef45d9665383047ae95c4f861307bdcf -->

<!-- START_1d7cc2266fdcf7c32ad26d14f04d23a4 -->
## Danh sách sản phẩm

status": 0 hiển thị - số còn lại là ẩn
has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)
has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)
total_stoking còn hàng
total_out_of_stock' hết hàng
total_hide' ẩn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/odio/1/products?page=7&search=nihil&sort_by=non&descending=porro&category_ids=saepe&category_children_ids=animi&details=maxime&status=id&filter_by=molestiae&filter_option=ut&filter_by_value=molestiae&is_get_all=perspiciatis&limit=doloribus&agency_type_id=cupiditate&is_show_description=velit&is_near_out_of_stock=veniam&check_inventory=ratione" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/odio/1/products"
);

let params = {
    "page": "7",
    "search": "nihil",
    "sort_by": "non",
    "descending": "porro",
    "category_ids": "saepe",
    "category_children_ids": "animi",
    "details": "maxime",
    "status": "id",
    "filter_by": "molestiae",
    "filter_option": "ut",
    "filter_by_value": "molestiae",
    "is_get_all": "perspiciatis",
    "limit": "doloribus",
    "agency_type_id": "cupiditate",
    "is_show_description": "velit",
    "is_near_out_of_stock": "veniam",
    "check_inventory": "ratione",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price,views, sales
    `descending` |  optional  | Giảm dần không VD: false
    `category_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `category_children_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `details` |  optional  | Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
    `status` |  optional  | (0 -1) còn hàng hay không! không truyền lấy cả 2
    `filter_by` |  optional  | Chọn trường nào để lấy
    `filter_option` |  optional  | Kiểu filter ( > = <)
    `filter_by_value` |  optional  | Giá trị trường đó
    `is_get_all` |  optional  | boolean Lấy tất cá hay không
    `limit` |  optional  | int Số item 1 trangơ
    `agency_type_id` |  optional  | int id Kiểu đại lý
    `is_show_description` |  optional  | bool Cho phép trả về mô tả
    `is_near_out_of_stock` |  optional  | boolean Gần hết kho
    `check_inventory` |  optional  | boolean lấy danh sách theo trạng thái

<!-- END_1d7cc2266fdcf7c32ad26d14f04d23a4 -->

<!-- START_9f42813a043621973cfd21e1cc3f20f7 -->
## Cập nhật 1 sản phẩm

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store_v2/qui/1/products/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"ipsum","sku":"minus","description":"rerum","content_for_collaborator":"totam","price":18,"import_price":37637.617,"status":4,"barcode":"quidem","list_distribute":"nobis","images":"aliquam","percent_collaborator":273180.81939587,"list_promotion":"cum","categories":"velit","category_children_ids":"exercitationem","seo_title":"debitis","seo_description":"repellat","check_inventory":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/qui/1/products/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "ipsum",
    "sku": "minus",
    "description": "rerum",
    "content_for_collaborator": "totam",
    "price": 18,
    "import_price": 37637.617,
    "status": 4,
    "barcode": "quidem",
    "list_distribute": "nobis",
    "images": "aliquam",
    "percent_collaborator": 273180.81939587,
    "list_promotion": "cum",
    "categories": "velit",
    "category_children_ids": "exercitationem",
    "seo_title": "debitis",
    "seo_description": "repellat",
    "check_inventory": true
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store_v2/{store_code}/{branch_id}/products/{product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên sản phẩm
        `sku` | string |  required  | Sku
        `description` | string |  required  | Mô tả sản phẩm
        `content_for_collaborator` | string |  required  | Nội dung mô tả cho cộng tác viên bán
        `price` | integer |  required  | Giá sản phẩm
        `import_price` | float |  required  | Giá nhập
        `status` | integer |  optional  | Trạng thái sản phẩm (0 Hiển thị -1 Đang ẩn)
        `barcode` | string |  optional  | Barcode sản phẩm
        `list_distribute` | string |  optional  | List chi tiết [  {name:"ten", "sub_element_distribute_name": "Sub ten",element_distributes:[{name:"ten",image_url:"image",price:1000,"price": 1, "quantity_in_stock": 2,},{name:"ten",image_url:"image",price:1000"price": 1, "quantity_in_stock": 2,"sub_element_distributes": [ {"name": "XL", "price": 3, "quantity_in_stock": 4 }]}]}  ] toi da 1 item
        `images` | string |  optional  | List chi tiết [ link1 link2 ]
        `percent_collaborator` | float |  optional  | chia se cho CTV
        `list_promotion` | List |  optional  | [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]
        `categories` | List |  optional  | Danh sach danh muc
        `category_children_ids` | List |  optional  | Danh sach danh muc con
        `seo_title` | string |  optional  | tiêu đề cho seo
        `seo_description` | string |  optional  | Mô tả cho seo
        `check_inventory` | boolean |  optional  | Có kiểm kho hay ko (ko gửi mặc định false)
    
<!-- END_9f42813a043621973cfd21e1cc3f20f7 -->

<!-- START_33ec3af77f9499f761d6d58483b3d863 -->
## Thông tin một sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/et/1/products/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/et/1/products/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/products/{product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
    `id` |  required  | ID product cần lấy thông tin.

<!-- END_33ec3af77f9499f761d6d58483b3d863 -->

<!-- START_2c4f79ec0706d29b9cdff66515577b6d -->
## Tạo sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/laborum/products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"ut","sku":"est","description":"debitis","video_url":"blanditiis","content_for_collaborator":"voluptas","price":81,"import_price":2065.43163788,"main_cost_of_capital":83.95,"main_stock":10,"status":19,"barcode":"est","percent_collaborator":32996.91220478,"list_distribute":"praesentium","list_attribute":"blanditiis","images":"itaque","list_promotion":"dignissimos","categories":"labore","category_children_ids":"rerum","seo_title":"nostrum","seo_description":"itaque","check_inventory":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/laborum/products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "ut",
    "sku": "est",
    "description": "debitis",
    "video_url": "blanditiis",
    "content_for_collaborator": "voluptas",
    "price": 81,
    "import_price": 2065.43163788,
    "main_cost_of_capital": 83.95,
    "main_stock": 10,
    "status": 19,
    "barcode": "est",
    "percent_collaborator": 32996.91220478,
    "list_distribute": "praesentium",
    "list_attribute": "blanditiis",
    "images": "itaque",
    "list_promotion": "dignissimos",
    "categories": "labore",
    "category_children_ids": "rerum",
    "seo_title": "nostrum",
    "seo_description": "itaque",
    "check_inventory": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên sản phẩm
        `sku` | string |  required  | Sku
        `description` | string |  required  | Mô tả sản phẩm
        `video_url` | string |  required  | Video sản phẩm
        `content_for_collaborator` | string |  required  | Nội dung mô tả cho cộng tác viên bán
        `price` | float |  required  | Giá sản phẩm
        `import_price` | float |  required  | Giá nhập
        `main_cost_of_capital` | float |  optional  | giá vốn
        `main_stock` | integer |  optional  | tồn kho ban đầu
        `status` | integer |  optional  | Trạng thái sản phẩm (0 Hiển thị -1 Đang ẩn  1 Đã xóa)
        `barcode` | string |  optional  | Barcode sản phẩm
        `percent_collaborator` | float |  optional  | chia se cho CTV
        `list_distribute` | string |  optional  | List chi tiết [  {name:"ten", "sub_element_distribute_name": "Sub ten",element_distributes:[{name:"ten",image_url:"image",price:1000,"import_price":1,"cost_of_capital": 1, "stock": 1,barcode:"123456","price": 1, "quantity_in_stock": 2,},{name:"ten",image_url:"image",price:1000",barcode:"123456",price": 1, "quantity_in_stock": 2,"sub_element_distributes": [ {"name": "XL", "price": 3,barcode:"123456", "quantity_in_stock": 4,"cost_of_capital": 1,"import_price":1, "stock": 1, }]}]}  ] toi da 1 item
        `list_attribute` | string |  optional  | List chi tiết [  {"name": "Màu","value": "Xanh" }, { "name": "Xuất xứ", "value": "Vàng"}  ]
        `images` | string |  optional  | List chi tiết [ link1 link2 ]
        `list_promotion` | List |  optional  | [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]
        `categories` | List |  optional  | Danh sach danh muc
        `category_children_ids` | List |  optional  | Danh sach danh muc con
        `seo_title` | string |  optional  | tiêu đề cho seo
        `seo_description` | string |  optional  | Mô tả cho seo
        `check_inventory` | boolean |  optional  | Có kiểm kho hay ko (ko gửi mặc định false)
    
<!-- END_2c4f79ec0706d29b9cdff66515577b6d -->

<!-- START_8b1fff5b35028c5d1e7c9db889768475 -->
## Thêm nhiều sản phẩm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/products/all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"allow_skip_same_name":false,"list":"corrupti","item":"accusantium","category_id":"fuga"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/products/all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_skip_same_name": false,
    "list": "corrupti",
    "item": "accusantium",
    "category_id": "fuga"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/products/all`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `allow_skip_same_name` | boolean |  required  | Có bỏ qua sản phẩm trùng tên không (Không bỏ qua sẽ replace sản phẩm trùng tên)
        `list` | List |  required  | List danh sách sản phẩm  (item json như thêm 1 product)
        `item` | product |  optional  | thêm {category_name}
        `category_id` | danh |  optional  | mục cần thêm vào
    
<!-- END_8b1fff5b35028c5d1e7c9db889768475 -->

<!-- START_4b5b7b5cca87789749c3b06adfb1a492 -->
## Danh sách sản phẩm

status": 0 hiển thị - số còn lại là ẩn
has_in_discount: boolean (sp có chuẩn bị và đang diễn ra trong discount)
has_in_combo: boolean (sp có chuẩn bị và đang diễn ra trong combo)
total_stoking còn hàng
total_out_of_stock' hết hàng
total_hide' ẩn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/temporibus/products?page=19&search=sed&sort_by=consectetur&descending=dolor&category_ids=laboriosam&category_children_ids=sequi&details=ullam&status=pariatur&filter_by=alias&filter_option=voluptas&filter_by_value=eligendi&is_get_all=placeat&limit=nemo&agency_type_id=molestiae&is_show_description=ducimus&is_near_out_of_stock=expedita&check_inventory=nihil" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/temporibus/products"
);

let params = {
    "page": "19",
    "search": "sed",
    "sort_by": "consectetur",
    "descending": "dolor",
    "category_ids": "laboriosam",
    "category_children_ids": "sequi",
    "details": "ullam",
    "status": "pariatur",
    "filter_by": "alias",
    "filter_option": "voluptas",
    "filter_by_value": "eligendi",
    "is_get_all": "placeat",
    "limit": "nemo",
    "agency_type_id": "molestiae",
    "is_show_description": "ducimus",
    "is_near_out_of_stock": "expedita",
    "check_inventory": "nihil",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: samsung
    `sort_by` |  optional  | Sắp xếp theo VD: price,views, sales
    `descending` |  optional  | Giảm dần không VD: false
    `category_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `category_children_ids` |  optional  | Thuộc category id nào VD: 1,2,3
    `details` |  optional  | Filter theo thuộc tính VD: Màu:Đỏ|Size:XL
    `status` |  optional  | (0 -1) còn hàng hay không! không truyền lấy cả 2
    `filter_by` |  optional  | Chọn trường nào để lấy
    `filter_option` |  optional  | Kiểu filter ( > = <)
    `filter_by_value` |  optional  | Giá trị trường đó
    `is_get_all` |  optional  | boolean Lấy tất cá hay không
    `limit` |  optional  | int Số item 1 trangơ
    `agency_type_id` |  optional  | int id Kiểu đại lý
    `is_show_description` |  optional  | bool Cho phép trả về mô tả
    `is_near_out_of_stock` |  optional  | boolean Gần hết kho
    `check_inventory` |  optional  | boolean lấy danh sách theo trạng thái

<!-- END_4b5b7b5cca87789749c3b06adfb1a492 -->

<!-- START_183545e1df730bd6bd52908a42ab05c1 -->
## Thông tin một sản phẩm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/error/products/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/error/products/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/products/{product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
    `id` |  required  | ID product cần lấy thông tin.

<!-- END_183545e1df730bd6bd52908a42ab05c1 -->

<!-- START_1700685a9e41eaeb1792ebf300982f2c -->
## xóa một sản phẩm

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/velit/products/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/velit/products/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/products/{product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID product cần xóa thông tin.

<!-- END_1700685a9e41eaeb1792ebf300982f2c -->

<!-- START_ef53094bf0e58d5b69de043c9fd94853 -->
## xóa nhiều sản phẩm

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/provident/products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"list_id":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/provident/products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "list_id": "et"
}

fetch(url, {
    method: "DELETE",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/products`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `list_id` | danh |  optional  | sách id cần xóa
    
<!-- END_ef53094bf0e58d5b69de043c9fd94853 -->

<!-- START_07db1a5f0eac5baa81ce28470a572ae3 -->
## Cập nhật 1 sản phẩm

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/asperiores/products/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"quos","sku":"nostrum","description":"nemo","content_for_collaborator":"architecto","price":8,"import_price":1.0819685,"status":16,"barcode":"a","list_distribute":"autem","images":"beatae","percent_collaborator":6628.18486,"list_promotion":"cupiditate","categories":"consequuntur","category_children_ids":"exercitationem","seo_title":"et","seo_description":"eum","check_inventory":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/asperiores/products/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "quos",
    "sku": "nostrum",
    "description": "nemo",
    "content_for_collaborator": "architecto",
    "price": 8,
    "import_price": 1.0819685,
    "status": 16,
    "barcode": "a",
    "list_distribute": "autem",
    "images": "beatae",
    "percent_collaborator": 6628.18486,
    "list_promotion": "cupiditate",
    "categories": "consequuntur",
    "category_children_ids": "exercitationem",
    "seo_title": "et",
    "seo_description": "eum",
    "check_inventory": true
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/products/{product_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên sản phẩm
        `sku` | string |  required  | Sku
        `description` | string |  required  | Mô tả sản phẩm
        `content_for_collaborator` | string |  required  | Nội dung mô tả cho cộng tác viên bán
        `price` | integer |  required  | Giá sản phẩm
        `import_price` | float |  required  | Giá nhập
        `status` | integer |  optional  | Trạng thái sản phẩm (0 Hiển thị -1 Đang ẩn)
        `barcode` | string |  optional  | Barcode sản phẩm
        `list_distribute` | string |  optional  | List chi tiết [  {name:"ten", "sub_element_distribute_name": "Sub ten",element_distributes:[{name:"ten",image_url:"image",price:1000,"price": 1, "quantity_in_stock": 2,},{name:"ten",image_url:"image",price:1000"price": 1, "quantity_in_stock": 2,"sub_element_distributes": [ {"name": "XL", "price": 3, "quantity_in_stock": 4 }]}]}  ] toi da 1 item
        `images` | string |  optional  | List chi tiết [ link1 link2 ]
        `percent_collaborator` | float |  optional  | chia se cho CTV
        `list_promotion` | List |  optional  | [{content:"Noi dung khuyen mai","post_id":1, "post_name":"ten bai viet"  }]
        `categories` | List |  optional  | Danh sach danh muc
        `category_children_ids` | List |  optional  | Danh sach danh muc con
        `seo_title` | string |  optional  | tiêu đề cho seo
        `seo_description` | string |  optional  | Mô tả cho seo
        `check_inventory` | boolean |  optional  | Có kiểm kho hay ko (ko gửi mặc định false)
    
<!-- END_07db1a5f0eac5baa81ce28470a572ae3 -->

#User/Thuộc tính sản phẩm


<!-- START_2a75361c98eca6a57641c3fcbbe7703c -->
## Xem tất cả thuộc tính

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/eum/attribute_fields?with_product_id=non&no_attribute_default=aperiam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/eum/attribute_fields"
);

let params = {
    "with_product_id": "non",
    "no_attribute_default": "aperiam",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/attribute_fields`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `with_product_id` |  optional  | Với attribute của product
    `no_attribute_default` |  optional  | không cần thiết đặt

<!-- END_2a75361c98eca6a57641c3fcbbe7703c -->

#User/Thông báo


<!-- START_88d1e9d9b32ed2d1568f1d8e3b748963 -->
## Danh sách thông báo

total_unread số chưa đọc

page số trang

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/notifications_history" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/notifications_history"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/notifications_history`


<!-- END_88d1e9d9b32ed2d1568f1d8e3b748963 -->

<!-- START_06745b1c2ba54b7167c0ce57c93a18d6 -->
## Đã đọc tất cả

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/notifications_history/read_all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/notifications_history/read_all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/notifications_history/read_all`


<!-- END_06745b1c2ba54b7167c0ce57c93a18d6 -->

<!-- START_9eb1d50f6a04d46f3f4e5438896e5264 -->
## Danh sách thông báo

total_unread số chưa đọc

page số trang

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/notifications_history" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications_history"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/notifications_history`


<!-- END_9eb1d50f6a04d46f3f4e5438896e5264 -->

<!-- START_dd3705bd972b5da65587be9c38a5d939 -->
## Đã đọc tất cả

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/notifications_history/read_all" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/notifications_history/read_all"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/notifications_history/read_all`


<!-- END_dd3705bd972b5da65587be9c38a5d939 -->

#User/Thưởng đại lý


<!-- START_e7a340c579d603590bed396a503479b7 -->
## Lấy cấu hình thưởng cho đại lý

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/dolorem/bonus_agency_config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/dolorem/bonus_agency_config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/bonus_agency_config`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_e7a340c579d603590bed396a503479b7 -->

<!-- START_78cf9c0124dcd5f95d93dfac7ab1568f -->
## xóa một bac thang

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/est/bonus_agency_config/bonus_steps/nam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/est/bonus_agency_config/bonus_steps/nam"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/bonus_agency_config/bonus_steps/{step_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `step_id` |  required  | ID Step cần xóa thông tin.

<!-- END_78cf9c0124dcd5f95d93dfac7ab1568f -->

<!-- START_7880479d2fb1688276c4ab367df23b5f -->
## Thêm 1 bậc tiền thưởng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/et/bonus_agency_config/bonus_steps" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"threshold":2065050.08471,"reward_name":159411737.10735,"reward_description":5.82,"reward_image_url":8611373,"reward_value":136199.117891,"limit":46781863}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/et/bonus_agency_config/bonus_steps"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "threshold": 2065050.08471,
    "reward_name": 159411737.10735,
    "reward_description": 5.82,
    "reward_image_url": 8611373,
    "reward_value": 136199.117891,
    "limit": 46781863
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/bonus_agency_config/bonus_steps`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `threshold` | float |  required  | Giới hạn được thưởng
        `reward_name` | float |  required  | Tên giải thưởng
        `reward_description` | float |  required  | Mô tả thưởng
        `reward_image_url` | float |  required  | Link ảnh thưởng
        `reward_value` | float |  required  | Giá trị thưởng
        `limit` | float |  required  | Giới hạn
    
<!-- END_7880479d2fb1688276c4ab367df23b5f -->

<!-- START_0661e1fbcfa772fb25c909aee7340905 -->
## update một Step

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/eveniet/bonus_agency_config/bonus_steps/autem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"threshold":0,"reward_name":150.57,"reward_description":334970.864,"reward_image_url":0.89,"reward_value":92946.598695316,"limit":20593.62067}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/eveniet/bonus_agency_config/bonus_steps/autem"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "threshold": 0,
    "reward_name": 150.57,
    "reward_description": 334970.864,
    "reward_image_url": 0.89,
    "reward_value": 92946.598695316,
    "limit": 20593.62067
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/bonus_agency_config/bonus_steps/{step_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `step_id` |  required  | Step_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `threshold` | float |  required  | Giới hạn được thưởng
        `reward_name` | float |  required  | Tên giải thưởng
        `reward_description` | float |  required  | Mô tả thưởng
        `reward_image_url` | float |  required  | Link ảnh thưởng
        `reward_value` | float |  required  | Giá trị thưởng
        `limit` | float |  required  | Giới hạn
    
<!-- END_0661e1fbcfa772fb25c909aee7340905 -->

<!-- START_c70cc94e8458c4e796fa027c8a2076eb -->
## Cấu hình thưởng cho đại lý

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/repellendus/bonus_agency_config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_end":"et","start_time":"et","end_time":"laborum"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/repellendus/bonus_agency_config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_end": "et",
    "start_time": "et",
    "end_time": "laborum"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/bonus_agency_config`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_end` | required |  optional  | is_end
        `start_time` | required |  optional  | start_time
        `end_time` | required |  optional  | end_time
    
<!-- END_c70cc94e8458c4e796fa027c8a2076eb -->

#User/Tính công làm việc


<!-- START_4118718aceec947b871881e21b4e0039 -->
## Tính số giờ làm việc

Nếu ngày kết thúc - ngày bắt đầu lớn hơn 1 thì  sẽ không có keeping_histories (danh sách checkin checkout)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/1/1/timekeeping/calculate?date_from=ut&date_to=autem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/timekeeping/calculate"
);

let params = {
    "date_from": "ut",
    "date_to": "autem",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/timekeeping/calculate`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `date_from` |  optional  | string datetime Ngày bắt đầu
    `date_to` |  optional  | string datetime Ngày kết thúc

<!-- END_4118718aceec947b871881e21b4e0039 -->

#User/Vip user


<!-- START_ca07a2a8ef59b12e7554138364c41ea0 -->
## Cập nhật cấu hình user vip

> Example request:

```bash
curl -X POST \
    "http://localhost/api/vip_user/config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"trader_mark_name":"dolores","url_logo_image":"et","url_logo_small_image":"eum","url_login_image":"excepturi","user_copyright":"et","customer_copyright":"blanditiis","url_customer_copyright":"numquam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/vip_user/config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "trader_mark_name": "dolores",
    "url_logo_image": "et",
    "url_logo_small_image": "eum",
    "url_login_image": "excepturi",
    "user_copyright": "et",
    "customer_copyright": "blanditiis",
    "url_customer_copyright": "numquam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/vip_user/config`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `user_id` |  optional  | int user id
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `trader_mark_name` | string |  optional  | Tên nhãn hiệu
        `url_logo_image` | string |  optional  | Url logo
        `url_logo_small_image` | string |  optional  | url logo nhỏ khi thu nhỏ thanh công cụ
        `url_login_image` | string |  optional  | user login image
        `user_copyright` | string |  optional  | thương hiệu dưới trang user quản lý
        `customer_copyright` | string |  optional  | thương hiệu dưới trang customer
        `url_customer_copyright` | string |  optional  | đường link trỏ đi của thương hiệu customer
    
<!-- END_ca07a2a8ef59b12e7554138364c41ea0 -->

<!-- START_9314bfca8a66e64c39b365a412fb20d9 -->
## Lấy cấu hình hình

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/vip_user/config" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/vip_user/config"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/vip_user/config`


<!-- END_9314bfca8a66e64c39b365a412fb20d9 -->

#User/Voucher

discount_type // 0 gia co dinh - 1 theo phan tram
voucher_type //0 All - 1 Mot So sp
set_limit_total khi set giá trị true - yêu cầu khách hàng phải mua đủ sản phẩm
thuộc voucher_type 1
<!-- START_88af57bf547e915bd668a6f773741c86 -->
## Tạo voucher mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/provident/vouchers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"ipsa","is_show_voucher":false,"description":"aut","image_url":"quo","start_time":"tempore","end_time":"quia","discount_for":2,"is_free_ship":true,"ship_discount_value":5515.07,"voucher_type":14,"discount_type":17,"value_discount":450.28,"set_limit_value_discount":false,"max_value_discount":39972,"set_limit_total":false,"value_limit_total":8359.53785,"value":16.3448968,"set_limit_amount":true,"amount":16,"product_ids":"nihil","group_type_id":19,"group_type_name":17,"agency_type_id":9,"agency_type_name":"nesciunt"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/provident/vouchers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "ipsa",
    "is_show_voucher": false,
    "description": "aut",
    "image_url": "quo",
    "start_time": "tempore",
    "end_time": "quia",
    "discount_for": 2,
    "is_free_ship": true,
    "ship_discount_value": 5515.07,
    "voucher_type": 14,
    "discount_type": 17,
    "value_discount": 450.28,
    "set_limit_value_discount": false,
    "max_value_discount": 39972,
    "set_limit_total": false,
    "value_limit_total": 8359.53785,
    "value": 16.3448968,
    "set_limit_amount": true,
    "amount": 16,
    "product_ids": "nihil",
    "group_type_id": 19,
    "group_type_name": 17,
    "agency_type_id": 9,
    "agency_type_name": "nesciunt"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/vouchers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | Tên chương trình
        `is_show_voucher` | boolean |  required  | Có hiển thị cho khách hàng thấy không
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
        `discount_for` | integer |  required  | 0 trừ vào đơn hàng,1 trừ phí ship
        `is_free_ship` | boolean |  required  | dành cho discount_for == 1
        `ship_discount_value` | float |  required  | dành cho discount_for == 1
        `voucher_type` | integer |  required  | 0 áp dụng tất cả sp - 1 cho một số sản phẩm
        `discount_type` | integer |  required  | (voucher_type == 1) 0 giám giá cố định - 1 theo %
        `value_discount` | float |  required  | (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
        `set_limit_value_discount` | boolean |  required  | (value_discount ==1) set giá trị giảm tối đa hay không
        `max_value_discount` | float |  required  | giá trị giảm tối đa
        `set_limit_total` | boolean |  required  | Có tối thiểu hóa đơn hay không
        `value_limit_total` | float |  required  | Giá trị tối thiểu của hóa đơn
        `value` | float |  required  | Giá trị % giảm giá 1 - 99
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `product_ids` | List&lt;int&gt; |  required  | danh sách id sản phẩm kèm số lượng 1,2,...
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_88af57bf547e915bd668a6f773741c86 -->

<!-- START_6cdf5135b0372964768092cad7a5deb4 -->
## Update voucher
Muốn kết thúc chương trình chỉ cần truyền is_end = false (Còn lại truyền đầy đủ)

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/excepturi/vouchers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"is_end":false,"is_show_voucher":true,"name":"odio","description":"exercitationem","image_url":"nihil","start_time":"animi","end_time":"quam","voucher_type":8,"discount_type":16,"value_discount":19708.78388528,"set_limit_value_discount":true,"max_value_discount":4691410.4945655,"set_limit_total":false,"value_limit_total":62.30721132,"value":8,"set_limit_amount":true,"amount":8,"product_ids":"ut","group_type_id":14,"group_type_name":16,"agency_type_id":4,"agency_type_name":"id"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/excepturi/vouchers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "is_end": false,
    "is_show_voucher": true,
    "name": "odio",
    "description": "exercitationem",
    "image_url": "nihil",
    "start_time": "animi",
    "end_time": "quam",
    "voucher_type": 8,
    "discount_type": 16,
    "value_discount": 19708.78388528,
    "set_limit_value_discount": true,
    "max_value_discount": 4691410.4945655,
    "set_limit_total": false,
    "value_limit_total": 62.30721132,
    "value": 8,
    "set_limit_amount": true,
    "amount": 8,
    "product_ids": "ut",
    "group_type_id": 14,
    "group_type_name": 16,
    "agency_type_id": 4,
    "agency_type_name": "id"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/vouchers/{voucher_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `Voucher_id` |  required  | Id Voucher
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `is_end` | boolean |  required  | Chương trình đã kết thúc chưa
        `is_show_voucher` | boolean |  required  | Có hiển thị cho khách hàng thấy không
        `name` | string |  required  | Tên chương trình
        `description` | string |  required  | Mô tả chương trình
        `image_url` | string |  required  | Link ảnh chương trình
        `start_time` | datetime |  required  | Thời gian bắt đầu
        `end_time` | datetime |  required  | thông tin người liên hệ (name,address_detail,country,province,district,wards,village,postcode,email,phone,type)
        `voucher_type` | integer |  required  | 0 áp dụng tất cả sp - 1 cho một số sản phẩm
        `discount_type` | integer |  required  | (voucher_type == 1) 0 giám giá cố định - 1 theo %
        `value_discount` | float |  required  | (value_discount==0) số tiền | value_discount ==1 phần trăm (0-100)
        `set_limit_value_discount` | boolean |  required  | (value_discount ==1) set giá trị giảm tối đa hay không
        `max_value_discount` | float |  required  | giá trị giảm tối đa
        `set_limit_total` | boolean |  required  | Có tối thiểu hóa đơn hay không
        `value_limit_total` | float |  required  | Giá trị tối thiểu của hóa đơn
        `value` | float |  required  | Giá trị % giảm giá 1 - 99
        `set_limit_amount` | boolean |  required  | Set giới hạn khuyến mãi
        `amount` | integer |  required  | Giới hạn số lần khuyến mãi có thể sử dụng
        `product_ids` | List&lt;int&gt; |  required  | danh sách id sản phẩm kèm số lượng 1,2,...
        `group_type_id` | integer |  required  | id của group cần xử lý
        `group_type_name` | integer |  required  | name của group cần xử lý
        `agency_type_id` | integer |  required  | id tầng đại lý trường hợp group là 2
        `agency_type_name` | Tên |  required  | name cấp đại lý VD:Cấp 1
    
<!-- END_6cdf5135b0372964768092cad7a5deb4 -->

<!-- START_8735ed56c6388505602cdf06fee92555 -->
## Xem 1 voucher

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/minima/vouchers/ut" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/minima/vouchers/ut"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/vouchers/{voucher_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `voucher_id` |  required  | Id Voucher

<!-- END_8735ed56c6388505602cdf06fee92555 -->

<!-- START_8752127ab436a4f5422548fd24475d3a -->
## Xem tất cả voucher chuẩn vị và đang phát hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/veniam/vouchers" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/veniam/vouchers"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/vouchers`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_8752127ab436a4f5422548fd24475d3a -->

<!-- START_98e59086ae5c6e9de864ef0081175c6e -->
## Xem tất cả voucher đã kết thúc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/assumenda/vouchers_end?page=4" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/assumenda/vouchers_end"
);

let params = {
    "page": "4",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/vouchers_end`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách item mỗi trang {page} (Mỗi trang có 20 item)

<!-- END_98e59086ae5c6e9de864ef0081175c6e -->

<!-- START_ed878146df1c4ed17e5efb50481e102c -->
## xóa một chương trình voucher

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/atque/vouchers/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/atque/vouchers/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/vouchers/{voucher_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID voucher cần xóa thông tin.

<!-- END_ed878146df1c4ed17e5efb50481e102c -->

#User/Vị trí làm việc


APIs AppTheme
<!-- START_bd2b232007f9d4d00ade509b4675fe2d -->
## Thêm vị trí làm việc

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/dolore/1/checkin_location" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"repudiandae","wifi_name":"sunt","wifi_mac":"dolorem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/dolore/1/checkin_location"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "repudiandae",
    "wifi_name": "sunt",
    "wifi_mac": "dolorem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/checkin_location`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | name
        `wifi_name` | string |  optional  | wifi_name
        `wifi_mac` | string |  optional  | wifi_mac
    
<!-- END_bd2b232007f9d4d00ade509b4675fe2d -->

<!-- START_2188a05b0992906d3aa3211b18b9cf24 -->
## update một CheckinLocation

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store_v2/qui/1/checkin_location/et" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"aut","wifi_name":"aut","wifi_mac":"est"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/qui/1/checkin_location/et"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "aut",
    "wifi_name": "aut",
    "wifi_mac": "est"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store_v2/{store_code}/{branch_id}/checkin_location/{checkin_location_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `checkin_location_id` |  required  | ID checkinLocation cần xóa thông tin.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | name
        `wifi_name` | string |  optional  | wifi_name
        `wifi_mac` | string |  optional  | wifi_mac
    
<!-- END_2188a05b0992906d3aa3211b18b9cf24 -->

<!-- START_82cb7198da8830cd2394ba07b8705af3 -->
## xóa một vị trí

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store_v2/laboriosam/1/checkin_location/beatae" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/laboriosam/1/checkin_location/beatae"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store_v2/{store_code}/{branch_id}/checkin_location/{checkin_location_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `checkin_location_id` |  required  | ID checkinLocation cần xóa thông tin.

<!-- END_82cb7198da8830cd2394ba07b8705af3 -->

<!-- START_ac0bcff0a81c73e1474caea753240fd8 -->
## Danh sách Vị trí làm việc

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/vitae/1/checkin_location" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/vitae/1/checkin_location"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/checkin_location`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_ac0bcff0a81c73e1474caea753240fd8 -->

#User/Webhook ship


Webhook
<!-- START_48df563951cf0cd38a6789cacc159142 -->
## Nhận dữ liệu từ giao vận

> Example request:

```bash
curl -X POST \
    "http://localhost/api/webhook/ship" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":546036.991006424,"bonus":2.06}'

```

```javascript
const url = new URL(
    "http://localhost/api/webhook/ship"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 546036.991006424,
    "bonus": 2.06
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/webhook/ship`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn được thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_48df563951cf0cd38a6789cacc159142 -->

<!-- START_bcab02addc133522072257d3cf1c3f62 -->
## Nhận dữ liệu từ giao vận

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/webhook/ship" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":51.28387,"bonus":497.029}'

```

```javascript
const url = new URL(
    "http://localhost/api/webhook/ship"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 51.28387,
    "bonus": 497.029
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/webhook/ship`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn được thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_bcab02addc133522072257d3cf1c3f62 -->

<!-- START_de30db8b34b93265bd54f82e2f9be74e -->
## Nhận dữ liệu từ giao vận

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/webhook/ship" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"limit":3.9,"bonus":0.550417}'

```

```javascript
const url = new URL(
    "http://localhost/api/webhook/ship"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "limit": 3.9,
    "bonus": 0.550417
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/webhook/ship`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `limit` | float |  required  | Giới hạn được thưởng
        `bonus` | float |  required  | Số tiền thưởng
    
<!-- END_de30db8b34b93265bd54f82e2f9be74e -->

#User/Web theme


<!-- START_cb50ae79f2cf8a665970ab2dc60a33be -->
## Cập nhật WebTheme

Gửi một trong các trường lên để cập nhật

> Example request:

```bash
curl -X POST \
    "http://localhost/api/web-theme/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"logo_url":"dolorem","favicon_url":"minus","image_share_web_url":"id","home_title":"ad","home_description":"cupiditate","domain":"culpa","is_show_logo":"consequuntur","color_main_1":"velit","color_main_2":"quam","font_color_all_page":"fuga","font_color_title":"dolore","font_color_main":"dignissimos","font_family":"delectus","icon_hotline":"nulla","is_show_icon_hotline":"culpa","note_icon_hotline":"est","phone_number_hotline":"blanditiis","icon_email":"qui","is_show_icon_email":"sed","title_popup_icon_email":"molestiae","title_popup_success_icon_email":"praesentium","email_contact":"omnis","body_email_success_icon_email":"id","icon_facebook":"adipisci","is_show_icon_facebook":"error","note_icon_facebook":"error","id_facebook":"amet","icon_zalo":"nemo","is_show_icon_zalo":"dolorum","note_icon_zalo":"suscipit","id_zalo":"et","is_scroll_button":"architecto","type_button":"sit","header_type":"ipsum","color_background_header":"vel","color_text_header":"aut","type_navigator":"ut","type_loading":"asperiores","type_of_menu":"quia","product_item_type":"nihil","search_background_header":"quaerat","search_text_header":"nihil","carousel_type":"sapiente","home_id_carousel_app_image":"consequatur","home_list_category_is_show":"voluptatibus","home_id_list_category_app_image":"occaecati","home_top_is_show":"eius","home_top_text":"consequatur","home_top_color":"et","home_carousel_is_show":"quaerat","home_page_type":"ipsa","category_page_type":"sint","product_page_type":"unde","is_show_same_product":"doloremque","is_show_list_post_contact":"voluptatum","post_id_help":"voluptas","post_id_contact":"sed","post_id_about":"ipsum","post_id_terms":"recusandae","post_id_return_policy":"aspernatur","post_id_support_policy":"ratione","post_id_privacy_policy":"voluptas","contact_page_type":"doloribus","contact_google_map":"vero","contact_address":"optio","contact_email":"maxime","contact_phone_number":"sed","contact_time_work":"aut","contact_info_bank":"iure","contact_individual_organization_name":"molestiae","contact_short_description":"et","contact_business_registration_certificate":"pariatur","contact_fanpage":"et","html_footer":"vel","banner_type":14,"product_home_type":"qui","post_home_type":"maxime","footer_type":"earum","is_use_footer_html":"vel","carousel_app_images":"laudantium"}'

```

```javascript
const url = new URL(
    "http://localhost/api/web-theme/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "logo_url": "dolorem",
    "favicon_url": "minus",
    "image_share_web_url": "id",
    "home_title": "ad",
    "home_description": "cupiditate",
    "domain": "culpa",
    "is_show_logo": "consequuntur",
    "color_main_1": "velit",
    "color_main_2": "quam",
    "font_color_all_page": "fuga",
    "font_color_title": "dolore",
    "font_color_main": "dignissimos",
    "font_family": "delectus",
    "icon_hotline": "nulla",
    "is_show_icon_hotline": "culpa",
    "note_icon_hotline": "est",
    "phone_number_hotline": "blanditiis",
    "icon_email": "qui",
    "is_show_icon_email": "sed",
    "title_popup_icon_email": "molestiae",
    "title_popup_success_icon_email": "praesentium",
    "email_contact": "omnis",
    "body_email_success_icon_email": "id",
    "icon_facebook": "adipisci",
    "is_show_icon_facebook": "error",
    "note_icon_facebook": "error",
    "id_facebook": "amet",
    "icon_zalo": "nemo",
    "is_show_icon_zalo": "dolorum",
    "note_icon_zalo": "suscipit",
    "id_zalo": "et",
    "is_scroll_button": "architecto",
    "type_button": "sit",
    "header_type": "ipsum",
    "color_background_header": "vel",
    "color_text_header": "aut",
    "type_navigator": "ut",
    "type_loading": "asperiores",
    "type_of_menu": "quia",
    "product_item_type": "nihil",
    "search_background_header": "quaerat",
    "search_text_header": "nihil",
    "carousel_type": "sapiente",
    "home_id_carousel_app_image": "consequatur",
    "home_list_category_is_show": "voluptatibus",
    "home_id_list_category_app_image": "occaecati",
    "home_top_is_show": "eius",
    "home_top_text": "consequatur",
    "home_top_color": "et",
    "home_carousel_is_show": "quaerat",
    "home_page_type": "ipsa",
    "category_page_type": "sint",
    "product_page_type": "unde",
    "is_show_same_product": "doloremque",
    "is_show_list_post_contact": "voluptatum",
    "post_id_help": "voluptas",
    "post_id_contact": "sed",
    "post_id_about": "ipsum",
    "post_id_terms": "recusandae",
    "post_id_return_policy": "aspernatur",
    "post_id_support_policy": "ratione",
    "post_id_privacy_policy": "voluptas",
    "contact_page_type": "doloribus",
    "contact_google_map": "vero",
    "contact_address": "optio",
    "contact_email": "maxime",
    "contact_phone_number": "sed",
    "contact_time_work": "aut",
    "contact_info_bank": "iure",
    "contact_individual_organization_name": "molestiae",
    "contact_short_description": "et",
    "contact_business_registration_certificate": "pariatur",
    "contact_fanpage": "et",
    "html_footer": "vel",
    "banner_type": 14,
    "product_home_type": "qui",
    "post_home_type": "maxime",
    "footer_type": "earum",
    "is_use_footer_html": "vel",
    "carousel_app_images": "laudantium"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/web-theme/{store_code}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `logo_url` | required |  optional  | Logo Chính hiển thị trên header
        `favicon_url` | required |  optional  | link favicon_url
        `image_share_web_url` | required |  optional  | link ảnh khi chia sẻ trang web đến facebook hoặc MXH
        `home_title` | required |  optional  | Title chính cho trang home
        `home_description` | required |  optional  | Mo ta cho trang home
        `domain` | required |  optional  | Tên miền
        `is_show_logo` | required |  optional  | no
        `color_main_1` | required |  optional  | Màu chính cho web
        `color_main_2` | required |  optional  | no
        `font_color_all_page` | required |  optional  | no
        `font_color_title` | required |  optional  | no
        `font_color_main` | required |  optional  | no
        `font_family` | required |  optional  | Kiểu chữ cho web
        `icon_hotline` | required |  optional  | no
        `is_show_icon_hotline` | required |  optional  | có show nút hotline ko
        `note_icon_hotline` | required |  optional  | no
        `phone_number_hotline` | required |  optional  | Số điện thoại hotline chạy theo web
        `icon_email` | required |  optional  | no
        `is_show_icon_email` | required |  optional  | email có show email liên hệ chạy theo web
        `title_popup_icon_email` | required |  optional  | no
        `title_popup_success_icon_email` | required |  optional  | no
        `email_contact` | required |  optional  | địa chỉ email liên hệ  no
        `body_email_success_icon_email` | required |  optional  | no
        `icon_facebook` | required |  optional  | no
        `is_show_icon_facebook` | required |  optional  | show icon facelieen hệ ko
        `note_icon_facebook` | required |  optional  | no
        `id_facebook` | required |  optional  | id fanpage
        `icon_zalo` | required |  optional  | no
        `is_show_icon_zalo` | required |  optional  | show zalo ko
        `note_icon_zalo` | required |  optional  | no
        `id_zalo` | id |  optional  | zalo required no
        `is_scroll_button` | required |  optional  | no
        `type_button` | required |  optional  | no
        `header_type` | required |  optional  | no
        `color_background_header` | required |  optional  | no
        `color_text_header` | required |  optional  | no
        `type_navigator` | required |  optional  | no
        `type_loading` | required |  optional  | no
        `type_of_menu` | required |  optional  | no
        `product_item_type` | required |  optional  | no
        `search_background_header` | required |  optional  | no
        `search_text_header` | required |  optional  | no
        `carousel_type` | required |  optional  | no
        `home_id_carousel_app_image` | required |  optional  | no
        `home_list_category_is_show` | required |  optional  | no
        `home_id_list_category_app_image` | required |  optional  | no
        `home_top_is_show` | required |  optional  | no
        `home_top_text` | required |  optional  | no
        `home_top_color` | required |  optional  | no
        `home_carousel_is_show` | required |  optional  | no
        `home_page_type` | required |  optional  | no
        `category_page_type` | required |  optional  | no
        `product_page_type` | required |  optional  | no
        `is_show_same_product` | required |  optional  | no
        `is_show_list_post_contact` | required |  optional  | boolean có show bài viết hỗ trợ không
        `post_id_help` | required |  optional  | id bài viết giúp đỡ
        `post_id_contact` | required |  optional  | id bài viết liên hệ
        `post_id_about` | required |  optional  | id bời viết giới thiệu
        `post_id_terms` | required |  optional  | id bời viết điều khoản điều kiện
        `post_id_return_policy` | required |  optional  | id chính sách hoàn trả
        `post_id_support_policy` | required |  optional  | id chính sách hỗ trợ
        `post_id_privacy_policy` | required |  optional  | id chính sách bảo mật
        `contact_page_type` | required |  optional  | no
        `contact_google_map` | required |  optional  | no
        `contact_address` | required |  optional  | Địa chỉ dưới footer
        `contact_email` | required |  optional  | email dưới footer
        `contact_phone_number` | required |  optional  | sdt dưới footer
        `contact_time_work` | required |  optional  | thời gian làm việc dưới footer
        `contact_info_bank` | required |  optional  | thông tin ngân hàng dưới footer
        `contact_individual_organization_name` | required |  optional  | Tên cá nhân hoặc tổ chức
        `contact_short_description` | required |  optional  | Mô tả ngắn dưới footer
        `contact_business_registration_certificate` | required |  optional  | Giấy đăng ký kinh doanh
        `contact_fanpage` | required |  optional  | fanpage dưới footer
        `html_footer` | required |  optional  | html tùy chỉnh dưới footer
        `banner_type` | integer |  optional  | banner type home
        `product_home_type` | kiểu |  optional  | sản phẩm
        `post_home_type` | kiểu |  optional  | bài viết
        `footer_type` | kiểu |  optional  | footer
        `is_use_footer_html` | sử |  optional  | dụng html thay vì type footer
        `carousel_app_images` | required |  optional  | List<json>   VD: [ {image_url:"link",title:"title"} ] danh sach banner
    
<!-- END_cb50ae79f2cf8a665970ab2dc60a33be -->

<!-- START_48817a648002cc9a96b7344e45b519f6 -->
## get WebTheme

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/web-theme/veniam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/web-theme/veniam"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/web-theme/{store_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy

<!-- END_48817a648002cc9a96b7344e45b519f6 -->

#User/Yêu cầu thanh toán


<!-- START_042440314a0ed98a3ed2cfe9c24ec422 -->
## Danh sách yêu cầu thanh toán
status&quot;:  //0 chờ xử lý - 1 huy yeu cau - 2 đã thanh toán
 from&quot;:  // //0 yêu cầu từ CTV - 1 Do user lên danh sách

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/agencies/request_payment/current" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/agencies/request_payment/current"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agencies/request_payment/current`


<!-- END_042440314a0ed98a3ed2cfe9c24ec422 -->

<!-- START_c9182bfedb793bc6739bbb4928b46c96 -->
## Lịch sử yêu cầu thanh toán

status 0 chờ xử lý - 1 hoàn lại - 2 đã thanh toán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/ea/agencies/request_payment/history?page=9" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/ea/agencies/request_payment/history"
);

let params = {
    "page": "9",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/agencies/request_payment/history`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)

<!-- END_c9182bfedb793bc6739bbb4928b46c96 -->

<!-- START_bdfa84e17966d942086b97b8f90555de -->
## Thay đổi trạng thái chờ xỷ lý sang đã thanh toán hoặc hoàn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/assumenda/agencies/request_payment/change_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":"dignissimos","list_id":"ut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/assumenda/agencies/request_payment/change_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "dignissimos",
    "list_id": "ut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/agencies/request_payment/change_status`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | string |  optional  | chờ xử lý - 1 hoàn lại - 2 đã thanh toán
        `list_id` | id |  optional  | xử lý
    
<!-- END_bdfa84e17966d942086b97b8f90555de -->

<!-- START_07a078715cfe2f61c83479fdbbbdd69d -->
## Quyết toán toàn bộ CTV

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/rerum/agencies/request_payment/settlement" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/rerum/agencies/request_payment/settlement"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/agencies/request_payment/settlement`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_07a078715cfe2f61c83479fdbbbdd69d -->

<!-- START_a466d94fa4d5bf6148fb9322198a4c52 -->
## Danh sách yêu cầu thanh toán
status&quot;:  //0 chờ xử lý - 1 huy yeu cau - 2 đã thanh toán
 from&quot;:  // //0 yêu cầu từ CTV - 1 Do user lên danh sách

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/collaborators/request_payment/current" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/collaborators/request_payment/current"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/collaborators/request_payment/current`


<!-- END_a466d94fa4d5bf6148fb9322198a4c52 -->

<!-- START_4ad0d80962857a4fba4e608b5afd3d6e -->
## Lịch sử yêu cầu thanh toán

status 0 chờ xử lý - 1 hoàn lại - 2 đã thanh toán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/dolor/collaborators/request_payment/history?page=9" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/dolor/collaborators/request_payment/history"
);

let params = {
    "page": "9",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/collaborators/request_payment/history`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách sản phẩm ở trang {page} (Mỗi trang có 20 item)

<!-- END_4ad0d80962857a4fba4e608b5afd3d6e -->

<!-- START_54602d6e191b8ba98b53c5b7d9d2aed7 -->
## Thay đổi trạng thái chờ xỷ lý sang đã thanh toán hoặc hoàn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/quia/collaborators/request_payment/change_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":"doloremque","list_id":"eos"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/quia/collaborators/request_payment/change_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "doloremque",
    "list_id": "eos"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/collaborators/request_payment/change_status`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | string |  optional  | chờ xử lý - 1 hoàn lại - 2 đã thanh toán
        `list_id` | id |  optional  | xử lý
    
<!-- END_54602d6e191b8ba98b53c5b7d9d2aed7 -->

<!-- START_3242d29a5a2fc3c5484939873821d7d9 -->
## Quyết toán toàn bộ CTV

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/voluptas/collaborators/request_payment/settlement" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptas/collaborators/request_payment/settlement"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/collaborators/request_payment/settlement`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_3242d29a5a2fc3c5484939873821d7d9 -->

#User/Điện thoại chấm công


<!-- START_61182f8661af3729ccdecfb114617af3 -->
## Danh sách điện thoại chấm công

status 0 chưa duyệt, 1 đã duyệt

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store_v2/nostrum/1/mobile_checkin" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/nostrum/1/mobile_checkin"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store_v2/{store_code}/{branch_id}/mobile_checkin`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_61182f8661af3729ccdecfb114617af3 -->

<!-- START_5f27fe872c3168a4d8e831529a63657a -->
## Thêm thiết bị mới

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/aperiam/1/mobile_checkin" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"perspiciatis","device_id":"nemo"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/aperiam/1/mobile_checkin"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "perspiciatis",
    "device_id": "nemo"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/mobile_checkin`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên điện thoại
        `device_id` | string |  optional  | device_id
    
<!-- END_5f27fe872c3168a4d8e831529a63657a -->

<!-- START_f8373596641aaaafff293088a55afaf0 -->
## Cập nhật điện thoại

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store_v2/ullam/1/mobile_checkin/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"consequuntur","device_id":"voluptatem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/ullam/1/mobile_checkin/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "consequuntur",
    "device_id": "voluptatem"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store_v2/{store_code}/{branch_id}/mobile_checkin/{mobile_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  optional  | Tên điện thoại
        `device_id` | string |  optional  | device_id
    
<!-- END_f8373596641aaaafff293088a55afaf0 -->

<!-- START_c8eda6ba99ea330dc053e10a3d71ce61 -->
## Xóa thiết bị

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store_v2/dignissimos/1/mobile_checkin/aliquid" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/dignissimos/1/mobile_checkin/aliquid"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store_v2/{store_code}/{branch_id}/mobile_checkin/{mobile_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code
    `mobile_id` |  required  | ID thiết bị

<!-- END_c8eda6ba99ea330dc053e10a3d71ce61 -->

#User/Đánh giá sản phẩm


<!-- START_23580bce2431cf5169c0799e513c9fc7 -->
## //status 0 đang chờ duyệt  1 ok -1 hủy
Danh sách đánh giá của sản phẩm
averaged_stars trung bình sao

filter_by  (theo số sao stars hoặc status )
filter_by_value (giá trị muốn lấy)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/reviews" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/reviews"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/reviews`


<!-- END_23580bce2431cf5169c0799e513c9fc7 -->

<!-- START_d75773d9e1874d7a2125ca19551343c6 -->
## xóa một đánh giá

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/sint/reviews/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/sint/reviews/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/reviews/{review_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID review cần xóa

<!-- END_d75773d9e1874d7a2125ca19551343c6 -->

<!-- START_55a072a691c91a8cebe008e2b80b19b7 -->
## update một Review

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/voluptas/reviews/voluptatum" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"status":15,"content":"molestiae"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/voluptas/reviews/voluptatum"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": 15,
    "content": "molestiae"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/reviews/{review_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần update
    `review_id` |  required  | review_id cần update
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `status` | integer |  required  | 0 đang chờ duyệt  1 ok -1 hủy
        `content` | required |  optional  | nội dung
    
<!-- END_55a072a691c91a8cebe008e2b80b19b7 -->

#User/Đăng ký


<!-- START_d7b7952e7fdddc07c978c9bdaf757acf -->
## Register

> Example request:

```bash
curl -X POST \
    "http://localhost/api/register" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"et","email":"minus","password":"atque","otp":"enim","otp_from":"odio"}'

```

```javascript
const url = new URL(
    "http://localhost/api/register"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "et",
    "email": "minus",
    "password": "atque",
    "otp": "enim",
    "otp_from": "odio"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/register`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `email` | string |  required  | Email
        `password` | string |  required  | Password
        `otp` | string |  optional  | gửi tin nhắn (DV SAHA gửi tới 8085)
        `otp_from` | string |  optional  | phone(từ sdt)  email(từ email) mặc định là phone
    
<!-- END_d7b7952e7fdddc07c978c9bdaf757acf -->

#User/Đăng nhập


<!-- START_c3fa189a6c95ca36ad6ac4791a873d23 -->
## Login

> Example request:

```bash
curl -X POST \
    "http://localhost/api/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email_or_phone_number":"dolorem","password":"aliquam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/login"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email_or_phone_number": "dolorem",
    "password": "aliquam"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/login`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `email_or_phone_number` | string |  required  | (Username, email hoặc số điện thoại)
        `password` | string |  required  | Password
    
<!-- END_c3fa189a6c95ca36ad6ac4791a873d23 -->

<!-- START_b0cb4f2b1b7e547be6c7b4750c86bc53 -->
## Lấy lại mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/reset_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"saepe","password":"sunt","otp":"voluptates","otp_from":"voluptate"}'

```

```javascript
const url = new URL(
    "http://localhost/api/reset_password"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "saepe",
    "password": "sunt",
    "otp": "voluptates",
    "otp_from": "voluptate"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/reset_password`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | string |  required  | Số điện thoại
        `password` | string |  required  | Mật khẩu mới
        `otp` | string |  optional  | gửi tin nhắn (DV SAHA gửi tới 8085)
        `otp_from` | string |  optional  | phone(từ sdt)  email(từ email) mặc định là phone
    
<!-- END_b0cb4f2b1b7e547be6c7b4750c86bc53 -->

<!-- START_fdf0e4e01f3d3644775396601dfb156e -->
## Thay đổi mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/change_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"password":"optio"}'

```

```javascript
const url = new URL(
    "http://localhost/api/change_password"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "password": "optio"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/change_password`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `password` | string |  required  | Mật khẩu mới
    
<!-- END_fdf0e4e01f3d3644775396601dfb156e -->

<!-- START_cafb5aa901ca495af2f8e22b832dad75 -->
## Kiểm tra email,phone_number đã tồn tại
Sẽ ưu tiên kiểm tra phone_number (kết quả true tồn tại, false không tồn tại)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/login/check_exists" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"et","email":"facilis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/login/check_exists"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "phone_number": "et",
    "email": "facilis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/login/check_exists`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `phone_number` | required |  optional  | phone_number
        `email` | string |  required  | email
    
<!-- END_cafb5aa901ca495af2f8e22b832dad75 -->

#User/Đơn hàng


APIs Đơn hàng
<!-- START_49eb538f73ff213722f667c92f0dc1ca -->
## Thanh toán đơn hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/kds/1/orders/pay_order/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"1","amount_money":19043.8747,"payment_method":18}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/kds/1/orders/pay_order/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "1",
    "amount_money": 19043.8747,
    "payment_method": 18
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/orders/pay_order/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | required |  optional  | Mã đơn hàng.
        `amount_money` | float |  optional  | số tiền thanh toán  (truyền lên đầy đủ sẽ tự động thanh toán, truyền 1 phần tự động chuyển thanh trạng thái thanh toán 1 phần, truyền 0 chưa thanh toán)
        `payment_method` | integer |  optional  | phương thức thanh toán
    
<!-- END_49eb538f73ff213722f667c92f0dc1ca -->

<!-- START_8deff7e0ecff3e3586b0c35a81483381 -->
## Danh sách Order
Trạng thái đơn hàng saha
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đang chuẩn bị hàng (PACKING)
- Hết hàng (OUT_OF_STOCK)
- Shop huỷ (USER_CANCELLED)
- Khách đã hủy (CUSTOMER_CANCELLED)
- Đang giao hàng (SHIPPING)
- Lỗi giao hàng (DELIVERY_ERROR)
- Đã hoàn thành (COMPLETED)
- Chờ trả hàng (CUSTOMER_RETURNING)
- Đã trả hàng (CUSTOMER_HAS_RETURNS)
############################################################################
Trạng thái thanh toán
- Chưa thanh toán (UNPAID)
- Chờ xử lý (WAITING_FOR_PROGRESSING)
- Đã thanh toán (PAID)
- Đã thanh toán một phần (PARTIALLY_PAID)
- Đã hủy (CANCELLED)
- Đã hoàn tiền (REFUNDS)

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/orders?page=18&search=est&sort_by=ut&descending=unde&field_by=illum&field_by_value=ex&time_from=sit&time_to=saepe&order_status_code=dolorum&payment_status_code=aut&collaborator_by_customer_id=totam&agency_by_customer_id=voluptatibus&from_pos=ducimus&phone_number=sunt&branch_id=a&order_from_list=sit&branch_id_list=voluptate&order_status_list=eos&payment_status_list=ut&order_status_code_list=possimus&payment_status_code_list=quo" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders"
);

let params = {
    "page": "18",
    "search": "est",
    "sort_by": "ut",
    "descending": "unde",
    "field_by": "illum",
    "field_by_value": "ex",
    "time_from": "sit",
    "time_to": "saepe",
    "order_status_code": "dolorum",
    "payment_status_code": "aut",
    "collaborator_by_customer_id": "totam",
    "agency_by_customer_id": "voluptatibus",
    "from_pos": "ducimus",
    "phone_number": "sunt",
    "branch_id": "a",
    "order_from_list": "sit",
    "branch_id_list": "voluptate",
    "order_status_list": "eos",
    "payment_status_list": "ut",
    "order_status_code_list": "possimus",
    "payment_status_code_list": "quo",
};
Object.keys(params)
    .forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/orders`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    `page` |  optional  | Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
    `search` |  optional  | Tên cần tìm VD: covid 19
    `sort_by` |  optional  | Sắp xếp theo VD: time
    `descending` |  optional  | Giảm dần không VD: false
    `field_by` |  optional  | Chọn trường nào để lấy
    `field_by_value` |  optional  | Giá trị trường đó
    `time_from` |  optional  | Từ thời gian nào
    `time_to` |  optional  | Đến thời gian nào.
    `order_status_code` |  optional  | trạng thái order
    `payment_status_code` |  optional  | trạng thái thanh toán
    `collaborator_by_customer_id` |  optional  | CTV theo customer id
    `agency_by_customer_id` |  optional  | Dai ly theo customer id
    `from_pos` |  optional  | boolean From pos
    `phone_number` |  optional  | sdt khach hang
    `branch_id` |  optional  | int Branch_id chi nhánh
    `order_from_list` |  optional  | List danh sách order từ nguồn nào VD: 2,3
    `branch_id_list` |  optional  | List danh sách chi nhánh VD: 1,2,3
    `order_status_list` |  optional  | List danh sách trạng thái đơn hàng: VD: 1,2
    `payment_status_list` |  optional  | List danh sách trạng thái thanh toán : VD: 2,3
    `order_status_code_list` |  optional  | List danh sách trạng thái đơn hàng: VD: 1,2
    `payment_status_code_list` |  optional  | List danh sách trạng thái thanh toán : VD: 2,3

<!-- END_8deff7e0ecff3e3586b0c35a81483381 -->

<!-- START_5c4a8e2aa5858b102a1290e4052d4e57 -->
## Lấy thông tin 1 đơn hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/orders/order_code" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/order_code"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/orders/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
    `order_code` |  required  | order_code.

<!-- END_5c4a8e2aa5858b102a1290e4052d4e57 -->

<!-- START_85a3aa584db3372cf592f642428dcf8f -->
## Lịch sử trạng thái đơn hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/orders/status_records/kds" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/status_records/kds"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/orders/status_records/{order_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
    `order_id` |  required  | order_id.

<!-- END_85a3aa584db3372cf592f642428dcf8f -->

<!-- START_f1ae29cda66f315c88d929c64b7add78 -->
## Thay đổi trạng thái đơn hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/kds/orders/change_order_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"1","order_status_code":"nihil"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/change_order_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "1",
    "order_status_code": "nihil"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/orders/change_order_status`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | required |  optional  | Mã đơn hàng.
        `order_status_code` | string |  required  | Mã trạng thái đơn hàng
    
<!-- END_f1ae29cda66f315c88d929c64b7add78 -->

<!-- START_a59b833e1184aeadbe8de45381ea200d -->
## Thay đổi trạng thái thanh toán

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/kds/orders/change_payment_status" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"1","payment_status_code":"omnis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/change_payment_status"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "1",
    "payment_status_code": "omnis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/orders/change_payment_status`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | required |  optional  | Mã đơn hàng.
        `payment_status_code` | string |  required  | Mã trạng thái thanh toán
    
<!-- END_a59b833e1184aeadbe8de45381ea200d -->

<!-- START_dac732359871e12d321e2a806adfa62a -->
## Cập nhật thông tin 1 đơn hàng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/kds/orders/update/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"partner_shipper_id":"ad","total_shipping_fee":"esse","branch_id":"aut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/update/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "partner_shipper_id": "ad",
    "total_shipping_fee": "esse",
    "branch_id": "aut"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/orders/update/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `partner_shipper_id` | required |  optional  | Phương thức vận chuyển
        `total_shipping_fee` | required |  optional  | Số tiền ship
        `branch_id` | required |  optional  | Id chi nhánh
    
<!-- END_dac732359871e12d321e2a806adfa62a -->

<!-- START_cac756435b1e41b16af2590fd4636edb -->
## Thanh toán đơn hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/kds/orders/pay_order/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"1","amount_money":13.7003,"payment_method":19}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/pay_order/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "1",
    "amount_money": 13.7003,
    "payment_method": 19
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/orders/pay_order/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | required |  optional  | Mã đơn hàng.
        `amount_money` | float |  optional  | số tiền thanh toán  (truyền lên đầy đủ sẽ tự động thanh toán, truyền 1 phần tự động chuyển thanh trạng thái thanh toán 1 phần, truyền 0 chưa thanh toán)
        `payment_method` | integer |  optional  | phương thức thanh toán
    
<!-- END_cac756435b1e41b16af2590fd4636edb -->

<!-- START_0a86cfff38b5d0839beb8e73009d85b2 -->
## Thay đổi trạng thái thanh toán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/kds/orders/history_pay/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"1","payment_status_code":"suscipit"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/orders/history_pay/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "1",
    "payment_status_code": "suscipit"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/orders/history_pay/{order_code}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | required |  optional  | Mã đơn hàng.
        `payment_status_code` | string |  required  | Mã trạng thái thanh toán
    
<!-- END_0a86cfff38b5d0839beb8e73009d85b2 -->

#User/Đơn vị vận chuyển


<!-- START_4a5c602d9f5ef0a5d7ef3c31de90a13c -->
## Danh cách tất cả đơn vị vận chuyển

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/shipments" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipments"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/shipments`


<!-- END_4a5c602d9f5ef0a5d7ef3c31de90a13c -->

<!-- START_3462a36d8cbc9638311a0923cd3d5a6d -->
## Tính phí ship

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/shipments/maxime/calculate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipments/maxime/calculate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/shipments/{partner_id}/calculate`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `partner_id` |  required  | id cần sửa

<!-- END_3462a36d8cbc9638311a0923cd3d5a6d -->

<!-- START_1f37a7b378617edc96cb96db87015c6d -->
## Cập nhật cấu thông số cho 1 đơn vị vận chuyển

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/shipments/accusantium" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"token":"neque","use":false,"cod":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipments/accusantium"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "token": "neque",
    "use": false,
    "cod": true
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/shipments/{partner_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `partner_id` |  required  | id cần sửa
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `token` | string |  optional  | token được cung cấp
        `use` | boolean |  optional  | Sử dụng hay không
        `cod` | boolean |  optional  | COD hay không
    
<!-- END_1f37a7b378617edc96cb96db87015c6d -->

<!-- START_cf759f937046be12706b8e8652c95513 -->
## Danh cách tất cả đơn vị vận chuyển

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/shipment_get_token/viettel" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/shipment_get_token/viettel"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/shipment_get_token/viettel`


<!-- END_cf759f937046be12706b8e8652c95513 -->

#User/Địa chỉ store


<!-- START_df4cc54846b334d154e2d447f8e3de37 -->
## Thêm địa chỉ cho store

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/store_address" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"inventore","address_detail":"qui","country":13,"province":8,"district":9,"village":3,"wards":7,"postcode":"sequi","email":"voluptatum","phone":"consectetur","is_default_pickup":"sed","is_default_return":"perspiciatis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/store_address"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "inventore",
    "address_detail": "qui",
    "country": 13,
    "province": 8,
    "district": 9,
    "village": 3,
    "wards": 7,
    "postcode": "sequi",
    "email": "voluptatum",
    "phone": "consectetur",
    "is_default_pickup": "sed",
    "is_default_return": "perspiciatis"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/store_address`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | họ tên
        `address_detail` | string |  optional  | Địa chỉ chi tiết
        `country` | integer |  required  | id country
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `village` | integer |  required  | id village
        `wards` | integer |  required  | id wards
        `postcode` | string |  required  | postcode
        `email` | string |  required  | email
        `phone` | string |  required  | phone
        `is_default_pickup` | string |  required  | Địa chỉ nhận hàng mặc định hay không
        `is_default_return` | string |  required  | Địa chỉ trả hàng
    
<!-- END_df4cc54846b334d154e2d447f8e3de37 -->

<!-- START_ccbf5a31b0243503f01fff13a0ea3093 -->
## Cập nhật địa chỉ cho store

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/store_address/optio" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","address_detail":"repellendus","country":18,"province":19,"district":2,"village":2,"wards":3,"postcode":"omnis","email":"ut","phone":"dicta","is_default_pickup":"ea","is_default_return":"ut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/store_address/optio"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "et",
    "address_detail": "repellendus",
    "country": 18,
    "province": 19,
    "district": 2,
    "village": 2,
    "wards": 3,
    "postcode": "omnis",
    "email": "ut",
    "phone": "dicta",
    "is_default_pickup": "ea",
    "is_default_return": "ut"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/store_address/{store_address_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_address_id` |  required  | id địa chỉ cần sửa
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | string |  required  | họ tên
        `address_detail` | string |  optional  | Địa chỉ chi tiết
        `country` | integer |  required  | id country
        `province` | integer |  required  | id province
        `district` | integer |  required  | id district
        `village` | integer |  required  | id village
        `wards` | integer |  required  | id wards
        `postcode` | string |  required  | postcode
        `email` | string |  required  | email
        `phone` | string |  required  | phone
        `is_default_pickup` | string |  required  | Địa chỉ nhận hàng mặc định hay không
        `is_default_return` | string |  required  | Địa chỉ trả hàng
    
<!-- END_ccbf5a31b0243503f01fff13a0ea3093 -->

<!-- START_71618606b2ed4c2a4413d680bc373a4d -->
## Xem tất cả store address

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/store_address" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/store_address"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/store_address`


<!-- END_71618606b2ed4c2a4413d680bc373a4d -->

<!-- START_5f2cde7660a08e60e50caa0a88855243 -->
## xóa một địa chỉ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/ipsum/store_address/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/ipsum/store_address/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/store_address/{store_address_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `id` |  required  | ID địa chỉ cần xóa

<!-- END_5f2cde7660a08e60e50caa0a88855243 -->

#Ussr/Thông tin cá nhân


<!-- START_3c520b0ccdbf5100b6f6994368e1b344 -->
## Tạo Lấy thông tin profile

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/profile"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/profile`


<!-- END_3c520b0ccdbf5100b6f6994368e1b344 -->

<!-- START_cf95104e8d1e3bda6b10e9b856955ac6 -->
## Cập nhật thông tin profile

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"aut","date_of_birth":"deleniti","avatar_image":"autem","sex":3}'

```

```javascript
const url = new URL(
    "http://localhost/api/profile"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "aut",
    "date_of_birth": "deleniti",
    "avatar_image": "autem",
    "sex": 3
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/profile`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | String |  optional  | Họ và tên
        `date_of_birth` | Date |  optional  | Ngày sinh
        `avatar_image` | String |  optional  | Link ảnh avater
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nam - 2 Nữ
    
<!-- END_cf95104e8d1e3bda6b10e9b856955ac6 -->

#general


<!-- START_644235920cedb69d84f67f8982fc0984 -->
## api/handle_receiver_sms
> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/handle_receiver_sms" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/handle_receiver_sms"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/handle_receiver_sms`


<!-- END_644235920cedb69d84f67f8982fc0984 -->

<!-- START_8c74755de4d6af116667705bad06f2a2 -->
## api/send_email_otp
> Example request:

```bash
curl -X POST \
    "http://localhost/api/send_email_otp" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/send_email_otp"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/send_email_otp`


<!-- END_8c74755de4d6af116667705bad06f2a2 -->

<!-- START_dbf281431cc3a40d0ae09a3771e239a1 -->
## Hoàn sản phẩm hoặc đổi trả, hoàn tiền

@bodyParam  string Mã đơn hàng hoàn trả

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/1/1/pos/refund" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"List":"in"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/pos/refund"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "List": "in"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/pos/refund`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `List` | danh |  optional  | sách refund_line_items cần giữ lại list[]
    
<!-- END_dbf281431cc3a40d0ae09a3771e239a1 -->

<!-- START_11dc0dbc5a4018a632d85f39fe8590b2 -->
## Tính tiền hoàn

@bodyParam  string Mã đơn hàng hoàn trả

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store_v2/1/1/pos/refund/calculate" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"List":"aut"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store_v2/1/1/pos/refund/calculate"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "List": "aut"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store_v2/{store_code}/{branch_id}/pos/refund/calculate`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `List` | danh |  optional  | sách refund_line_items cần giữ lại list[]
    
<!-- END_11dc0dbc5a4018a632d85f39fe8590b2 -->

<!-- START_af3073b2023e594e8c42e83facfa9377 -->
## Thêm 1 nhân viên

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/staffs" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"ullam","phone_number":"dolorem","email":"cupiditate","name":"perspiciatis","salary":"a","salary_one_hour":"velit","sex":6,"id_decentralization":5,"branch_id":11}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/staffs"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "username": "ullam",
    "phone_number": "dolorem",
    "email": "cupiditate",
    "name": "perspiciatis",
    "salary": "a",
    "salary_one_hour": "velit",
    "sex": 6,
    "id_decentralization": 5,
    "branch_id": 11
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/staffs`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `username` | string |  optional  | Tên tài khoản
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `salary` | string |  optional  | Lương
        `salary_one_hour` | string |  optional  | Lương theo giờ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `id_decentralization` | integer |  optional  | id phân quyền (ủy quyền cho nhân viên)
        `branch_id` | integer |  optional  | id chi nhánh
    
<!-- END_af3073b2023e594e8c42e83facfa9377 -->

<!-- START_e8e3ed699afe30e1c75c4dfb2890ff65 -->
## Danh cách nhân viên

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/staffs" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/staffs"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/staffs`


<!-- END_e8e3ed699afe30e1c75c4dfb2890ff65 -->

<!-- START_9c7c5a812c651d517d28b333fbadc2c9 -->
## Xóa 1 nhân viên

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/kds/staffs/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/kds/staffs/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/staffs/{staff_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code.

<!-- END_9c7c5a812c651d517d28b333fbadc2c9 -->

<!-- START_0347021b37e8f8d5e5b5129c7d716795 -->
## Cập nhật thông tin nhân viên

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/staffs/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"username":"dolores","phone_number":"quo","email":"fuga","name":"quisquam","salary":"enim","salary_one_hour":"occaecati","sex":6,"id_decentralization":11,"branch_id":7}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/staffs/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "username": "dolores",
    "phone_number": "quo",
    "email": "fuga",
    "name": "quisquam",
    "salary": "enim",
    "salary_one_hour": "occaecati",
    "sex": 6,
    "id_decentralization": 11,
    "branch_id": 7
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/staffs/{staff_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `username` | string |  optional  | Tên tài khoản
        `phone_number` | string |  optional  | Số điện thoại
        `email` | string |  optional  | Email
        `name` | string |  optional  | Tên đầy đủ
        `salary` | string |  optional  | Lương
        `salary_one_hour` | string |  optional  | Lương theo giờ
        `sex` | integer |  optional  | Giới tính 0 Ko xác định - 1 Nan - 2 Nữ
        `id_decentralization` | integer |  optional  | id phân quyền (ủy quyền cho nhân viên)
        `branch_id` | integer |  optional  | id chi nhánh
    
<!-- END_0347021b37e8f8d5e5b5129c7d716795 -->

<!-- START_72e63b18adae30839defe49083d2b931 -->
## Lấy danh sách sản phẩm ở sàn TMDT

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/ecommerce/products" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"page":18,"shop_id":20,"provider":16}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/ecommerce/products"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "page": 18,
    "shop_id": 20,
    "provider": 16
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/ecommerce/products`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `page` | integer |  required  | Trang
        `shop_id` | integer |  required  | Id của shop
        `provider` | integer |  required  | Sàn nào (shopee,lazada,sendo)
    
<!-- END_72e63b18adae30839defe49083d2b931 -->

<!-- START_9f8be508d1b527c38ef58c2ee7c56cb3 -->
## Xóa sản phẩm trong giỏ hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/carts/1/list/1/clear_carts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/carts/1/list/1/clear_carts"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/carts/{branch_id}/list/{cart_id}/clear_carts`


<!-- END_9f8be508d1b527c38ef58c2ee7c56cb3 -->

<!-- START_a7924821bc5b150fdbe869e30232a9d5 -->
## Danh sách popup

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/maxime/popups" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/maxime/popups"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/popups`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code

<!-- END_a7924821bc5b150fdbe869e30232a9d5 -->

<!-- START_ae6a03446c82e8655ec1c6493849dde0 -->
## Thông tin một bài viết
  Gửi danh sách button lên type_action gồm: PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/et/popups" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"velit","link_image":"delectus","show_once":"quam","type_action":"quidem","value_action":"voluptatem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/et/popups"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "velit",
    "link_image": "delectus",
    "show_once": "quam",
    "type_action": "quidem",
    "value_action": "voluptatem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/popups`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | required |  optional  | Tên
        `link_image` | required |  optional  | Ảnh hiển thị lên
        `show_once` | required |  optional  | Chỉ show 1 lần
        `type_action` | gồm: |  optional  | PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
        `value_action` | string |  optional  | giá trị thực thi ví dụ  id cate,product hoặc link (string)
    
<!-- END_ae6a03446c82e8655ec1c6493849dde0 -->

<!-- START_8cca4ef1b4656de722c23b69d3cb7b24 -->
## update một Popup

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/laborum/popups/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"natus","link_image":"ut","show_once":"adipisci","type_action":"praesentium","value_action":"amet"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/laborum/popups/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "natus",
    "link_image": "ut",
    "show_once": "adipisci",
    "type_action": "praesentium",
    "value_action": "amet"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/popups/{popup_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần lấy.
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `name` | required |  optional  | Tên
        `link_image` | required |  optional  | Ảnh hiển thị lên
        `show_once` | required |  optional  | Chỉ show 1 lần
        `type_action` | gồm: |  optional  | PRODUCT,CATEGORY_PRODUCT,CALL,LINK,CATEGORY_POST,POST,QR,VOURCHER,PRODUCTS_TOP_SALES,PRODUCTS_DISCOUNT,PRODUCTS_NEW,MESSAGE_TO_SHOP,SCORE
        `value_action` | string |  optional  | giá trị thực thi ví dụ  id cate,product hoặc link (string)
    
<!-- END_8cca4ef1b4656de722c23b69d3cb7b24 -->

<!-- START_123125d5a13152004ba210a4e6316bf2 -->
## xóa một danh mục

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/sit/popups/atque" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/sit/popups/atque"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/popups/{popup_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `store_code` |  required  | Store code cần xóa.
    `popup_id` |  required  | ID Popup cần xóa thông tin.

<!-- END_123125d5a13152004ba210a4e6316bf2 -->

<!-- START_e16b2f7cc1c47eba348b648824c9249b -->
## Hoàn sản phẩm hoặc đổi trả, hoàn tiền

@bodyParam  string Mã đơn hàng hoàn trả

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/pos/refund" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"List":"expedita"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/pos/refund"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "List": "expedita"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/pos/refund`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `List` | danh |  optional  | sách refund_line_items cần giữ lại list[]
    
<!-- END_e16b2f7cc1c47eba348b648824c9249b -->

<!-- START_6d20d9ab1a803d9c2fb43c741c3e534a -->
## Gửi email hóa đơn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/pos/send_order_email" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"order_code":"blanditiis","email":"soluta"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/pos/send_order_email"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "order_code": "blanditiis",
    "email": "soluta"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/pos/send_order_email`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `order_code` | string |  optional  | mã hóa đơn
        `email` | string |  optional  | email gửi tới
    
<!-- END_6d20d9ab1a803d9c2fb43c741c3e534a -->

#Đào tạo/Trắc nghiệm


<!-- START_9e58089c567b2be2eaca2680c345c536 -->
## Thêm 1 bài thi trắc nghiệm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/train_courses/voluptatem/quiz" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"est","short_description":"soluta","minute":13,"show":true,"auto_change_order_questions":false,"auto_change_order_answer":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/voluptatem/quiz"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "est",
    "short_description": "soluta",
    "minute": 13,
    "show": true,
    "auto_change_order_questions": false,
    "auto_change_order_answer": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/train_courses/{train_course_id}/quiz`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `train_course_id` |  optional  | int ID khóa học
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  optional  | Tiêu đề bài thi
        `short_description` | string |  optional  | Mô tả ngắn
        `minute` | integer |  optional  | số phút thi
        `show` | boolean |  optional  | hiển thị bài thi hay không
        `auto_change_order_questions` | boolean |  optional  | cho phép tự động đổi vị trí câu hỏi
        `auto_change_order_answer` | boolean |  optional  | cho phép tự động đổi vị trí câu trả lời ABCD
    
<!-- END_9e58089c567b2be2eaca2680c345c536 -->

<!-- START_f68b2e560827f5da34eb7a8d2d82ff64 -->
## Danh sách bài thi trắc nghiệm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/train_courses/sit/quiz" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/sit/quiz"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/train_courses/{train_course_id}/quiz`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `train_course_id` |  optional  | int ID khóa học

<!-- END_f68b2e560827f5da34eb7a8d2d82ff64 -->

<!-- START_9724252fdff8b0d0b6638a3b0af76d7f -->
## Cập nhật bài thi

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_courses/voluptatum/quiz/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"alias","short_description":"qui","minute":18,"show":true,"auto_change_order_questions":false,"auto_change_order_answer":true}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/voluptatum/quiz/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "alias",
    "short_description": "qui",
    "minute": 18,
    "show": true,
    "auto_change_order_questions": false,
    "auto_change_order_answer": true
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}`

#### URL Parameters

Parameter | Status | Description
--------- | ------- | ------- | -------
    `train_course_id` |  optional  | int ID khóa học
#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  optional  | Tiêu đề bài thi
        `short_description` | string |  optional  | Mô tả ngắn
        `minute` | integer |  optional  | số phút thi
        `show` | boolean |  optional  | hiển thị bài thi hay không
        `auto_change_order_questions` | boolean |  optional  | cho phép tự động đổi vị trí câu hỏi
        `auto_change_order_answer` | boolean |  optional  | cho phép tự động đổi vị trí câu trả lời ABCD
    
<!-- END_9724252fdff8b0d0b6638a3b0af76d7f -->

<!-- START_fe82f7fa01c8b37644bdee206e3c8e9a -->
## Xóa 1 bài học

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/train_courses/1/quiz/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1/quiz/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}`


<!-- END_fe82f7fa01c8b37644bdee206e3c8e9a -->

<!-- START_01022a4704a8e67252dbe5846cdf2efc -->
## Thông tin 1 bài trắc nghiệm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/train_courses/1/quiz/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1/quiz/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}`


<!-- END_01022a4704a8e67252dbe5846cdf2efc -->

<!-- START_2ac42ee3cf7e27b68df53eaa79232b1e -->
## Thêm 1 câu trắc ngiệm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/train_courses/1/quiz/1/questions" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"quiz_id":2,"question":"sunt","question_image":"impedit","answer_a":"qui","answer_b":"similique","answer_c":"qui","answer_d":"iste","right_answer":"unde"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1/quiz/1/questions"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "quiz_id": 2,
    "question": "sunt",
    "question_image": "impedit",
    "answer_a": "qui",
    "answer_b": "similique",
    "answer_c": "qui",
    "answer_d": "iste",
    "right_answer": "unde"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/questions`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `quiz_id` | integer |  optional  | id bài thi
        `question` | string |  optional  | Câu hỏi
        `question_image` | string |  optional  | Ảnh câu hỏi
        `answer_a` | string |  optional  | Câu trả lời A
        `answer_b` | string |  optional  | Câu trả lời B
        `answer_c` | string |  optional  | Câu trả lời C
        `answer_d` | string |  optional  | Câu trả lời D
        `right_answer` | string |  optional  | Câu trả lời đúng (A,B,C,D)
    
<!-- END_2ac42ee3cf7e27b68df53eaa79232b1e -->

<!-- START_5829923765fd3d28c2e42c3264bde468 -->
## Cập nhật cau hỏi

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_courses/1/quiz/1/questions/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"quiz_id":15,"question":"aut","question_image":"architecto","answer_a":"ab","answer_b":"non","answer_c":"velit","answer_d":"odit","right_answer":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1/quiz/1/questions/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "quiz_id": 15,
    "question": "aut",
    "question_image": "architecto",
    "answer_a": "ab",
    "answer_b": "non",
    "answer_c": "velit",
    "answer_d": "odit",
    "right_answer": "et"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/questions/{question_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `quiz_id` | integer |  optional  | id bài thi
        `question` | string |  optional  | Câu hỏi
        `question_image` | string |  optional  | Ảnh câu hỏi
        `answer_a` | string |  optional  | Câu trả lời A
        `answer_b` | string |  optional  | Câu trả lời B
        `answer_c` | string |  optional  | Câu trả lời C
        `answer_d` | string |  optional  | Câu trả lời D
        `right_answer` | string |  optional  | Câu trả lời đúng (A,B,C,D)
    
<!-- END_5829923765fd3d28c2e42c3264bde468 -->

<!-- START_d0fd19e908230ca2ae2c94ff60ac5caf -->
## Xóa 1 câu hỏi

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/train_courses/1/quiz/1/questions/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1/quiz/1/questions/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/questions/{question_id}`


<!-- END_d0fd19e908230ca2ae2c94ff60ac5caf -->

<!-- START_b6ad35bb53bd14a185c0ba01884ebcb0 -->
## Danh sách bài thi trắc nghiệm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/train_courses/1/quiz" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_courses/1/quiz"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/train_courses/{train_course_id}/quiz`


<!-- END_b6ad35bb53bd14a185c0ba01884ebcb0 -->

<!-- START_50f3a10517482ce9a75314c8c80ea133 -->
## Thông tin 1 bài trắc nghiệm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/train_courses/1/quiz/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_courses/1/quiz/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}`


<!-- END_50f3a10517482ce9a75314c8c80ea133 -->

<!-- START_c53bad8b9b0930846d7611ddd8c0941e -->
## Nộp bài trắc nghiệm

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/train_courses/1/quiz/1/submit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"work_time":17,"define_sort_answers":14,"answers":"voluptatem"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_courses/1/quiz/1/submit"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "work_time": 17,
    "define_sort_answers": 14,
    "answers": "voluptatem"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/submit`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `work_time` | integer |  optional  | work_time giây làm bài
        `define_sort_answers` | integer |  optional  | chuỗi câu trả lời định nghĩa lại
        `answers` | list |  optional  | danh sách câu trả lời dạng [ { "question_id": 5,  "answer": "A" }  ]
    
<!-- END_c53bad8b9b0930846d7611ddd8c0941e -->

<!-- START_5d66ca89ae7130ff4413bcbb97d22d64 -->
## Lịch sử bài làm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/train_courses/1/quiz/1/history_submit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_courses/1/quiz/1/history_submit"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/train_courses/{train_course_id}/quiz/{quiz_id}/history_submit`


<!-- END_5d66ca89ae7130ff4413bcbb97d22d64 -->

#Đào tạo/giáo án chương trình học


<!-- START_8499dfe8bbd25053429e1c326f3f7e10 -->
## Danh sách chương và bài học

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/train_chapter_lessons/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_chapter_lessons/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/train_chapter_lessons/{train_course_id}`


<!-- END_8499dfe8bbd25053429e1c326f3f7e10 -->

<!-- START_41dd8cb87fafeb509bf3837ad4ff2393 -->
## Thêm 1 chương học

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/train_chapters" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"train_course_id":5,"title":"fugit","short_description":"ab"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_chapters"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "train_course_id": 5,
    "title": "fugit",
    "short_description": "ab"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/train_chapters`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `train_course_id` | integer |  optional  | id khóa học
        `title` | string |  optional  | Tiêu đề chương học
        `short_description` | string |  optional  | Mô tả ngắn
    
<!-- END_41dd8cb87fafeb509bf3837ad4ff2393 -->

<!-- START_88d78dd77c99f9965b0f3bed7eb844be -->
## Cập nhật chương học

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_chapters/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"train_course_id":10,"title":"totam","short_description":"corporis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_chapters/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "train_course_id": 10,
    "title": "totam",
    "short_description": "corporis"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_chapters/{train_chapter_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `train_course_id` | integer |  optional  | id khóa học
        `title` | string |  optional  | Tiêu đề bài học
        `short_description` | string |  optional  | Mô tả ngắn
    
<!-- END_88d78dd77c99f9965b0f3bed7eb844be -->

<!-- START_6135c31b3ed305c23fa3df9e16d02e0b -->
## Xóa 1 chương học

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/train_chapters/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_chapters/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/train_chapters/{train_chapter_id}`


<!-- END_6135c31b3ed305c23fa3df9e16d02e0b -->

<!-- START_36c8e8c13883740ec028de94e5f4be1f -->
## Sắp xếp lại chương học

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_chapters_sort" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"list_sort":"fugiat"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_chapters_sort"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "list_sort": "fugiat"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_chapters_sort`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `list_sort` | List |  optional  | gồm id cần sort và vị trí của nó [{ id:1, position:1 }, { id:2, position:2 }
    
<!-- END_36c8e8c13883740ec028de94e5f4be1f -->

<!-- START_58b019e2c0a4edaa907a762f17a6f8b9 -->
## Thêm 1 bài học

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/train_lessons" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"train_chapter_id":13,"title":"consequatur","short_description":"repellendus","link_video_youtube":"officiis","description":"maxime"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_lessons"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "train_chapter_id": 13,
    "title": "consequatur",
    "short_description": "repellendus",
    "link_video_youtube": "officiis",
    "description": "maxime"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/train_lessons`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `train_chapter_id` | integer |  optional  | ID chương học
        `title` | string |  optional  | Tiêu đề bài học
        `short_description` | string |  optional  | Mô tả ngắn
        `link_video_youtube` | string |  optional  | Link video bài học
        `description` | string |  optional  | Nội dung bài học
    
<!-- END_58b019e2c0a4edaa907a762f17a6f8b9 -->

<!-- START_60e240951d7c084b2924c9259ec384ca -->
## Cập nhật bài học

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_lessons/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"train_chapter_id":7,"title":"facilis","short_description":"quisquam","link_video_youtube":"incidunt","description":"quam"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_lessons/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "train_chapter_id": 7,
    "title": "facilis",
    "short_description": "quisquam",
    "link_video_youtube": "incidunt",
    "description": "quam"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_lessons/{train_lesson_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `train_chapter_id` | integer |  optional  | ID chương học
        `title` | string |  optional  | Tiêu đề bài học
        `short_description` | string |  optional  | Mô tả ngắn
        `link_video_youtube` | string |  optional  | Link video bài học
        `description` | string |  optional  | Nội dung bài học
    
<!-- END_60e240951d7c084b2924c9259ec384ca -->

<!-- START_4a34a65b8959a99c7e57928dc5e40af0 -->
## Xóa 1 bài học

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/train_lessons/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_lessons/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/train_lessons/{train_lesson_id}`


<!-- END_4a34a65b8959a99c7e57928dc5e40af0 -->

<!-- START_9901184b75a21fc74aa6e3a7d2bee27e -->
## Sắp xếp lại bài học

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_lessons_sort" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"train_chapter_id":17,"list_sort":"et"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_lessons_sort"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "train_chapter_id": 17,
    "list_sort": "et"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_lessons_sort`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `train_chapter_id` | integer |  optional  | ID chương học
        `list_sort` | List |  optional  | gồm id cần sort và vị trí của nó [{ id:1, position:1 }, { id:2, position:2 }
    
<!-- END_9901184b75a21fc74aa6e3a7d2bee27e -->

<!-- START_3fdb1d0b197006f0e7dfc360dc4bf3f5 -->
## Danh sách chương và bài học

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/train_chapter_lessons/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_chapter_lessons/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/train_chapter_lessons/{train_course_id}`


<!-- END_3fdb1d0b197006f0e7dfc360dc4bf3f5 -->

<!-- START_4844c0b68623d2179da03f74ff2709a8 -->
## Thông tin 1 bài học

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/lessons/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/lessons/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/lessons/{train_lesson_id}`


<!-- END_4844c0b68623d2179da03f74ff2709a8 -->

<!-- START_884bd0dda4e613cd593cacf5f2607248 -->
## Học 1 bài học

> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/lessons/1/learn" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/lessons/1/learn"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/lessons/{train_lesson_id}/learn`


<!-- END_884bd0dda4e613cd593cacf5f2607248 -->

#Đào tạo/khóa học


<!-- START_3bbb65ed155d3a0413a0815f4f5cb505 -->
## Thêm 1 khóa học

> Example request:

```bash
curl -X POST \
    "http://localhost/api/store/1/train_courses" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"eveniet","short_description":"ipsa","description":"veritatis","image_url":"deleniti"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "eveniet",
    "short_description": "ipsa",
    "description": "veritatis",
    "image_url": "deleniti"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/store/{store_code}/train_courses`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  optional  | tiêu đề khóa học
        `short_description` | string |  optional  | Mô tả ngắn
        `description` | string |  optional  | Mô tả dài html
        `image_url` | string |  optional  | Anh
    
<!-- END_3bbb65ed155d3a0413a0815f4f5cb505 -->

<!-- START_3c2cacf86031d7ea559c116d4483ab2d -->
## Danh sách khóa học

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/train_courses" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/train_courses`


<!-- END_3c2cacf86031d7ea559c116d4483ab2d -->

<!-- START_17007f75e7c435088fa20ec16a34f3cb -->
## Sửa 1 khóa học

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/store/1/train_courses/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"title":"reiciendis","short_description":"ipsa","description":"magni"}'

```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "reiciendis",
    "short_description": "ipsa",
    "description": "magni"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/store/{store_code}/train_courses/{course_id}`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `title` | string |  optional  | tiêu đề khóa học
        `short_description` | string |  optional  | Mô tả ngắn
        `description` | string |  optional  | Mô tả dài html
    
<!-- END_17007f75e7c435088fa20ec16a34f3cb -->

<!-- START_05bdae1687ec9d04a61957e08e8041c5 -->
## Thông tin 1 khóa học

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/store/1/train_courses/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/store/{store_code}/train_courses/{course_id}`


<!-- END_05bdae1687ec9d04a61957e08e8041c5 -->

<!-- START_ae8353da92673c0722015a45864d2cf9 -->
## Xóa1 khóa học

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/store/1/train_courses/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/store/1/train_courses/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/store/{store_code}/train_courses/{course_id}`


<!-- END_ae8353da92673c0722015a45864d2cf9 -->

<!-- START_6ce99567fc56852e53a8be9b77be417e -->
## Danh sách khóa học

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/train_courses" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_courses"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/train_courses`


<!-- END_6ce99567fc56852e53a8be9b77be417e -->

<!-- START_4327e4ddf7af06f96fd2271e8d0163eb -->
## Thong tin 1 bai hco

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/customer/1/train_courses/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/train_courses/1"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`GET api/customer/{store_code}/train_courses/{course_id}`


<!-- END_4327e4ddf7af06f96fd2271e8d0163eb -->

#Đánh giá


<!-- START_ad7ca75330f29d3043de62d3a68952c4 -->
## api/customer/{store_code}/products/{product_id}/reviews
> Example request:

```bash
curl -X POST \
    "http://localhost/api/customer/1/products/1/reviews" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"product_id":"ea","order_id":"quaerat","stars":"unde","content":"et","images":"facere"}'

```

```javascript
const url = new URL(
    "http://localhost/api/customer/1/products/1/reviews"
);

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "product_id": "ea",
    "order_id": "quaerat",
    "stars": "unde",
    "content": "et",
    "images": "facere"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/customer/{store_code}/products/{product_id}/reviews`

#### Body Parameters
Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    `product_id` | string |  required  | product_id
        `order_id` | string |  required  | order_id
        `stars` | string |  required  | Số sao
        `content` | string |  required  | Họ và tên
        `images` | string |  required  | chuỗi link hình ảnh vd: http://link1.jpg|http://link2.jpg
    
<!-- END_ad7ca75330f29d3043de62d3a68952c4 -->


