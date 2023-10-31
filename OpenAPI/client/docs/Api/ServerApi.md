# OpenAPI\Client\ServerApi

All URIs are relative to https://virtserver.swaggerhub.com/VitexSoftware/MultiFlexi/1.0.0, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**getServerById()**](ServerApi.md#getServerById) | **GET** /server/{serverId}.{suffix} | Get Server by ID |
| [**listServers()**](ServerApi.md#listServers) | **GET** /servers | Show All Servers |
| [**setServerById()**](ServerApi.md#setServerById) | **POST** /server/ | Create or Update Server record |


## `getServerById()`

```php
getServerById($server_id, $suffix): \OpenAPI\Client\Model\Server
```

Get Server by ID

Returns a single Server

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new OpenAPI\Client\Api\ServerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$server_id = 56; // int | ID of app to return
$suffix = json; // string | force format suffix

try {
    $result = $apiInstance->getServerById($server_id, $suffix);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServerApi->getServerById: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **server_id** | **int**| ID of app to return | |
| **suffix** | **string**| force format suffix | [default to &#39;html&#39;] |

### Return type

[**\OpenAPI\Client\Model\Server**](../Model/Server.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listServers()`

```php
listServers(): \OpenAPI\Client\Model\Server[]
```

Show All Servers

All Server servers registered

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new OpenAPI\Client\Api\ServerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->listServers();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServerApi->listServers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\Server[]**](../Model/Server.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `setServerById()`

```php
setServerById($server_id): \OpenAPI\Client\Model\Server
```

Create or Update Server record

Create or Update single Server record

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure HTTP basic authorization: basicAuth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()
              ->setUsername('YOUR_USERNAME')
              ->setPassword('YOUR_PASSWORD');


$apiInstance = new OpenAPI\Client\Api\ServerApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$server_id = 56; // int | ID of app to return

try {
    $result = $apiInstance->setServerById($server_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ServerApi->setServerById: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **server_id** | **int**| ID of app to return | [optional] |

### Return type

[**\OpenAPI\Client\Model\Server**](../Model/Server.md)

### Authorization

[basicAuth](../../README.md#basicAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
