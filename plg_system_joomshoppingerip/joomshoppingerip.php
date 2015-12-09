<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.redirect
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Plugin class for JoomShoppingErip handling.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.redirect
 * @since       1.6
 */
class PlgSystemJoomShoppingErip extends JPlugin
{
	protected $api_url;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onAfterRoute()
	{
		$option 	= JRequest::getCmd('option');
		$task 		= JRequest::getCmd('task');
		$controller = JRequest::getCmd('controller');

		if ($option == "com_jshopping" && $controller == "orders") {//$task == "show" &&

			//Load language file.
			JPlugin::loadLanguage('plg_system_joomshoppingerip', JPATH_ADMINISTRATOR);

			$db 	= JFactory::getDBO();
			$query 	= "UPDATE #__jshopping_order_status SET `name_en-GB` = '".JText::_('PLG_JSERIPPAYMENT_PAYMENT_OPTION')."', `name_de-DE` = '".JText::_('PLG_JSERIPPAYMENT_PAYMENT_OPTION')."' WHERE `status_code` = 'A'";

			$db->setQuery($query);
			$db->execute();
		}
	}

	public function update($args){
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		if (!JFolder::exists(JPATH_ROOT.'/components/com_jshopping')) {
			throw new RuntimeException('JoomShopping component not found!');
			return false;
		}
		return $this->$args['event']($args);
	}

	public function onBeforeChangeOrderStatusAdmin($args){

		if (!JFolder::exists(JPATH_ROOT.'/components/com_jshopping')) {
			throw new RuntimeException('JoomShopping component not found!');
			return false;
		}

		//Load language file.
        JPlugin::loadLanguage('plg_system_joomshoppingerip', JPATH_ADMINISTRATOR);

		$db 	= JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->clear();
		$query->select('os.status_code')
			->from($db->quoteName('#__jshopping_order_status') . ' AS os')
			->where($db->quoteName('os.status_id') . ' = ' . (int) $args[1]);
		$db->setQuery($query);
		$new_status = $db->loadResult();

		if ($new_status != 'A') {
			return true;
		}

		$query->clear();
		$query->select('*')
			->from($db->quoteName('#__jshopping_payment_method') . ' AS pm')
			->where($db->quoteName('pm.scriptname') . ' = "pm_erip"');
		$db->setQuery($query);
		$payment_details = $db->loadObject();

		if (empty($payment_details) || empty($payment_details->payment_params)) {
			return true;
		}

		$payment_details_explode = explode("\n",$payment_details->payment_params);

		if (empty($payment_details_explode)) {
			return true;
		}

		$payment_format = array();
		foreach ($payment_details_explode as $value) {
			$value_explode = explode("=",$value);
			if(isset($value_explode[1])){
				$payment_format[$value_explode[0]] = $value_explode[1];
			}
		}

		if (empty($payment_format)) {
			return true;
		}

		$query->clear();
		$query->select('o.*')
			->from($db->quoteName('#__jshopping_orders') . ' AS o')
			->where($db->quoteName('o.order_id') . ' = ' . (int) $args[0]);
		$db->setQuery($query);
		$order_details = $db->loadObject();

		if (empty($order_details)) {
			return true;
		}

		$query->clear();
		$query->select('u.*')
			->from($db->quoteName('#__jshopping_users') . ' AS u')
			->where($db->quoteName('u.user_id') . ' = ' . (int) $order_details->user_id);
		$db->setQuery($query);
		$user_details = $db->loadObject();

		if (empty($user_details)) {
			return true;
		}

		$query->clear();
		$query->select('ju.*')
			->from($db->quoteName('#__users') . ' AS ju')
			->where($db->quoteName('ju.id') . ' = ' . (int) $order_details->user_id);
		$db->setQuery($query);
		$joomla_user_details = $db->loadObject();

		if (empty($joomla_user_details)) {
			return true;
		}

		$countries = JshopHelpersSelectOptions::getCountrys();

		$_country = JTable::getInstance('country', 'jshop');
		$_country->load($order_details->country);
		$country = $_country->country_code_2;

    $notification_url = JURI::root()."plugins/system/joomshoppingerip/libraries/callback.php";
		$notification_url = str_replace('carts.local','webhook.begateway.com:8443', $notification_url);

		$post_data=array();
		$post_data["request"]["amount"] = $order_details->order_total;
		$post_data["request"]["currency"] = $order_details->currency_code_iso;
		$post_data["request"]["description"] = JText::_('PLG_JSERIPPAYMENT_API_CALL_ORDER').$order_details->order_id;
		$post_data["request"]["email"] = $user_details->email;
		$post_data["request"]["ip"] = $_SERVER['REMOTE_ADDR'];
		$post_data["request"]["order_id"] = $order_details->order_id;
		$post_data["request"]["notification_url"] = $notification_url; 
		$post_data["request"]["customer"]["first_name"] = $user_details->f_name;
		$post_data["request"]["customer"]["last_name"] = $user_details->l_name;
		$post_data["request"]["customer"]["country"] = $country;
		$post_data["request"]["customer"]["city"] = $user_details->city;
		$post_data["request"]["customer"]["zip"] = $user_details->zip;
		$post_data["request"]["customer"]["address"] = $user_details->street . " " . $user_details->street_nr;
		$post_data["request"]["customer"]["phone"] = $user_details->phone;
		$post_data["request"]["payment_method"]["type"] = "erip";
		$post_data["request"]["payment_method"]["account_number"] = $order_details->order_id;
		$post_data["request"]["payment_method"]["service_no"] = $payment_format['service_no'];
		$post_data["request"]["payment_method"]["instruction"][] = $payment_format['payment_instruction'];
		$post_data["request"]["payment_method"]["service_info"][] = $payment_format['service_text'];
		$post_data["request"]["payment_method"]["receipt"][] = $payment_format['receipt_text'];

		//$post_data_format = http_build_query($post_data);
		$post_data_format = json_encode($post_data, JSON_NUMERIC_CHECK);

		$this->api_url = strstr($payment_format['api_url'],"http") ? $payment_format['api_url'] : "https://".$payment_format['api_url']."/beyag/payments";

		$response = $this->send_request_via_curl($post_data_format, $payment_format['shop_id'], $payment_format['shop_key']);

		$response_format = json_decode($response);

		if (isset($response_format->errors)) {
			//JError::raiseError(500, $response_format->message);
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_jshopping&controller=orders&task=show&order_id='.$order_details->order_id, $response_format->message, error);
			exit;
		}
		$config = JFactory::getConfig();
		try{

      $query = "INSERT INTO #__jshopping_order_history(`order_id`,`order_status_id`,`status_date_added`,`comments`) VALUES ($order_details->order_id," . (int)$args[1] . ",now(),'" . $response_format->transaction->uid . "')";
      $db->setQuery($query);
      $db->execute();

			$return = JFactory::getMailer()->sendMail(
			  $config->get('mailfrom'),
				$config->get('fromname'),
				$joomla_user_details->email,
			  JText::_('PLG_JSERIPPAYMENT_EMAIL_INSTRUCTION_SUBJECT'),
				JText::sprintf('PLG_JSERIPPAYMENT_EMAIL_INSTRUCTION',
			    $user_details->f_name . " " . $user_details->l_name,
					$order_details->order_id,
					JURI::root(),
					$payment_format['company_name'],
					$payment_format['tree_path_email'],
					$payment_format['tree_path_email'],
					$order_details->order_id
				)
			);
		}
		catch(RuntimeException $e){
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_jshopping&controller=orders&task=show&order_id='.$order_details->order_id, $e->getMessage(), error);
			exit;
			//JError::raiseWarning(500, $e->getMessage());
		}
		return true;
	}

	//function to send xml request via curl
	protected function send_request_via_curl($post_data, $shop_id, $shop_key)
	{
		$posturl = $this->api_url;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json",
                      'Content-Length: ' . strlen($post_data)));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$shop_id:$shop_key");
		$response = curl_exec($ch);

		return $response;
	}
}
