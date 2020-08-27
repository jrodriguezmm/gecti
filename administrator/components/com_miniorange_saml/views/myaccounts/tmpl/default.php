<?php
	JHtml::_('stylesheet', JUri::root() . 'media/com_miniorange_saml/css/miniorange_saml.css');
	
?>
<h2>miniOrange Saml Single Sign-On</h2>

<?php
	if(!Mo_Saml_Local_Util::is_curl_installed()) {
		?>
		
		<div id="help_curl_warning_title" class="mo_saml_title_panel">
			<p><a target="_blank" style="cursor: pointer;"><font color="#FF0000">Warning: PHP cURL extension is not installed or disabled. <span style="color:blue">Click here</span> for instructions to enable it.</font></a></p>
		</div>
		<div hidden="" id="help_curl_warning_desc" class="mo_saml_help_desc">
				<ul>
					<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Open php.ini file located under php installation folder.</li>
					<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Search for <b>extension=php_curl.dll</b> </li>
					<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Uncomment it by removing the semi-colon(<b>;</b>) in front of it.</li>
					<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Restart the Apache Server.</li>
				</ul>
				For any further queries, please <a href="mailto:info@miniorange.com">contact us</a>.								
		</div>
				
		<?php
	}
	
	if(!Mo_Saml_Local_Util::is_extension_installed('mcrypt')) {
		?>
		<p><font color="#FF0000">(Warning: <a target="_blank" href="http://php.net/manual/en/mcrypt.installation.php">PHP mcrypt extension</a> is not installed or disabled)</font></p>
		<?php
	}
	
$tab = "account";
if(isset($_GET['tab']) && !empty($_GET['tab'])){
	$tab = $_GET['tab'];
}
?>
	


<div class="form-horizontal">
    <ul class="nav nav-tabs" id="myTabTabs">
        <li <?php if($tab=="account") echo 'class="active"';?>><a href="#account-setup" data-toggle="tab">My Account</a></li>
        <li <?php if($tab=="description") echo 'class="active"';?>><a href="#description" data-toggle="tab">Description</a></li>
		<li <?php if($tab=="idp") echo 'class="active"';?>><a href="#identity-provider" data-toggle="tab">Identity Provider Settings</a></li>
        <li <?php if($tab=="sso_settings") echo 'class="active"';?>><a href="#service-provider" data-toggle="tab">SSO Login Settings</a></li>
        <li <?php if($tab=="attribute_mapping") echo 'class="active"';?>><a href="#attribute-mapping" data-toggle="tab">Attribute Mapping</a></li>
        <li <?php if($tab=="group_mapping") echo 'class="active"';?>><a href="#group-mapping" data-toggle="tab">Group Mapping</a></li>
        <li <?php if($tab=="troubleshooting") echo 'class="active"';?>><a href="#help" data-toggle="tab">Troubleshooting</a></li>
		<li <?php if($tab=="licensing") echo 'class="active"';?>><a href="#licensing-plans" data-toggle="tab">Licensing Plan</a></li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div id="account-setup" class="tab-pane <?php if($tab=='account') echo 'active';?> ">
            <div class="row-fluid">
                <?php account_tab(); ?>
            </div>
        </div>
		<div id="description" class="tab-pane">
             <table style="width:100%;">
				<tr>	<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php description();
						?>
					</td>
					
				</tr>
			</table> 
        </div>
        <div id="service-provider" class="tab-pane">
            <div class="row-fluid">
				<table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php mo_sso_login();
						?>
					</td>
					
				</tr>
			 </table> 
            </div>
        </div>
        <div id="identity-provider" class="tab-pane <?php if($tab=='idp') echo 'active';?>">
            <div class="row-fluid">
			  <table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php identity_provider_settings();
						?>
					</td>
				</tr>
		   	 </table> 
            </div>
        </div>
        <div id="attribute-mapping" class="tab-pane <?php if($tab=='attribute_mapping') echo 'active';?>">
            <div class="row-fluid">
                <table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php attribute_mapping();
						?>
					</td>
					
				</tr>
			 </table> 
            </div>
        </div>
		<div id="group-mapping" class="tab-pane <?php if($tab=='group_mapping') echo 'active';?>">
            <div class="row-fluid">
                <table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php group_mapping();
						?>
					</td>
				</tr>
			 </table> 
            </div>
        </div>
        <div id="licensing-plans" class="tab-pane">
            <div class="row-fluid">
                <table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php Licensing_page();
						?>
					</td>
					
				</tr>
			</table> 
            </div>
        </div>
        <div id="help" class="tab-pane">
            <div class="row-fluid">
                <table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" class="configurationForm">
						<?php help();
						?>
					</td>
					
				</tr>
			</table>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function() {

	jQuery('.premium').click(function() {
		 jQuery('.nav-tabs a[href=#licensing-plans]').tab('show');   
    });
	
});
</script>

<?php  

function account_tab(){
	?>
	
   <table style="width:100%;">
				<tr>
					<td style="width:65%;vertical-align:top;" id="registrationForm">
						<?php 
							$customer_details = Mo_Saml_Local_Util::getCustomerDetails();
							$login_status = $customer_details['login_status'];
							$registration_status = $customer_details['registration_status'];
							
							if($login_status){
								mo_saml_local_login_page();
							}else if($registration_status == 'MO_OTP_DELIVERED_SUCCESS' || $registration_status == 'MO_OTP_VALIDATION_FAILURE' || $registration_status == 'MO_OTP_DELIVERED_FAILURE'){
							mo_saml_local_show_otp_verification();
							}else if (! Mo_Saml_Local_Util::is_customer_registered()) {
								mo_saml_local_registration_page();
							}else{
								mo_saml_local_account_page();
							}
						?>
					</td>
					<td style="vertical-align:top;padding-left:1%;">
						<?php echo mo_saml_local_support(); ?>
					</td>
				</tr>
			</table>
	<?php
}

		
		function mo_saml_local_login_page() {
		?>

<form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.verifyCustomer');?>">
			<input type="hidden" name="option1" value="mo_saml_local_verify_customer" />
			<div class="mo_saml_table_layout">
				<h3>Login with miniOrange</h3>
				<div id="panel1">
					<table class="mo_saml_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_saml_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							<td><input class="mo_saml_table_textbox" required type="password"
								name="password" placeholder="Enter your miniOrange password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" class="btn btn-medium btn-success" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a
								href="#cancel_link">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<a href="#mo_saml_local_forgot_password_link">Forgot your password?</a></td>
						</tr>
					</table>
				</div>
			</div>
			
			
			
		</form>
		
		<form id="forgot_password_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.forgotPassword');?>">
			<input type="hidden" name="option1" value="user_forgot_password" />
		</form>
		<form id="cancel_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.cancelform');?>">
			<input type="hidden" name="option1" value="mo_saml_local_cancel" />
		</form>
		<script>

			jQuery('a[href=#cancel_link]').click(function(){
				jQuery('#cancel_form').submit();
			});

			jQuery('a[href=#mo_saml_local_forgot_password_link]').click(function(){
				jQuery('#forgot_password_form').submit();
			});
		</script>
		
	<?php 
		}
	function description(){
		$siteUrl = JURI::root();
		$sp_base_url= $siteUrl;
		$sp_entity_id=$siteUrl.'plugins/authentication/miniorangesaml/';

?>	<p>This Plugin acts as a SAML 2.0 Service Provider which can be configured to establish the trust between the plugin and various SAML 2.0 supported Identity Providers to securely authenticate the user to the Joomla site.</p><br>
<a href='http://miniorange.com/joomla-single-sign-on-guide' target='_blank'>Click here</a> for detailed documentaion to setup the plugin. <br><br>

You will need the following information to configure your IdP. Copy it and keep it handy:<br>
<table class='table table-bordered table-hover table-striped'>
<tr class='info'>
<td><b>SP-EntityID / Issuer</b></td>
<td><span class='site-url'><?php echo $sp_entity_id;?></span></td>
</tr>
<tr>
<td><b>ACS (AssertionConsumerService) URL / Single Sign-On URL (SSO)</b></td>
<td><?php echo $sp_base_url.'?morequest=acs';?></td>
</tr>
<tr class='info'>
<td><b>Single Logout URL (SLO)</b></td>
<td>Available in the <b><a href='#' class='premium'><b>Premium</b></a></b> version</td>
</tr>
<tr>
<td><b>Audience URI</b></td>
<td><?php echo $sp_base_url.'?morequest=sso';?></td>
</tr>
<tr class='info'>
<td><b>NameID Format</b></td>
<td>urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</td>
</tr>
<tr>
<td><b>Default Relay State (Optional)</b></td>
<td>Available in the <b><a href='#' class='premium'><b>Premium</b></a></b> version</td>
</tr>
<tr class='info'>
<td><b>Certificate (Optional)</b></td>
<td>Available in the <b><a href='#' class='premium'><b>Premium</b></a></b> version</td>
</tr>
</table>
<script>
jQuery(document).ready(function() {
	var basepath = window.location.href;
	basepath = basepath.substr(0,basepath.indexOf('administrator')) + 'plugins/authentication/miniorangesaml/';
	jQuery('.site-url').text(basepath);
	
	jQuery('.premium').click(function() {
		 jQuery('.nav-tabs a[href="#attrib-licensing_plans"]').tab('show');   
    });
	
});
</script>
<p class='text-center'>OR</p>
<p>Provide Metadata URL to your Identity Provider: Metadata URL is available in the <a href='#' class='premium'><b>Premium</b></a> version of the plugin.</p>
<a href='http://miniorange.com/miniorange_as_idp_joomla' target='_blank'>Click here</a> to see step by step guide to configure miniorange as IDP.<br><br>
We also have step by step <b>Do It Yourself Guides</b> available for all known IdPs - Google Apps, ADFS, Okta, Salesforce, Shibboleth, SimpleSAMLphp, OpenAM, Centrify, Ping, RSA, IBM, Oracle, OneLogin, Bitium, WSO2, NetIQ etc. <b>( Supported in <a href='#' class='premium'><b>Premium</b></a> Version )</b> If you can't find your Idp in this list, write us the name of your Idp via Contact Form or you can email us at info@miniorange.com. <a href='http://miniorange.com/contact' target='_blank'>Click here</a> to Contact Us.<br><br>
<h3>Identity Provider Settings</h3>Here you can set the IDP settings like IDP Entity ID, Single SignOn Service Url and X.509 Certificate.<br><br><strong>Warning! You must have atleast one authentication plugin enabled or else you will lose all access to your site.</strong><br><br>
<h3>Attribute Mapping</h3>Sometimes the names of the attributes sent by the IdP not match the names used by Joomla for the user accounts. In this section we can set the mapping between IdP fields and Joomla fields.<br><br>
<h3>Group Mapping</h3>You can map the user groups in IdP to Joomla user groups at the time of auto registration of users.<br><br>
	<?php	
}	

function identity_provider_settings()
{
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	$query->select('*');
	$query->from($db->quoteName('#__miniorange_saml_config'));
	$query->where($db->quoteName('id')." = 1");

	$db->setQuery($query);
	$attribute = $db->loadAssoc();
	$idp_entity_id = ""; 
	$single_signon_service_url = ""; 
	$certificate = "";
	if(isset($attribute['idp_entity_id'])){
		$idp_entity_id= $attribute['idp_entity_id']; 
		$single_signon_service_url= $attribute['single_signon_service_url']; 
		$certificate= $attribute['certificate'];
	}	
	
	?>
	<form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.saveConfig'); ?>" method="post" name="adminForm" id="identity_provider_settings_form">
		<input id="mo_saml_local_configuration_form_action" type="hidden" name="option1" value="mo_saml_save_config" />

	<table class="mo_saml_settings_table">
				<tr>
					<td><b>IdP Entity ID*&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><input  class="mo_saml_table_textbox" type="text" name="idp_entity_id" value="<?php echo $idp_entity_id;?>"
						required />
						</td>
				</tr>
				<tr>
					<td><b>Single Sign-On Service Url* &nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><input class="mo_saml_table_textbox" type="text" name="single_signon_service_url" value="<?php echo $single_signon_service_url;?>" required /></td>
				</tr>
				<tr>
					<td><b>X.509 Certificate*&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><textarea rows="8" cols="5" class="mo_saml_table_textbox"  name="certificate" placeholder="Copy and Paste the content from the downloaded certificate or copy the content enclosed in 'X509Certificate' tag (has parent tag 'KeyDescriptor use=signing') in IdP-Metadata XML file " style="width: 95%;"><?php echo $certificate;?></textarea>
						</td>
				
				<td>&nbsp;</td>
				<td><b>NOTE:</b> Format of the certificate:<br/><b>-----BEGIN CERTIFICATE-----<br/>XXXXXXXXXXXXXXXXXXXXXXXXXXX<br/>-----END CERTIFICATE-----</b></i><br/>
				</tr>
	
				<tr>
					<td><input type="submit" class="btn btn-medium btn-success" value="Save"/><input type="button" id='test-config' title='You can only test your configuration after saving your Identity Provider Settings. 'class='btn btn-primary' onclick='showTestWindow()' style="margin-left:10px" value="Test Configuration">
					</td>
				</tr>
			</table>
			<br><p>You can only test your configuration after saving your Identity Provider Settings.</p>
		</form>

		<script>
			<?php if (! Mo_Saml_Local_Util::is_customer_registered()) { ?>
				jQuery( document ).ready(function() {
						jQuery("#identity_provider_settings_form :input").prop("disabled", true);
						jQuery("#identity_provider_settings_form :input[type=text]").val("");
						jQuery("#identity_provider_settings_form :input[type=submit]").prop("disabled", true);
						jQuery("#identity_provider_settings_form :button").prop("disabled", true);
					});
			<?php } ?>
			function showTestWindow() {
				var testconfigurl = window.location.href;
				testconfigurl = testconfigurl.substr(0,testconfigurl.indexOf('administrator')) + '?morequest=sso&q=test_config';
				var myWindow = window.open(testconfigurl, 'TEST SAML IDP', 'scrollbars=1 width=800, height=600');	
			}
		</script>
	<?php	
}
function Licensing_page() { ?>
		<div class='container'><div><div class='span4'><div class='thumbnail text-center alert-info'><div class='caption'><h3 >Free</h3><p style='margin-bottom: 27px;!important' >( You are automatically on this plan )<br></p><hr><p>$0</p><hr><p>Unlimited Authentications via IDP<br>
Basic Role Mapping<br>Basic Attribute Mapping<br><br><br><br><br><br><br><br><br><hr><p>Basic Support by Email</p></div></div></div><div class='span4'><div class='thumbnail text-center alert-info'><div class='caption'><h3>Do it yourself Plan</h3><p><a href='<?php echo Mo_Saml_Local_Util::getHostname();?>/moas/login?redirectUrl=<?php echo Mo_Saml_Local_Util::getHostname();?>/moas/initializepayment&requestOrigin=joomla_saml_sso_basic_plan' class='btn btn-primary' role='button' target='_blank'>Click here to upgrade</a> *</p><hr><p>$349 - One Time Payment</p><hr><p>Unlimited Authentications supported *<br>
Customized Role Mapping<br>Customized Attribute Mapping<br>Auto-Redirect to Idp<br>Step-By-Step Guide to Setup IdP<br>Single Logout<br>Multi-Site Support<br>Backend and Frontend Login for Super Users<br><br><br><br></p><hr><p>Basic Support by Email</p></div></div></div><div class='span4'><div class='thumbnail text-center alert-info'><div class='caption'><h3>Premium Plan</h3><p><a href='<?php echo Mo_Saml_Local_Util::getHostname();?>/moas/login?redirectUrl=<?php echo Mo_Saml_Local_Util::getHostname();?>/moas/initializepayment&requestOrigin=joomla_saml_sso_premium_plan' class='btn btn-primary' role='button' target='_blank'>Click here to upgrade</a> *</p><hr><p>$349 - One Time Setup Fees ( $60 per hour )</p><hr><p>
Unlimited Authentications supported *<br>Customized Role Mapping<br>Customized Attribute Mapping<br>Auto-Redirect to Idp<br>Step by step Guide to Setup IdP<br>Single Logout<br>Multi-site Support<br>Backend and Frontend Login for Super Users<br />Integrated Windows Authentication<br>Multiple IDP Support for Cloud Service Providers<br>End to End Identity Provider Configuration **<br>
</p><hr><p>Premium Support Plans Available</p></div></div></div></div>
<br>
<div><div class='span10'>
<h4>* This the price for 1 instance. Check our pricing page for full details.</h4>
<h4>Steps to Upgrade to Premium Plugin -</h4>
<p>1. You will be redirected to miniOrange Login Console. Enter your username and password with which you created an account with us. After that you will be redirected to payment page.</p>
<p>2. Enter you card details and complete the payment. On successful payment completion, you will see the link to download the premium plugin.</p>
<p>3. Once you download the premium plugin, first delete existing plugin then install the premium plugin. <br>
<h4>** End to End Identity Provider Integration - </h4>
<p>We will setup a Conference Call / Gotomeeting and do end to end configuration for you for IDP as well as plugin. We provide services to do the configuration on your behalf. </p>
<p>If you have any doubts regarding the licensing plans, you can email us at <b>info@miniorange.com</b></a>.
</p></div></div></div>
		
	<?php }
function group_mapping(){

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	$query->select('*');
	$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
	$query->where($db->quoteName('id')." = 1");

	$db->setQuery($query);
	$role_mapping = $db->loadAssoc();
	$role_mapping_key_value = array();
	if(isset($role_mapping['mapping_value_default']))
		$mapping_value_default = $role_mapping['mapping_value_default'];
	else
		$mapping_value_default = "";
	$enable_role_mapping = 0;
	if(isset($role_mapping['enable_saml_role_mapping']))
		$enable_role_mapping = $role_mapping['enable_saml_role_mapping'];
?>
<form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.saveRolemapping'); ?>" method="post" name="adminForm" id="group_mapping_form">
		<input id="mo_saml_local_configuration_form_action" type="hidden" name="option1" value="mo_saml_group_mapping" />

		<input type="checkbox" name="enable_role_mapping" value="1" <?php if($enable_role_mapping==1) echo "checked"; ?> style="float: left;margin-right: 10px;"> Enable Group Mapping
		
<table class="mo_saml_settings_table">
<tr><td>
Select default group for the new users.&nbsp;&nbsp;</td>
  <td>
								
								<select name="mapping_value_default" style="width:100%" id="default_group_mapping">
									 
									<?php
										$noofroles = 0;
							   
										//$default_role = $role_mapping['default_role'];
										?>  <?php
										$db = JFactory::getDbo();
										$db->setQuery($db->getQuery(true)
											->select('*')
											->from("#__usergroups")
										);
										$groups = $db->loadRowList();
										foreach ($groups as $group) {
											if($mapping_value_default ==  $group[0])
												echo	'<option selected="selected" value = "'. $group[0].'">'.$group[4].'</option>';
											else
												echo	'<option  value = "'. $group[0].'">'.$group[4].'</option>';
										}
										
									?>
								</select>
								<select style="display:none" id="wp_roles_list">
									<?php   
										$db = JFactory::getDbo();
										$db->setQuery(
											'SELECT `title`' .
											' FROM `#__usergroups`'
											
										);
										$groupNames = $db->loadColumn();
										$noofroles = count($groupNames);
										
										for($i = 0; $i < $noofroles;  $i++) {
											echo	'<option  value = "'. $groupNames[$i].'">'.$groupNames[$i].'</option>';
										} 
									?>
								</select>
							</td>
						</tr>
  </table>
<br><p class='alert alert-info'>NOTE: Customized group mapping options shown below are configurable in the <a href="#" class='premium'><b>Premium</b></a> version of plugin.</p>

<table class="mo_saml_settings_table" id="saml_role_mapping_table">

						<tr>
							<td style="width:20%"><b>Group Name in Joomla</b></td>
							<td style="width:50%"><b>Group Name from IDP</b></td>
						</tr>
						
						<?php 
						$user_role=array();
							$db = JFactory::getDbo();
		$db->setQuery($db->getQuery(true)
			->select('*')
			->from("#__usergroups")
		);
		$groups = $db->loadRowList();
					if(empty($role_mapping_key_value)){
						foreach ($groups as $group) {
							if($group[4] != 'Super Users'){
								echo '<tr><td><b>' . $group[4] .'</b></td><td><input type="text" name="saml_am_group_attr_values_' . $group[0] . '" value= "" placeholder="Semi-colon(;) separated Group/Role value for ' . $group[4] . '"  disabled style="width: 400px;"' . ' /></td></tr><tr><td></td><td></td></tr>';
							}
						}?>
						 <tr>
														
							<td>
								    </select>
							</td>
						</tr>
						<?php }else{
								$j = 1;
							foreach ($role_mapping_key_value as $mapping_key=>$mapping_value){
							?>	<tr>
									<td><input class="mo_saml_table_textbox"  type="text" name="mapping_key_<?php echo $j;?>"
										 value="<?php echo $mapping_key;?>"  placeholder="cn=group,dc=domain,dc=com" />
									</td>
									<td>
										<select name="mapping_value_<?php echo $j;?>" id="role" style="width:100%">
											 <?php
													$db = JFactory::getDbo();
													$db->setQuery(
														'SELECT `title`' .
														' FROM `#__usergroups`'
														
													);
													$groupNames = $db->loadColumn();
													$noofroles = count($groupNames);
													
													for($i = 0; $i < $noofroles ;  $i++) {
														if( $mapping_value == $groupNames[$i])
															echo	'<option selected="selected" value = "'. $groupNames[$i].'">'.$groupNames[$i].'</option>';
														else
															echo '<option value = "'. $groupNames[$i].'">'.$groupNames[$i].'</option>';
													}
											 ?>
										</select>
										
										
									</td>
								</tr>
									
								
						<?php $j++;	}
						}
						
						?>
						
											
				</table>
				<p><input type="submit" class="btn btn-medium btn-success" value="Save"/></p>
							
</form>
<script>

	jQuery('#add_mapping').click(function() {
		var dropdown = jQuery("#wp_roles_list").html();
		var new_row = '<tr><td><input disabled class="mo_saml_table_textbox" type="text" placeholder="cn=group,dc=domain,dc=com" name="mapping_key_1" value="" /></td><td><select disabled name="mapping_value_1" style="width:100%" id="role">'+dropdown+'</select></td></tr>';
		jQuery('#saml_role_mapping_table tr:last').after(new_row);
	});
	
	<?php if (! Mo_Saml_Local_Util::is_customer_registered()) { ?>
		jQuery( document ).ready(function() {
			jQuery("#group_mapping_form :input").prop("disabled", true);
			jQuery("#group_mapping_form :input[type=text]").val("");
		});
		<?php } ?>
	</script>
	<?php	
}

function mo_sso_login(){
	$siteUrl = JURI::root();
	$sp_base_url = $siteUrl;
?>
<h3><b>1. Add a link or button on your site login page.</b></h3>
<p style='font-weight:normal!important;'>Step 1: Go to Template Manager under extensions. Click on Templates in the sidebar.<br><br>
Step 2: Select the site template that is currently being used (for example: Protostar).<br><br>
Step 3: Now select default_login.php under html->com_users->login. Search for the JLOGIN button in default_login.php.<br><br>
Step 4: After this button, add the SAML Login link by adding code - <br>
</p>
<code>
&lsaquo;a href='<?php echo $sp_base_url.'?morequest=sso';?>' 		
 style='padding-left:20px;'&rsaquo;Login with Idp&lsaquo;/a&rsaquo;
</code>
<br><br>
<p><h3><b>2. Auto Redirect the user to IDP.&nbsp;&nbsp;<a href='#auto-redirect' class='collapsed' data-toggle='collapse'>What does this mean?</a>"</b></h3>
<div id='auto-redirect' class='collapse'>Enable this if you want to restrict your site to only logged in users. Enabling this plugin will redirect the users to your IdP if logged in session is not found.<br></div>
This feature is available in the <a href='#' class='premium'><b>Premium</b></a> version of plugin.</p>
<?php	
}
	function attribute_mapping(){

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	$query->select('*');
	$query->from($db->quoteName('#__miniorange_saml_config'));
	$query->where($db->quoteName('id')." = 1");

	$db->setQuery($query);
	$attribute = $db->loadAssoc();
	
	if(isset($attribute['username'])){
		$username_attr = $attribute['username']; 
		$email_attr = $attribute['email']; 
		$name_attr = $attribute['name']; 
		$group_attr = $attribute['grp']; 
		$enable_email = $attribute['enable_email']; 
	}else{
		$username_attr = ""; 
		$email_attr = ""; 
		$name_attr = ""; 
		$group_attr = ""; 
		$enable_email = 0; 
	}
?>
<a class='collapsed' data-toggle='collapse'  href='#info1' aria-expanded='false'>Click here to know how atrribute mapping is useful ?</a>
<div id='info1' class='collapse'><ol><li>Attributes are user details that are stored in your Identity Provider.</li>
<li>Attribute Mapping helps you to get user attributes from your IdP and map them to Joomla user attributes like firstname, lastname etc.</li>
<li>While auto registering the users in your Joomla site these attributes will automatically get mapped to your Joomla user details.</li>
</ol></div>
<br>
<form action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.saveConfig'); ?>" method="post" name="adminForm" id="attribute_mapping_form">
		<input id="mo_saml_local_configuration_form_action" type="hidden" name="option1" value="mo_saml_save_attribute" />


	Match (Login/Create) Joomla Account By
	<table class="mo_saml_settings_table">
		<tr>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<input class="mo_saml_table_textbox" type="radio" name="enable_email" value="1" <?php if($enable_email==1) echo "checked"; ?> /></td>
			<td><b>Email</b></td>
		</tr>
		<tr>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<input  class="mo_saml_table_textbox" type="radio" name="enable_email" value="0"  <?php if($enable_email==0) echo "checked"; ?> /></td>
			<td><b>Username</b></td>
		</tr>
	</table>	
<br>
<p class='alert alert-info'>NOTE: Use attribute name NameID if Identity is in the NameIdentifier element of the subject statement in SAML Response.</p>		
			   <table class="mo_saml_settings_table">
				<tr>
					<td><b>Username&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><input disabled class="mo_saml_table_textbox" type="text" name="username"
						required placeholder="NAME ID" value="NAME_ID" />
					</td>
				</tr>
				<tr>
					<td><b>Email&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><input disabled class="mo_saml_table_textbox" type="text" name="email"
						required placeholder="NAME ID" value="NAME_ID" />
					</td>
				</tr>
				<tr>
					<td><b>Name&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><input class="mo_saml_table_textbox" type="text" name="name" value="<?php echo $name_attr; ?>" placeholder="Enter Attribute Name for Name" /></td>
				</tr>
                <tr>
					<td><b>Group&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td><input disabled class="mo_saml_table_textbox" type="text" name="grp" value="<?php echo $group_attr; ?>" placeholder="Enter Attribute Name for Group" /></td>
				</tr>
				
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" class="btn btn-medium btn-success" value="Save Attribute Mapping"/>&nbsp;&nbsp; </td>
				</tr>
</table>
</form>
<p class='alert alert-info'>NOTE: Customized attribute mapping options shown above are configurable in the <a href='#' class='premium'><b>Premium</b></a> version of plugin.</p>

	<script>
		<?php if (! Mo_Saml_Local_Util::is_customer_registered()) { ?>
			jQuery( document ).ready(function() {
					jQuery("#attribute_mapping_form :input").prop("disabled", true);
					jQuery("#attribute_mapping_form :input[type=text]").val("");
					
				});
		<?php } ?>
	</script>

<?php	
}	
function help(){

?>
<a class='collapsed faqheading' data-toggle='collapse'  href='#faq1' aria-expanded='false'>How to setup this SAML SSO Plugin.</a>
<div id='faq1' class='collapse faqcontent'>Step 1: Setup your Identity Provider by following these steps:<br><br>
Step 2: Download X.509 certificate from your Identity Provider.<br><br>
Step 3: Enter appropriate values in the Identity Provider settings Tab. <a href='http://miniorange.com/joomla-single-sign-on-guide#idpurls' target='_blank'>Click here</a> to see sample values for some of the Idps.<br><br>
Step 4:  After saving your configuration. Go to template manager to add saml login link to your login page.<br><br>
<a href='http://miniorange.com/joomla-single-sign-on-guide' target='_blank'>Click here</a> for detailed documentaion to setup the plugin.</div>

<a class='collapsed faqheading' data-toggle='collapse'  href='#faq5' aria-expanded='false'>How to add login link or button to my joomla site login page.</a>
<div id='faq5' class='collapse faqcontent'>Step 1: Go to Template Manager under extensions. Click on Templates in the sidebar.<br><br>
Step 2: Select the site template that is currently being used (for example: Protostar).<br><br>
Step 3: Now select default_login.php under html->com_users->login. Search for the JLOGIN button in default_login.php.<br><br>
Step 4: After this button, add the SAML Login link by adding code - <br><br>
<code>
&lsaquo;a href='https://path-to-joomla-site/plugins/authentication/miniorangesaml/miniorangesaml.php' 		
 style='padding-left:20px;'&rsaquo;Login with Idp&lsaquo;/a&rsaquo;

 </code>
<br><br>
Edit the login link, replace path-to-joomla-site to your own site url. You can customize the look and feel of the login link as you want.<br><br> 
For detailed documentaion with screenshots to add the login link, <a href='http://miniorange.com/joomla-single-sign-on-guide#loginlink' target='_blank'>Click here</a> to see.
</div>

<a class='collapsed faqheading' data-toggle='collapse'  href='#faq2' aria-expanded='false'>I'm getting a 404 error page when I click on saml login link to login.</a>

<div id='faq2' class='collapse faqcontent'>This could mean that you have not entered the correct SAML Single Sign On Url. Please enter the correct SAML Login URL (with HTTP-Redirect binding) provided by your Identity Provider and try again.

If the problem persists, please contact us at info@miniorange.com or <a href='http://miniorange.com/contact' target='_blank'>click here</a> to contact us for support. It would be helpful if you could share your Identity Provider details with us.</div>

<a class='collapsed faqheading' data-toggle='collapse'  href='#faq3' aria-expanded='false'>I clicked on login link but I cannot see the login page of my Identity Provider.</a>
<div id='faq3' class='collapse faqcontent'>This could mean that you have not entered the correct SAML Single Sign On Url. Please enter the correct SAML Single Sign On URL (with HTTP-Redirect binding) provided by your Identity Provider and try again.

If the problem persists, please contact us at info@miniorange.com or <a href='http://miniorange.com/contact' target='_blank'>click here</a> to contact us for support. It would be helpful if you could share your Identity Provider details with us.</div>

<a class='collapsed faqheading' data-toggle='collapse'  href='#faq4' aria-expanded='false'>I logged in to my Identity Provider and it redirected me to Joomla site, but I'm not logged in.</a>

<div id='faq4' class='collapse faqcontent'>Here are the some frequent errors that can occur:<br><br>
<b>INVALID_ISSUER</b> : This means that you have NOT entered the correct Issuer or Entity ID value provided by your Identity Provider. You'll see in the error message what was the expected value (that you have configured) and what actually found in the SAML Response.<br><br>
<b>INVALID_AUDIENCE</b> : This means that you have NOT configured Audience URL in your Identity Provider correctly. It must be set to <b>https://path-to-joomla-site/plugins/authentication/miniorangesaml/</b> in your Identity Provider.<br><br>
<b>INVALID_DESTINATION</b> : This means that you have NOT configured Destination URL in your Identity Provider correctly. It must be set to <b>https://path-to-joomla-site/plugins/authentication/miniorangesaml/saml2/acs.php</b> in your Identity Provider.<br><br>
<b>INVALID_SIGNATURE</b> : This means that the certificate you provided did not match the certificate found in the SAML Response. Make sure you provide the same certificate that you downloaded from your IdP. If you have your IdP's Metadata XML file then make sure you provide certificate enclosed in X509 Certificate tag which has an attribute use="signing".<br><br>
<b>INVALID_CERTIFICATE</b> : This means that the certificate you provided is not in proper format. Make sure you have copied the entire certificate provided by your IdP. If coiped from IdP's Metadata XML file, make sure that you copied the entire value.<br><br>
If you need help resolving the issue, you can contact us at info@miniorange.com or <a href='http://miniorange.com/contact' target='_blank'>click here</a> to contact us for support. We will get back to you shortly.</div>

For any other query/problem/request, please feel free to contact us at info@miniorange.com or <a href='http://miniorange.com/contact' target='_blank'>click here</a> to submit a query.  We will get back to you as soon as possible.
	

<?php	
}	
		
		function mo_saml_local_account_page() {
	
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__miniorange_saml_customer_details'));
				$query->where($db->quoteName('id')." = 1");
		 
				$db->setQuery($query);
				$result = $db->loadAssoc();
				
				$email = $result['email'];
				$customer_key = $result['customer_key'];
				$api_key = $result['api_key'];
				$customer_token = $result['customer_token'];
				$hostname = Mo_Saml_Local_Util::getHostname();
				
			?>
			<div style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px; width:98%;height:344px">
				<div>
					<h4>Thank You for registering with miniOrange.</h4>
					<h3>Your Profile</h3>
					<table border="1" style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:85%">
						<tr>
							<td style="width:45%; padding: 10px;">Username/Email</td>
							<td style="width:55%; padding: 10px;"><?php echo $email?></td>
						</tr>
						<tr>
							<td style="width:45%; padding: 10px;">Customer ID</td>
							<td style="width:55%; padding: 10px;"><?php echo $customer_key?></td>
						</tr>
						<!--<tr>
							<td style="width:45%; padding: 10px;">API Key</td>
							<td style="width:55%; padding: 10px;"><?//php echo $api_key?></td>
						</tr>
						<tr>
							<td style="width:45%; padding: 10px;">Token Key</td>
							<td style="width:55%; padding: 10px;"><?//php echo $customer_token?></td>
						</tr>-->
					
					</table>
					
							<a href="#mo_saml_local_forgot_password_link">Forgot your password</a>
						
					<br/>
					<!--<p><a target="_blank" href="<?//php echo $hostname . "/moas/idp/userforgotpassword"; ?>">Click here</a> if you forgot your password to your miniOrange account.</p>-->
				</div>
			</div> 
				<form id="forgot_password_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.forgotPassword');?>">
			<input type="hidden" name="option1" value="user_forgot_password" />
		</form>
			<script>

			jQuery('a[href=#cancel_link]').click(function(){
				jQuery('#cancel_form').submit();
			});

			jQuery('a[href=#mo_saml_local_forgot_password_link]').click(function(){
				jQuery('#forgot_password_form').submit();
			});
		</script>
			<?php
		}

/* Show OTP verification page*/
function mo_saml_local_show_otp_verification(){
	?>
		<div class="mo_saml_table_layout">
			<div id="panel2">
				<table class="mo_saml_settings_table" style="width:100%">
		<!-- Enter otp -->
					<form name="f" method="post" id="saml_form" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.validateOtp');?>">
						<input type="hidden" name="option1" value="mo_saml_local_validate_otp" />
						<h3>Verify Your Email</h3>
						<tr>
							<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
							<td colspan="2"><input class="mo_saml_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:61%;" />
							 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP over Email</a></td>
						</tr>
						<tr><td colspan="3"></td></tr>
					<tr>
					<td></td>
					<td>
						<input type="button" value="Back" id="back_btn" class="btn btn-medium btn-primary" />
						<input type="submit" value="Validate OTP" class="btn btn-medium btn-success" />
					</td>
					</form>
						<td>
							<form method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.cancelform');?>" id="mo_saml_cancel_form">
								<input type="hidden" name="option1" value="mo_saml_local_cancel" />
							</form>
						</td>
					</tr>
					<tr>
						<td>
							<form name="f" id="resend_otp_form" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.resendOtp');?>">
								<input type="hidden" name="option1" value="mo_saml_local_resend_otp"/>
							</form>
						</td>
					</tr>
				</table>
				<br>
				<hr>

				<h3>I did not recieve any email with OTP . What should I do ?</h3>
				<form id="phone_verification" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.phoneVerification');?>">
					<input type="hidden" name="option1" value="mo_saml_local_phone_verification" />
					 If you can't see the email from miniOrange in your mails, please check your <b>SPAM Folder</b>. If you don't see an email even in SPAM folder, verify your identity with our alternate method.
					 <br><br>
						<b>Enter your valid phone number here and verify your identity using one time passcode sent to your phone.</b><br><br><input class="mo_saml_table_textbox" required="true" pattern="[\+]\d{1,3}\d{10}" autofocus="true" type="text" name="phone_number" id="phone" placeholder="Enter Phone Number" style="width:40%;"  title="Enter phone number without any space or dashes."/>
						<br><br><input type="submit" value="Send OTP on Phone" class="btn btn-medium btn-primary" />
				
				</form>
			</div>
		</div>
		<script>
	//jQuery("#phone").intlTelInput();
	jQuery('#back_btn').click(function(){
			jQuery('#mo_saml_cancel_form').submit();
	});
	
</script>
<?php
}
/* End Show OTP verification page*/

/* Create Customer function */
function mo_saml_local_registration_page(){
	//update_option ( 'mo_saml_local_new_registration', 'true' );
	$db = JFactory::getDbo();
 	$query = $db->getQuery(true);
	 // Fields to update.
	$fields = array(
		$db->quoteName('new_registration') . ' = ' . $db->quote(true)
	);
	 
	// Conditions for which records should be updated.
	$conditions = array(
		$db->quoteName('id') . ' = 1'
	);
	 
	$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
	$db->setQuery($query);
	$result = $db->execute();
	?>

<!--Register with miniOrange-->
<form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.registerCustomer');?>">
	<input type="hidden" name="option1" value="mo_saml_local_register_customer" />
	<p>Just complete the short registration below to configure your own saml Server. Please enter a valid email id that you have access to. You will be able to move forward after verifying an OTP that we will send to this email.</p>
	<div class="mo_saml_table_layout" style="min-height: 274px;">
		<h3>Register with miniOrange</h3>
		<div id="panel1">
			<table class="mo_saml_settings_table">
				<tr>
					<td><b><font color="#FF0000">*</font>Email:</b></td>
					<td>
					<?php 	$current_user =  JFactory::getUser();
							$db = JFactory::getDbo();
							$query = $db->getQuery(true);
							$query->select('*');
							$query->from($db->quoteName('#__miniorange_saml_customer_details'));
							$query->where($db->quoteName('id')." = 1");
					 
							$db->setQuery($query);
							$result = $db->loadAssoc();
							$admin_email = $result['email'];
							$admin_phone = $result['admin_phone'];							
							if($admin_email==''){
								$admin_email = $current_user->email;
							}
					
							 ?>
					<input class="mo_saml_table_textbox" type="email" name="email"
						required placeholder="person@example.com"
						value="<?php echo $admin_email;?>"  /></td>
				</tr>

				<tr>
					<td><b>Phone number:</b></td>
					<td><input class="mo_saml_table_textbox" type="tel" id="phone"
						pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" name="phone"
						title="Phone with country code eg. +1xxxxxxxxxx"
						placeholder="Phone with country code eg. +1xxxxxxxxxx"
						value="<?php echo $admin_phone;?>" />
						<i>We will call only if you call for support</i><br><br></td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Password:</b></td>
					<td><input class="mo_saml_table_textbox" required type="password"
						name="password" placeholder="Choose your password (Min. length 6)" />
					</td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
					<td><input class="mo_saml_table_textbox" required type="password"
						name="confirmPassword" placeholder="Confirm your password" />
						</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Save" class="btn btn-medium btn-success" /></td>
				</tr>
			</table>
		</div>
	</div>
</form>

<?php
}
/* End of Create Customer function */



?>