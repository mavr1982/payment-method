<?php
/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Config for future countries
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    model
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Model_Config
{

    const XML_PATH_DEFAULT_COUNTRY              = 'general/country/default';

    protected $_storeId = null;

    /**
     * Return merchant country code
     */
    public function getMerchantCountry()
    {
        $countryCode = Mage::getStoreConfig("fintonic/general/merchant_country", $this->_storeId);
        if (!$countryCode) {
            $countryCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_COUNTRY, $this->_storeId);
        }
        return $countryCode;
    }




    /**
     * Check whether specified currency code is supported
     */
    public function isCurrencyCodeSupported($code)
    {
        if ($this->getMerchantCountry() == 'ES' && $code == 'EUR') {
            return true;
        }
        return false;
    }
}