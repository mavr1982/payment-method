<?php

/**
 * Extendemos / Rewrite del bloque estandar de Magento
 * para asegurarnos que el formulario del checkout completa
 * la dirección, aunque el usuario no este logueado,
 * con la dirección del quote.
 *
 * Class Magentodesarrollo_Fintonic_Block_Checkout_Onepage_Billing
 */
class Magentodesarrollo_Fintonic_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getQuote()->getBillingAddress();
        }

        return $this->_address;
    }
}
