<?php

$raw_post_data 	= file_get_contents('php://input');
$json = json_decode($raw_post_data,true);

if (!is_array($json)) {
	exit;
}

if ($json['transaction']['status'] != 'successful') {
	exit;
}


if(!defined('_JEXEC')) define( '_JEXEC', 1 );
$get_file_info  = pathinfo(__FILE__);
$jpath = preg_replace('/(templates|modules|components|plugins)(.*)/','',$get_file_info['dirname']);
define('JPATH_BASE',rtrim($jpath,DIRECTORY_SEPARATOR));
require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'defines.php' ); 
require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'framework.php' );

jimport('joomla.session.session');
$mainframe  = JFactory::getApplication('site');// change if site
$mainframe->initialise();

$now	= JFactory::getDate()->toSQL();
$db 	= JFactory::getDBO();
$query 	= $db->getQuery(true);

$query->clear();
$query = $db->getQuery(true);
$query->select('*')
	->from($db->quoteName('#__jshopping_payment_method') . ' AS pm')
	->where($db->quoteName('pm.scriptname') . ' = "pm_erip"');
$db->setQuery($query);
$payment_details = $db->loadObject();

if (!isset($payment_details->payment_params)) {
	exit;
}

$payment_details_explode = explode(PHP_EOL,$payment_details->payment_params);

if(empty($payment_details_explode)) {
	exit;
}

$payment_format = array();
foreach ($payment_details_explode as $value) {
	$value_explode = explode("=",$value);
	if(isset($value_explode[1])){
		$payment_format[$value_explode[0]] = $value_explode[1];
	}
}

if (empty($payment_format) || ($payment_format['shop_id'] != $_SERVER['PHP_AUTH_USER'] || $payment_format['shop_key'] != $_SERVER['PHP_AUTH_PW'])) {
	exit;
}

$filter 	= JFilterInput::getInstance();
$order_id	= (int)$json['transaction']['order_id'];
$order_id  	= $filter->clean($order_id,'int');

if($order_id > 0){

	try {
    $query	= "UPDATE #__jshopping_orders SET `order_status` = 2, transaction = '" . $json['transaction']['uid'] . "' WHERE order_id = " . $order_id;
    $db->setQuery($query);
    $db->execute();

    $query = "INSERT INTO #__jshopping_order_history(`order_id`,`order_status_id`,`status_date_added`,`comments`) VALUES (" . $order_id . ",2,now(),'" . $json['transaction']['uid'] . "')";
    $db->setQuery($query);
    $db->execute();

		// Check for a database error.
/*		if ($db->getErrorNum()) {
			$log_action = "Database not updated";
		}else{
			$log_action = "Database updated";
		}*/

	}
	catch (RuntimeException $e)
	{
		//$log_action = "Database not updated";
	}
}
