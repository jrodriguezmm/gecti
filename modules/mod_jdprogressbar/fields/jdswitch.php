<?php
// Licensed under the GPL v3
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.form.formfield');

class JFormFieldJDSwitch extends JFormField {

   protected $type = 'jdswitch';

   public function getInput() {
   
      $layout = new JLayoutFile('jdswitch', JPATH_ROOT . '/modules/mod_jdprogressbar/fields/layouts');
      return $layout->render(['field' => $this,'element'=> $this->element]);

   }
   
   
}
