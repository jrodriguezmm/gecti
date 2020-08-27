<?php
// Licensed under the GPL v3
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.form.formfield');

class JFormFieldJDRange extends JFormField {

   protected $type = 'jdrange';

   public function getInput() {
   
      $layout = new JLayoutFile('jdrange', JPATH_ROOT . '/modules/mod_jdprogressbar/fields/layouts');
      return $layout->render(['field' => $this,'element'=> $this->element]);
   }

}