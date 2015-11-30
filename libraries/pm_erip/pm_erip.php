<?php

defined('_JEXEC') or die('Restricted access');

class pm_erip extends PaymentRoot{
    
	public function __construct(){
		//Load language file.
		//JSFactory::loadLanguageFile('plg_system_joomshoppingcustom', JPATH_ADMINISTRATOR);
		JFactory::getLanguage()->load('plg_system_joomshoppingerip', JPATH_ADMINISTRATOR, null, false, false);		
	}
	
    function showPaymentForm($params, $pmconfigs){
        echo JText::_('PLG_JSERIPPAYMENT_DESCRIPTION');
    }

	//function call in admin
	function showAdminFormParams($params){

		$orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model
		include(dirname(__FILE__)."/adminparamsform.php");	  

	}
	
	function showEndForm($pmconfigs, $order){

        $jshopConfig = JSFactory::getConfig();
        $pm_method = $this->getPmMethod();

		$db 	= JFactory::getDBO();
		$query = $db->getQuery(true);	
		
		$query->clear();
		$query->update('#__jshopping_orders')
			->set($db->quoteName('order_created') . ' = 1 ')
			->where($db->quoteName('order_id') . ' = ' . (int) $order->order_id);
		$db->setQuery($query);		
		
		try
		{
			$db->execute();
			$model = JSFactory::getModel('orderMail', 'jshop');
			$model->setData($order->order_id, 0);
			$model->send();			
		}
		catch (RuntimeException $e)
		{
			//throw new RuntimeException('Custom payment option can not be created!<br/>'.$e->getMessage());
			echo JText::_('PLG_JSERIPPAYMENT_ORDER_ERROR');
		}
		
/*		$checkout 	= JSFactory::getModel('checkoutBuy', 'jshop');
		if (!$checkout->loadUrlParams()){
			JError::raiseWarning('', $checkout->getError());
            return 0;
		}		
		$codebuy 	= $checkout->buy();

		if ($codebuy==0){
			JError::raiseWarning('', $checkout->getError());
            return 0;
		}
		if ($codebuy==2){
			die();
		}*/			
		
		//echo $pmconfigs['payment_method_description'];
		echo JText::_('PLG_JSERIPPAYMENT_ORDER_CONFIRMATION');		
	}	
}