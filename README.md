# Experius Magento 2 Api SDK Client

    `experius/magento2apiclient`
    
This is a simple PHP SDK lib to easy create Rest or Soap API Calls to Magento.

## REST Example usage:

```php
<?php

// Git clone
// require_once ('Service/RestApi.php');

// Install via Composer
require __DIR__ . '/vendor/autoload.php';

$service = new \Experius\Magento2ApiClient\Service\RestApi();

// Integration Access Token
$service->setToken('12341234123423423134123413243124');

// Admin User Token Integration 
//$service->setUsername('username');
//$service->setPassword('password');rd');

$service->setUrl('https://www.example.com/index.php/rest/%storecode/V1/');

// OPTIONAL > default = all
$service->setStoreCode('default');

$service->init();

// Create product
$data = json_decode('
{
    "product": {
        "custom_attributes": [
                    {
                "attribute_code": "url_key",
                "value": "experius-example-product-new"
            }
        ],
        "name": "Experius Example Product",
        "weight": 1.2,
        "visibility": 4,
        "extension_attributes": {
            "website_ids": [
                "1"
            ],
            "stock_item": {
                "is_in_stock": true
            }
        },
        "sku": "experius-example-product",
        "status": 1,
        "type_id": "simple",
        "attribute_set_id": "4",
        "price": 10
    }
}');
$result = $service->call('products', $data, 'POST');
var_dump($result);

// update product
$data = json_decode('
{
    "product": {
        "custom_attributes": [
                    {
                "attribute_code": "url_key",
                "value": "experius-example-product-new"
            }
        ],
        "name": "Experius Example Product",
        "weight": 1.2,
        "visibility": 4,
        "extension_attributes": {
            "website_ids": [
                "1"
            ],
            "stock_item": {
                "is_in_stock": true
            }
        },
        "sku": "experius-example-product",
        "status": 1,
        "type_id": "simple",
        "attribute_set_id": "4",
        "price": 10
    }
}');
$result = $service->call('products/experius-example-product', $data, 'PUT');
var_dump($result);


// Retrieve product
$result = $service->call('products/experius-example-product');
var_dump($result);

$dataArray = [
    'searchCriteria' => [
        'pageSize' => 10
    ]
];

$result = $service->call('products', $dataArray);
var_dump($result);

```

## SOAP Example usage:

```php
<?php

// Git clone
// require_once ('Service/SoapApi.php');

// Install via Composer
require __DIR__ . '/vendor/autoload.php';

$service = new \Experius\Magento2ApiClient\Service\SoapApi();

// Integration Access Token
$service->setToken('12341234123423423134123413243124');

// Admin User Token Integration 
//$service->setUsername('username');
//$service->setPassword('password');

$service->setUrl('https://www.example.com');

$service->setStoreCode('default');
$service->init();

$serviceArgs = [
    'searchCriteria' => [
        'filterGroups' => [
            [
                'filters' => [
                        [
                            'field' => 'increment_id',
                            'value' => '000000002',
                            'condition_type' => 'eq'
                        ]
                    ]
            ]
        ]
    ]
];

$result = $service->call(
    'salesOrderRepositoryV1',
    'salesOrderRepositoryV1GetList',
    $serviceArgs
);

print_r($result);

$productData = array(
    'sku'               => 'ZZ-TEST 2',
    'name'              => 'testproduct2',
    'visibility'        => 4,
    'typeId'           => 'simple',
    'price'             => 45,
    'status'            => 1,
    'attributeSetId'  => '4',
    'weight'            => 1,
    'customAttributes' => [
        [
            "attributeCode" => "<your_customer_attribute_code>",
            "value" => "1"
        ]
    ]
);

$result = $service->call(
    'catalogProductRepositoryV1',
    'catalogProductRepositoryV1save',
    ['product' => $productData]
);

print_r($result);
```
