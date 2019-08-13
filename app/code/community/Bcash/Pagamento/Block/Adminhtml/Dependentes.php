<?php

/**
 * Class Bcash_Pagamento_Block_Adminhtml_Dependentes
 */
class Bcash_Pagamento_Block_Adminhtml_Dependentes extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * @var array
     */
    protected $_addRowButtonHtml = array();
    /**
     * @var array
     */
    protected $_removeRowButtonHtml = array();

   /**
    * Returns html part of the setting
    *
    * @param Varien_Data_Form_Element_Abstract $element
    * @return string
    */
   protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
   {
       $this->setElement($element);

       $html = '<div id="email_dependentes_template" style="display:none">';
       $html .= $this->_getRowTemplateHtml();
       $html .= '</div>';

       $html .= '<ul id="email_dependentes_container" style="width:100%;">';
       if ($this->_getValue('dependente')) {
           foreach ($this->_getValue('dependente') as $i => $f) {
               if ($i) {
                   $html .= $this->_getRowTemplateHtml($i);
               }
           }
       }
       $html .= '</ul>';
       $html .= $this->_getAddRowButtonHtml('email_dependentes_container', 'email_dependentes_template', $this->__('Adicionar nova Transação dependente'));

       return $html;
   }

   /**
    * Retrieve html template for setting
    *
    * @param int $rowIndex
    * @return string
    */
   protected function _getRowTemplateHtml($rowIndex = 0)
   {
       $html = '<li>';

       $html .= '<div style="margin:5px 0 10px;width:100%;">';
       $html .= '<label style="width:80px;float: left;">Email:</label>'
           . '<input style="width:200px;" class="validate-email" name="' . $this->getElement()->getName() . '[dependente][]"'
           .' value="' . $this->_getValue('dependente/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
       $html .= '<label style="width:80px;float: left;">Percentual:</label>'
           . '<input style="width:70px;" onblur="if (this.value.indexOf(\',\') > -1) {  this.value = this.value.replace(\',\',\'.\') }" maxlength="4" name="'
           . $this->getElement()->getName() . '[percentual][]" class="validate-number validate-number-range number-range-0-10" value="'
           . $this->_getValue('percentual/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
       $html .= $this->_getRemoveRowButtonHtml();
       $html .= '</div>';
       $html .= '</li>';

       return $html;
   }

    /**
     * @return string
     */
    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/' . $key);
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
    }

    /**
     * @param $container
     * @param $template
     * @param string $title
     * @return mixed
     */
    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('adminhtml/widget_button')
               ->setType('button')
               ->setClass('add ' . $this->_getDisabled())
               ->setLabel($this->__($title))
               ->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML})")
               ->setDisabled($this->_getDisabled())
               //  ->setStyle("float:left;clear:right;")
               ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    /**
     * @param string $selector
     * @param string $title
     * @return array
     */
    protected function _getRemoveRowButtonHtml($selector = 'li', $title = 'Delete')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')
               ->setType('button')
               ->setClass('delete v-middle ' . $this->_getDisabled())
               ->setLabel($this->__($title))
               ->setOnClick("Element.remove($(this).up('" . $selector . "'))")
               ->setDisabled($this->_getDisabled())
               ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
}
