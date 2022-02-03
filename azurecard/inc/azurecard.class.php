<?php
 
class Pluginazurecard extends CommonGLPI
{
    /**
     * Função chamada pelo GLPI para permitir o plugin inserir um ou mais itens 
     * dentro do menu esquerdo
     */
function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
   switch ($item::getType()) {
      case Ticket::getType():
         return __('Tab from my plugin', 'azurecard');
         break;
   }
   return '';
} 
     /**
     * Função chamada pelo GLPI para renderizar o formulario quando o usuario clicar
     * no item de menu gerado a partir do metodo getTabNameForItem() 
     */

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
    {
        ?>
        <form action="../plugins/azurecard/front/azurecard.form.php" method="post">
            <?php echo Html::hidden('id', array('value' => $item->getID())); ?>
            <?php echo Html::hidden('_glpi_csrf_token', array('value' => Session::getNewCSRFToken())); ?>
            <div class="spaced" id="tabsbody">
                <table class="tab_cadre_fixe">
                    <tr class="tab_bg_1">
                        <td>
                            New Computer name: &nbsp;&nbsp;&nbsp;
                            <input type="text" name="name" size="40" class="ui-autocomplete-input" autocomplete="off"> &nbsp;&nbsp;&nbsp;
                            <input type="submit" class="submit" value="azure" name="azure"/>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <?php
        return true;
    }
}
