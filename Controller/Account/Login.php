<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Affiliate
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Lof\Affiliate\Model\AccountAffiliate;

class Login extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AccountAffiliate
     */
    protected $_accountAffiliate;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param AccountAffiliate $accountAffiliate
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        AccountAffiliate $accountAffiliate
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->_accountAffiliate = $accountAffiliate;
        parent::__construct($context);
    }

    /**
     * Executes the login check and shows the appropriate page.
     *
     * @return \Magento\Framework\Controller\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Check if the customer is already logged in
        if ($this->session->isLoggedIn()) {
            $emailCustomer = $this->session->getCustomer()->getEmail();
            // Check if the affiliate account exists
            $checkAccountExist = $this->_accountAffiliate->checkAccountExist($emailCustomer);

            // If account exists, redirect to account edit page
            if ($checkAccountExist == '1') {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/edit');
                return $resultRedirect;
            }
        }

        // If not logged in or no affiliate account, display the login page
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Login'));
        return $resultPage;
    }
}
