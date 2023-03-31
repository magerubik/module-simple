<?php
namespace Magerubik\Simple\Controller\Adminhtml\Message;
use Magento\Framework\Exception\LocalizedException;
class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('messages_id');
			$data = $this->_filterAttachmentData($data);
			if($id){
				$model = $this->_objectManager->create(\Magerubik\Simple\Model\Message::class)->load($id);
				if (!$model->getMessagesId()) {
					$this->messageManager->addErrorMessage(__('This message no longer exists.'));
					return $resultRedirect->setPath('*/*/');
				}
				$model->setData($data)->setId($id);
			}else{
				$model = $this->_objectManager->create(\Magerubik\Simple\Model\Message::class);
				$model->setData($data)->setId(NULL);
			}
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the message.'));
                $this->dataPersistor->clear('mrsimple_message');
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getMessagesId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the message.'));
            }
            $this->dataPersistor->set('mrsimple_message', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('messages_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
	public function _filterAttachmentData(array $rawData)
    {
        $data = $rawData;
        if (isset($data['img_attachment'][0]['name'])) {
            $data['img_attachment'] = $data['img_attachment'][0]['name'];
        } else {
            $data['img_attachment'] = null;
        }
        return $data;
    }
}