<?php
ob_start();
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.plugin.plugin' );
 
/**
 * miniOrange SAML System plugin
 */
class plgSystemSamlredirect extends JPlugin
{
	
	public function onAfterRoute(){
		
		$user = JFactory::getUser();
		$app = JFactory::getApplication('site');
		$app->initialise();
		if (!$app->isAdmin()){
			if(!$user->id) {
				jimport('miniorangesamlplugin.utility.Utilities');
		
				$customerResult = Utilities::getCustomerDetails();
				
			}
		}   
	}
	
	public function onAfterInitialise()
	{
                /*Edicion para generar las solicitudes de cierre de session Joomla y AZURE.
                 @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                 */            
		jimport('miniorangesamlplugin.utility.Utilities');
		
		if (isset($_GET['morequest']) && $_GET['morequest']=='sso') {
			$pluginconfig = Utilities::getSAMLConfiguration();
			$this->sendSamlRequest($pluginconfig);
		}else if (isset($_GET['morequest']) && $_GET['morequest']=='acs') {
			$pluginconfig = Utilities::getSAMLConfiguration();
			$this->getSamlResponse($pluginconfig);
		} 
                else if(isset($_GET['morequest']) && $_GET['morequest']=='ssout'){
			$pluginconfig = Utilities::getSAMLConfiguration();
			$this->sendSamlRequest($pluginconfig,"Azure");
                }
                else if(isset($_GET['morequest']) && $_GET['morequest']=='sout'){
			$pluginconfig = Utilities::getSAMLConfiguration();
			$this->sendSamlRequest($pluginconfig,"Joomla");
                }
		
	}
	
	function sendSamlRequest($pluginconfig,$logout=false){
		
		$sp_base_url = ""; 
		$sp_entity_id = "";
		
		$siteUrl = JURI::root();
                /*Edicion para generar la url de retorno personalizada.
                 @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                 */
                $get_url=(empty(JRequest::getVar("return")))?base64_encode($siteUrl):JRequest::getVar("return");
		$session = JFactory::getSession();
                $session->set('url_return', $get_url);
		$sp_base_url = $siteUrl;
		
			$sp_entity_id = $siteUrl.'plugins/authentication/miniorangesaml/';
		
		if (!defined('_JDEFINES')) {
			require_once JPATH_BASE . '/includes/defines.php';
		}
		require_once JPATH_BASE . '/includes/framework.php';
			
		// Instantiate the application.
		$app = JFactory::getApplication('site');
		$app->initialise();
		$login_url = $sp_base_url;
		
		$user = JFactory::getUser(); #Get current user info
		
		$acsUrl = $sp_base_url . '?morequest=acs';
		$ssoUrl = $pluginconfig['single_signon_service_url'];
		$sso_binding_type = $pluginconfig['binding'];
				
		$sendRelayState = $this->getRelayState($sp_base_url, $_REQUEST);
                /*Edicion para generar las solicitudes de cierre de session Joomla y AZURE.
                 @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                 */		
                if($logout=="Joomla"){                    
                    $user_id = $user->get('id');            
                    $db = JFactory::getDBO();
                    $query = "DELETE FROM #__session WHERE userid = ".$user_id;
                    $db->setQuery( $query );
                    $db->query();                                        
                    $acsUrl=$sp_base_url;
                    $samlRequest=false;
//                    $samlRequest = Utilities::createLogoutResponse($acsUrl,$sp_entity_id, $ssoUrl, $sso_binding_type);
                }
                else if($logout=="Azure"){
                    $acsUrl=JRequest::getVar("nameID");
                    $samlRequest = Utilities::createLogoutRequest($acsUrl,'',$sp_entity_id, $ssoUrl, $sso_binding_type);
                }
                else{
                    $samlRequest = Utilities::createAuthnRequest($acsUrl, $sp_entity_id, $ssoUrl, 'false', $sso_binding_type);
                }
                if($samlRequest!=false){
                    $this->sendSamlRequestByBindingType($samlRequest, $sso_binding_type, $sendRelayState,  $ssoUrl);
                }
	}
	
	function sendSamlRequestByBindingType($samlRequest, $sso_binding_type, $sendRelayState, $ssoUrl){
		
		if(empty($sso_binding_type) || $sso_binding_type == 'HttpRedirect') {
						
			$samlRequest = "SAMLRequest=" . $samlRequest . "&RelayState=" . $sendRelayState;
			// . '&SigAlg='. urlencode(XMLSecurityKey::RSA_SHA256);
			$param =array( 'type' => 'private');
			// $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, $param);
			// $certFilePath = JPATH_BASE . DIRECTORY_SEPARATOR. 'plugins'. DIRECTORY_SEPARATOR. 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml' . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR .'cert' . DIRECTORY_SEPARATOR . 'sp-key.key';
			// $key->loadKey($certFilePath, TRUE);
			// $objXmlSecDSig = new XMLSecurityDSig();
			// $signature = $key->signData($samlRequest);
			// $signature = base64_encode($signature);
			$redirect = $ssoUrl;
			if (strpos($ssoUrl,'?') !== false) {
				$redirect .= '&';
			} else {
				$redirect .= '?';
			}
			$redirect .=  $samlRequest;
			// . '&Signature=' . urlencode($signature);
			header('Location: '.$redirect);
			exit();
			
		}else{
			
			// $privateKeyPath = JPATH_BASE . DIRECTORY_SEPARATOR. 'plugins'. DIRECTORY_SEPARATOR. 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml' . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR .'cert' . DIRECTORY_SEPARATOR . 'sp-key.key';
			// $publicCertPath = JPATH_BASE. DIRECTORY_SEPARATOR. 'plugins'. DIRECTORY_SEPARATOR. 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml' . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'sp-certificate.crt';
			$base64EncodedXML = Utilities::signXML( $samlRequest, $publicCertPath, $privateKeyPath, 'NameIDPolicy' );
			Utilities::postSAMLRequest($ssoUrl, $samlRequest, $sendRelayState);
						
		}
	}
	
	function getRelayState($sp_base_url, $request){
		
		$sendRelayState = $sp_base_url;
		
		if(isset($request['q'])) {
			if($request['q'] == 'test_config'){
				$sendRelayState = 'testValidate';
			}
		}
		
		if(isset($request['RelayState'])){
			$sendRelayState = $request['RelayState'];
		}
		
		return $sendRelayState;
	}
	
	function getSamlResponse($pluginconfig){
		
		if (!defined('_JDEFINES')) {
			require_once JPATH_BASE . '/includes/defines.php';
		}
		require_once JPATH_BASE . '/includes/framework.php';
		
		$authBase = JPATH_BASE. DIRECTORY_SEPARATOR. 'plugins'. DIRECTORY_SEPARATOR. 'authentication' . DIRECTORY_SEPARATOR .		 'miniorangesaml';
		include_once $authBase . DIRECTORY_SEPARATOR  . 'saml2' . DIRECTORY_SEPARATOR. 'Response.php';
		
		jimport('miniorangesamlplugin.utility.encryption');
		jimport ( 'joomla.application.application' );
		jimport ( 'joomla.html.parameter' );
		
		$sp_base_url = ""; 
		$sp_entity_id = "";
		if(isset($pluginconfig['sp_base_url'])){
			$sp_base_url= $pluginconfig['sp_base_url'];
			$sp_entity_id = $pluginconfig['sp_entity_id'];
		}
		
		$siteUrl = JURI::root();
		
		if(empty($sp_base_url))
			$sp_base_url = $siteUrl;
		if(empty($sp_entity_id))
			$sp_entity_id = $siteUrl.'plugins/authentication/miniorangesaml/';
		
		$app = JFactory::getApplication('site');
		$app->initialise();
		
		if (array_key_exists ( 'SAMLResponse', $_POST )) {
			$this->validateSamlResponse($_POST, $sp_base_url, $sp_entity_id, $pluginconfig, $app);
		}else{
			throw new Exception ( 'Missing SAMLRequest or SAMLResponse parameter.' );
		}
	}
	
	function validateSamlResponse($post, $sp_base_url, $sp_entity_id, $attribute, $app) {
		
		$samlResponse = $post ['SAMLResponse'];
				
		if (array_key_exists ( 'RelayState', $_REQUEST )) {
			$relayState = $_REQUEST ['RelayState'];
		} else {
			$relayState = '';
		}
		
		$samlResponse = base64_decode ( $samlResponse );
		$document = new DOMDocument ();
		$document->loadXML ( $samlResponse );
		$samlResponseXml = $document->firstChild;
		
		$acsUrl = $sp_base_url . '?morequest=acs';
		
		$certFromPlugin = $attribute['certificate'];
		$certFromPlugin = Utilities::sanitize_certificate ( $certFromPlugin );
		$certfpFromPlugin = XMLSecurityKey::getRawThumbprint ( $certFromPlugin );
		$samlResponse = new SAML2_Response ( $samlResponseXml );
		
		$responseSignatureData = $samlResponse->getSignatureData();
				//print_r($samlResponse->getAssertions());exit();
		$assertionSignatureData = current($samlResponse->getAssertions())->getSignatureData();
		/* convert to UTF-8 character encoding */
		$certfpFromPlugin = iconv ( "UTF-8", "CP1252//IGNORE", $certfpFromPlugin );
				
		/* remove whitespaces */
		$certfpFromPlugin = preg_replace ( '/\s+/', '', $certfpFromPlugin );
		
		/* Validate signature */
		if(!empty($responseSignatureData)) {
				$validSignature = Utilities::processResponse($acsUrl, $certfpFromPlugin, $responseSignatureData, $samlResponse, $certFromPlugin, $relayState);
				if($validSignature === FALSE) {
					echo "Invalid signature in the SAML Response.<br><br>";
					exit;
				}
		}
			
		if(!empty($assertionSignatureData)) {
				//$validSignature = Utilities::processResponse($acsUrl, $certfpFromPlugin, $assertionSignatureData, $samlResponse);
				$validSignature = Utilities::processResponse($acsUrl, $certfpFromPlugin, $assertionSignatureData, $samlResponse, $certFromPlugin, $relayState);
				if($validSignature === FALSE) {
					echo "Invalid signature in the SAML Assertion.<br><br>";
					exit;
				}
		}
		
		if(empty($assertionSignatureData) && empty($responseSignatureData) ) {
			echo "No signature in SAML Response or Assertion.";
			exit;
		}
		
		// verify the issuer and audience from saml response
		$issuer = $attribute['idp_entity_id']; 
				
		Utilities::validateIssuerAndAudience ( $samlResponse, $sp_entity_id, $issuer, $relayState);
		
		$username = current ( current ( $samlResponse->getAssertions () )->getNameId () );
		$attrs = current ( $samlResponse->getAssertions () )->getAttributes ();
		$attrs ['ASSERTION_NAME_ID'] = current ( current ( $samlResponse->getAssertions () )->getNameId () );
		
		if($relayState=='testValidate'){
			Utilities::mo_saml_show_test_result($username,$attrs,$sp_base_url);
		}
					
		$sessionIndex = current ( $samlResponse->getAssertions () )->getSessionIndex ();
		$attrs ['ASSERTION_SESSION_INDEX'] = $sessionIndex;
		
		$email = $username;
		$name = '';
		$fname = '';
		$lname = '';
		$saml_groups = '';
		
		$nameMapping = $attribute['name'];
		/*$firstNameMapping = $attribute['first_name'];
		$lastNameMapping = $attribute['last_name'];
		*/$usernameMapping = $attribute['username'];
		$mailMapping = $attribute['email'];
		//$groupsMapping = $attribute['grp'];
		
		if (!empty($usernameMapping) && isset($attrs[$usernameMapping]) && !empty($attrs[$usernameMapping])) {
			$username = $attrs[$usernameMapping];
			if(is_array($username))
				$username = $username[0];
                        /*Edicion para usar solo el usuario de AZURE en Joomla sin la cuenta completa.
                         @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                         */
                        $username=array_values(explode('@',$username))[0];
		}
		if (!empty($mailMapping) && isset($attrs[$mailMapping]) && !empty($attrs[$mailMapping])) {
			$email = $attrs[$mailMapping];
			if(is_array($email))
				$email = $email[0];
		}
		
		if (!empty($nameMapping) && isset($attrs[$nameMapping]) && !empty($attrs[$nameMapping])) {
			$name = $attrs[$nameMapping];
			if(is_array($name))
				$name = $name[0];
		}
	
		if (!empty($groupsMapping) && isset($attrs[$groupsMapping]) && !empty($attrs[$groupsMapping])) {
			$saml_groups = $attrs[$groupsMapping];
		} else {
			$saml_groups = array();
		}
		
		if(isset($attribute['enable_email']) && $attribute['enable_email']==0){
			$matcher = 'username';
		}else{
			$matcher = 'email';
		}
		
		
		$result = Utilities::get_user_from_joomla($matcher,$username,$email);
                /*Edicion para enviar url de retorno personalizada.
                 @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                 */
//		$login_url = isset($relayState) ? $relayState : $sp_base_url;
                $session = JFactory::getSession();
		$login_url = (empty($session->get('url_return')))?(isset($relayState) ? $relayState : $sp_base_url):  base64_decode($session->get('url_return'));                                
		
		
		if($result){
			$this->loginCurrentUser($result, $attrs, $login_url, $name, $username, $email, $matcher, $app);
		}else{
			$this->RegisterCurrentUser($attrs, $login_url, $name, $username, $email, $saml_groups, $matcher, $app);
		}
		
	}
	
	function loginCurrentUser($result, $attrs, $login_url, $name, $username, $email, $matcher, $app){
		
		$user = JUser::getInstance($result->id);
		
		
		Utilities::updateCurrentUserName($user->id, $name);
		
		//$this->updateUserProfileAttributes($user->id, $attrs, $user_profile_attributes);
		
		$session = JFactory::getSession(); #Get current session vars
		// Register the needed session variables
		$session->set('user',$user);
		$session->set('MO_SAML_NAMEID', $attrs['ASSERTION_NAME_ID']);
		$session->set('MO_SAML_SESSION_INDEX', $attrs['ASSERTION_SESSION_INDEX']);
		
		$app->checkSession();
                
                /*Edicion para autenticar de forma correcta el usuario en Joomla Front-end.
                 @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                 */                
                $credentials = array(
                    'username' => $username,
                    'password' => $password);

                $options = array();

                $result2 = $app->login($credentials, array('action' => 'core.login.admin')); // boolean true, if login suceeded 
		$sessionId = $session->getId();
		Utilities::updateUsernameToSessionId($user->username, $sessionId);
		
		$user->setLastVisit();
                /*Edicion para permitir la autenticacion en el administrator.
                 @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                 */                            
			$app->redirect(urldecode($login_url."?ssso=".md5($sessionId)));
		
	}
	
	function RegisterCurrentUser($attrs, $login_url, $name, $username, $email, $saml_groups, $matcher, $app){
		
		$role_mapping = Utilities::getRoleMapping();
		$default_group  = 2;
		if(isset($role_mapping['mapping_value_default']))
			$default_group = $role_mapping['mapping_value_default'];
		
		$role_mapping_key_value = array();
		if(isset($role_mapping['role_mapping_key_value']))
			$role_mapping_key_value = json_decode($role_mapping['role_mapping_key_value']);
		
		$enable_saml_role_mapping = 0;
		if(isset($role_mapping['enable_saml_role_mapping']))
			$enable_saml_role_mapping = json_decode($role_mapping['enable_saml_role_mapping']);
		
		// user data
		$data['name'] = (isset($name) && !empty($name)) ? $name : $username;
		$data['username'] = $username;
		$data['email'] = $data['email1'] = $data['email2'] = JStringPunycode::emailToPunycode($email);
		$data['password'] = $data['password1'] = $data['password2'] = JUserHelper::genRandomPassword();
		$data['activation'] = '0';
		$data['block'] = '0';
		
		if($enable_saml_role_mapping==1){
			$groups = Utilities::get_mapped_groups($role_mapping_key_value , $saml_groups);
			if (empty($groups)) {
				$data['groups'][] = $default_group;
			}else{
				foreach ($groups as $group) {
					$data['groups'][] = $group;
				}
			}
		}
		
		// Get the model and validate the data.
		jimport('joomla.application.component.model');

		if (!defined('JPATH_COMPONENT')) {
			define('JPATH_COMPONENT', JPATH_BASE . '/components/');
		}
		
		$user = new JUser;
		//Write to database
		if(!$user->bind($data)) {
			//print_r($user->getError());exit;
			throw new Exception("Could not bind data. Error: " . $user->getError());
		}
		if (!$user->save()) {
			//print_r($user->getError());exit;
			$siteUrl = JURI::root();
			ob_end_clean();
			$siteUrl = $siteUrl. '/plugins/authentication/miniorangesaml/';
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">
			<img style="width:15;"src="'. $siteUrl . 'images/wrong.png"> ERROR</div>
			<div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Could not save user. '. $user->getError().'</p>
			<p>You are receiving this error because your email address is invalid.</p>
			<p>If you have checked your email address and the error still persists then please report following error to your System Administrator:
					<ul>
					<li>Attribute name for e-mail should be NAME_ID only.</li>
					<li>Please change the attribute name in your IdP.</li> <li>You can Upgrade to <b>Premium</b> version if you wish to create custom attribute name for e-mail.</li></ul>
					</p>

				</div>
				<div style="text-align:center;"><a href="index.php" target="_blank">Back to Home</a></div>';
			exit;
			
		}
		
		//Utilities::updateActivationStatusForUser($username);
		
		$result = Utilities::get_user_from_joomla($matcher,$username,$email);
		if($result){
			$user = JUser::getInstance($result->id);
			
			//$this->updateUserProfileAttributes($user->id, $attrs, $user_profile_attributes);
			
			$session = JFactory::getSession(); #Get current session vars
			// Register the needed session variables
			$session->set('user',$user);
			$session->set('MO_SAML_NAMEID', $attrs['ASSERTION_NAME_ID']);
			$session->set('MO_SAML_SESSION_INDEX', $attrs['ASSERTION_SESSION_INDEX']);
			
			$app->checkSession();
                        /*Edicion para autenticar de forma correcta el usuario en Joomla Front-end.
                         @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                         */    
                        $credentials = array(
                            'username' => $user->username,
                            'password' => $password);

                        $options = array();

                        $result2 = $app->login($credentials, array('action' => 'core.login.admin'));                         
			$sessionId = $session->getId();
			Utilities::updateUsernameToSessionId($user->username, $sessionId);
			
			/* Update Last Visit Date */
			$user->setLastVisit();
                        /*Edicion para permitir la autenticacion en el administrator.
                         @author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
                         */                          
                        $lang =JFactory::getLanguage();
                        $lang->load('com_miniorange_saml');                                              
			$app->redirect(urldecode($login_url."?ssso=".md5($sessionId)), JText::_('COM_MINIORANGE_SAML_MSGREGIS')." ".$user->username, 'message');
		}
		
	}
	
	
}