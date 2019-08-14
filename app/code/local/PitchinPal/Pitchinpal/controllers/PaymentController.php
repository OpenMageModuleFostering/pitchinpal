<?php

class PitchinPal_Pitchinpal_PaymentController extends Mage_Core_Controller_Front_Action
{


  public function generateAction(){
    $q = $_SERVER["QUERY_STRING"];
    parse_str($q, $data);

    $cart_identifier = $data['cart_identifier'];
    Mage::getSingleton('core/session')->setCartIdentifier($cart_identifier);


    $cart = Mage::getSingleton('checkout/cart');
//print_r($cart);die();
    foreach ($data['items'] as $key) {
      $product = Mage::getModel('catalog/product')
                     // set the current store ID
                     ->setStoreId(Mage::app()->getStore()->getId())
                     // load the product object
                     ->load($key['id']);

      $qtyStock =(int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
     //print_r($qtyStock);die();
     //echo "<pre>";print_r($data);exit;
    if($qtyStock >= $key['quantity']){
        if( isset($key['variation_id']) && !empty($key['variation_id'])){
          $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
          // check if something is returned
          if (!empty($parentIds)) {
              $pid = $parentIds[0];
          }else{
            $pid = 0;
          }
          // Collect options applicable to the configurable product
          $configurableProduct = Mage::getModel('catalog/product')->load($pid);
          $cart->addProduct(
            $configurableProduct,
            array(
              'product' => $configurableProduct->getId(),
              'qty' => $key['quantity'],
              'super_attribute' => $key['variation_id']
            )
          );

        }else{
          $cart->addProduct($product, array('qty' => $key['quantity']));
        }

     }else{
        Mage::getSingleton('core/session')->addError($product->getName().'  - required quantity unavailable');
     }

    }
    $cart->save();
    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
    $this->_redirect('checkout/cart');

  }


}
