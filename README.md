# Shiphero
Simple PHP Wrapper for the Shiphero API

***IMPORTANT: I am not currently, nor previously have been, affiliated with Shiphero or any of Shiphero's professional associates. This wrapper is purely my own work and carries no endorsements other than my own.***

## Installation
The easiest way to install this package is via composer. Use the following to install this package:
`composer require mdeschermeier/shiphero`

Alternatively, these files can be downloaded and stored for use in any location you see fit, so long as `Shiphero.php` and `Shiphero_const.php` remain in the same directory.

## Introduction
This is a *bare-bones* wrapper for Shiphero's REST API. While fully capable of interacting with the Shiphero interface, please note
that most methods require multi-dimensional associative arrays that mimic the structure of the JSON objects that ultimately get passed
into cURL. 

Be sure to [look at Shiphero's API Documentation](http://docs.shipheropublic.apiary.io/#) in order to understand how to structure your arrays and format your data. 

Examples are provided to assist you with understanding this wrapper, ***but it is not a replacement for understanding the actual API documentation***.

This wrapper will handle all required cURL functions and JSON encoding/decoding. In the event that additional cURL options 
need to be modified, a method is provided to [accomodate this need](#shipherosetadditionalcurloptopt-val). Additionally, once the API 
Key is set via `Shiphero::setKey($k)`, the `'token' => '<Your API Key>'` element of the arrays passed into the various methods of the wrapper will not be required and should be omitted from the arrays. 

## Table of Contents
##### [Set Up](#set-up)
##### [Product](#products)
##### [Order](#orders)
##### [Vendor](#vendors)
##### [Fulfillment Status](#fulfillment-statuses)
##### [Purchase Order](#purchase-orders)
##### [Shipment](#shipments)
##### [Webhook](#webhooks)
##### [Contact](#contact-me)

## Set Up

### Shiphero::setKey($k) - ***Required***
**Parameters:** $k *string*

This method sets the variable that holds your API Key. *This method MUST be executed prior to making API calls!*
```PHP
Shiphero::setKey('<your API key>');
```
### Shiphero::verifyPeer($b)
**Parameters:** $b *bool*

Toggle Host/Peer verification on cURL requests. Can be toggled as needed. *set to true by default*
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

Query the API for product information. This method handles both the retrieval of a single product (by sku) and the retrieval of *all* items using default settings.
```PHP
$prod = array('sku' => 12345);
$response = Shiphero::getProduct($prod) // - Get one item.

$prod = array('page'=>2, 'count'=>150);
$response = Shiphero::getProduct($prod); // - Of all items, return second page, limit 150 items per page.

$response = Shiphero::getProduct(); // - Get all items, no paging (returns the first 50 products).
```

### Shiphero::createProduct($sku, $prod, $type = 'simple')
**Parameters:** $sku *string*, $prod *array*, $type *string - default "simple"*

Given a sku and an array containing product information, this method will create a product. There are two types of products: `simple` and `configurable`. Configurable is generally used for creating products with variants. This method uses the `$type` parameter to distinguish
between the two. Defaults to `simple`.
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

### Shiphero::addProductToUpdateQueue($prod)
**Parameters:** $prod *array*

This method will add a product to the Product Inventory Update Queue. When used in conjunction with `Shiphero::updateInventory()`,
basic product information can be updated. [Please see the API documentation](http://docs.shipheropublic.apiary.io/#reference/products/update-inventory/update-product-inventory) for a complete list of parameters that can be set.
```PHP
$product_1 = array('sku'=>'abc-123', 'quantity'=> -2, 'warehouse'=>'Secondary', 'width'=>'2.5');
Shiphero::addProductToUpdateQueue($product_1);

//Product Inventory Update Queue Contains: $product_1

$product_2 = array('sku'=>'def-456', 'new_quantity'=>200);
Shiphero::adddProductToUpdateInventoryQueue($product_2);

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
	    			array(
                                 'sku'=>'product_1-sku-123',
                                 'qty'=> 3
                            	),
                            	array(
                                 'sku'=>'product_2-sku-456',
                                 'qty'=>1
                            	)
			    )
            );
     
Shiphero::addKitToCreationQueue($kit);

//Kit Creation Queue Contains: $kit (3 of 'product_1-sku-123, 1 of 'product_2-sku-456')

$another_kit = array(
            'parent_sku' => 'kit-sku-456',
            'components' => array(
	    			array(
                                 'sku'=>'product_1-sku-123',
                                 'qty'=> 10
                            	),
                            	array(
                                 'sku'=>'product_2-sku-456',
                                 'qty'=>3
                            	)
			   )
            );
     
Shiphero::addKitToCreationQueue($another_kit);

//Kit Creation Queue Contains: $kit (3 of 'product_1-sku-123, 1 of 'product_2-sku-456')
//                             $another_kit (10 of 'product_1-sku-123, 3 of 'product_2-sku-456')
```

### Shiphero::createKits()
**Parameters:**

Creates a kit for each kit in the Kit Creation Queue. Clears Kit Creation Queue when finished. Example below continued from [Shiphero::addKitToCreationQueue($kit) Example](#shipheroaddkittocreationqueuekit). 
```PHP
//Kit Creation Queue Contains: $kit (3 of 'product_1-sku-123, 1 of 'product_2-sku-456')
//                             $another_kit (10 of 'product_1-sku-123, 3 of 'product_2-sku-456')
                               
Shiphero::createKits();  // - Kits are now created in Shiphero.

//Kit Creation Queue Contains: Nothing (empty)

```

### Shiphero::addToRemoveKitComponentQueue($kit_sku, $component_skus)
**Parameters:** $kit_sku *string*, $component_skus *array*

Adds a component or list of components to be removed from a kit. `$kit_sku` is the sku of the kit you want to remove components from.
`$component_skus` is an array of component skus.
```PHP
// Kit 1 (sku 'abc-123') Components (skus): p-123, p-456, p-789
// Kit 2 (sku 'def-456') Components (skus): p-456, p-111, p-222

// - Queue Removal of Item Skus 'p-456' and 'p-789' from Kit 1
Shiphero::addToRemoveKitComponentQueue('abc-123', array('p-456', 'p-789')); 

// - Queue Removal of Item Sku 'p-111' from Kit 2
Shiphero::addToRemoveKitComponentQueue('def-456', array('p-111'));
```

### Shiphero::removeKitComponents()
**Parameters:**

Removes queued items from specified kits. Queue is cleared when this method is finished. The example below assumes a continuation of the [Shiphero::addToRemoveKitComponentQueue($kit_sku, $component_skus) Example](#shipheroaddtoremovekitcomponentqueuekit_sku-component_skus)
```PHP
// Kit 1 (sku 'abc-123') Components (skus): p-123, p-456, p-789
// Kit 2 (sku 'def-456') Components (skus): p-456, p-111, p-222

//Removal Queue:
//      Kit 1: p-456, p-789
//      Kit 2: p-111

Shiphero::removeKitComponents();

// Kit 1 (sku 'abc-123') Components (skus): p-123
// Kit 2 (sku 'def-456') Components (skus): p-456, p-222

//Removal Queue: 
//      Nothing (empty)
```

### Shiphero::clearKit($sku)
**Parameters:** $sku *string*

This method removes all components from a kit, given the kit's sku.
```PHP
// Kit 1 (sku 'abc-123') Components (skus): p-123, p-456, p-789

Shiphero::clearKit('abc-123');

// Kit 1 (sku 'abc-123') Components (skus): Nothing (empty)
```
## Orders

### Shiphero::getOrders($filter, $all_stores=0)
**Parameters:** $filter *array*, $all_stores *int*

Returns all orders that fit the parameters set in the `$filter` array. Setting `$all_stores` to `1` will return all orders associated
with the current Shiphero account. Setting `$all_stores` to `0` will only return orders associated with the store tied to the API Key 
being used. *Default `$all_stores` value is `0`.*

Please see the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/orders/get-orders/get-orders) for more information on the parameters being set in the `$filter` array.
```PHP
$filter = array('page'=>1, 'from'=>'2016-9-1', 'to'=>'2016-9-28');

//Return all orders matching filter for current store
Shiphero::getOrders($filter)

//return all orders matching filter for ALL stores
Shiphero::getOrders($filter, 1);
```

### Shiphero::getOrderById($id)
**Parameters:** $id *string*

Return an order by ID.
```PHP
//Returns order with ID: 12345
Shiphero::getOrderById('12345');
```

### Shiphero::getOrder($order_num)
**Parameters:** $order_num *string*

Return an order by order number.
```PHP
//Returns order number: 427895-Fri-1
Shiphero::getOrder('427895-Fri-1');
```

### Shiphero::createOrder($order)
**Parameters:** $order *array*

This method creates an order. Due to the size of the array needing to be passed in, please see the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/orders/create-order/create-order) for a detailed list of all required and optional parameters. The following example shows the basic structure of the array.

*Note: There are plans to add methods to this class to aid in the construction of an order, so as to make this process less painful.*
```PHP
// Define the order
$order = array(
    'email' => 'name@email.com,
    'line_items' => array(
        array(
            'sku' => 'abc-123',
            'name' => 'Item 1', 
            ...
        ),
        array(
            'sku' => 'def-456',
            'name' => 'Item 2',
            ...
        )
    ),
    'note_attributes' => array(
        ...
    ),
    'shipping_address' => array(
        'address1' => '123 Address Ave',
        'address2' => 'Apt 202',
        'city'     => 'Anytown',
        ...
    ),
    'order_id' => '1234567890',
    ...
);

// Create the Order
Shiphero::createOrder($order);
```

### Shiphero::updateOrder($order)
**Parameters:** $order *array*

Similar to the `Shiphero::createOrder($order)` method, this method updates order information. As with the `Shiphero::createOrder($order)` method, please refer to the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/orders/update-order/update-order) for a detailed list of parameters. 

Additionally, the only *required* parameters are the `'order_number'` and the parameters you wish to update.
```PHP
$order = array(
    'order_number' => '1234-123',
    ...
);

Shiphero::updateOrder($order);
```

### Shiphero::createOrderHistory($hist)
**Parameters:** $hist *array*

This method adds an order history to a given order. Please note that `'order_id'` is *not* the same as `'order_number'`.
```PHP
$hist = array(
    'order_id' => 123456,
    'username' => 'user@email.com',
    'information' => 'A note on the order history'
);

Shiphero::createOrderHistory($hist);
```

## Vendors

### Shiphero::getVendorList()
**Parameters:**

This method returns the full list of vendors.
```PHP
$vendors = Shiphero::getVendorList();
```

### Shiphero::createVendor($vendor)
**Parameters:** $vendor *array*

Creates a vendor. See the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/vendors/create-vendor/create-vendor) for a complete list of parameters that can be set.
```PHP
$vendor = array('vendor_name' => 'Wyld Stallyn Guitars', // - vendor_name is the only required parameter.
                'vendor_city' => 'San Dimas',
                'vendor_state' => 'California'
          );
          
Shiphero::createVendor($vendor);

```

### Shiphero::addProductToVendorQueue($data)
**Parameters:** $data *array*
Adds a product to the Vendor Queue. When used in conjunction with `Shiphero::addProductsToVendor()`, products can be added to vendors. For a full list of required/optional parameters, see the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/vendors/add-products-to-vendors/add-products-to-vendors).

```PHP
$product1 = array('sku' => 'abc-123', 'vendor_id' => '1234');
Shiphero::addProductToVendorQueue($product1);

// Products in Vendor Queue:
//          $product1:
//              sku: '1234'
//              vendor_id: '1234'
//

$product2 = array('sku' => '5678', 'vendor_id' => '9012', 'price' => 40.99, 'manufacturer_sku' => 'mSku-494');
Shiphero::addProductToVendorQueue($product2);
// Products in Vendor Queue:
//          $product1:
//              sku: '1234'
//              vendor_id: '1234'
//
//          $product2:
//              sku: '5678'
//              vendor_id: '9012'
//              price: 40.99
//              manufacturer_sku: 'msku-494'
//
```

### Shiphero::addProductsToVendors()
**Parameters:**

Takes products from the Vendor Queue and adds them to vendors. Once this method is complete, the Vendor Queue is emptied. Example below is a continuation of the example for [Shiphero::addProductToVendorQueue($data)](#shipheroaddproducttovendorqueuedata).

```PHP
// Products in Vendor Queue:
//          $product1:
//              sku: '1234'
//              vendor_id: '1234'
//
//          $product2:
//              sku: '5678'
//              vendor_id: '9012'
//              price: 40.99
//              manufacturer_sku: 'msku-494'
//

Shiphero::addProductsToVendors();

// Products in Vendor Queue:
//          Nothing (empty)
//
```

### Shiphero::removeProductFromVendor($vendor_id, $sku)
**Parameters:** $vendor_id *int*, $sku *string*

This method will remove a product from a vendor.
```PHP

//Vendor ID: 123456
//      contains items (skus):
//          'abc-123'
//          'def-456'
//          'ghi-789'
//

Shiphero::removeProductFromVendor(123456, 'def-456');

//Vendor ID: 123456
//      contains items (skus):
//          'abc-123'
//          'ghi-789'
//
```

## Fulfillment Statuses

### Shiphero::createFulfillmentStatus($status)
**Parameters:** $status *string*

Creates a fulfillment status.
```PHP
Shiphero::createFulfillmentStatus('My Custom Status');
```

### Shiphero::deleteFulfillmentStatus($status)
**Parameters:** $status *string*

Deletes a fulfillment status.
```PHP
Shiphero::deleteFulfillmentStatus('My Custom Status');
```

## Purchase Orders

### Shiphero::addProductToPoQueue($line_item)
**Parameters:** $line_item *array*

This method will add line items to the PO Line Item Queue. The queue (similar in functionality to all other queues) will store line items for a PO until the method `Shiphero::createPO($po)` is called.

***NOTE:*** *Per the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/purchase-orders/create-po/create-po) (specifically the parameters prefixed by `line_items[n]`),* ***BOTH `id` and `sku` parameters must be set in addition to all other required parameters.*** 
```PHP
	$line_item1 = array('id'=>3456789012, 'sku'=>'abc-123', 'name'=>'Test1',
						'price'=>'25.00', 'quantity'=>30, 'sell_ahead'=>0);

    //PO Line Item Queue Contains: $line_item1

	$line_item2 = array('id'=>2345678901, 'sku'=>'def-456', 'name'=>'Test2',
						'price'=>'0.00', 'quantity'=>40, 'sell_ahead'=>0);
    
    //PO Line Item Queue Contains: $line_item1, $line_item2
    
    $line_item3 = array('id'=>1234567890, 'sku'=>'ghi-789', 'name'=>'Item 3',
						'price'=>'25.00', 'quantity'=>20, 'sell_ahead'=>0);
                        
    //PO Line Item Queue Contains: $line_item1, $line_item2, $line_item3
    
```

### Shiphero::createPO($po)
**Parameters:** $po *array*

Given basic information about a Purchase Order in the form of an array, this method will construct a Purchase Order using the information provided both in the `$po` array and PO Line Item Queue (see [Shiphero::addProductToPoQueue($line_item)](#shipheroaddproducttopoqueueline_item)). For a complete list of required/optional parameters, please see the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/purchase-orders/create-po/create-po), and note that the `line_items` parameter in its entirety *does not pertain to this method.*

Upon completion, this method clears the PO Line Item Queue.

*NOTE: While optional, if the `expected_date` parameter is not set, Shiphero defaults to setting the date as `11/30/-0001`. In Shiphero, this is an invalid date, and when updating PO's the system will reject the updates until this date is manually corrected. Therefore, this wrapper automatically sets the `expected_date` parameter to the current date if the parameter is not already set.*

The following example is a continuation of the example for [Shiphero::addProductToPoQueue($line_item)](#shipheroaddproducttopoqueueline_item). 
```PHP
    ...
    Shiphero::addProductToPoQueue($line_item1);
	Shiphero::addProductToPoQueue($line_item2);
	Shiphero::addProductToPoQueue($line_item3);
    
    //PO Line Item Queue Contains: $line_item1, $line_item2, $line_item3
    
    $po = array('email'=>'example@email.com', 'vendor_name'=>'A. Vendor, Inc', 'created_at'=>'2016-10-03 10:42:25');
    
    Shiphero::createPO($po);
    
    //PO Line Item Queue Contains: Nothing (empty)
```

### Shiphero::getPO($po_id)
**Parameters:** $po_id *int*

This method retrieves information about a given PO.
```PHP
    
    $po_1 = Shiphero::getPO(1);     // - Return information from PO ID: 1
    $po_2 = Shiphero::getPO(2);     // - Return information from PO ID: 2
    $po_3 = Shiphero::getPO(456789); // - Return information from PO ID: 456789
    
```
## Shipments

### Shiphero::getShipments($filter)
**Parameters:** $filter *array*

This method returns a collection of Shipments based on filtering criteria. See the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/shipment/get-shipments/get-shipments) for a full list of parameters.

*NOTE: All returned collections come back from the Shiphero API as a paginated response. Do not forget to set your page parameter!*

```PHP
    
    //Get page 1 of all shipments from Sept. 28, 2016
    $filter = array('page'=>1, 'from'=>'2016-09-28', 'to'=>'2016-09-28');
    $collection1 = Shiphero::getShipments($filter);
    
    //Get page 6 of all shipments from Sept. 28, 2016 to Oct. 2, 2016
    $filter = array('page'=>6, 'from'=>'2016-09-28', 'to'=>'2016-10-2');
    $collection2 = Shiphero::getShipments($filter);
    
```

### Shiphero::createShipment($shipment)
**Parameters:** $shipment *array*

This method creates a Shipment. As the input array is quite long, viewing the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/shipment/create-shipment/create-shipment) is highly encouraged.

*NOTE: Future plans include breaking this process up into smaller chunks to make things more easily managable.*

```PHP

    $shipment = array('warehouse'=>'Primary', 
                      'profile'=>'default',
                      'shipment'=>array(
                            'shipment_id'=>'123456',
                            'order_id'=>'78901',
                            'carrier'=>'UPS',
                            'shipping_method'=>'UPS GROUND',
                            'tracking_number'=>'abcde1234567890',
                            'cost'=>10.76,
                            'dimensions'=>array(
                                'weight'=>20,
                                'length'=>5,
                                'width'=>8,
                                'height'=>4
                            ),
                            'address'=>array(
                                'name'=>'John Doe',
                                'address1'=>'123 Anywhere St.',
                                'address2'=>'Apt 2',
                                'city'=>'Anytown',
                                'state'=>'CA',
                                'postal_code'=>'12345',
                                'country'=>'US'
                            ),
                            'label'=>array(
                                'pdf'=>'http://example.url.to/pdf/file.pdf',
                                'png'=>array(
                                    'http://example.url.to/png/page1.png',
                                    'http://example.url.to/png/page2.png'
                                )
                            ),
                            'customs_info'=>array(
                                'http://example.url.to/customs/info/page1.png',
                                'http://example.url.to/customs/info/page2.png'
                            )
                        )
                    );
                    
    Shiphero::createShipment($shipment);
    
```
                            
## Webhooks

### Shiphero::registerWebhook($hook_info)
**Parameters:** $hook_info *array*

This method will register a webhook with the Shiphero API. Take special note of the parameters `name` and `source` in the [Shiphero API Documentation](http://docs.shipheropublic.apiary.io/#reference/webhooks/register-webhook/register-webhook), as these are pre-defined values.

*NOTE: Webhooks for `Capture Payment` and `Return Update` are still in development at Shiphero. This file will be updated to reflect the changes when they become available. Additionally, the Shiphero API requires a response header from any created webhooks, else the transaction will not be complete!* 

```PHP
    //register a webhook to post to URL 'http://my.owned.url.for/webhooks/' every time inventory is updated on my bigcommerce store.
    $hook_info = array('name'=>'Inventory Update', 'url'=>'http://my.owned.url.for/webhooks/', 'source'=>'bigcommerce');
    Shiphero::registerWebhook($hook_info);
```

### Shiphero::getWebhooks()
**Parameters:**

This method returns a list of all currently created webhooks.

```PHP
    
    $webhook_list = Shiphero::getWebhooks();
    
```


## Contact Me
Please feel free to contact me at miked.github@gmail.com with any questions or concerns. Please include the words "Shiphero API Wrapper" in the subject line, and I will be sure to respond as soon as possible.

Additionally, please log any issues/bugs with GitHub's issue tracker.
