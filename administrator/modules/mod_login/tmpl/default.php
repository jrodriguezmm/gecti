<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

// Load chosen if we have language selector, ie, more than one administrator language installed and enabled.
if ($langs)
{
	JHtml::_('formbehavior.chosen', '.advancedSelect');
}

/*Edición para verificar si hay más plugins habilitados aparte de SSO.
@author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
*/
$plugins = JPluginHelper::getPlugin('authentication');

$Url_SSO=JURI::root()."?morequest=sso&return=".base64_encode(JURI::current());
$bool_SSO=false;
foreach ($plugins as $plugin) {
	if($plugin->name=='miniorangesaml'){
		$bool_SSO=true;
		break;
	}
}

/*Edición para iniciar la autenticación SSO si no hay mas plugins habilitados aparte de SSO.
@author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
*/
if($plugins[0]->name=='miniorangesaml' and count($plugins)==1){
	header("Location: ".$Url_SSO);
	die();
}

?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure', 0)); ?>" method="post" id="form-login" class="form-inline">
	<fieldset class="loginform">
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-user hasTooltip" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>"></span>
						<label for="mod-login-username" class="element-invisible">
							<?php echo JText::_('JGLOBAL_USERNAME'); ?>
						</label>
					</span>
					<input name="username" tabindex="1" id="mod-login-username" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="15" autofocus="true" />
					<a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=remind" class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_LOGIN_REMIND'); ?>">
						<span class="icon-help"></span>
					</a>
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-lock hasTooltip" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>"></span>
						<label for="mod-login-password" class="element-invisible">
							<?php echo JText::_('JGLOBAL_PASSWORD'); ?>
						</label>
					</span>
					<input name="passwd" tabindex="2" id="mod-login-password" type="password" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" size="15"/>
					<a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=reset" class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_LOGIN_RESET'); ?>">
						<span class="icon-help"></span>
					</a>
				</div>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1): ?>
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"></span>
						<label for="mod-login-secretkey" class="element-invisible">
							<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
						</label>
					</span>
					<input name="secretkey" autocomplete="off" tabindex="3" id="mod-login-secretkey" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" size="15"/>
					<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<span class="icon-help"></span>
					</span>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<?php if (!empty($langs)) : ?>
			<div class="control-group">
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">
							<span class="icon-comment hasTooltip" title="<?php echo JHtml::_('tooltipText', 'MOD_LOGIN_LANGUAGE'); ?>"></span>
							<label for="lang" class="element-invisible">
								<?php echo JText::_('MOD_LOGIN_LANGUAGE'); ?>
							</label>
						</span>
						<?php echo $langs; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<div class="btn-group">
					<button tabindex="5" class="btn btn-primary btn-block btn-large login-button">
						<span class="icon-lock icon-white"></span> <?php echo JText::_('MOD_LOGIN_LOGIN'); ?>
					</button>
				</div>
				
				<?php
					/*Edición para validar si se debe o no mostrar el link de autenticación SSO.
					@author John Fredy Velasco Bareño <jf.velasco20@uniandes.edu.co>
					*/
					if($bool_SSO==true){
						$lang =JFactory::getLanguage();
						$lang->load('com_miniorange_saml');
						echo '<div style="margin-top: 20px; text-align: center;"><a href="'.$Url_SSO.'" >'.JText::_('COM_MINIORANGE_SAML_LINK_SSO').'</a></div>';
					}
				?>
				
			</div>
		</div>
		<input type="hidden" name="option" value="com_login"/>
		<input type="hidden" name="task" value="login"/>
		<input type="hidden" name="return" value="<?php echo $return; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>