<?php
define('PLUGIN_AZURECARD_VERSION', '0.4.0');

class PluginAzureCardConfig extends CommonDBTM {

   static protected $notable = true;
   static function getMenuName() {
      return __('AzureCard');
   }
   
   static function getMenuContent() {
    global $CFG_GLPI;
   
    $menu = array();

      $menu['title']   = __('AzureCard','azurecard');
      return $menu;
   }	
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Ticket':
            return array(1 => __('AzureCard','azurecard'));
         default:
            return '';
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Ticket':
            $config = new self();
            $config->showFormDisplay();
            break;
      }
      return true;
   }

   function showFormDisplay() {
      global $CFG_GLPI, $DB;
      $ID = $_REQUEST['id'];      
      $botao = Session::haveRight(Config::$rightname, UPDATE);
      echo "<form name='form' action='../plugins/azurecard/front/azurecard.php' method='POST'>\n";
      echo Html::hidden('config_context', ['value' => 'azurecard']);
      echo Html::hidden('config_class', ['value' => __CLASS__]);
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "<div class='center' id='tabsbody'>\n";
      echo "<table class='tab_cadre_fixe' style='width:95%;'>\n";
      echo "<tr class='tab_bg_2'>\n";
      echo "<td colspan='4' class='center'>\n";
      echo "<input type='submit' name='update' class='submit' value=\"" . __('Gerar Card na Azure', 'azurecard') . "\">\n";
      echo "</td></tr>\n";
      echo "</table></div>";
      Html::closeForm();

   }
}

function plugin_init_azurecard() {
  global $PLUGIN_HOOKS, $LANG;
  
  $PLUGIN_HOOKS['csrf_compliant']['azurecard'] = true;

   Plugin::registerClass('PluginAzureCardConfig', [
      'addtabon' => ['Ticket']
   ]);   
  
  $PLUGIN_HOOKS["menu_toadd"]['azurecard'] = array('plugins'  => 'PluginAzureCardConfig');
  $PLUGIN_HOOKS['config_page']['azurecard'] = 'front/index.php';
}


function plugin_version_azurecard(){
  global $DB, $LANG;

  return array('name'     => __('AzureCard','azurecard'),
          'version'   => PLUGIN_AZURECARD_VERSION ,
          'author'         => '<a href="https://github.com/infratidev/glpi-plugin-azurecard">Andrei Antonelli</b></a>',
          'license'     => 'GPLv2+',
          'minGlpiVersion'  => '9.3',
          );
}

function plugin_azurecard_check_prerequisites(){
        if (GLPI_VERSION>=9.3){
                return true;
        } else {
                echo "GLPI version NOT compatible. Requires GLPI 9.3";
        }
}


function plugin_azurecard_check_config($verbose=false){
  if ($verbose) {
    echo 'Installed / not configured';
  }
  return true;
}
?>
