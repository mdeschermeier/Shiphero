# Shiphero
Simple PHP Wrapper for the Shiphero API

## Introduction
This is a *bare-bones* wrapper for Shiphero's REST API. While fully capable of interacting with the Shiphero interface, please note
that most methods require multi-dimensional associative arrays that mimic the structure of the JSON objects that ultimately get passed
into cURL. 

This wrapper will handle all required cURL functions and JSON encoding/decoding. In the event that additional cURL options 
need to be modified, a method is provided to [accomodate this need](#shiphero-setadditionalcurlopt-opt-val).

To make a short story even shorter: [Look at Shiphero's API Documentation](http://docs.shipheropublic.apiary.io/#) in order to understand
how to structure your arrays and format your data. 

Examples are provided to assist you with understanding this wrapper, ***but it is no excuse for not understanding the actual API***.

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
There really isn't too much in the way of setting up. There are only three methods that fit into this category, with just one of them
being required.

### Shiphero::setKey($k) - ***Required***
**Parameters:** $k *string*
This method sets the variable that holds your API Key. *This method MUST be executed prior to making API calls!*
```PHP
Shiphero::setKey('<your API key'>);
```
### Shiphero::verifyPeer($b)
**Parameters:** $b *bool*
Toggle Host/Peer verification on cURL requests. Can be toggled as needed.
```PHP
Shiphero::verifyPeer(true); //Enables Host/Peer Verification

    /*==== or ====*/
    
Shiphero::verifyPeer(false); //Disables Host/Peer Verification
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
