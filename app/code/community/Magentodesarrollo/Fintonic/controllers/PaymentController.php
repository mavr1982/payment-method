<?php
/******************************************************************************************************
 * Main Controller
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Payment Method Controller
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    controller
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');        
    }

    public function redirectAction()
    {
        $session = $this->_getCheckoutSession();

        if (!$session->getLastRealOrderId()) {
            $session->addError($this->__('Your order has expired.'));
            $this->_redirect('checkout/cart');
            return;
        }

        $this->getResponse()->setBody($this->getLayout()->createBlock('fintonic/payment_redirect')->toHtml());

        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }


    public function confirmAction()
    {
        $session = $this->_getCheckoutSession();

        if ($session->getLastRealOrderId()) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
           
            $order->getPayment()->getMethodInstance()->processConfirmOrder($order,$_POST['term']);
			Mage::getModel('checkout/session')->setData('period',$_POST['term']);
			$this->getResponse()->setBody($this->getLayout()->createBlock('fintonic/payment_info')->toHtml());
          
        }
        
    }
    
    public function printqrAction()
    {
    	$session = $this->_getCheckoutSession();
    	Mage::getSingleton('checkout/session')->setphoneresponse("");
    	$this->getResponse()->setBody($this->getLayout()->createBlock('fintonic/payment_qrcode')->toHtml());
    }
    
    public function deniedAction()
    {
    	$this->getResponse()->setBody($this->getLayout()->createBlock('fintonic/payment_cancel')->toHtml());
    }

    public function cancelAction()
    {
        $session = $this->_getCheckoutSession();
        $orderId = $session->getLastRealOrderId();

        if ($orderId) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getId() && $order->getState() === Mage_Sales_Model_Order::STATE_NEW) {
                Mage::helper('fintonic/cart')->resuscitateCartFromOrder($order, $this);
            }
        }

        $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
        $session->unsRedirectUrl();
    }

    public function modifyAction()
    {
    	$cartToken = Mage::getSingleton("checkout/session")->getcartToken();
    	
    	$data = array(
    		'phone'	=> $_POST['phone'],
    		'cartToken'	=> $cartToken['token'],
    		);
	    	
    	$phoneResponse = Mage::getModel('fintonic/payment')->modifyTelephone($data);
    	$phoneResponse = json_decode(json_encode($phoneResponse), true);
  	
    	Mage::getSingleton('checkout/session')->setphoneresponse($phoneResponse['result'][0]);
    	
    $this->getResponse()->setBody($this->getLayout()->createBlock('fintonic/payment_qrcode')->toHtml());	
    }
    
    public function successAction()
    {
    	$this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
    
    public function manageAction()
    {
    	Mage::getModel('checkout/session')->setData('period',$value);	
    }
    
    public function callbackAction()
    {
    	
		$orderId = Mage::app()->getRequest()->getParam('id_basket');
		$token = Mage::app()->getRequest()->getParam('token');
		$result = Mage::app()->getRequest()->getParam('result');

		if (strtoupper($result) == 'OK'){
			//Save order
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			$order->sendNewOrderEmail();
			$payment = $order->getPayment();
			$payment->setTransactionId($token);
			$payment->Capture(NULL);
		
			$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
		}
		else{
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			$order -> setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
		}
    }
}
