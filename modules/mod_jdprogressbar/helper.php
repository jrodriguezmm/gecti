<?php
// Licensed under the GPL v3
defined('_JEXEC') or die;
$doc = JFactory::getDocument();
//Style Sheet
$doc->addStyleSheet(JURI::root().'media/mod_jdprogressbar/css/jdprogressbar.css');

class modJDprogressBarsHelper {
   public static function GetColor($type,$params){
      $return='';
      
      switch($type){
            case 'color1':
               $return = $params->get('color1');
               break;
            case 'color2':
               $return = $params->get('color2');
               break;
            case 'color3':
               $return = $params->get('color3');
               break;
            case 'color4':
               $return = $params->get('color4');
               break;
            case 'color5':
               $return = $params->get('color5');
               break;
      }
      return $return;
   }
}