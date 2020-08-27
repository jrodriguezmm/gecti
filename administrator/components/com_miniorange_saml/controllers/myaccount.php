<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_miniorange_saml
 * @author     miniOrange Security Software Pvt. Ltd. <info@miniorange.com>
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
 * @license    GNU/GPLv3
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Myaccount controller class.
 *
 * @since  1.6
 */
class Miniorange_samlControllerMyaccount extends JControllerForm {
	function __construct(){
		$this->view_list = 'myaccounts';
		parent::__construct();
	}

	function verifyCustomer(){
		$post = JRequest::get( 'post' );
		
		$email = '';
		$password = '';
		
		if( Mo_Saml_Local_Util::check_empty_or_null( $post['email'] ) ||Mo_Saml_Local_Util::check_empty_or_null( $post['password'] ) ) {
			JError::raiseError( 4711, 'All the fields are required. Please enter valid entries.' );
			return;
		} else{
			$email =$post['email'];
			$password =  $post['password'] ;
		}
		
		$db = JFactory::getDbo();
 
		$query = $db->getQuery(true);
		 // Fields to update.
		$fields = array(
			$db->quoteName('email') . ' = ' . $db->quote($email),
			$db->quoteName('password') . ' = ' . $db->quote(base64_encode($password)),
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		
		$customer = new Mo_saml_Local_Customer();
		$content = $customer->get_customer_key();
		
		$customerKey = json_decode( $content, true );
		if( strcasecmp( $customerKey['apiKey'], 'CURL_ERROR') == 0) {
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',$customerKey['token'],'error');
		} else if( json_last_error() == JSON_ERROR_NONE ) {
			$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'],$customerKey['phone'],'Your account has been retrieved successfully.' );
		} else {
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account','Invalid username or password. Please try again.','error');
		}
	}

	/*
	 * Save all required fields on customer registration/retrieval complete.
	 */
	function save_success_customer_config($id, $apiKey, $token, $phone,$msg) {
		
		$db = JFactory::getDbo();
 
		$query = $db->getQuery(true);
		 // Fields to update.
		$fields = array(
			$db->quoteName('customer_key') . ' = '.$db->quote($id),
			$db->quoteName('api_key') . ' = '.$db->quote($apiKey),
			$db->quoteName('customer_token') . ' = '.$db->quote($token),
			$db->quoteName('admin_phone') . ' = '.$db->quote($phone),
			$db->quoteName('login_status') . ' = '.$db->quote(false),
			$db->quoteName('new_registration') .' = '.$db->quote(false),
			$db->quoteName('registration_status') .' = NULL'
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		
		$query = $db->getQuery(true);
		 
		$erMsg = 'Your account has been retrieved successfully.';
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',$erMsg);
		
	}
	
	
 function saveConfig(){
		
		if( $_POST['option1'] == "mo_saml_save_config" ) {
		
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				 // Fields to update.
				$fields = array(
					$db->quoteName('idp_entity_id') . ' = '.$db->quote( isset( $_POST['idp_entity_id']) ? $_POST['idp_entity_id'] : 0),
					$db->quoteName('single_signon_service_url') . ' = '.$db->quote( isset( $_POST['single_signon_service_url']) ? $_POST['single_signon_service_url'] : 0),
					$db->quoteName('binding') . ' = '.$db->quote('HttpRedirect'),
					$db->quoteName('certificate') . ' = '.$db->quote( isset( $_POST['certificate']) ? $_POST['certificate'] : 0)

				);
				
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				
				//delete_option('mo_saml_local_message');
				$mo_saml_config = new Mo_saml_Local_Config();

				//Save saml configuration
				$message =  'Your configuration has been saved successfully.';
				$status = 'success';
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=idp',$message );
				
			} else if( $_POST['option1'] == "mo_saml_save_attribute" ) {
				
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				 // Fields to update.
				$fields = array(
				$db->quoteName('enable_email') . ' = '.$db->quote( isset( $_POST['enable_email']) ? $_POST['enable_email'] : 0),
				//$db->quoteName('username') . ' = '.$db->quote( isset( $_POST['username']) ? $_POST['username'] : 0),
				//$db->quoteName('email') . ' = '.$db->quote( isset( $_POST['email']) ? $_POST['email'] : 0),
				$db->quoteName('name') . ' = '.$db->quote( isset( $_POST['name']) ? $_POST['name'] : 0),
				//$db->quoteName('grp') . ' = '.$db->quote( isset( $_POST['grp']) ? $_POST['grp'] : 0)					
				);
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				
				//delete_option('mo_saml_local_message');
				$mo_saml_config = new Mo_saml_Local_Config();

				//Save saml configuration
				$message =  'Your configuration has been saved successfully.';
				$status = 'success';
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=attribute_mapping',$message );
			}
		}	
	
	public function saveRolemapping(){
	
		$max_allowed_mappings = 100;
		$added_mappings_count = 0 ;
		$role_mapping_key_value = array();
		for($i=1;$i<=$max_allowed_mappings;$i++){
			if(isset($_POST['mapping_key_'.$i]) && isset($_POST['mapping_key_'.$i])){
				if($_POST['mapping_key_'.$i]=="")
					continue;
			$role_mapping_key_value[($_POST['mapping_key_'.$i])] = $_POST['mapping_value_'.$i];
				//update_option( 'mo_ldap_local_mapping_key_'.$i, $_POST['mapping_key_'.$i]);
				//update_option( 'mo_ldap_local_mapping_value_'.$i, $_POST['mapping_value_'.$i]);
				$added_mappings_count++;
			}else{
				break;
			}
		}
		//update_option( 'mo_ldap_local_role_mapping_count', $added_mappings_count);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$fields = array(
		
		$db->quoteName('mapping_value_default') . ' = ' . $db->quote( $_POST['mapping_value_default']),
		$db->quoteName('mapping_memberof_attribute') . ' = ' . $db->quote( $_POST['mapping_memberof_attribute']),
		$db->quoteName('enable_saml_role_mapping') . ' = ' . $db->quote( $_POST['enable_role_mapping']),
		$db->quoteName('role_mapping_key_value') . ' = ' . $db->quote(json_encode($role_mapping_key_value)),
		$db->quoteName('role_mapping_count') . ' = ' . $db->quote($added_mappings_count)
		);
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_role_mapping'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		
		
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$result = $db->loadAssoc();
		$enable_role_mapping = $result['enable_saml_role_mapping'];
		$statusMessage='';
		if(!$enable_role_mapping)
			$statusMessage = ' Please check <b>"Enable Group Mapping"</b> to activate it.';
		
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=group_mapping','SAML Group to Joomla role mapping has been updated.'.$statusMessage);
	}
	function registerCustomer(){
		//validate and sanitize
		
		$email = '';
		$phone = '';
		$password = '';
		$confirmPassword = '';
		if( Mo_Saml_Local_Util::check_empty_or_null( $_POST['email'] ) || Mo_Saml_Local_Util::check_empty_or_null( $_POST['password'] ) || Mo_Saml_Local_Util::check_empty_or_null( $_POST['confirmPassword'] ) ) {
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',  'All the fields are required. Please enter valid entries.','error');
			return;
		} else if( strlen( $_POST['password'] ) < 6 || strlen( $_POST['confirmPassword'] ) < 6){	//check password is of minimum length 6
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',  'Choose a password with minimum length 6.','error');
			return;
		} else{
			$email =  $_POST['email'];
			$phone =  $_POST['phone'];
			$password =  $_POST['password'] ;
			$confirmPassword = $_POST['confirmPassword'];
		}

		//update_option( 'mo_saml_local_admin_email', $email );
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		 // Fields to update.
		$fields = array(
			$db->quoteName('email') . ' = ' . $db->quote($email)
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
	
		if($phone != ''){
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('admin_phone') . ' = ' . $db->quote($phone)
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
		}
			
			

		if( strcmp( $password, $confirmPassword) == 0 ) {
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('password') . ' = ' . $db->quote(base64_encode($password))
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			$customer = new Mo_saml_Local_Customer();
			$content = json_decode($customer->check_customer($email), true);
			if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
				$auth_type = 'EMAIL';
				$content = json_decode($customer->send_otp_token($auth_type, null), true);
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('email_count') . ' = ' . $db->quote(1),
					$db->quoteName('transaction_id') . ' = ' . $db->quote($content['txId']),
					$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_SUCCESS')
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'A One Time Passcode has been sent <b>( 1 )</b> to <b>' . $email . '</b>. Please enter the OTP below to verify your email. ');
					
					
				} else {
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_FAILURE')
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'There was an error in sending email. Please click on Resend OTP to try again. ','error');
					
				}
			} else if( strcasecmp( $content['status'], 'CURL_ERROR') == 0 ){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$fields = array(
				$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_FAILURE')
				);
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', $content['statusMessage'],'error');
				
			} else{
				$content = $customer->get_customer_key();
				$customerKey = json_decode($content, true);
				if(json_last_error() == JSON_ERROR_NONE) {
					$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], $customerKey['phone'],'Your account has been retrieved successfully.' );
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('password') . ' = ' . $db->quote('')
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
				} else {
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('login_status') . ' = ' . $db->quote(true),
					$db->quoteName('registration_status') . ' = ' . $db->quote('')
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'You already have an account with miniOrange. Please enter a valid password. ','error');
					
				}
			}

		} else {
			$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$fields = array(
				$db->quoteName('login_status') . ' = ' . $db->quote(false)
				);
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Password and Confirm password do not match.','error');
		}
	}
	
	 function validateOtp(){
		//validation and sanitization
		$otp_token = '';
		if( Mo_Saml_Local_Util::check_empty_or_null( $_POST['otp_token'] ) ) {
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_VALIDATION_FAILURE')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();	
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Please enter a value in otp field.','error');
			return;
		} else{
			$otp_token =  $_POST['otp_token'] ;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('transaction_id');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$transaction_id = $db->loadResult();
		
		$customer = new Mo_saml_Local_Customer();
		$content = json_decode($customer->validate_otp_token($transaction_id, $otp_token ),true);
		if(strcasecmp($content['status'], 'SUCCESS') == 0) {
			$customer = new Mo_saml_Local_Customer();
			$customerKey = json_decode($customer->create_customer(), true);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('email_count') . ' = ' . $db->quote(''),
			$db->quoteName('sms_count') . ' = ' . $db->quote('')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			if(strcasecmp($customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0) {	//admin already exists in miniOrange
				$content = $customer->get_customer_key();
				$customerKey = json_decode($content, true);
				if(json_last_error() == JSON_ERROR_NONE) {
					$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], $customerKey['phone'],'Your account has been retrieved successfully.');
				} else {
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
						$db->quoteName('login_status') . ' = ' . $db->quote(true),
						$db->quoteName('new_registration') . ' = ' . $db->quote(false),
						$db->quoteName('password') . ' = ' . $db->quote(''),
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'You already have an account with miniOrange. Please enter a valid password.','error');
					
				}
			} else if(strcasecmp($customerKey['status'], 'INVALID_EMAIL') == 0) {
				$this->setRedirect('index.php?option=com_miniorange_saml&tab=account','Invalid Email ID. Please use a valid Email ID to register .','error');
			}
			
			else if(strcasecmp($customerKey['status'], 'SUCCESS') == 0) { 
				
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$fields = array(
				$db->quoteName('password') . ' = ' . $db->quote(''),
				);
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				
				//registration successful
				$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'],$customerKey['phone'], 'Registration complete!');
				
			}
			//update_option('mo_saml_local_password', '');
		} else if( strcasecmp( $content['status'], 'CURL_ERROR') == 0) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_VALIDATION_FAILURE')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', $content['statusMessage'],'error');
				
			} else{
						$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_VALIDATION_FAILURE')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account','Invalid one time passcode. Please enter a valid otp.','error');

		}
	}
	
	 function resendOtp(){
		$customer = new Mo_saml_Local_Customer();
		$auth_type = 'EMAIL';
		$content = json_decode($customer->send_otp_token($auth_type, null), true);
		if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('email_count');
				$query->from($db->quoteName('#__miniorange_saml_customer_details'));
				$query->where($db->quoteName('id')." = 1");
		 
				$db->setQuery($query);
				$email_count = $db->loadResult();
				
				$customer_details = Mo_Saml_Local_Util::getCustomerDetails();
				$email_count = $customer_details['email_count'];
				$admin_email = $customer_details['email'];
				
				if($email_count != ''){
					$email_count = $email_count + 1; 
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('email_count') . ' = ' . $db->quote($email_count),
					$db->quoteName('transaction_id') . ' = ' . $db->quote($content['txId']),
					$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_SUCCESS')
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Another One Time Passcode has been sent <b>( ' .$email_count .' )</b> to <b>' . ( $admin_email) . '</b>. Please enter the OTP below to verify your email.');
					
				}else{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('email_count') . ' = ' . $db->quote(1),
					$db->quoteName('transaction_id') . ' = ' . $db->quote($content['txId']),
					$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_SUCCESS')
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',  'An OTP has been sent to <b>' . ($admin_email) . '</b>. Please enter the OTP below to verify your email.');
					
				}

		} else if( strcasecmp( $content['status'], 'CURL_ERROR') == 0) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_FAILURE')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',  $content['statusMessage'],'error');
			
		} else{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
			$db->quoteName('registration_status') . ' = ' . $db->quote('MO_OTP_DELIVERED_FAILURE')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account',  'There was an error in sending email. Please click on Resend OTP to try again.','error');
				
		}
	}
	
	 function cancelform(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$fields = array(
		$db->quoteName('email') . ' = ' . $db->quote(''),
		$db->quoteName('registration_status') . ' = ' . $db->quote(''),
		$db->quoteName('login_status') . ' = ' . $db->quote(false),
		$db->quoteName('email_count') . ' = ' . $db->quote(''),
		$db->quoteName('sms_count') . ' = ' . $db->quote(''),
		);
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		$this->setRedirect('index.php?option=com_miniorange_saml&tab=account');
		
	}
	
	 function phoneVerification(){
		$phone = $_POST['phone_number'];
		$phone = str_replace(' ', '', $phone);
		
		$pattern = "/[\+][0-9]{1,3}[0-9]{10}/";					
		
		if(preg_match($pattern, $phone, $matches, PREG_OFFSET_CAPTURE)){
			$auth_type = 'SMS';
			$customer = new Mo_saml_Local_Customer();
			$send_otp_response = json_decode($customer->send_otp_token($auth_type, $phone));
			if($send_otp_response->status == 'SUCCESS'){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('sms_count');
				$query->from($db->quoteName('#__miniorange_saml_customer_details'));
				$query->where($db->quoteName('id')." = 1");
		 
				$db->setQuery($query);
				$sms_count = $db->loadResult();
				if($sms_count != ''){
					$sms_count = $sms_count + 1; 
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('sms_count') . ' = ' . $db->quote($sms_count),
					$db->quoteName('transaction_id') . ' = ' . $db->quote($send_otp_response->txId)
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Another One Time Passcode has been sent <b>(' . $sms_count . ')</b> for verification to ' . $phone);
					
					
			} else{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array(
					$db->quoteName('sms_count') . ' = ' . $db->quote(1),
					$db->quoteName('transaction_id') . ' = ' . $db->quote($send_otp_response->txId)
					);
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_customer_details'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'One Time Passcode has been sent ( <b>1</b> ) for verification to ' . $phone);
			}
				
			}
		}else{
			
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Please enter the phone number in the following format: <b>+##country code## ##phone number##','error');
		}
	}
	
	 function forgotPassword(){
		//$admin_email = get_option('mo_saml_local_admin_email');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('email');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$admin_email = $db->loadResult();
		
		$customer = new Mo_saml_Local_Customer();
		$forgot_password_response = json_decode($customer->mo_saml_local_forgot_password($admin_email));
		if($forgot_password_response->status == 'SUCCESS'){
			$message = 'You password has been reset successfully. Please enter the new password sent to your registered mail here.';
			$this->setRedirect('index.php?option=com_miniorange_saml&tab=account', $message);
			
		}
	}
	
	
	function contactUs() {
	   if( Mo_saml_Local_Util::check_empty_or_null( $_POST['query_email'] ) || Mo_saml_Local_Util::check_empty_or_null( $_POST['query'] ) ) {
			   $this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Please submit your query with email.','error');
			   return;
	   } else{
			   $query = $_POST['query'];
			   $email = $_POST['query_email'];
			   $phone = $_POST['query_phone'];
			   $contact_us = new Mo_saml_Local_Customer();
			   $submited = json_decode($contact_us->submit_contact_us($email, $phone, $query),true);
			   if(json_last_error() == JSON_ERROR_NONE) {
					   if(is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR'){
							   $this->setRedirect('index.php?option=com_miniorange_saml&tab=account', $submited['message'],'error');
					   }else{
							   if ( $submited == false ) {
									   $this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Your query could not be submitted. Please try again.','error');
							   } else {
									   $this->setRedirect('index.php?option=com_miniorange_saml&tab=account', 'Thanks for getting in touch! We shall get back to you shortly.');
							   }
					   }
			   }

	   }
   }
	   
	
}
	
 

?>