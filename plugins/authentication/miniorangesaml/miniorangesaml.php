<?php
/**
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
 *
 *
 * This file is part of miniOrange SAML plugin.
 *
 * miniOrange SAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * miniOrange SAML plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */
 
 if (defined('_JEXEC')) {
	 
	 
	/**
	 * This block of code is not used.
	 */
	class plgauthenticationminiorangesaml extends JPlugin
	{
		/**
		 * This method should handle any authentication and report back to the subject
		 * 
		 *
		 * @access    public
		 * @param     array     $credentials    Array holding the user credentials ('username' and 'password')
		 * @param     array     $options        Array of extra options
		 * @param     object    $response       Authentication response object
		 * @return    boolean
		 */
		function onUserAuthenticate( $credentials, $options, &$response )
		{
                        /*Edicion para autenticar al usuario en joomla desde SSO.
                         @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                         */                        
                        $session = JFactory::getSession();
                        $Session_date=$session->get("MO_SAML_NAMEID");
                        if(isset($Session_date)==true and empty($Session_date)==false){   
                            $email = $session->get("user"); // Bring this in line with the rest of the system
                            $response->email = $email->email;
                            $response->status = JAuthentication::STATUS_SUCCESS;                            
                        }
			
			if (isset($_COOKIE['mosamlauthadmin']) && $_COOKIE['mosamlauthadmin'] != '-1'){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('api_key','customer_token'));
				$query->from($db->quoteName('#__miniorange_saml_customer_details'));
				$query->where($db->quoteName('id')." = 1");
				$db->setQuery($query);
				$customerResult = $db->loadAssoc();
				/*$is_admin_login_
				enabled = $customerResult['enable_admin_login'];
				if($is_admin_login_enabled){
					jimport('miniorangesamlplugin.utility.encryption');
					$cValue = $_COOKIE['mosamlauthadmin'];
					$decKey = $customerResult['customer_token']; 
					$api_key = $customerResult['api_key'];
					$cValue = AESEncryption::decrypt_data($cValue, $decKey);
					
					$token_time = array();
					$token_time = explode(":",$cValue);
					$cookie_time = $token_time[0];
					$cookie_token = $token_time[1];
					$current_time = time();
					if($cookie_token == $api_key && ($current_time - $cookie_time < 30)){
						setcookie('mosamlauthadmin','-1',time() - 100, '/');
						unset($_COOKIE['mosamlauthadmin']);
						$db    = JFactory::getDbo();
						$query = $db->getQuery(true)
							->select('id, password')
							->from('#__users')
							->where('username=' . $db->quote($credentials['username']));

						$db->setQuery($query);
						$result = $db->loadObject();

						if ($result)
						{
							// Bring this in line with the rest of the system
							$user               = JUser::getInstance($result->id);
							$response->email    = $user->email;
							$response->fullname = $user->name;
							$response->email    = $user->email;
							$response->fullname = $user->name;
							$response->language = $user->getParam('admin_language');
							$response->language = $user->getParam('language');
							$response->status        = JAuthentication::STATUS_SUCCESS;
							$response->error_message = '';
							
						}
					}
				}*/ 
			}
		
		}
	}

 }
?>