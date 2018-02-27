<?php
/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Deprecated. Supermercados Dia uses directly Payment Title not Label.
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    block
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Block_Payment_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
    	
        parent::_construct();
        $this->setMethodLabel();
        $this->setTemplate('fintonic/payment/form.phtml');
    }
	private function getHTML(){
		return $this->html;
	}
    private function setMethodLabel()
    {
    	
        //$this->setMethodTitle("");

		$quote = Mage::getModel('checkout/cart')->getQuote()->getData();
		
		$total = $quote['grand_total'];
		
		/*$session = Mage::getSingleton('checkout/session');
		$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
		
		$total = $order->getGrandTotal();*/
		
		$terms = Mage::getModel('fintonic/payment')->loadTerms($total);
		
        $this->html= 'en '.$terms[0]["month"] . ' plazos de ' . number_format($terms[0]["term"],2,".","") . ' €.';
										
        //$this->setMethodTitle($this->html);

    }

    public function getCountry()
    {
    
        $quote = Mage::getModel('checkout/cart')->getQuote();
        $countryId = $quote->getBillingAddress()->getCountryId();

        return $countryId;
    }
  
}
