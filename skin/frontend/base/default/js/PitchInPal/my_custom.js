 function showPartialPay(chk) {
      document.getElementById('customcardpay').style.display = chk.checked ? 'block' : 'none';
  }



var $j = jQuery.noConflict();

$j(document).ready(function(){
    $j("body").on('click', '#have_crowdfund',function(e){

    	e.preventDefault();
    	$j("#go_for_crowd_fund").hide();
    	$j("#pay_with_pitchinpal").show();
    	$j('#checkbox_crowdfund').prop('checked', false);
    	$j('span.pitchinpal_meta').css('display','none');

    });

    $j("body").on('click', '#go_crowdfund',function(e){

    	e.preventDefault();
    	$j("#pay_with_pitchinpal").hide();
    	$j("#go_for_crowd_fund").show();
        $j('#checkbox_crowdfund').prop('checked', true);
    	$j('#checkbox_partial').prop('checked', false);
    	$j('#text_pitchinpal').val('');
    	$j('#text_card_number').val('');
    	$j('#text_cvv').val('');
    	$j('#customcardpay').css('display','none');
    	$j('span.pitchinpal_meta').css('display','none');

        var urlTo = $j('#pitchinpal_urlToSend').val();
        console.log($j('#pitchinpal_urlToSend').val());
        console.log('yes');


         $j(location).attr('href', 'https://pitchinpal.com/cart/data/' + urlTo)
         return false;



    });
});
