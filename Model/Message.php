<?php
namespace Magerubik\Simple\Model;
class Message extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magerubik\Simple\Model\ResourceModel\Message');
    }
}