# Shiphero
Simple PHP Wrapper for the Shiphero API

## Introduction
This is a *bare-bones* wrapper for Shiphero's REST API. While fully capable of interacting with the Shiphero interface, please note
that most methods require multi-dimensional associative arrays that mimic the structure of the JSON objects that ultimately get passed
into cURL. 

This wrapper will handle all required cURL functions and JSON encoding/decoding. In the event that additional cURL options 
need to be modified, a method is provided to [accomodate this need](#shipherosetadditionalcurloptopt-val).

To make a short story even shorter: [Look at Shiphero's API Documentation](http://docs.shipheropublic.apiary.io/#) in order to understand
how to structure your arrays and format your data. 

Examples are provided to assist you with understanding this wrapper, ***but it is not a replacement for understanding the actual API documentation***.

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
There really isn't too much in the way of setting up, but you *must* set the API key before using this class!

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

If you need cURL to remember your options for each request, this method will allow for it. Once passed a true value, the Shiphero class
will remember your cURL option settings for each request until this method is passed a false value. *Default state is false*
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
More stuff

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
