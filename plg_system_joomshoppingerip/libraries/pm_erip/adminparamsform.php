<?php



defined('_JEXEC') or die('Restricted access');

?>

<div class="col100">

<fieldset class="adminform">

<table class="admintable" width = "100%" >

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_SHOP_ID_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[shop_id]" size="45" value = "<?php echo $params['shop_id']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_SHOP_KEY_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[shop_key]" size="45" value = "<?php echo $params['shop_key']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_API_URL_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[api_url]" size="45" value = "<?php echo $params['api_url']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_SERVICE_NO_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[service_no]" size="45" value = "<?php echo $params['service_no']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_COMPANY_NAME_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[company_name]" size="45" value = "<?php echo $params['company_name']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_PATH_EMAIL_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[tree_path_email]" size="45" value = "<?php echo $params['tree_path_email']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_RECEIPT_TEXT_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[receipt_text]" size="45" value = "<?php echo $params['receipt_text']?>" />

   </td>

 </tr>

 <tr>

   <td  class="key">

     <?php echo JText::_('PLG_JSERIPPAYMENT_PARAMS_SERVICE_TEXT_LABEL');?>

   </td>

   <td>

     <input type = "text" class = "inputbox" name = "pm_params[service_text]" size="45" value = "<?php echo $params['service_text']?>" />

   </td>

 </tr>

</table>

</fieldset>

</div>

<div class="clr"></div>
