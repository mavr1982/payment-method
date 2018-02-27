<?php

/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Get control from event payment.
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    Observer
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function is_fintonic_payment($order)
    {
        $code = Magentodesarrollo_Fintonic_Model_Payment::METHOD_CODE;

        /** @var Mage_Sales_Model_Order $parentOrder */
        $parentOrder = Mage::getModel('sales/order')->loadByIncrementId(
            (int)$order->getIncrementId());

        return ($code == $parentOrder->getPayment()->getMethod());
    }
    
    protected function is_simulation_payment()
    {
    	$simulation_code = Mage::getStoreConfig('payment/fintonic/sandbox');
    	
    	return $simulation_code;
    }

}
