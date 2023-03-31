<?php
namespace Magerubik\Simple\Model\ResourceModel;
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('vendor_message', 'messages_id');
    }
}