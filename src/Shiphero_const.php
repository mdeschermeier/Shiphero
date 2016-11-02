<?php
		const V1_BASE_URL 		= 'https://api-gateway.shiphero.com/v1/general-api/';

		//---- PRODUCT/INVENTORY CONSTANTS ----//
		const PRODUCT_GET 			= 'get-product/';		//q-string
		const PRODUCT_CREATE 		= 'product-creation/';
		const INVENTORY_UPDATE 		= 'update-inventory/';
		const KIT_CREATE			= 'kit-creation/';
		const KIT_REMOVE			= 'remove-kit/';
		const KIT_CLEAR				= 'clear-kit/';

		//---- ORDER CONSTANTS ----//
		const ORDERS_GET			= 'get-orders/';		//q-string
		const ORDER_GET				= 'get-order/';		//q-string
		const ORDER_CREATE			= 'order-creation/';
		const ORDER_UPDATE			= 'order-update/';
		const ORDER_CREATE_HIST 	= 'order-history-creation/';

		//---- VENDOR CONSTANTS ----//
		const VENDORS_LIST			= 'list-vendors/';		//q-string
		const VENDORS_REMOVE_PROD	= 'remove-product-from-vendor/';		//q-string
		const VENDORS_ADD_PROD		= 'add-products-to-vendor/';
		const VENDORS_CREATE		= 'vendor-create/';

		//---- FULFILLMENT STATUS CONSTANTS ----//
		const FULFILLMENT_STATUS_CREATE		= 'fulfillment-status-create/';
		const FULFILLMENT_STATUS_DELETE		= 'fulfillment-status-delete/';

		//---- PURCHASE ORDER CONSTANTS ----//
		const PO_CREATE		= 'po-creation/';
		const PO_GET		= 'get-po/';		//q-string

		//---- SHIPMENT CONSTANTS ----//
		const SHIPMENTS_GET		= 'get-shipments/';		//q-string
		const SHIPMENT_CREATE	= 'create-shipment/';

		//---- WEBHOOK CONSTANTS ----//
		const WEBHOOK_REGISTER			= 'register-webhook/';
		const WEBHOOK_GET				= 'get-webhooks/';		//q-string

?>
