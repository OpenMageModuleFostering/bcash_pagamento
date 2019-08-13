<?php

/**
 * Classe para reescrita de funcionalidades no Block de listagem de pedidos
 */
class Bcash_Pagamento_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order_with_bcash', array(
                'label' => $this->__('Cancelar com Bcash'),
                'url' => $this->getUrl('bcash/admin_sales_order/masscanceltransaction/')
            ));
        }
    }
}