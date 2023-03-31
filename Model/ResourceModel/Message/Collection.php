<?php
namespace Magerubik\Simple\Model\ResourceModel\Message;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'messages_id';
    protected function _construct()
    {
        $this->_init('Magerubik\Simple\Model\Message', 'Magerubik\Simple\Model\ResourceModel\Message');
    }
}