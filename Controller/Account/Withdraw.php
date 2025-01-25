<?php

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;

class Withdraw extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    protected $_helper;

    /**
     * Constructor to initialize dependencies.
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Lof\Affiliate\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        \Lof\Affiliate\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Handles the withdrawal page.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // If the affiliate is not logged in, redirect to the login page.
        if (!$this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/account/login');
            return $resultRedirect;
        }

        // Check if withdrawal is enabled in the configuration.
        $enable_withdrawl = $this->_helper->getConfig("general_settings/enable_withdrawl");

        // If withdrawals are disabled, redirect to the affiliate home page.
        if (!$enable_withdrawl) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/affiliate/home');
            return $resultRedirect;
        }

        // Generate the withdrawal page if everything is set up correctly.
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Your Withdrawals'));
        return $resultPage;
    }
}
