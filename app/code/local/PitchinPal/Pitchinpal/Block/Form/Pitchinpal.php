<?php

class PitchinPal_Pitchinpal_Block_Form_Pitchinpal extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
   // $this->setTemplate('Custompaymentmethod/form/custompaymentmethod.phtml');

    
    $imageUrl = Mage::getBaseUrl('skin')."frontend/base/default/images/logoPitchinpal.png";
    $mark = Mage::getConfig()->getBlockClassName('core/template');
    $mark = new $mark;
    $mark->setTemplate('Pitchinpal/form/mark.phtml')
        ->setPaymentAcceptanceMarkHref("https://vimeo.com/142940582")
        ->setPaymentAcceptanceMarkSrc($imageUrl)
    ; // known issue: code above will render only static mark image
   $this->setTemplate('Pitchinpal/form/pitchinpal.phtml')
        ->setMethodTitle('') // Output PayPal mark, omit title
        ->setMethodLabelAfterHtml($mark->toHtml());
  }
}
