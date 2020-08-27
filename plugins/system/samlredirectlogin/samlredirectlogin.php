<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.plugin.plugin' );

/**
* Nombre: plgSystemSamlredirectlogin
* Detalle: Plugin para autenticar usuario SSO en el administrator.
* @author: John Fredy Velasco Bareño
* fecha de creacion: Septiembre de 2017
* @link jf.velasco20@uniandes.edu.co
*/
class plgSystemSamlredirectlogin extends JPlugin
{
    	/**
	 * Se valida la existencia de la variable ssso y se envia a validar.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		$ssso=JRequest::getVar("ssso");          
		if (isset($ssso) && empty($ssso)==false) {
			$this->validarLogin($ssso);
		} 
	}
	/**
	 * Se valida si el usuario esta autenticado y la solicitud es desde el administrator y se autentica en el administrador.
	 *
	 * @param   string  $ssso identificador de session cifrado con MD5.
	 *
	 * @return  void
	 */        
	function validarLogin($ssso){
                $app = JFactory::getApplication();
		$bool_admin = $app->isAdmin();
                if($bool_admin==1){
                    $db = JFactory::getDBO();
                    $fsess = $ssso;

                    $conf = JFactory::getConfig();
                    if (version_compare(JVERSION,'3.0.0','>=')){
                            $cfgSessTimeSeconds = $conf->get('lifetime');
                    }else{
                            $cfgSessTimeSeconds = $conf->getValue('config.lifetime');
                    }
                    $cfgSessTimeSeconds = $cfgSessTimeSeconds * 60;

                    $q = "SELECT * FROM #__session WHERE 
                                            `time` + ".$db->quote($cfgSessTimeSeconds) ." > UNIX_TIMESTAMP()
                                            AND md5(session_id) = ".$db->quote($fsess);
                    $fsess = $this->getRow($q);
                    $suid  = $fsess->userid;
                    $session = JFactory::getSession();

                    if($suid<=0){	//destroy back end session if front end user is not logged in.
                            $q = "DELETE FROM #__session WHERE session_id = ".$db->quote($session->getId());
                            $this->doQuery($q);
                            return;
                    }
                    $user=new JUser($suid);
                    $session->set('user', $user);
                    $session->set('MO_SAML_NAMEID',$ssso);
                    $app->checkSession();                    
                    $credentials = array(
                        'username' => $user->username,
                        'password' => $password);

                    $options = array();

                    $result2 = $app->login($credentials, array('action' => 'core.login.admin'));                     

                }
	}
	
	function getRows( $query, $limitStart=0, $recs=0, $key='' ){
		$db	= JFactory::getDBO();

		if ( $recs > 0 ) {
			$db->setQuery( $query, $limitStart, $recs );
		}else{
			$db->setQuery( $query );
		}

		if ($key!=''){
			$rows = $db->loadObjectList($key);
		}else{
			$rows = $db->loadObjectList();
		}
		return $rows;
	}

	function getRow( $query ){
	    $rows = $this->getRows( $query );
		$row  = '';
		if ( count( $rows ) > 0 ) $row  = $rows[0];
		return $row;
	}
	function doQuery( $query ){
		$db	= JFactory::getDBO();
		$db->setQuery( $query );

	    return $db->query();
	}        
	
}