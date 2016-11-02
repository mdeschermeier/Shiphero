<?php
	require_once "Shiphero_const.php";

	class Shiphero{

		protected static $verifyHost = 2;
		protected static $verifyPeer = true;
		protected static $key;
		protected static $ch;
		protected static $blacklist 				= array(CURLOPT_POST, CURLOPT_HTTPHEADER, CURLOPT_POSTFIELDS);
		protected static $additional_options 		= array();
		protected static $preserve_options 			= false;
		protected static $productUpdateQueue 		= array();
		protected static $kitCreationQueue 			= array();
		protected static $removeKitComponentQueue 	= array();
		protected static $addProductToVendorQueue 	= array();
		protected static $poLineItemQueue 			= array();

		//====================================//
		//========= PRIVATE METHODS ==========//
		//====================================//
		private function __construct(){
			// Dummy Constructor
		}

		static protected function init(){
			self::$ch = curl_init();
			curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt(self::$ch, CURLOPT_HEADER, FALSE);
			curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, self::$verifyHost);
			curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, self::$verifyPeer);
			self::setAdditionalCurlOptions();
		}

		static protected function setAdditionalCurlOptions(){
			if (!empty(self::$additional_options)){
				foreach(self::$additional_options as $opt => $val){
					curl_setopt(self::$ch, $opt, $val);
				}

				if (!self::$preserve_options){
					self::$additional_options = array();
				}
			}
		}

		static protected function cExec(){
			$res = curl_exec(self::$ch);
			curl_close(self::$ch);
			return json_decode($res);
		}

		static protected function buildEndpointURL($endpoint, $params = null, $needsToken = false){
			$url = V1_BASE_URL.$endpoint;
			if ($needsToken){
				$url .= '?token='.self::$key;
			}
			if ($params !== null){
				if (!$needsToken){
					$url .= '?';
				}
				foreach($params as $param => $value){
					$url .= '&'.$param.'='.$value;
				}
			}
			curl_setopt(self::$ch, CURLOPT_URL, $url);
		}

		static protected function getTruncProductResponse($p, $r){
			if ($p !== null){
				return $r->products->results[0];
			}
			return $r->products->results;
		}

		static protected function postRequestSetup($endpoint){
			self::init();
			self::buildEndpointUrl($endpoint);
			curl_setopt(self::$ch, CURLOPT_POST, true);
			curl_setopt(self::$ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/json"
			));
		}

		static protected function cSubmitPost($p){
			var_dump(json_encode($p));
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, json_encode($p));
			return self::cExec();
		}

		static protected function clearQueue($queue_name){
			self::${$queue_name} = array();
		}

		static protected function setPOExpectedDate($po){
			if (!isset($po['expected_date'])){
				return date('Y-m-d');
			}
			return $po['expected_date'];
		}

		//====================================//
		//========== PUBLIC METHODS ==========//
		//====================================//

		static public function preserveAdditionalCurlOpt($b){
			self::$preserve_options = (bool)$b;

			if (!$b){
				self::$additional_options = array();
			}
		}

		static public function setAdditionalCurlOpt($opt, $val){
			if (!in_array($opt, self::$blacklist)){
				self::$additional_options[$opt] = $val;
			}
		}

		static public function setKey($k){
			self::$key = $k;
		}

		static public function verifyPeer($bool){
			if ($bool){
				self::$verifyPeer = true;
				self::$verifyHost = 2;
			}else{
				self::$verifyPeer = false;
				self::$verifyHost = 0;
			}
		}

		//---------------------------------------//
		//---- Products, Kits, and Inventory ----//
		//---------------------------------------//
		static public function getProduct($prod = null){
			self::init();
			self::buildEndpointUrl(PRODUCT_GET, $prod, true);
			return self::cExec();
		}

		static public function updateInventory($prod){
			self::postRequestSetup(INVENTORY_UPDATE);
			$res = self::cSubmitPost(array('token'=>self::$key, 'products'=>self::$productUpdateQueue));
			self::clearQueue('productUpdateQueue');
			return $res;
		}

		static public function addProductToUpdateQueue($prod){
			array_push(self::$productUpdateQueue, $prod);
		}

		static public function createProduct($sku, $prod, $type = 'simple'){
			self::postRequestSetup(PRODUCT_CREATE);
			$basic = array('token' => self::$key, 'sku' => $sku, 'type' => $type);
			return self::cSubmitPost(array_merge($basic, $prod));
		}

		static public function addKitToCreationQueue($kit){
			array_push(self::$kitCreationQueue, $kit);
		}

		static public function createKits(){
			self::postRequestSetup(KIT_CREATE);
			$res = self::cSubmitPost(array('token'=>self::$key, 'kits'=>self::$kitCreationQueue));
			self::clearQueue('kitCreationQueue');
			var_dump(self::$kitCreationQueue);
			return $res;
		}

		static public function addToRemoveKitComponentQueue($kit_sku, $components){
			$kit = array('parent_sku'=>$kit_sku, 'components'=>array());
			foreach($components as $component){
				$kit['components'][] = array('sku'=>$component);
			}
			array_push(self::$removeKitComponentQueue, $kit);
		}

		static public function removeKitComponents(){
			self::postRequestSetup(KIT_REMOVE);
			return self::cSubmitPost(array('token'=>self::$key, 'kits'=>self::$removeKitComponentQueue));
		}

		static public function clearKit($sku){
			self::postRequestSetup(KIT_CLEAR);
			return self::cSubmitPost(array('token'=>self::$key, 'sku'=>$sku));
		}

		static public function getOrders($filter, $all_stores=0){
			self::init();
			self::buildEndpointUrl(ORDERS_GET, array_merge(array('all_orders'=>$all_stores), $filter), true);
			return self::cExec();
		}

		static public function getOrderById($id){
			self::init();
			self::buildEndpointUrl(ORDER_GET, array('id'=>$id), true);
			return self::cExec();
		}

		static public function getOrder($order_num){
			self::init();
			self::buildEndpointUrl(ORDER_GET, array('order_number'=>$order_num), true);
			return self::cExec();
		}

		static public function createOrder($order){
			self::postRequestSetup(ORDER_CREATE);
			return self::cSubmitPost(array_merge($order, array('token'=>self::$key)));
		}

		static public function updateOrder($order){
			self::postRequestSetup(ORDER_UPDATE);
			return self::cSubmitPost(array_merge($order, array('token'=>self::$key)));
		}

		static public function createOrderHistory($hist){
			self::postRequestSetup(ORDER_CREATE_HIST);
			return self::cSubmitPost(array_merge($hist, array('token'=>self::$key)));
		}

		static public function getVendorList(){
			self::init();
			self::buildEndpointUrl(VENDORS_LIST, null, true);
			return self::cExec();
		}

		static public function removeProductFromVendor($vendor_id, $sku){
			self::init();
			self::buildEndpointUrl(VENDORS_REMOVE_PROD, array('vendor_id'=>$vendor_id, 'sku'=>$sku), true);
			return self::cExec();
		}

		static public function createVendor($vendor){
			self::postRequestSetup(VENDOR_CREATE);
			return self::cSubmitPost(array_merge($vendor, array('token'=>self::$key)));
		}

		static public function addProductToVendorQueue($data){
			self::$addProductToVendorQueue[] = $data;
		}

		static public function addProductsToVendors(){
			self::postRequestSetup(VENDORS_ADD_PROD);
			$res = self::cSubmitPost(array('token'=>self::$key, 'data'=>self::$addProductToVendorQueue));
			self::clearQueue('addProductToVendorQueue');
			return $res;
		}

		static public function createFulfillmentStatus($status){
			self::postRequestSetup(FULFILLMENT_STATUS_CREATE);
			return self::cSubmitPost(array('fulfillment_status'=>$status, 'token'=>self::$key));
		}

		static public function deleteFulfillmentStatus($status){
			self::postRequestSetup(FULFILLMENT_STATUS_DELETE);
			return self::cSubmitPost(array('fulfillment_status'=>$status, 'token'=>self::$key));
		}

		static public function getPO($po_id){
			self::init();
			self::buildEndpointUrl(PO_GET, array('po_id' => $po_id), true);
			return self::cExec();
		}

		static public function addProductToPoQueue($product){
			self::$poLineItemQueue[] = $product;
		}

		static public function createPO($po){
			self::postRequestSetup(PO_CREATE);
			$po['line_items'] = self::$poLineItemQueue;
			$po['expected_date'] = self::setPOExpectedDate($po);
			$res = self::cSubmitPost(array_merge($po, array('token'=>self::$key)));
			self::clearQueue('poLineItemQueue');
			return $res;
		}

		static public function getShipments($filter){
			self::init();
			self::buildEndpointUrl(SHIPMENTS_GET, $filter, true);
			return self::cExec();
		}

		static public function createShipment($shipment){
			self::postRequestSetup(SHIPMENTS_CREATE);
			return self::cSubmitPost(array_merge($shipment, array('token'=>self::$key)));
		}

		static public function registerWebhook($hook_info){
			self::postRequestSetup(WEBHOOK_REGISTER);
			return self::cSubmitPost(array_merge($hook_info, array('token'=>self::$key)));
		}

		static public function getWebhooks(){
			self::init();
			self::buildEndpointUrl(WEBHOOK_GET, null, true);
			return self::cExec();
		}

	}
?>
