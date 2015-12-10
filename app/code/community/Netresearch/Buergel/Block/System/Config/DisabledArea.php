<?php
class Netresearch_Buergel_Block_System_Config_DisabledArea extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<div id="%s" class="box">%s</div>',
            $element->getHtmlId(),
            $element->getEscapedValue()
        );
    }
}