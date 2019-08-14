<?php
class PitchinPal_Pitchinpal_Helper_Data extends Mage_Core_Helper_Data{
// my methods

	function getPaymentGatewayUrl() 
  {
    return Mage::getUrl('pitchinpal/payment/gateway', array('_secure' => false));
  }
}