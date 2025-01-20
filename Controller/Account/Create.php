<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Lof\Affiliate\Helper\Data as AffiliateHelper;

class Create extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var Registration */
    protected $registration;

    /** @var Session */
    protected $session;

    /** @var AffiliateHelper */
    protected $helper;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var ManagerInterface */
    protected $messageManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param Registration $registration
     * @param AffiliateHelper $helper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Registration $registration,
        AffiliateHelper $helper,
        ManagerInterface $messageManager
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->registration = $registration;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Customer register form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Check if the user is logged in and registration is allowed
        if (!$this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to join the affiliate program.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }
    
        // Check if the customer is already an affiliate
        $customerId = $this->session->getCustomer()->getId();
        $existingAffiliate = $this->helper->getAffiliateAccountByCustomerId($customerId);
    
        if ($existingAffiliate) {
            $this->messageManager->addNoticeMessage(__('You are already part of the affiliate program.'));
            return $this->resultRedirectFactory->create()->setPath('affiliate/account');
        }
    
        // Process form data and create an affiliate account
        $postData = $this->getRequest()->getPostValue();
        $customerData = $this->session->getCustomer();
        $data = [
            'customer_id' => $customerId,
            'email' => $customerData->getEmail(),
            'fullname' => $customerData->getName(),
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    
        try {
            // Call the helper function to create the affiliate account
            $this->helper->createAffiliateAccount($data, $customerData);
            $this->messageManager->addSuccessMessage(__('You have successfully joined the affiliate program.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while creating your affiliate account. Please try again.'));
        }
    
        // Redirect to the affiliate account page
        return $this->resultRedirectFactory->create()->setPath('affiliate/account');
    }
}
