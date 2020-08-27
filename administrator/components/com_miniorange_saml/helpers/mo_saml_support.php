<?php

function mo_saml_local_support(){

	$current_user =  JFactory::getUser();
	$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$result = $db->loadAssoc();
		$admin_email = $result['email'];
		$admin_phone = $result['admin_phone'];
	
	if($admin_email == '')
		$admin_email = $current_user->email;
	

	
?>
	<div class="mo_saml_support_layout">
		<h3>Support</h3>
			<form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_saml&task=myaccount.contactUs');?>">
				<div>Need any help? Just send us a query so we can help you.<br /><br /></div>
				<div>
					<table class="mo_saml_settings_table">
						<tr><td>
							<input type="email" class="mo_saml_table_textbox" id="query_email" name="query_email" value="<?php echo $admin_email; ?>" placeholder="Enter your email" required />
							</td>
						</tr>
						<tr><td>
							<input type="text" class="mo_saml_table_textbox" name="query_phone" id="query_phone" value="<?php echo $admin_phone; ?>" placeholder="Enter your phone"/>
							</td>
						</tr>
						<tr>
							<td>
								<textarea id="query" name="query" class="mo_saml_settings_textarea" style="border-radius:4px;resize: vertical;width:100%" cols="52" rows="7" onkeyup="mo_saml_valid(this)" onblur="mo_saml_valid(this)" onkeypress="mo_saml_valid(this)" placeholder="Write your query here"></textarea>
							</td>
						</tr>
					</table>
				</div>
				<input type="hidden" name="option1" value="mo_saml_login_send_query"/>
				<input type="submit" name="send_query" id="send_query" value="Submit Query" style="margin-bottom:3%;" class="btn btn-medium btn-success" />
			</form>
			<br />			
	</div>
	<script>
		//jQuery("#query_phone").intlTelInput();
		function mo_saml_valid(f) {
			!(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
		}
	</script>
<?php
}
?>