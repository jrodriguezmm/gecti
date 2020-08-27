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
This class contains all the utility functions

**/
class Mo_saml_Local_Util{

	public static function is_customer_registered() {
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$result = $db->loadAssoc();
		
		$email 			= $result['email'];
		$customerKey 	= $result['customer_key'];
		if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
			return 0;
		} else {
			return 1;
		}
	}
	
	public static function check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	public static function is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else 
			return 0;
	}
	
	public static function is_extension_installed($name) {
		if  (in_array  ($name, get_loaded_extensions())) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public static function encrypt($str){
		$str = stripcslashes($str);
		if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('customer_token');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$key = $db->loadResult();
		
		//$key = get_option('mo_saml_local_customer_token');
		$block = mcrypt_get_block_size('rijndael_128', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB));
	}
	
	public static function decrypt($value)
	{
		if(!Mo_saml_Local_Util::is_extension_installed('mcrypt')) {
			return;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('customer_token');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$key = $db->loadResult();
		
		//$key = get_option('mo_saml_local_customer_token');
		$string = rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128, 
				$key, 
				base64_decode($value), 
				MCRYPT_MODE_ECB,
				mcrypt_create_iv(
					mcrypt_get_iv_size(
						MCRYPT_RIJNDAEL_128,
						MCRYPT_MODE_ECB
					), 
					MCRYPT_RAND
				)
			), "\0"
		);
		return trim($string,"\0..\32");
	}
	
	public static function getHostname(){		
		return "https://auth.miniorange.com";
	}
	
	public static function getCustomertoken(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('customer_token');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$customer_token = $db->loadResult();
		return $customer_token;
	}
	
	public static function getCustomerKey(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('customer_key');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$customer_key = $db->loadResult();
		return $customer_key;
	}
	
	public static function getCustomerDetails(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_customer_details'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$customer_details = $db->loadAssoc();
		return $customer_details;
	}
	
	public static function getDefaultConfig(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_config'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$default_config = $db->loadAssoc();
		return $default_config;
	}
	
	public static function getLoginRequests(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('enable_log_requests');
		$query->from($db->quoteName('#__miniorange_saml_config'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$enable_log_requests = $db->loadResult();
		return $enable_log_requests;
	}
	
	public static function getServerurl(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('server_url');
		$query->from($db->quoteName('#__miniorange_saml_config'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$server_url = $db->loadResult();
		return $server_url;
	}
		public static function getMappingAttribute(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('mapping_memberof_attribute');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$mapping_attribute = $db->loadResult();
		return $mapping_attribute;
	}
	
	public static function getRoleMappingCount(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('role_mapping_count');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$role_mapping_count = $db->loadResult();
		return $role_mapping_count;
	}
	
	public static function getRoleMapping(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$role_mapping = $db->loadAssoc();
		return $role_mapping;
	}
	
	public static function getRoleMappingKeyValue(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('role_mapping_key_value');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$role_mapping_key_value = $db->loadResult();
		return json_decode($role_mapping_key_value,true);
	}
	
	public static function getMappingValueDefault(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('mapping_value_default');
		$query->from($db->quoteName('#__miniorange_saml_role_mapping'));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$mapping_value_default = $db->loadResult();
		return $mapping_value_default;
	}
	
}
?>