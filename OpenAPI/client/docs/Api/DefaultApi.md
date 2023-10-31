# OpenAPI\Client\DefaultApi

All URIs are relative to https://virtserver.swaggerhub.com/VitexSoftware/MultiFlexi/1.0.0, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**getApiIndex()**](DefaultApi.md#getApiIndex) | **GET** /index.{suffix} | Endpoints listing |
| [**loginSuffixGet()**](DefaultApi.md#loginSuffixGet) | **GET** /login.{suffix} | Return User&#39;s token |
| [**loginSuffixPost()**](DefaultApi.md#loginSuffixPost) | **POST** /login.{suffix} | Return User&#39;s token |
| [**pingSuffixGet()**](DefaultApi.md#pingSuffixGet) | **GET** /ping.{suffix} | Server heartbeat operation |
| [**rootGet()**](DefaultApi.md#rootGet) | **GET** / | Redirect to index |


## `getApiIndex()`

```php
getApiIndex($suffix)
```

Endpoints listing

Show current API

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$suffix = json; // string | force format suffix

try {
    $apiInstance->getApiIndex($suffix);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getApiIndex: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `loginSuffixGet()`

```php
loginSuffixGet($username, $password, $suffix)
```

Return User's token

Send login & password to obtain oAuth token

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$username = 'username_example'; // string | existing user name
$password = 'password_example'; // string | existing user password
$suffix = json; // string | force format suffix

try {
    $apiInstance->loginSuffixGet($username, $password, $suffix);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->loginSuffixGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **username** | **string**| existing user name | |
| **password** | **string**| existing user password | |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `loginSuffixPost()`

```php
loginSuffixPost($username, $password, $suffix)
```

Return User's token

Send login & password to obtain oAuth token

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$username = 'username_example'; // string | existing user name
$password = 'password_example'; // string | existing user password
$suffix = json; // string | force format suffix

try {
    $apiInstance->loginSuffixPost($username, $password, $suffix);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->loginSuffixPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **username** | **string**| existing user name | |
| **password** | **string**| existing user password | |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `pingSuffixGet()`

```php
pingSuffixGet($suffix)
```

Server heartbeat operation

This operation shows how to override the global security defined above, as we want to open it up for all users.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$suffix = json; // string | force format suffix

try {
    $apiInstance->pingSuffixGet($suffix);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->pingSuffixGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `rootGet()`

```php
rootGet()
```

Redirect to index

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $apiInstance->rootGet();
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->rootGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
