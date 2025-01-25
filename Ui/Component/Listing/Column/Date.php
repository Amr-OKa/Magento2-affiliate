<?php
namespace Lof\Affiliate\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;

class Date extends Column
{
    protected $timezone;
    protected $_logger;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        // Fetch the logger using ObjectManager
        $this->_logger = ObjectManager::getInstance()->get(LoggerInterface::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')]) && !empty($item[$this->getData('name')])) {
                    // Log the raw date value for debugging
                    $this->_logger->info('Raw Date: ' . $item[$this->getData('name')]);

                    $date = $item[$this->getData('name')];
                    // Format the date using Magento's timezone
                    $formattedDate = $this->timezone->date(new \DateTime($date))->format('Y-m-d H:i:s');
                    $item[$this->getData('name')] = $formattedDate;
                } else {
                    $item[$this->getData('name')] = ''; // Set empty value for empty dates
                }
            }
        }

        return $dataSource;
    }
}