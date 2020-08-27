<?php
/** miniOrange enables user to log in using saml credentials.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This library is miniOrange Authentication Service. 
Contains Request Calls to saml Service.

**/
class Mo_saml_Local_Config{
	
	function ping_saml_server($saml_server_url){
		//delete_option('mo_saml_local_server_url_status');
		//delete_option('mo_saml_local_service_account_status');
		if(!Mo_saml_Local_Util::is_extension_installed('saml')) {
			return "saml_ERROR";
		}
	
		$result = Mo_saml_Local_Util::getCustomerDetails();
		$customer_id = $result['customer_token'];
		$application_name = $_SERVER['SERVER_NAME'];
		$admin_email = $result['email'];
		$app_type = 'WP saml for Intranet';
		$request_type = 'Ping saml Server';
		
		$samlconn = saml_connect($saml_server_url);
		if ($samlconn) {
			saml_set_option($samlconn, saml_OPT_PROTOCOL_VERSION, 3);
			saml_set_option($samlconn, saml_OPT_REFERRALS, 0);
			// binding anonymously
			$samlbind = @saml_bind($samlconn);
			if ($samlbind) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				 // Fields to update.
				$fields = array(
					$db->quoteName('server_url_status') . ' = '.$db->quote('VALID')
				);
				 
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				//add_option( 'mo_saml_local_server_url_status', 'VALID', '', 'no');
				return "SUCCESS";
			}
		}
		//add_option( 'mo_saml_local_server_url_status', 'INVALID', '', 'no');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		 // Fields to update.
		$fields = array(
			$db->quoteName('server_url_status') . ' = '.$db->quote('INVALID')
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		return "ERROR";
	
	}
	
	
	function saml_login($username, $password) {

		$username = stripcslashes($username);
		$password = stripcslashes($password);
		
		$authStatus = null;
		
		if(!Mo_saml_Local_Util::is_extension_installed('saml')) {
			$auth_response = new Mo_saml_Auth_Response();
			$auth_response->status = false;
			$auth_response->statusMessage = 'saml_ERROR';
			$auth_response->userDn = '';
			return $auth_response;

		}
		if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			$auth_response = new Mo_saml_Auth_Response();
			$auth_response->status = false;
			$auth_response->statusMessage = 'MCRYPT_ERROR';
			$auth_response->userDn = '';
			return $auth_response;
		}
		
		$samlconn = $this->getConnection();		
		if ($samlconn) {
			$default_config = Mo_saml_Local_Util::getDefaultConfig();
			$search_base_string   = $default_config['search_base'] ? Mo_saml_Local_Util::decrypt($default_config['search_base']) : '';
			$filter = $default_config['search_filter'] ? Mo_saml_Local_Util::decrypt($default_config['search_filter']) : '';
			$saml_bind_dn       = $default_config['service_account_dn'] ? Mo_saml_Local_Util::decrypt($default_config['service_account_dn']) : '';
			$saml_bind_password = $default_config['admin_password'] ? Mo_saml_Local_Util::decrypt($default_config['admin_password']) : '';
			
			
			
			/*$fname_attribute = strtolower(get_option('mo_saml_local_fname_attribute'));
			$lname_attribute = strtolower(get_option('mo_saml_local_lname_attribute'));
			$email_attribute = strtolower(get_option('mo_saml_local_email_attribute'));*/
			
			$fname_attribute = strtolower('');
			$lname_attribute = strtolower('');
			$email_attribute = strtolower('');

			$attr = array($fname_attribute, $lname_attribute, $email_attribute);

			/*$wp_options = wp_load_alloptions();
			foreach($wp_options as $option=>$value){
				if(strpos($option, "mo_saml_local_custom_attribute") === false){
					//Do nothing
				} else{
					array_push($attr, $value);
				}
			}*/
			$filter = str_replace('?', $username, $filter);

			$search_bases = explode(";", $search_base_string);			
			$user_search_result = null;
			$entry = null;
			$info = null;
			$bind = @saml_bind($samlconn, $saml_bind_dn, $saml_bind_password);
			$err = saml_error($samlconn);
			if(strtolower($err) != 'success'){
				$auth_response = new Mo_saml_Auth_Response();
				$auth_response->status = false;
				$auth_response->statusMessage = 'saml_NOT_RESPONDING';
				$auth_response->userDn = '';
				return $auth_response;
			}
			for($i = 0 ; $i < sizeof($search_bases) ; $i++){
				if(saml_search($samlconn, $search_bases[$i], $filter, $attr))
					$user_search_result = saml_search($samlconn, $search_bases[$i], $filter, $attr);
				else{
					$auth_response = new Mo_saml_Auth_Response();
					$auth_response->status = false;
					$auth_response->statusMessage = 'USER_NOT_EXIST';
					$auth_response->userDn = '';
					return $auth_response;
				}
				$info = saml_first_entry($samlconn, $user_search_result);
				$entry = saml_get_entries($samlconn, $user_search_result);
				if($info)
					break;
			}
			if($info){
				$userDn = saml_get_dn($samlconn, $info);
			} else{
				$auth_response = new Mo_saml_Auth_Response();
				$auth_response->status = false;
				$auth_response->statusMessage = 'USER_NOT_EXIST';
				$auth_response->userDn = '';
				return $auth_response;
			}
			$authentication_response = $this->authenticate($userDn, $password);
			if($authentication_response->statusMessage == 'SUCCESS'){
				$attributes_array = array();
				$profile_attributes = array();

				//Unset fname, lname and email attributes from $attr
				unset($attr[0]);
				unset($attr[1]);
				unset($attr[2]);

				foreach($attr as $attribute){
					/*if(strpos($attribute, "mo_saml_local_custom_attribute") === false){
						//Do nothing
					}else{*/
						$attributes_array['mo_saml_local_custom_attribute_'.$attribute] = $entry[0][strtolower($attribute)][0];
					//}
					
				}
				
				$authentication_response->attributeList = $attributes_array;

				//Save email, fname, lname in attributes
				$profile_attributes['mail'] = $entry[0][$email_attribute][0];
				$profile_attributes['fname'] = $entry[0][$fname_attribute][0];
				$profile_attributes['lname'] = $entry[0][$lname_attribute][0];
				
				$authentication_response->profile_attributes = $profile_attributes;
				//var_dump($profile_attributes);exit();

			}
			//var_dump($authentication_response);exit();	
			return $authentication_response;	
			//return $this->authenticate($userDn,$password);
		} else{
			$auth_response = new Mo_saml_Auth_Response();
			$auth_response->status = false;
			$auth_response->statusMessage = 'ERROR';
			$auth_response->userDn = '';
			return $auth_response;
		}

	}
	

	
	
	/*
	*	Test connection for default config or user config
	*/
	function test_connection() {
		
		if(!Mo_saml_Local_Util::is_extension_installed('saml')) {
			return json_encode(array("statusCode"=>'saml_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/saml.installation.php">PHP saml extension</a> is not installed or disabled. Please enable it.'));
		} else if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return json_encode(array("statusCode"=>'MCRYPT_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/mcrypt.installation.php">PHP mcrypt extension</a> is not installed or disabled. Please enable it.'));
		}
		
		//delete_option('mo_saml_local_server_url_status');
		//delete_option('mo_saml_local_service_account_status');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		 // Fields to update.
		$fields = array(
			$db->quoteName('server_url_status') . ' = ' . $db->quote(''),
			$db->quoteName('service_account_status') . ' = ' . $db->quote('')
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		
		$default_config = Mo_saml_Local_Util::getDefaultConfig();
		
		
		$server_name = $default_config['server_url'] ? Mo_saml_Local_Util::decrypt($default_config['server_url']) : '';
		$pingResult = $this->ping_saml_server($server_name);
		if($pingResult=='ERROR')
		{	
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			 // Fields to update.
			$fields = array(
				$db->quoteName('server_url_status') . ' = ' . $db->quote('INVALID'),
			);
			 
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			
			//add_option( 'mo_saml_local_server_url_status', 'INVALID', '', 'no');
			return json_encode(array("statusCode"=>'PING_ERROR','statusMessage'=>$error . 'Cannot connect to saml Server. Make sure you have entered server url in format <b>saml://server_address:port</b> and if there is a firewall, please open the firewall to allow incoming requests to your saml from your wordpress IP and port 389. Also check troubleshooting or contact us using support below.'));
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		 // Fields to update.
		$fields = array(
			$db->quoteName('server_url_status') . ' = ' . $db->quote('VALID'),
		);
		 
		// Conditions for which records should be updated.
		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);
		 
		$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		

		$samlconn = $this->getConnection();
		if ($samlconn) {
			$saml_bind_dn = $default_config['service_account_dn'] ? Mo_saml_Local_Util::decrypt($default_config['service_account_dn']) : '';
			$saml_bind_password = $default_config['admin_password'] ? Mo_saml_Local_Util::decrypt($default_config['admin_password']) : '';
				
			$samlbind = @saml_bind($samlconn, $saml_bind_dn, $saml_bind_password);
			// verify binding
			if ($samlbind) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				 // Fields to update.
				$fields = array(
					$db->quoteName('service_account_status') . ' = ' . $db->quote('VALID')
				);
				 
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				return json_encode(array("statusCode"=>'SUCCESS','statusMessage'=>'Connection was established successfully. Please test authentication to verify saml User Mapping Configuration.'));
			} else {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				 // Fields to update.
				$fields = array(
						$db->quoteName('service_account_status') . ' = ' . $db->quote('INVALID')
				);
				 
				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);
				 
				$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				return json_encode(array("statusCode"=>'ERROR','statusMessage'=>$error . 'Invalid service account credentials. Make sure you have entered correct Service Account DN and password.'));
			}
		} else {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			 // Fields to update.
			$fields = array(
					$db->quoteName('service_account_status') . ' = ' . $db->quote('INVALID')
			);
			 
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			
			return json_encode(array("statusCode"=>'ERROR','statusMessage'=>$error . 'Invalid service account credentials. Make sure you have entered correct Service Account DN and password.'));
		}
	}
	
	/*
	*	Test authentication for default config or user config
	*/
	function test_authentication($username, $password, $is_default) {

		if(!Mo_saml_Local_Util::is_extension_installed('saml')) {
			return json_encode(array("statusCode"=>'saml_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/saml.installation.php">PHP saml extension</a> is not installed or disabled. Please enable it.'));
		} else if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return json_encode(array("statusCode"=>'MCRYPT_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/mcrypt.installation.php">PHP mcrypt extension</a> is not installed or disabled. Please enable it.'));
		}
		
		$default_config = Mo_saml_Local_Util::getDefaultConfig();
		$server_name = $default_config['server_url'] ? Mo_saml_Local_Util::decrypt($default_config['server_url']) : '';
		//$pingResult = $this->ping_saml_server($server_name);
		//if($pingResult=='ERROR')
		if($default_config['server_url_status'] && $default_config['server_url_status']=='INVALID')
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			 // Fields to update.
			$fields = array(
				$db->quoteName('server_url_status') . ' = ' . $db->quote('INVALID'),
				$db->quoteName('service_account_status') . ' = ' . $db->quote('')
			);
			 
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);
			 
			$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			
			//delete_option('mo_saml_local_server_url_status');
			//delete_option('mo_saml_local_service_account_status');
			//add_option( 'mo_saml_local_server_url_status', 'INVALID', '', 'no');
			return json_encode(array("statusCode"=>'PING_ERROR','statusMessage'=>$error . 'Cannot connect to saml Server. Make sure you have entered server url in format <b>saml://server_address:port</b> and if there is a firewall, please open the firewall to allow incoming requests to your saml from your wordpress IP and port 389. Also check troubleshooting or contact us using support below.'));
		}
		
		
		//Check if request is for default auth
		if(Mo_saml_Local_Util::check_empty_or_null($is_default)) {
						
			$auth_response = $this->saml_login($username, $password);
				if($auth_response->statusMessage == "SUCCESS")
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
			 // Fields to update.
					$fields = array(
						$db->quoteName('server_url_status') . ' = ' . $db->quote('VALID'),
						$db->quoteName('service_account_status') . ' = ' . $db->quote('VALID'),
						$db->quoteName('user_mapping_status') . ' = ' . $db->quote('VALID'),
						$db->quoteName('username_status') . ' = ' . $db->quote('VALID')
					);
					 
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					//add_option( 'mo_saml_local_server_url_status', 'VALID', '', 'no');
					//add_option( 'mo_saml_local_service_account_status', 'VALID', '', 'no');
					//add_option( 'mo_saml_local_user_mapping_status', 'VALID', '', 'no');
					//add_option( 'mo_saml_local_username_status', 'VALID', '', 'no');
					return json_encode(array("statusCode"=>'SUCCESS','statusMessage'=>'You have successfully configured your saml settings.'));
				}
				else if($auth_response->statusMessage =="USER_NOT_EXIST")
				{	
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					 // Fields to update.
					$fields = array(
						$db->quoteName('user_mapping_status') . ' = ' . $db->quote('INVALID'),
						$db->quoteName('username_status') . ' = ' . $db->quote('INVALID')
					);
					 
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					
					return json_encode(array("statusCode"=>'ERROR','statusMessage'=>'Cannot find user <b>'.$username.'</b> in the directory.<br>Possible reasons:<br>1. The <b>search base</b> URL is typed incorrectly. Please verify if that search base is present.<br>2. User is not present in that search base. The user may be present in the directory but in some other tree and you may have entered a tree where this users is not present.<br>3. <b>Search filter</b> is incorrect - User is present in the search base but the username is mapped to a different attribute in the search filter. E.g. you may be logging in with username and may have mapped it to the email attribute. So this wont work. Please make sure that the right attribute is mentioned in the search filter (with which you want the mapping to happen).<br> 4. User is actually not present in the search base. Please make sure that the user is present and test with the right user.'));
				}
				else
				{	$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					 // Fields to update.
					$fields = array(
						$db->quoteName('user_mapping_status') . ' = ' . $db->quote('VALID'),
						$db->quoteName('username_status') . ' = ' . $db->quote('INVALID')
					);
					 
					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('id') . ' = 1'
					);
					 
					$query->update($db->quoteName('#__miniorange_saml_config'))->set($fields)->where($conditions);
					$db->setQuery($query);
					$result = $db->execute();
					return json_encode(array("statusCode"=>'ERROR','statusMessage'=>'Invalid Password. Please check your password and try again.'));
				}
		} else {
			// Default is removed
			return json_encode(array("statusCode"=>'SUCCESS','statusMessage'=>''));
		}
		
	}
	
	function authenticate($userDn, $password) {
		
		if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return false;
		}
		
		$server_url = Mo_saml_Local_Util::getServerurl();
		$server_name = $server_url ? Mo_saml_Local_Util::decrypt($server_url) : ''; 
						
		$samlconn = saml_connect($server_name);
		if ( version_compare(PHP_VERSION, '5.3.0') >= 0 ) {
			saml_set_option(null, saml_OPT_NETWORK_TIMEOUT, 5);
		}
	
		saml_set_option($samlconn, saml_OPT_PROTOCOL_VERSION, 3);
		saml_set_option($samlconn, saml_OPT_REFERRALS, 0);
		// binding to saml server
		$samlbind = @saml_bind($samlconn, $userDn, $password);
		// verify binding
		if ($samlbind) {

			$search_result = saml_search($samlconn, $userDn);
			$auth_response = new Mo_saml_Auth_Response();
			$auth_response->status = true;
			$auth_response->statusMessage = 'SUCCESS';
			$auth_response->userDn = $userDn;
			return $auth_response;
		} else {
			$auth_response = new Mo_saml_Auth_Response();
			$auth_response->status = false;
			$auth_response->userDn = $userDn;
		}
		$auth_response = new Mo_saml_Auth_Response();
		$auth_response->status = false;
		$auth_response->statusMessage = 'ERROR';
		$auth_response->userDn = $userDn;
		return $auth_response;
	}
	
	
	function get_encrypted_config($request_type, $is_default) {
		$current_user = JFactory::getUser();
				
		$server_name = '';
		$dn = '';
		$admin_saml_password = '';
		$dn_attribute = '';
		$search_base = '';
		$search_filter = '';
		$username = $current_user->email;
		
		if(Mo_saml_Local_Util::check_empty_or_null($is_default)) {
			$default_config = Mo_saml_Local_Util::getDefaultConfig();

				
			$server_name = $default_config['server_url'];
			$dn = $default_config['service_account_dn'];
			//$admin_saml_password = $default_config['admin_password'];
			//$dn_attribute = $default_config['dn_attribute'];;
			$search_base = $default_config['search_base'];
			$search_filter = $default_config['search_filter'];
			$username = $default_config['admin_password'];
		}
		
		$customer_key= Mo_saml_Local_Util::getCustomerKey();
		$customer_id = $customer_key ? $customer_key : null;
		
		$fields = array(
			'customerId' => $customer_id,
			'samlAuditRequest' => array(
				'endUserEmail' => $username,
				'applicationName' => $_SERVER['SERVER_NAME'],
				'appType' => 'WP saml Login Plugin',
				'requestType' => $request_type
			),
			'gatewayConfiguration' => array(
				'samlServer' =>$server_name,
				'bindAccountDN'=>$dn,
				'bindAccountPassword'=>Mo_saml_Local_Util::encrypt(''),
				'searchBase'=>$search_base,
				'dnAttribute'=>Mo_saml_Local_Util::encrypt(''),
				'samlSearchFilter'=>$search_filter
			)
		);
		
		return $fields;
	}
	
	
	function get_login_config($encrypted_username, $username, $encrypted_password, $request_type, $is_default) {
		$current_user = JFactory::getUser();
		
		$customer_key= Mo_saml_Local_Util::getCustomerKey();
		$customer_id = $customer_key ? $customer_key : null;
		
		$fields = array(
			'customerId' => $customer_id,
			'userName' => $encrypted_username,
			'password' => $encrypted_password,
			'samlAuditRequest' => array(
				'endUserEmail' => $username,
				'applicationName' => $_SERVER['SERVER_NAME'],
				'appType' => 'WP saml Login Plugin',
				'requestType' => $request_type
			)
		);
		
		return $fields;
	}
	
	
	
	function send_audit_request($username, $request_type, $status_code, $status_message){
		
		
		$result = Mo_saml_Local_Util::getCustomerDetails();
		
		
		if(!Mo_saml_Local_Util::is_curl_installed()) {
			return "CURL_ERROR";
		}
		
		if($request_type == "Ping saml Server"){
			$username = $result['email'];
		}
		
		$customer_id = $result['customer_token'];
		$hostname = Mo_saml_Local_Util::getHostname();
		$url = $hostname . '/moas/api/saml/audit';
		$ch = curl_init($url);
		
		$fields = array(
			'customerId' => $customer_id,
			'samlAuditRequest' => array(
				'endUserEmail' => $username,
				'applicationName' => $_SERVER['SERVER_NAME'],
				'appType' => 'WP saml for Intranet',
				'requestType' => $request_type,
				'statusCode' => $status_code,
				'statusMessage' => $status_message
			),

		);
		
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 1);
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'charset: UTF - 8',
			'Authorization: Basic'
		));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		curl_exec($ch);
		
		return "SUCCESS";
	}
	
	function get_user_saml_info($username) {
	 	if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return null;
		}
		
		$default_config = Mo_saml_Local_Util::getDefaultConfig();
		//get configuration options
		
		$server             = $default_config['server_url'] ? Mo_saml_Local_Util::decrypt($default_config['server_url']) : '';
		$saml_bind_dn       = $default_config['service_account_dn'] ? Mo_saml_Local_Util::decrypt($default_config['service_account_dn']) : '';
		$saml_bind_password = $default_config['admin_password'] ? Mo_saml_Local_Util::decrypt($default_config['admin_password']) : '';
	
		$search_base   = $default_config['search_base'] ? Mo_saml_Local_Util::decrypt($default_config['search_base']) : '';
		$search_filter = $default_config['search_filter'] ? Mo_saml_Local_Util::decrypt($default_config['search_filter']) : '';
			
		$saml = saml_connect($server);
	
		if ($saml) { //connection made! lets bind
			saml_set_option($saml, saml_OPT_PROTOCOL_VERSION, 3);
			saml_set_option($saml, saml_OPT_REFERRALS, 0);
			$samlbind = @saml_bind($saml, $saml_bind_dn, $saml_bind_password);
	
			if ($samlbind) { //should be bound to the saml server
				$filter = str_replace('?', $username, $search_filter); //construct the filter
				$sr = saml_search($saml, $search_base, $filter); //search the saml directory
				$info = saml_get_entries($saml, $sr); //retrieve our entries
				if (is_null($info)) { // check if we found something
					$info = false;
				}
			} else {
				$info = false;
			}
		} else {
			$info = false; //todo fancy error handling
		}
		if ($info != false) 
			$info = $info[0];
		saml_close($saml);
		return $info; //$info is either an array with all the information for the user or false if nothing found
	}

	function getConnection() {
		
		if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return null;
		}
		
		$default_config = Mo_saml_Local_Util::getDefaultConfig();
		$server_name             = $default_config['server_url'] ? Mo_saml_Local_Util::decrypt($default_config['server_url']) : '';
		$samlrdn       = $default_config['service_account_dn'] ? Mo_saml_Local_Util::decrypt($default_config['service_account_dn']) : '';
		$samlpass = $default_config['admin_password'] ? Mo_saml_Local_Util::decrypt($default_config['admin_password']) : '';
		
		
		$samlconn = saml_connect($server_name);	
		if ( version_compare(PHP_VERSION, '5.3.0') >= 0 ) {
			saml_set_option($samlconn, saml_OPT_NETWORK_TIMEOUT, 5);
		}

		saml_set_option($samlconn, saml_OPT_PROTOCOL_VERSION, 3);
		saml_set_option($samlconn, saml_OPT_REFERRALS, 0);
		return $samlconn;

	}

	
	
	
}?>