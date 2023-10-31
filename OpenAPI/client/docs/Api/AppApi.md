# OpenAPI\Client\AppApi

All URIs are relative to https://virtserver.swaggerhub.com/VitexSoftware/MultiFlexi/1.0.0, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**getAppById()**](AppApi.md#getAppById) | **GET** /app/{appId}.{suffix} | Get App by ID |
| [**listApps()**](AppApi.md#listApps) | **GET** /apps.{suffix} | Show All Apps |
| [**setAppById()**](AppApi.md#setAppById) | **POST** /app/ | Create or Update Application |


## `getAppById()`

```php
getAppById($app_id, $suffix): \OpenAPI\Client\Model\App
```

Get App by ID

Returns a single App

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new OpenAPI\Client\Api\AppApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | ID of app to return
$suffix = .json; // string | force format suffix

try {
    $result = $apiInstance->getAppById($app_id, $suffix);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AppApi->getAppById: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **app_id** | **string**| ID of app to return | |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

[**\OpenAPI\Client\Model\App**](../Model/App.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listApps()`

```php
listApps($suffix): \OpenAPI\Client\Model\App[]
```

Show All Apps

All apps registeres

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new OpenAPI\Client\Api\AppApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$suffix = json; // string | force format suffix

try {
    $result = $apiInstance->listApps($suffix);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AppApi->listApps: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

[**\OpenAPI\Client\Model\App[]**](../Model/App.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `setAppById()`

```php
setAppById($app_id): \OpenAPI\Client\Model\App
```

Create or Update Application

Create or Update App by ID

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new OpenAPI\Client\Api\AppApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 56; // int | ID of app to return

try {
    $result = $apiInstance->setAppById($app_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AppApi->setAppById: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **app_id** | **int**| ID of app to return | [optional] |

### Return type

[**\OpenAPI\Client\Model\App**](../Model/App.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
