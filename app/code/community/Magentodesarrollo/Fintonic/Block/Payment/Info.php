<?php
/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Get Message Codes from Payment Processor
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    block
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Block_Payment_Info extends Mage_Payment_Block_Info
{
	
		protected function _toHtml()
    {	
        /** @var Magentodesarrollo_Fintonic_Model_Payment $payment */
        $Model = Mage::getModel('fintonic/Payment');
		
		$session = Mage::getSingleton('checkout/session');
		$period = Mage::getModel("checkout/session")->getData("period");
		$cartToken = $Model->start($period);
		
		$completeData = $Model->apply($cartToken, $period);
	
	
	$completeData = json_decode(json_encode($completeData), True);
	$cartToken = json_decode(json_encode($cartToken), True);

		switch($completeData['code']){
			
			case 8100:
				Mage::getSingleton('checkout/session')->setcartToken($cartToken);
				Mage::getSingleton('checkout/session')->seturlimage($completeData['url_image']);
				
				return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('fintonic/payment/printqr'));
				break;
			
			case 8101:
			
				return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('fintonic/payment/denied'));
				break;
			
			default:
		    	
		    	return Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('fintonic/payment/denied'));
				break;
		}
		
		

    }
    
    
}
