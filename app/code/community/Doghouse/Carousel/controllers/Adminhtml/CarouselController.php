<?php

class Doghouse_Carousel_Adminhtml_CarouselController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu("cms/dhcarousel");

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__("Manage Carousel Items"));
        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('dhcarousel/adminhtml_item', 'carousel.items'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_title($this->__("New Carousel Item"));
        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock("dhcarousel/adminhtml_item_edit"));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__("Edit Carousel Item"));
        $this->_initAction();

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("dhcarousel/item")->load($id);

        if ($model->getId()) {
            Mage::register("carousel_item_data", $model);
            $this->_addContent($this->getLayout()->createBlock("dhcarousel/adminhtml_item_edit"));
            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("adminhtml")->__("Item does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function saveAction()
    {

        $post_data = $this->getRequest()->getPost();

        if ($post_data) {

            try {

                // File upload
                if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                    if($filename = Mage::helper('dhcarousel')->saveImage('image')) {
                        $post_data['image'] = $filename;
                    }
                } else {
                    if(!$this->getRequest()->getParam("id")) {
                        throw new Exception('An image is required!');
                    }
                    unset($post_data['image']);
                }

                // Product picker
                if (isset($post_data['product_link']) && !empty($post_data['product_link'])) {
                    list($trash, $productId) = explode('/', $post_data['product_link']);
                } else {
                    $productId = null;
                }

                $model = Mage::getModel("dhcarousel/item")
                    ->addData($post_data)
                    ->setProductId($productId)
                    ->setId($this->getRequest()->getParam("id"));

                Mage::dispatchEvent('dhcarousel_controller_action_form_save', array(
                    'controller' => $this,
                    'model'      => $model
                ));

                $model->save();

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setCarouselItemData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }

                $this->_redirect("*/*/");

                return;

            } catch (Exception $e) {

                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setCarouselItemData($this->getRequest()->getPost());

                if($this->getRequest()->getParam("id")) {
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                } else {
                    $this->_redirect("*/*/new");
                }

                return;
            }

        }

        $this->_redirect("*/*/");
    }

    public function deleteAction()
    {
        if( $this->getRequest()->getParam("id") > 0 ) {
            try {
                $model = Mage::getModel("dhcarousel/item");
                $model->setId($this->getRequest()->getParam("id"))->delete();
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
                $this->_redirect("*/*/");
            }
            catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            }
        }
        $this->_redirect("*/*/");
    }

    public function massRemoveAction()
    {
        try {
            $ids = $this->getRequest()->getPost('ids', array());
            foreach ($ids as $id) {
                  $model = Mage::getModel("dhcarousel/item");
                  $model->setId($id)->delete();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) successfully removed"));
        }
        catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'carousel.csv';
        $grid       = $this->getLayout()->createBlock('dhcarousel/adminhtml_item_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    /**
     *  Export grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'carousel.xml';
        $grid       = $this->getLayout()->createBlock('dhcarousel/adminhtml_item_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function gridAction()
    {
        $this->loadLayout();
        return $this->getResponse()->setBody(
            $this->getLayout()->createBlock('dhcarousel/adminhtml_item_grid')->toHtml()
        );
    }

}
