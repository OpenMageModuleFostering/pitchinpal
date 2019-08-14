<?php

class PitchinPal_Pitchinpal_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'pitchinpal';
  protected $_isInitializeNeeded      = true;
  protected $_formBlockType = 'pitchinpal/form_pitchinpal';
  protected $_infoBlockType = 'pitchinpal/info_pitchinpal';



  private function isCorrectExpireDate($month, $year)
      {
          $now       = time();
          $result    = false;
          $thisYear  = (int)date('y', $now);
          $thisMonth = (int)date('m', $now);

          if (is_numeric($year) && is_numeric($month))
          {
              if($thisYear == (int)$year)
            {
                $result = (int)$month >= $thisMonth;
            }
        else if($thisYear < (int)$year)
        {
          $result = true;
        }
          }

          return $result;
      }
      private function isCreditCardNumber($toCheck){
            if (!is_numeric($toCheck))
                return false;

            $number = preg_replace('/[^0-9]+/', '', $toCheck);
            $strlen = strlen($number);
            $sum    = 0;

            if ($strlen < 13)
                return false;

            for ($i=0; $i < $strlen; $i++)
            {
                $digit = substr($number, $strlen - $i - 1, 1);
                if($i % 2 == 1)
                {
            $sub_total = $digit * 2;
            if($sub_total > 9)
            {
                $sub_total = 1 + ($sub_total - 10);
            }
                }
                else
                {
            $sub_total = $digit;
                }
                $sum += $sub_total;
            }

            if ($sum > 0 AND $sum % 10 == 0)
                return true;

            return false;
    }

  public function assignData($data)
  {
    //print_r($data->getCustomFieldOne());die();

    $info = $this->getInfoInstance();

    if ($data->getCustomFieldOne())
    {
      $info->setCustomFieldOne($data->getCustomFieldOne());
    }


    if ($data->getCustomFieldTwo())
    {
      $info->setCustomFieldTwo($data->getCustomFieldTwo());
    }

    if ($data->getCustomPaycard())
    {
      $info->setCustomPaycard($data->getCustomPaycard());
    }
    if ($data->getCustomCardNumber())
    {
      $info->setCustomCardNumber($data->getCustomCardNumber());
    }
    if ($data->getCustomCardMonth())
    {
      $info->setCustomCardMonth($data->getCustomCardMonth());
    }
    if ($data->getCustomCardYear())
    {
      $info->setCustomCardYear($data->getCustomCardYear());
    }
    if ($data->getCustomCardCvv())
    {
      $info->setCustomCardCvv($data->getCustomCardCvv());
    }

    return $this;
  }

   public function validate()
  {
    parent::validate();



    $total = Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal();
    $config = Mage::getStoreConfig('payment/pitchinpal/merchant_id');
    $info = $this->getInfoInstance();

    $cartIdentifier = Mage::getSingleton('core/session')->getCartIdentifier();
    $cartIdentifier = $cartIdentifier ? $cartIdentifier : 'new';

    if (!$info->getCustomFieldOne() && !$info->getCustomFieldTwo())
    {
      $errorCode = 'invalid_data';
      $errorMsg = $this->_getHelper()->__("Select Option for Pitchinpal.\n");
       Mage::throwException($errorMsg);
     return $this;
    }else if ($info->getCustomFieldOne() && $info->getCustomFieldTwo()){

      $errorCode = 'invalid_data';
      $errorMsg = $this->_getHelper()->__("Select One Option Only for Pitchinpal.\n");
       Mage::throwException($errorMsg);
     return $this;
    }else if(!$info->getCustomFieldOne() && $info->getCustomFieldTwo()){

        if($info->getCustomPaycard() == 1){ //for partial payment plus pitchinpal payment

            if(!$info->getCustomCardNumber() || !$this->isCreditCardNumber($info->getCustomCardNumber())){
              $errorCode = 'invalid_data';
              $errorMsg = $this->_getHelper()->__("Select Correct Card Number.\n");
               Mage::throwException($errorMsg);
     return $this;
            }elseif(!$info->getCustomCardMonth()){
              $errorCode = 'invalid_data';
              $errorMsg = $this->_getHelper()->__("Select Card Expiration Month.\n");
               Mage::throwException($errorMsg);
     return $this;
            }elseif(!$info->getCustomCardYear()){
              $errorCode = 'invalid_data';
              $errorMsg = $this->_getHelper()->__("Select Card Expiration Year.\n");
               Mage::throwException($errorMsg);
     return $this;
            }elseif(!$this->isCorrectExpireDate($info->getCustomCardMonth() , $info->getCustomCardYear())){
              $errorCode = 'invalid_data';
              $errorMsg = $this->_getHelper()->__("Select Correct Expiration Date.\n");
               Mage::throwException($errorMsg);
     return $this;
            }elseif(!$info->getCustomCardCvv()){
              $errorCode = 'invalid_data';
              $errorMsg = $this->_getHelper()->__("Select Card CVV Number.\n");
               Mage::throwException($errorMsg);
     return $this;
            }else{ //all are good
               $cardNumber =  $info->getCustomCardNumber();
               $cardMonth =   $info->getCustomCardMonth();
               $cardYear = $info->getCustomCardYear();
               $cardCvv = $info->getCustomCardCvv();
                //include(plugin_dir_path( FILE ). 'lib/Stripe.php');
                $ExternalLibPath=Mage::getModuleDir('', 'PitchinPal_Pitchinpal') . DS . 'lib' . DS .'Stripe.php';
                require_once ($ExternalLibPath);
                //echo plugin_dir_path( FILE ). 'lib/Stripe.php';

                Stripe::setApiKey('pk_live_QyKz0Q376EY46n9r19e2wqhQ');
                $token_id = Stripe_Token::create(array(
                 "card" => array(
                      "number" => $cardNumber,
                      "exp_month" => $cardMonth,
                      "exp_year" => $cardYear,
                      "cvc" => $cardCvv )
                 ));
               $this->stripeToken = $token_id->id;
                $stripeToken = $this->stripeToken;


            $user_identifier = $info->getCustomFieldTwo();

             $curlObj = curl_init();



              $c_opt[CURLOPT_URL] = "https://pitchinpal.com/verify-user";
              $c_opt[CURLOPT_POSTFIELDS] = "cart_identifier=". $cartIdentifier . "&amount=".$total .  "&user_identifier=" . $user_identifier. "&stripeToken=". $stripeToken;
              $c_opt[CURLOPT_RETURNTRANSFER] = 1;
              $c_opt[CURLOPT_HEADER] = 0;


              curl_setopt_array($curlObj, $c_opt);

              $result = curl_exec($curlObj);

              $result = json_decode($result);

              curl_close($curlObj);


              if($result->status == 'error'){
                  $errorCode = 'invalid_data';
                  $errorMsg = $this->_getHelper()->__($result->message);
                  Mage::throwException($errorMsg);
                  return $this;
              }



            }



        }

       else{
// only pitchinpal payment; No partial payment

            $user_identifier = $info->getCustomFieldTwo();

             $curlObj = curl_init();



              $c_opt[CURLOPT_URL] = "https://pitchinpal.com/verify-user";
              $c_opt[CURLOPT_POSTFIELDS] = "cart_identifier=". $cartIdentifier . "&amount=".$total .  "&user_identifier=" . $user_identifier;
              $c_opt[CURLOPT_RETURNTRANSFER] = 1;
              $c_opt[CURLOPT_HEADER] = 0;


              curl_setopt_array($curlObj, $c_opt);

              $result = curl_exec($curlObj);

              $result = json_decode($result);

              curl_close($curlObj);


          if($result->status == 'error'){
                  $errorCode = 'invalid_data';
                  //$errorMsg = $this->_getHelper()->__($result->message);

                  if("invalid pitchinpal key" == strtolower($result->message)){
                     $errorMsg = $this->_getHelper()->__("Invalid PitchinPal™ key");
                  }elseif ("insufficient credit" == strtolower($result->message)) {
                    $errorMsg = $this->_getHelper()->__("You do not have enough PitchinPal™ FriendFunds™.  Please choose to get more FriendFunds™ or check the option to pay the difference with a credit card.");
                  }else{
                    $errorMsg = $this->_getHelper()->__($result->message);
                  }

                  Mage::throwException($errorMsg);
                  return $this;
              }

        }


    }else if($info->getCustomFieldOne() && !$info->getCustomFieldTwo()){

    }else{
      $errorCode = 'invalid_data';
      $errorMsg = $this->_getHelper()->__("Invalid Operation.\n");
      Mage::throwException($errorMsg);
     return $this;
    }

    if ($errorMsg)
    {

      Mage::throwException($errorMsg);
    }

    return $this;
  }


    public function initialize($paymentAction, $stateObject){
      parent::initialize($paymentAction, $stateObject);

      $info = $this->getInfoInstance();
      $config = Mage::getStoreConfig('payment/pitchinpal/merchant_id');

      $cartItems = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCollection();
      $options = array();
      $variables = array();

      foreach ($cartItems as $pitem) {
        $pro = Mage::getModel('catalog/product')->load($pitem->getProduct()->getId());

        if($pro->getTypeId() == "simple"){
            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($pro->getId());
            if(!$parentIds)
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($pro->getId());

            if(isset($parentIds[0])){
              $parent = Mage::getModel('catalog/product')->load($parentIds[0]);

              if($parent->isConfigurable())
              {
                $productAttributeOptions = $parent->getTypeInstance(true)->getConfigurableAttributesAsArray($parent);
                foreach ($productAttributeOptions as $productAttribute) {
                  //  echo "<pre>";print_r($productAttribute);
                  $allValues = array_column($productAttribute['values'], 'value_index');
                  $allLabelValues = array_column($productAttribute['values'], 'store_label');

                  $currentProductValue = $pro->getData($productAttribute['attribute_code']);
                  $attr = $pro->getResource()->getAttribute($productAttribute['attribute_code']);
                  $attribute_value = $attr ->getFrontend()->getValue($pro);
                  //print_r();
                  if (in_array($currentProductValue, $allValues)) {
                    $options[$parentIds[0]][$pitem->getProduct()->getId()][$productAttribute['attribute_id']] = $currentProductValue;
                    $variables[$parentIds[0]][$pitem->getProduct()->getId()][$productAttribute['label']] = $attribute_value;
                  }
                }
              }
            }
          }
        }

        if(!$info->getCustomFieldOne() && $info->getCustomFieldTwo()){
          $amount = Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal();
          $cartIdentifier = Mage::getSingleton('core/session')->getCartIdentifier();
          $shipmentAmount = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingAmount();
          $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
          $tax = 0;

          if(isset($totals['tax']) && $totals['tax']->getValue())
          {
            $tax = $totals['tax']->getValue(); //Tax value if present
          }

          $quote = Mage::helper('checkout/cart')->getCart()->getQuote();

          $valor = [];
          $items = [];
          $valor['base_url'] = Mage::getBaseUrl();
          $valor['store_identifier'] = $config;
          $valor['user_identifier'] = $info->getCustomFieldTwo();
          $valor['amount'] = $amount;
          $valor['total_tax'] = $tax;
          $valor['total_shipping'] = $shipmentAmount;

          if($cartIdentifier){
            $valor['cart_identifier'] = $cartIdentifier;
            Mage::getSingleton('core/session')->setCartIdentifier('');
          }else{
             $valor['cart_identifier'] = 'new';
          }

          foreach ($quote->getAllItems() as $item) {
            if ('simple' != $item->getProductType()) continue;

            $product_image = (string)Mage::helper('catalog/image')->init($item->getProduct(), 'thumbnail')->resize(100);
            $product = $item->getProduct();
            $productUrl = $product->getUrlModel()->getUrl($product);

            $items= array (
               'id' => $item->getProductId(),
               'name'=> $item->getName(),
                'url_pro'=> $productUrl,
                'image' => $product_image,
                'sku' => $item->getSku(),
                'quantity' => $item->getQty(),
                'price' => $item->getParentItemId() ? $item->getParentItem()->getPrice() : $item->getPrice(),
                'type'  => $item->getProductType(),
                'variation_id' => isset($options[$parent_id][$item->getProductId()]) ? $options[$parent_id][$item->getProductId()]:0,
                'variables' => isset($variables[$parent_id][$item->getProductId()]) ? $variables[$parent_id][$item->getProductId()]:0,
            );
            $valor['order_details']['items'][] = $items;
          }

          $valor['order_details']['amount'] = $amount;
          $valor['order_details']['total_tax'] = $tax;
          $valor['order_details']['total_shipping'] = $shipmentAmount;
          $valor['order_details']['store_identifier'] = $config;
          $valor['order_details']['base_url'] = Mage::getBaseUrl();

          if($info->getCustomPaycard() == 1){ // when partial payment occurs
              $valor['type'] = 'card';
              $valor['stripeToken'] = $this->stripeToken;
          }else{ // when no partial payment occurs
              $valor['type'] = 'pitchinpal';
          }

          $curlObj = curl_init();
          $c_opt[CURLOPT_URL] = "https://pitchinpal.com/payment/process";
          $c_opt[CURLOPT_POSTFIELDS] =  http_build_query($valor);
          $c_opt[CURLOPT_RETURNTRANSFER] = 1;
          $c_opt[CURLOPT_HEADER] = 0;
          curl_setopt_array($curlObj, $c_opt);
          $result = curl_exec($curlObj);
          curl_close($curlObj);

          if($result->status == 'error'){
              $errorCode = 'invalid_data';
              $errorMsg = $this->_getHelper()->__($result->message);
              Mage::throwException($errorMsg);
              return $this;
          }

      }

      return $this;

    }



}
