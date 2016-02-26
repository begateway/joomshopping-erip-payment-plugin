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
		//Load language file.
		JPlugin::loadLanguage('plg_system_joomshoppingerip', JPATH_ADMINISTRATOR);

		parent::__construct($subject, $config);
	}

	public function onAfterRoute()
	{
		$option 	= JRequest::getCmd('option');
		$task 		= JRequest::getCmd('task');
		$controller = JRequest::getCmd('controller');

		if ($option == "com_jshopping" && $controller == "orders") {//$task == "show" &&

			$db 	= JFactory::getDBO();
			$query 	= "UPDATE #__jshopping_order_status SET `name_en-GB` = '".JText::_('PLG_JSERIPPAYMENT_PAYMENT_OPTION')."', `name_de-DE` = '".JText::_('PLG_JSERIPPAYMENT_PAYMENT_OPTION')."' WHERE `status_code` = 'A'";

			$db->setQuery($query);
			$db->execute();
		}
	}

	public function update(&$args){
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		if (!JFolder::exists(JPATH_ROOT.'/components/com_jshopping')) {
      $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_JOOMSHOPPING_NOT_FOUND'));
		}
		return $this->$args['event']($args);
	}

	public function onBeforeChangeOrderStatusAdmin($args){

		if (!JFolder::exists(JPATH_ROOT.'/components/com_jshopping')) {
      $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_JOOMSHOPPING_NOT_FOUND'));
		}

		$config = JFactory::getConfig();

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
      $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_ERROR_PAYMENT_DETAILS'));
		}

		$payment_details_explode = explode("\n",$payment_details->payment_params);

		if (empty($payment_details_explode)) {
      $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_ERROR_PAYMENT_PARAMS'));
		}

		$payment_format = array();
		foreach ($payment_details_explode as $value) {
			$value_explode = explode("=",$value);
			if(isset($value_explode[1])){
				$payment_format[$value_explode[0]] = $value_explode[1];
			}
		}

		if (empty($payment_format)) {
      $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_ERROR_PAYMENT_PARAMS_EMPTY'));
		}

		$query->clear();
		$query->select('o.*')
			->from($db->quoteName('#__jshopping_orders') . ' AS o')
			->where($db->quoteName('o.order_id') . ' = ' . (int) $args[0]);
		$db->setQuery($query);
		$order_details = $db->loadObject();

		if (empty($order_details)) {
      $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_ERROR_ORDER_DETAILS'));
		}

		$query->clear();

		$countries = JshopHelpersSelectOptions::getCountrys();

		$_country = JTable::getInstance('country', 'jshop');
		$_country->load($order_details->country);
		$country = $_country->country_code_2;

    $notification_url = JURI::root()."plugins/system/joomshoppingerip/libraries/callback.php";
		$notification_url = str_replace('carts.local','webhook.begateway.com:8443', $notification_url);

    $order_number = ltrim($order_details->order_number,'0');

    $email = $order_details->email;

    if (strlen(trim($email)) == 0 ) {
      $email = $config->get('mailfrom');
    }

		$post_data=array();
		$post_data["request"]["amount"] = $order_details->order_total;
		$post_data["request"]["currency"] = $order_details->currency_code_iso;
		$post_data["request"]["description"] = JText::_('PLG_JSERIPPAYMENT_API_CALL_ORDER').$order_details->order_id;
		$post_data["request"]["email"] = $email;
		$post_data["request"]["ip"] = $_SERVER['REMOTE_ADDR'];
		$post_data["request"]["order_id"] = $order_details->order_id;
		$post_data["request"]["notification_url"] = $notification_url;
		$post_data["request"]["payment_method"]["type"] = "erip";
		$post_data["request"]["payment_method"]["account_number"] = $order_number;
		$post_data["request"]["payment_method"]["service_no"] = $payment_format['service_no'];
		$post_data["request"]["payment_method"]["service_info"][] = sprintf($payment_format['service_text'], $order_number);
		$post_data["request"]["payment_method"]["receipt"][] = sprintf($payment_format['receipt_text'], $order_number);

    if ($payment_format['customer_data'] == 1) {
  		$post_data["request"]["customer"]["first_name"] = $order_details->f_name;
  		$post_data["request"]["customer"]["last_name"] = $order_details->l_name;
  		$post_data["request"]["customer"]["country"] = $country;
  		$post_data["request"]["customer"]["city"] = $order_details->city;
  		$post_data["request"]["customer"]["zip"] = $order_details->zip;
  		$post_data["request"]["customer"]["address"] = $order_details->street . " " . $order_details->street_nr;
  		$post_data["request"]["customer"]["phone"] = $order_details->phone;
    }

		$post_data_format = json_encode($post_data, JSON_NUMERIC_CHECK);

		$this->api_url = strstr($payment_format['api_url'],"http") ? $payment_format['api_url'] : "https://".$payment_format['api_url']."/beyag/payments";

		$response = $this->send_request_via_curl($post_data_format, $payment_format['shop_id'], $payment_format['shop_key']);

		$response_format = json_decode($response);

		if (isset($response_format->errors)) {
      $this->report_error($order_details->order_id, $response_format->message);
		}

    if (isset($response_format->transaction) &&
        isset($response_format->transaction->status)) {
          if ($response_format->transaction->status == 'pending') {
            JFactory::getApplication()->enqueueMessage(JText::_('PLG_JSERIPPAYMENT_PAYMENT_REQUEST'));
          } else {
            $this->report_error((int)$args[0], JText::_('PLG_JSERIPPAYMENT_ERROR_PAYMENT_REQUEST'));
          }
        }

		try{

      $query = "INSERT INTO #__jshopping_order_history(`order_id`,`order_status_id`,`status_date_added`,`comments`) VALUES ($order_details->order_id," . (int)$args[1] . ",now(),'" . $response_format->transaction->uid . "')";
      $db->setQuery($query);
      $db->execute();

			$return = JFactory::getMailer()->sendMail(
			  $config->get('mailfrom'),
				$config->get('fromname'),
				$order_details->email,
			  JText::_('PLG_JSERIPPAYMENT_EMAIL_INSTRUCTION_SUBJECT'),
				JText::sprintf('PLG_JSERIPPAYMENT_EMAIL_INSTRUCTION',
			    $order_details->f_name . " " . $order_details->l_name,
					$order_details->order_number,
					$config->get('sitename'),
					$payment_format['company_name'],
					$payment_format['tree_path_email'],
					$payment_format['tree_path_email'],
					$order_number
				)
			);
		}
		catch(RuntimeException $e){
      $this->report_error($order_details->order_id, $e->getMessage());
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

    if (curl_errno($ch)) {
      $response = '{ "message" : "cURL: ' . curl_error($ch) . '", "errors":"cURL" }';
    }
    curl_close($ch);

		return $response;
	}

  protected function report_error($order_id, $message) {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_jshopping&controller=orders&task=show&order_id='.$order_id, $message, error);
			exit;
  }
}
