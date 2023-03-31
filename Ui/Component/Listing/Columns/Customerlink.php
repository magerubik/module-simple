<?php
namespace Magerubik\Simple\Ui\Component\Listing\Columns;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
class Customerlink extends Column
{
    protected $urlBuilder;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $customerId = $item['user_id'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerData = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                $userName = $customerData->getFirstname() . ' ' . $customerData->getLastname();
                $item[$fieldName] = "<a href='" . $this->urlBuilder->getUrl('customer/index/edit', ['id' => $item['user_id']]) . "' target='blank' title='".__('View Customer')."'>" . $userName . "</a>";
            }
        }

        return $dataSource;
    }
}