# CurlUtil

CurlUtil.php 是对 PHP CURL 的封装。使用单例模式，封装了最常用的 http/https 的 get/post 请求，内置支持 application/x-www-form-urlencoded 及 application/json 数据格式，便利的 header 配置，ssl 链式支持等

## Usage

* Init
```php
const API_URL = '';

$postData = [
  'page': 1,
  'size': 15
  // more data
];

$curl = CurlUtil::getInstance();
```

* Get
```php
$curl->get(API_URL);
```

* Post （formData ）
```php
$curl->get(API_URL);
```

* Post （jsonData）
```php
$curl->post(API_URL, $postData, true);
```

* Https
```php
$curl->ssl()->get(API_URL);
$curl->ssl()->post(API_URL, $formData);
$curl->ssl()->post(API_URL, $postData, true);
```

