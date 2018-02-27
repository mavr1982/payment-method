<?php
/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Communications with WSDL payment processor.
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    model
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const METHOD_CODE = 'fintonic';

    /**
     * options
     */
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'fintonic/payment_form';
    
    protected $_isInitializeNeeded     = true;
    protected $_canUseInternal         = false;
    protected $_canUseForMultishipping = false;

    /**
     * Config instance getter
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
            if ($store = $this->getStore()) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = Mage::getModel('fintonic/config', $params);
        }
        return $this->_config;
    }

    /**
     * Get fintonic session namespace
     */
    public function getSession()
    {
        return Mage::getSingleton('fintonic/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }


    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('fintonic/payment/redirect', array('_secure' => true));
    }

    public function initialize()
    {
    	$urlbase = $this->urlBase();
		
		$endpoint = '/process/initialize';
		$url = $urlbase . $endpoint;
		
		$oauth2_token_arguments = array(
			"user" => Mage::getStoreConfig('payment/fintonic/user'),
			"pass" => Mage::getStoreConfig('payment/fintonic/pass'),
		);
		
		$oauth2_token_response = Mage::helper('fintonic/data')->call($url, '', 'POST', $oauth2_token_arguments);
		
		//Mage::log('Response: '.print_r($oauth2_token_response,true),null,'fintonic.log',true);
		
		return $oauth2_token_response;
    }
    
    public function start($period)
    {
    	
    	$oauth2_token_response = $this->initialize();
    	$urlbase = $this->urlBase();	
		$url = $urlbase . "/process/start";
		
		$data = $this->cartData($period);
		$record_arguments = array(
			"amount_sim" => number_format($data['amount_sim'],2, '.', ''),
			"installment_sim" => number_format($data['installment_sim'],2, '.', ''),
			"duration_result" => $data['duration_result'],
			"amount_total" => number_format($data['amount_total'],2, '.', ''),
			"simulation_code" => 2,
			"basket_id" => $data['id_basket'],
		);
		
		
		$token_offer = Mage::helper('fintonic/data')->call($url, $oauth2_token_response->token, 'POST', $record_arguments);
		//Mage::log('Token respuesta start: '.print_r($token_offer,true),Zend_Log::INFO,'fintonic.log',true);
	
		return $token_offer;
		
    }
    
    public function apply($cartToken, $period)
    {
    	
    	$carToken = json_decode($cartToken);
    	
    	$urlbase = $this->urlBase();
    	
		Mage::getSingleton('core/session')->setcartToken($cartToken->{'token'});
		
		$url = $urlbase . "/process/apply/".$cartToken->{'token'};
		Mage::log('URL: '.print_r($url,true),Zend_Log::INFO,'fintonic.log',true);		
		
		$data = $this->cartData($period);
		
		//Añadimos un NIF aleatorio por si no existiese en entornos de prueba
    	if((Mage::getStoreConfig('payment/fintonic/sandbox') == 1) && ($data['nif'] == "" || $data['nif'] == null)){
    		$data['nif'] = '50455455A';
    	}
		
		$record_arguments = array(
			"cart_token" => $cartToken->{'token'},
			"name" => $data['name'],
			"lastname" => $data["lastname"],
			"email" => $data['email'],
			"phone" => $data['phone'],
			"address" => $data['address'],
			"postalcode" => $data['postalcode'],
			"province" => $data['province'],
			"nif" => $data['nif'],
			"amount_sim" =>  number_format($data['amount_sim'],2, '.', ''),
			"installment_sim" =>  number_format($data['installment_sim'],2, '.', ''),
			"duration_result" => $data['duration_result'],
			"tie" => $data['tie'],
			"tae" => $data['tae'],
			"amount_total" =>  number_format($data['amount_total'],2, '.', ''),
			"simulation_code" => 2,
			"basket_detail" => $data['basket_details'],
		);
		$oauth2_token_response = $this->initialize();
		
		$record_response = Mage::helper('fintonic/data')->call($url, $oauth2_token_response->token, 'POST', $record_arguments);
		Mage::log('Respuesta financiacion: '.print_r($record_response,true),Zend_Log::INFO,'fintonic.log',true);
		return $record_response;
    }
    
    public function modifyTelephone($data)
    {
    	$urlbase = $this->urlBase();	
		$url = $urlbase . "/process/modifyPhone/".$data['cartToken']."/".$data["phone"];
		Mage::log('URL telefono: '.print_r($url,true),Zend_Log::INFO,'fintonic.log',true);
		$oauth2_token_response = $this->initialize();
		$record_arguments = array(
			'token' => $data['cartToken'],
			'phone'	=>$data["phone"]
			);
		
		
		$record_response = Mage::helper('fintonic/data')->call($url, $oauth2_token_response->token, 'POST',$record_arguments);
		Mage::log('Respuesta modificacion telefono: '.print_r(json_decode(json_encode($record_response),true),true),null,'fintonic.log',true);
		return $record_response;
    }
    
    protected function urlBase()
    {
    	$is_sandbox = Mage::getStoreConfig('payment/fintonic/sandbox');
    	
    	if($is_sandbox == 1){
    		$urlbase = "https://ecompre.fintonic.com/diaecom";
    	}else{
    		$urlbase = "https://ecom.fintonic.com/diaecom";
    	}
    	
    	return $urlbase;
    }
    
    public function loadTerms($totalCart)
    {
    
    	$period = array(9,6);
    	
    	$tin = 18/100;
	
		$tie = pow((1+$tin),(1/12))-1;
		$tae = pow(1+($tin/12),12)-1;
    	$data = array();
    	
    	foreach($period as $month){
    	
			$factor = (1-pow(1+$tie,-$month))/$tie;
			//$cuota = round($totalCart/$factor,2);
			$cuota = $totalCart/$factor;
    	
    		$data[] = array(
    			"month" => $month,
    			//"term" 	=> $cuota,
    			"term" 	=> round($cuota,2),
    			//"total" => $month * $cuota,
    			"total" => round($month * $cuota,2),
    			'tie'	=> $tie,
    			"tin"	=> $tin,
    			"tae"	=> $tae,
    		);
    		
   		
    	}
  	
    	return $data;
    	
    }
    public function setTerms($totalCart, $period)
    {

		$tin = 18/100;
	
		$tie = pow((1+$tin),(1/12))-1;
	
	
    	$factor = (1-pow(1+$tie,-$period))/$tie;
	
		//$cuota = round($totalCart/$factor,2);
		$cuota = $totalCart/$factor;

 		$tae = pow(1+($tin/12),12)-1;

	
		$data = array(
    			"month" => $period,
    			//"term" 	=> $cuota,
    			"term" 	=> round($cuota,2),
    			//"total" => $month * $cuota,
    			"total" => round($period * $cuota,2),
    			'tie'	=> $tie,
    			"tin"	=> $tin,
    			"tae"	=> $tae,
    	);
    	
    	return $data;
    }
    
    public function cartData($period)
    {
    	$session = Mage::getSingleton('checkout/session');
    	
    	$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
    	$billingId = $order->getBillingAddressId();
		$order_address_billing = Mage::getModel('sales/order_address')->load($billingId);

		$shippingId = $order->getShippingAddressId();
		$order_address_shipping = Mage::getModel('sales/order_address')->load($shippingId);
    	$customerId = $order->getCustomerId();
		$customer_data = Mage::getModel('customer/customer')->load($customerId);
    	$terms = $this->setTerms($order->getGrandTotal(), $period);
 
		$data = array();
		$data['id_basket'] = $session->getLastRealOrderId();
		$data['name'] = $order['customer_firstname'];
		$data['lastname'] = $order['customer_lastname'];
		$data['email'] = $order['customer_email'];
		$data['phone'] = $order_address_billing['telephone'];
		$data['address'] = $order_address_billing['street'];
		$data['postalcode'] = $order_address_billing['postcode'];
		$data['province'] = $order_address_billing['region'];
		$data['nif'] = $order_address_billing['vat_id'];
		$data['amount_sim'] = $order->getGrandTotal();
		$data['installment_sim'] = $terms['term'];
		$data['duration_result'] = $terms['month'];
		$data['tie'] = number_format($terms['tie'],2, '.', '');
		$data['tae'] = number_format($terms['tae'],2, '.', '');
		$data['simulation_code'] = 2;
		
		$items = $order->getAllVisibleItems();
		
		$data['basket_details'] = "";
		
		foreach ($items as $item) {
		
			$data['basket_details'] = $item['name'] . " && " . $data['basket_details'];
			
		}
		
		$data['basket_details'] = substr($data['basket_details'], 0,255);
		
		$data['amount_total'] = $terms['total'];
		
    	return $data;
    }
    
    public function processConfirmOrder($order, $period)
    {

    	$payment = $order->getPayment();

    	$payment->setAdditionalInformation('period', $period);
    	
    	$order->save();

        return $this;

    }
}
