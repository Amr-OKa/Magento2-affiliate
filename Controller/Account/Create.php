<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Lof\Affiliate\Model\AccountAffiliate;
use Magento\Framework\Message\ManagerInterface;

class Create extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var Registration */
    protected $registration;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param Registration $registration
     * @param \Lof\Affiliate\Helper\Data $helper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Registration $registration,
        \Lof\Affiliate\Helper\Data $helper,
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
        // Redirect if the user is not logged in or registration is not allowed
        if (!$this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to join the affiliate program.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        // Get form data
        $postData = $this->getRequest()->getPostValue();

        // Get customer data
        $customerData = $this->session->getCustomer();
        $data = [
            'customer_id' => $customerData->getId(),
            'email' => $customerData->getEmail(),
            'fullname' => $customerData->getName(),
            'paypal_email' => $postData['paypal_email'] ?? '', // Add paypal_email from form submission
            'status' => 1, // Set status to active
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            // Create affiliate account
            $this->helper->createAffiliateAccount($data, $customerData);
            $this->messageManager->addSuccessMessage(__('You have successfully joined the affiliate program.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while creating your affiliate account. Please try again.'));
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        // Redirect to the affiliate dashboard or homepage
        return $this->resultRedirectFactory->create()->setPath('affiliate/account');
    }
}