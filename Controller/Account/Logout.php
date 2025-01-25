<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class Logout extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        $this->session = $customerSession;
        parent::__construct($context);
    }

    /**
     * Customer logout action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Store the current customer ID before logging out
        $lastCustomerId = $this->session->getId();

        // Log the customer out and set the before authentication URL
        $this->session->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl()) // Redirect user back to the previous page after logout
            ->setLastCustomerId($lastCustomerId);

        // Prepare a redirect result and send the user to a logout success page or home page
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        // Optionally, you can redirect to a custom success page here
        $resultRedirect->setPath('affiliate/account/login'); // Redirect user to the login page after logout

        return $resultRedirect;
    }
}
