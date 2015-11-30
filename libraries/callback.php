<?php

$raw_post_data 	= file_get_contents('php://input');
$raw_post_data  = str_replace("{","",$raw_post_data);
$raw_post_data  = str_replace("}","",$raw_post_data);
$raw_post_array = explode(',', $raw_post_data);
$myPost 		= array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode (':', $keyval);
  $keyval = str_replace('"','',$keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}

if (empty($myPost)) {
	exit;
}

if ($myPost['state'] != 'successful') {
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

$filter 	= JFilterInput::getInstance();
$order_id	= (int)$myPost['order_id'];
$order_id  	= $filter->clean($order_id,'int');

if($order_id > 0){

	$query	= " UPDATE #__jshopping_orders SET `order_status` = 2 WHERE order_id = " . $order_id;
	$db->setQuery($query);

	try {
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
