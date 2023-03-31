<?php
namespace Magerubik\Simple\Model\DataProvider;
use Magerubik\Simple\Model\ResourceModel\Message\CollectionFactory;
use Magerubik\Simple\Model\ImageProcessor;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
class MessageDataProvider extends AbstractDataProvider
{
    protected $loadedData;
	private $imageProcessor;
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
		ImageProcessor $imageProcessor,
		DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
		$this->imageProcessor = $imageProcessor;
		$this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    public function getData()
    {
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
			if($model->getImgAttachment()) {
                $img['img_attachment'][0]['name'] = $model->getImgAttachment();
                $img['img_attachment'][0]['url'] = $this->imageProcessor->getThumbnailUrl($model->getImgAttachment(), 'img_attachment');
                $fullData = $this->loadedData;
                $this->loadedData[$model->getId()] = array_merge($fullData[$model->getId()], $img);
            }
        }
		$data = $this->dataPersistor->get('mrsimple_message');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('mrsimple_message');
        }
        return $this->loadedData;
    }
}