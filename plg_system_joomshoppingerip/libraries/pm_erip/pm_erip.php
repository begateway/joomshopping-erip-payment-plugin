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

  function getStatusId() {
    $db 	= JFactory::getDBO();
    $query = $db->getQuery(true);

    $query->clear();
    $query->select('os.status_id')
      ->from($db->quoteName('#__jshopping_order_status') . ' AS os')
      ->where($db->quoteName('os.status_code') . ' = "A"');
    $db->setQuery($query);
    $status_id = $db->loadResult();
    return $status_id;
  }

	function showEndForm($pmconfigs, $order){
    $jshopConfig = JSFactory::getConfig();    $pm_method = $this->getPmMethod();

		$db 	= JFactory::getDBO();		$query = $db->getQuery(true);
		$query->clear();

		$query->update('#__jshopping_orders')			->set($db->quoteName('order_created') . ' = 1 ')
			->where($db->quoteName('order_id') . ' = ' . (int) $order->order_id);
		$db->setQuery($query);

    echo '<div id="begateway_erip">';		try		{
			$db->execute();
			$model = JSFactory::getModel('orderMail', 'jshop');
			$model->setData($order->order_id, 0);
			$model->send();

      if ($pmconfigs['auto'] == '1') {
        JPluginHelper::importPlugin('PlgSystemJoomShoppingErip');
        $dispatcher = JEventDispatcher::getInstance();
        $result = $dispatcher->trigger('onBeforeChangeOrderStatusAdmin', array($order->order_id, $this->getStatusId(), 'auto'));

        if (!$result)
          throw new Exception(JText::_('PLG_JSERIPPAYMENT_ORDER_ERROR'));

        $instruction = JText::_('PLG_JSERIPPAYMENT_ERIP_INSTRUCTION');
        $instruction = str_replace('#TABS#', '<strong>' . $pmconfigs['tree_path_email'] . '</strong>', $instruction);
        $instruction = str_replace('#ORDER_ID#', '<strong>' . $order->order_id . '</strong>', $instruction);
        echo nl2br($instruction);
      } else {
        echo nl2br(JText::_('PLG_JSERIPPAYMENT_ORDER_CONFIRMATION'));
      }
		}
		catch (RuntimeException $e)
		{
			echo JText::_('PLG_JSERIPPAYMENT_ORDER_ERROR');
		}

    echo '</div>';
	}
}
