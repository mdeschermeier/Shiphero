# Shiphero
Simple PHP Wrapper for the Shiphero API

## Introduction
This is a *bare-bones* wrapper for Shiphero's REST API. While fully capable of interacting with the Shiphero interface, please note
that most methods require multi-dimensional associative arrays that mimic the structure of the JSON objects that ultimately get passed
into cURL. 

Be sure to [look at Shiphero's API Documentation](http://docs.shipheropublic.apiary.io/#) in order to understand how to structure your arrays and format your data. 

Examples are provided to assist you with understanding this wrapper, ***but it is not a replacement for understanding the actual API documentation***.

This wrapper will handle all required cURL functions and JSON encoding/decoding. In the event that additional cURL options 
need to be modified, a method is provided to [accomodate this need](#shipherosetadditionalcurloptopt-val). Additionally, once the API 
Key is set via `Shiphero::setKey($k)`, the `'token' => '<Your API Key>` element of the arrays passed into the various methods of the wrapper will not be required and may be omitted from the arrays. 

## Table of Contents
##### [Set Up](#set-up)
##### [Products](#products)
##### [Orders](#orders)
##### [Vendors](#vendors)
##### [Fulfillment Statuses](#fulfillment-statuses)
##### [Purchase Orders](#purchase-orders)
##### [Shipments](#shipments)
##### [Webhooks](#webhooks)

## Set Up

### Shiphero::setKey($k) - ***Required***
**Parameters:** $k *string*

This method sets the variable that holds your API Key. *This method MUST be executed prior to making API calls!*
```PHP
Shiphero::setKey('<your API key>');
```
### Shiphero::verifyPeer($b)
**Parameters:** $b *bool*

Toggle Host/Peer verification on cURL requests. Can be toggled as needed.
```PHP
Shiphero::verifyPeer(true); //Enables Host/Peer Verification

    /*==== or ====*/
    
Shiphero::verifyPeer(false); //Disables Host/Peer Verification
```

### Shiphero::setAdditionalCurlOpt($opt, $val)
**Parameters:** $opt *const*, $val *mixed*

In the event that additional cURL options need to be configured with your requests, they can be set via this method.
*NOTE: CURLOPT_POST, CURLOPT_HTTPHEADER, and CURLOPT_POSTFIELDS are blacklisted and will NOT be modified!*
```PHP
Shiphero::setAdditionalCurlOpt(CURLOPT_VERBOSE, true);
```

### Shiphero::preserveAdditionalCurlOpt($b)
**Parameters:** $b *bool*

If you need cURL to remember your options for concurrent requests, this method will allow for it. Once passed a true value, the 
Shiphero class will remember your cURL option settings for each request until this method is passed a false value.
*Default setting is false*
```PHP
Shiphero::setAdditionalCurlOpt(CURLOPT_VERBOSE, true); // - set a custom cURL option for next API request.

Shiphero::preserveAdditionalCurlOpt(true); // - Will remember cURL option settings until turned off

//send some requests
Shiphero::getOrderById(383484); // - cURL options remembered (CURLOPT_VERBOSE = true)
Shiphero::getProducts();        // - cURL options remembered (CURLOPT_VERBOSE = true)

Shiphero::preserveAdditionalCurlOpt(false); //Turn off cURL option rememberance and clear out stored values.

Shiphero::getVendorList(): // - Request ran with default options (CURLOPT_VERBOSE = false)
```

## Products

### Shiphero::getProduct($prod = null)
**Parameters:** $prod *array* - *optional*

Query the API for product information. This method handles both the retrieval of a single product (by sku) and the retrieval of *all* items.
```PHP
$prod = array('sku' => 12345);
$response = Shiphero::getProduct($prod) // - Get one item.

$prod = array('page'=>2, 'count'=>150);
$response = Shiphero::getProduct($prod); // - Of all items, return second page, limit 150 items per page.

$response = Shiphero::getProduct(); // - Get all items, no paging
```

### Shiphero::createProduct($sku, $prod, $type = 'simple')
**Parameters:** $sku *string*, $prod *array*, $type *string - default "simple"*

Given a sku and an array containing product information, this method will create a product. There are two types of products: `simple` and `configurable`. Configurable is generally used for creating products with variants. This method uses the `$type` parameter to distinguish
between the two. Defaults to Simple.
```PHP
$sku = 'abc-123';
$product =  array('title'=>'Sample Item Name', 'available_inventory'=>500, 'value'=>5.00, 'price'=>5000.00,
            'images'=> array(
                   array('url' => 'http://made.up.domain.com/super_rad_product.png',
                   'position' => 1),
                   array('url' => 'http://another.made.up.domain.co.uk/more-rad-product.jpg',
                   'position' => 2)
             ));
             
Shiphero::createProduct($sku, $product); // - Create the simple product.
```

To create a Configurable Product (a product with Variants), the same structure for the `$product` array should be followed. 
[Please see the API documentation](http://docs.shipheropublic.apiary.io/#reference/products/create-product/create-and-update-product-with-variants) for a complete list of parameters that can be set.

### Shiphero::addProductToUpdateInventoryQueue($prod)
**Parameters:** $prod *array*

This method will add a product to the Product Inventory Update Queue. When used in conjunction with `Shiphero::updateInventory()`,
basic product information can be updated. [Please see the API documentation](http://docs.shipheropublic.apiary.io/#reference/products/update-inventory/update-product-inventory) for a complete list of parameters that can be set.
```PHP
$product_1 = array('sku'=>'abc-123', 'quantity'=> -2, 'warehouse'=>'Secondary', 'width'=>'2.5');
Shiphero::addProductToUpdateQueue($product_1);

//Product Inventory Update Queue Contains: $product_1

$product_2 = array('sku'=>'def-456', 'new_quantity'=>200);
Shiphero::adddProductToUpdateQueue($product_2);

//Product Inventory Update Queue Now Contains: $product_1, $product_2
```

### Shiphero::updateInventory()
**Parameters:** 

This method will update the inventory of all products in the Product Inventory Update Queue. This method will then empty the Product Inventory Update Queue.
```PHP
// Define Product Update Information
$product_1 = array('sku'=>'abc-123', 'quantity'=> -2, 'warehouse'=>'Secondary', 'width'=>'2.5');
$product_2 = array('sku'=>'def-456', 'new_quantity'=>200);

//Add Products to Update Queue
Shiphero::addProductToUpdateQueue($product_1);
Shiphero::addProductToUpdateQueue($product_2);

//Update Products in Queue
Shiphero::updateInventory();
```

### Shiphero::addKitToCreationQueue($kit)
**Parameters:** $kit *array*

Add a kit to the Kit Creation Queue. Used in conjunction with `Shiphero::createKits()`.
```PHP
$kit = array(
            'parent_sku' => 'kit-sku-123',
            'components' => array(
                                 'sku'=>'product_1-sku-123',
                                 'quantity'=> 3
                            ),
                            array(
                                 'sku'=>'product_2-sku-456',
                                 'quantity'=>1
                            )
            );
     
Shiphero::addKitToCreationQueue($kit);

//Kit Creation Queue Contains: $kit (3 of 'product_1-sku-123, 1 of 'product_2-sku-456)

$another_kit = array(
            'parent_sku' => 'kit-sku-456',
            'components' => array(
                                 'sku'=>'product_1-sku-123',
                                 'quantity'=> 10
                            ),
                            array(
                                 'sku'=>'product_2-sku-456',
                                 'quantity'=>3
                            )
            );
     
Shiphero::addKitToCreationQueue($another_kit);

//Kit Creation Queue Contains: $kit (3 of 'product_1-sku-123, 1 of 'product_2-sku-456)
                               $another_kit (10 of 'product_1-sku-123, 3 of 'product_2-sku-456')
```

### Shiphero::createKits()
**Parameters:**

Creates a kit for each kit in the Kit Creation Queue. Clears Kit Creation Queue when finished. Example below continued from [Shiphero::addKitToCreationQueue($kit) Example](#shipheroaddkittocreationqueuekit). 
```PHP
//Kit Creation Queue Contains: $kit (3 of 'product_1-sku-123, 1 of 'product_2-sku-456)
                               $another_kit (10 of 'product_1-sku-123, 3 of 'product_2-sku-456')
                               
Shiphero::createKits();  // - Kits are now created in Shiphero.

//Kit Creation Queue Contains: Nothing (empty)

```

## Orders
Order Stuff

## Vendors
Vendor stuff

## Fulfillment Statuses
Fulfillment stuff

## Purchase Orders
PO Stuff

## Shipments
Shipment stuff

## Webhooks
Webhook stuff
