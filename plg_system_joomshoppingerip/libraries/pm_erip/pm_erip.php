<?phpdefined('_JEXEC') or die('Restricted access');

class pm_erip extends PaymentRoot{
	public function __construct(){
		//Load language file.		JFactory::getLanguage()->load('plg_system_joomshoppingerip', JPATH_ADMINISTRATOR, null, false, false);
	}

  function showPaymentForm($params, $pmconfigs){
      echo JText::_('PLG_JSERIPPAYMENT_DESCRIPTION');
  }

	//function call in admin
	function showAdminFormParams($params){
		$orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model		include(dirname(__FILE__)."/adminparamsform.php");
	}

	function showEndForm($pmconfigs, $order){
    $jshopConfig = JSFactory::getConfig();    $pm_method = $this->getPmMethod();

		$db 	= JFactory::getDBO();		$query = $db->getQuery(true);
		$query->clear();

		$query->update('#__jshopping_orders')			->set($db->quoteName('order_created') . ' = 1 ')
			->where($db->quoteName('order_id') . ' = ' . (int) $order->order_id);
		$db->setQuery($query);

		try		{
			$db->execute();
			$model = JSFactory::getModel('orderMail', 'jshop');
			$model->setData($order->order_id, 0);
			$model->send();
		}
		catch (RuntimeException $e)
		{
			echo JText::_('PLG_JSERIPPAYMENT_ORDER_ERROR');
		}

    if ($pmconfigs['auto'] == '1') {
      $instruction = JText::_('PLG_JSERIPPAYMENT_ERIP_INSTRUCTION');
      $instruction = str_replace('#TABS#', $pmconfigs['tree_path_email'], $instruction);
      $instruction = str_replace('#ORDER_ID#', $order->order_id, $instruction);

      echo nl2br($instruction);
    } else {
      echo nl2br(JText::_('PLG_JSERIPPAYMENT_ORDER_CONFIRMATION'));
    }
	}
}
