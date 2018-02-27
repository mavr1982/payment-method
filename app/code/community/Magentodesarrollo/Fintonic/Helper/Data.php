<?php
/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * WSDL comm library.
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    block
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/fintonic/active');
    }
    
    public function call($url, $oauthtoken='', $type='GET', $arguments=array(), $encodeData=true, $returnHeaders=false)
	{
		$type = strtoupper($type);
	
		if ($type == 'GET')
		{
			$url .= "?" . http_build_query($arguments);
		}
	
		$curl_request = curl_init($url);
		
		if ($type == 'POST')
		{
			curl_setopt($curl_request, CURLOPT_POST, 1);
		}
		elseif ($type == 'PUT')
		{
			curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "PUT");
		}
		elseif ($type == 'DELETE')
		{
			curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		
		curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl_request, CURLOPT_HEADER, $returnHeaders);
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
		$length = json_encode($arguments);
		
		curl_setopt($curl_request, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($length)));
		
		if (!empty($oauthtoken))
		{
			$token = array('Content-Type:application/json',
				'Content-Length: ' . strlen($length),
				'Authorization: '. $oauthtoken);
			curl_setopt($curl_request, CURLOPT_HTTPHEADER, $token);
			//curl_setopt($curl_request, CURLOPT_POSTFIELDS, array();
			
		}else{
			
			
		}
		
		if (!empty($arguments) && $type !== 'GET')
		{
		if ($encodeData)
		{
			//encode the arguments as JSON
			$arguments = json_encode($arguments);
			
		}
			curl_setopt($curl_request, CURLOPT_POSTFIELDS, $arguments);
		}
		
		$result = curl_exec($curl_request);
		
		if ($returnHeaders)
		{
			//set headers from response
			list($headers, $content) = explode("\r\n\r\n", $result ,2);
			foreach (explode("\r\n",$headers) as $header)
		{
			header($header);
		}
		
		//return the nonheader data
		return trim($content);
		}
		
		curl_close($curl_request);
		
		//decode the response from JSON
		$response = json_decode($result);
		
		return $response;
	}
}
