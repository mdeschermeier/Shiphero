<?php
	require_once "Shiphero_const.php";

	class Shiphero{

		private static $verifyHost = 2;
		private static $verifyPeer = true;
		private static $key;
		private static $ch;

		//====================================//
		//========= PRIVATE METHODS ==========//
		//====================================//
		private function __construct(){
			// Dummy Constructor
		}

		static private function init(){
			self::$ch = curl_init();
			curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt(self::$ch, CURLOPT_HEADER, FALSE);
			curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, self::$verifyHost);
			curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, self::$verifyPeer);
		}

		static private function cExec(){
			$res = curl_exec(self::$ch);
			curl_close(self::$ch);
			return json_decode($res);
		}

		static private function buildEndpointURL($endpoint, $params = null, $needsToken = false){
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

		static private function getTruncProductResponse($p, $r){
			if ($p !== null){
				return $r->products->results[0];
			}
			return $r->products->results;
		}

		static private function postRequestSetup($endpoint){
			self::init();
			self::buildEndpointUrl($endpoint);
			curl_setopt(self::$ch, CURLOPT_POST, true);
			curl_setopt(self::$ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/json"
			));
		}

		static private function cSubmitPost($p){
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, json_encode($p));
			return self::cExec();
		}

		//====================================//
		//========== PUBLIC METHODS ==========//
		//====================================//
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
		static public function getProducts($prod = null, $verbose = false){
			self::init();
			self::buildEndpointUrl(PRODUCT_GET, $prod, true);
			$r = self::cExec();

			if ($verbose){
				return $r;
			}
			return self::getTruncProductResponse($prod, $r);
		}

		static public function updateInventory($prod){
			self::postRequestSetup(INVENTORY_UPDATE);
			return self::cSubmitPost(array('token'=>self::$key, 'products'=>array($prod)));
		}

		static public function createProduct($sku, $prod, $type = 'simple'){
			self::postRequestSetup(PRODUCT_CREATE);
			$basic = array('token' => self::$key, 'sku' => $sku, 'type' => $type);
			return self::cSubmitPost(array_merge($basic, $prod));
		}

		static public function createKit($kit){
			self::postRequestSetup(KIT_CREATE);
			return self::cSubmitPost(array('token'=>self::$key, 'kits'=>$kit));
		}

		static public function removeKitComponent($kits){
			self::postRequestSetup(KIT_REMOVE);
			return self::cSubmitPost(array('token'=>self::$key, 'kits'=>$kits));
		}

		static public function getOrders($filter, $all_stores=''){
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

		static public function addProductToVendor($data){
			self::postRequestSetup(VENDORS_ADD_PROD);
			return self::cSubmitPost(array_merge($data, array('token'=>self::$key)));
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

		static public function createPO($po){
			self::postRequestSetup(PO_CREATE);
			return self::cSubmitPost(array_merge($po, array('token'=>self::$key)));
		}

		static public function getShipments($params){
			self::init();
			self::buildEndpointUrl(SHIPMENTS_GET, $params, true);
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
