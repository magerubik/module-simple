<?php
namespace Magerubik\Simple\Controller\Adminhtml\Message;
class NewAction extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
