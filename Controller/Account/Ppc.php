<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Lof\Affiliate\Helper\Data;

class Ppc extends \Magento\Framework\App\Action\Action
{
    protected $session;
    protected $helper;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        // Get the param_code from the helper
        $param_code = $this->helper->getParamCode();
        
        // Validate required parameters from the request
        if ($this->getRequest()->getParam($param_code) && $this->getRequest()->getParam('bannerid') && $this->getRequest()->getParam('url')) {
            $bannerId = $this->getRequest()->getParam('bannerid');
            $url = $this->getRequest()->getParam('url');
            
            // Count the click using the helper
            $this->helper->countedClickBanner($bannerId, $this->getRequest()->getParam($param_code));

            // Ensure the URL is valid before redirecting
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($url);  // Redirect to the provided URL
                return $resultRedirect;
            } else {
                // If URL is invalid, redirect to a default page (can be customized)
                $this->messageManager->addError(__('Invalid URL provided.'));
                return $this->_redirect('affiliate/affiliate/home');
            }
        }

        // If parameters are missing, load the default PPC page
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
