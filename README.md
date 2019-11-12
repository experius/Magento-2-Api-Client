# Experius Magento 2 Api Client

    `experius/magento2apiclient`
    
This is a simple PHP lib to easy create test Rest API Calls with Magento.

## Example usage:

```
<?php
require __DIR__ . '/vendor/autoload.php';

$service = new \Experius\Magento2ApiClient\Service\RestApi();
$service->setUsername('username');
$service->setPassword('password');
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


```
