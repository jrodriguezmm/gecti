<?php

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
* Nombre: PlgUsersso
* Detalle: Plugin para realizar el cierre de session en AZURE al cerrar en Joomla.
* @author: John Fredy Velasco Bareño
* fecha de creacion: Septiembre de 2017
* @link jf.velasco20@uniandes.edu.co
*/
class PlgUsersso extends JPlugin
{

	/**
	 * name ID alamacenado en session del SSO.
	 *
	 * @var    nameID
	 */
        protected $nameID;

	/**
	 * Se ejecuta antes del cierre de session, se almacena la variable de session para nameID.
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (client, ...).
	 *
	 * @return  bool  True on success
	 */
	public function onUserLogout($user, $options = array())
	{
                $session = JFactory::getSession();
                $this->nameID=$session->get("MO_SAML_NAMEID");
		return true;
	}
        /**
         * Se ejecuta despues del cierre de session, valida si existe session SSO y envia para ser cerrada.
         * @return void
         */
        function onUserAfterLogout($credentials, $options){            
            $Url=JURI::current()."?morequest=ssout&nameID=".$this->nameID;
            if(isset($this->nameID) and empty($this->nameID)==false){
                $app = JFactory::getApplication();
                $app->redirect(urldecode($Url));
            }
        }
}
