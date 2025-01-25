<?php 

namespace Lof\Affiliate\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Lof\Affiliate\Model\AccountAffiliate;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AccountAffiliate
     */
    protected $_modelAccount;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * Constructor method to inject dependencies.
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AccountAffiliate $modelAccount,
        Session $customerSession // Make sure to inject this dependency
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_modelAccount = $modelAccount;
        $this->_session = $customerSession; // Ensure this is set properly
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Check if the customer is logged in
        if (!$this->_session->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        // Get customer ID
        $customerId = $this->_session->getCustomerId();
        
        // Check if the customer has already joined the affiliate program
        $affiliateData = $this->_modelAccount->getAffiliateData($customerId);
        
        if ($affiliateData) {
            $this->messageManager->addNoticeMessage(__('You have already joined the affiliate program.'));
            return $this->_redirect('affiliate/account');
        }

        // Try to create the affiliate account
        try {
            // Call the method to create affiliate account (update this method in the model accordingly)
            $this->_modelAccount->createAffiliateAccount($customerId);

            // Success message
            $this->messageManager->addSuccessMessage(__('You have successfully joined the affiliate program.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Handle unique constraint violation and other errors
            if (strpos($e->getMessage(), 'Unique constraint violation') !== false) {
                $this->messageManager->addErrorMessage(__('An error occurred while creating your affiliate account. Please try again.'));
            } else {
                $this->messageManager->addErrorMessage(__('An unknown error occurred. Please try again later.'));
            }
        }

        // Render the account page with success/error messages
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Account'));

        return $resultPage;
    }
}
