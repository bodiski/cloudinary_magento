<?php

class Cloudinary_Cloudinary_Adminhtml_CloudinaryController extends Mage_Adminhtml_Controller_Action
{
    private $_migrationTask;
    /**
     * @var Cloudinary_Cloudinary_Helper_Configuration
     */
    private $_cloudinaryConfig;

    public function preDispatch()
    {
        $this->_migrationTask = Mage::getModel('cloudinary_cloudinary/migration')->load(Cloudinary_Cloudinary_Model_Migration::CLOUDINARY_MIGRATION_ID);
        $this->_cloudinaryConfig = Mage::getModel('cloudinary_cloudinary/configuration');

        parent::preDispatch();
    }

    public function indexAction()
    {
        $layout = $this->loadLayout();

        if (!$this->_cloudinaryConfig->validateCredentials()) {
            $link = '<a href="/admin/system_config/edit/section/cloudinary/">here</a>';
            $this->_getSession()->addError(
                "Please enter your Cloudinary Credentials $link to Activate Cloudinary"
            );
        }

        if ($this->_migrationTask->hasStarted()) {
            $layout->_addContent($this->_buildMetaRefreshBlock());
        }

        $this->renderLayout();
    }

    public function configAction()
    {
        $this->_redirect("*/system_config/edit/section/cloudinary/");
    }

    public function startMigrationAction()
    {
        $this->_migrationTask->start();

        $this->_redirectToManageCloudinary();
    }

    public function stopMigrationAction()
    {
        $this->_migrationTask->stop();

        $this->_redirectToManageCloudinary();
    }

    public function enableCloudinaryAction()
    {
        if (!$this->_cloudinaryConfig->validateCredentials()) {
            $this->_getSession()->addError('Validating credentials failed. Cloudinary stays disabled');
        } else {
            $this->_cloudinaryConfig->enable();
        }
        $this->_redirectToManageCloudinary();
    }

    public function disableCloudinaryAction()
    {
        $this->_cloudinaryConfig->disable();

        $this->_redirectToManageCloudinary();
    }

    public function clearErrorsAction()
    {
        $items = Mage::getModel('cloudinary_cloudinary/migrationError')->getCollection()->getItems();

        foreach ($items as $error) {
            $error->delete();
        }

        $this->_redirectToManageCloudinary();
    }

    private function _redirectToManageCloudinary()
    {
        return $this->_redirect('*/cloudinary');
    }

    private function _buildMetaRefreshBlock()
    {
        return $this->getLayout()->createBlock('core/text')->setText('<meta http-equiv="refresh" content="5">');
    }

}
