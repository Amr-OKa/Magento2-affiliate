<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class LogoutSuccess extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor to inject dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute the action to show logout success page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Optionally, add a custom message here for user confirmation
        $this->messageManager->addSuccessMessage(__('You have been successfully logged out.'));

        // Render the logout success page
        return $this->resultPageFactory->create();
    }
}
