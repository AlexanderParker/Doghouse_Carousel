<?php

class Doghouse_Carousel_Block_Adminhtml_Item extends Mage_Adminhtml_Block_Widget_Grid_Container{

    public function __construct()
    {
        $this->_controller = "adminhtml_item";
        $this->_blockGroup = "dhcarousel";
        $this->_headerText = Mage::helper("dhcarousel")->__("Manage Carousel Items");
        $this->_addButtonLabel = Mage::helper("dhcarousel")->__("Add Carousel Item");
        parent::__construct();
    }

}