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
use Magento\Framework\Exception\LocalizedException;

class PaymentForm extends \Magento\Framework\App\Action\Action
{
    CONST EMAILIDENTIFIER = 'sent_mail_after_withdraw';

    protected $resultPageFactory;
    protected $_helper;
    protected $_accountModel;
    protected $session;
    protected $_scopeConfig;
    protected $_affData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\Affiliate\Helper\Data $helper,
        \Lof\Affiliate\Model\AccountAffiliate $accountModel,
        Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_accountModel = $accountModel;
        $this->session = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $customer = $this->session->getCustomer();
        $request = $this->getRequest()->getParam('request');
        $enable_withdrawl = $this->_helper->getConfig("general_settings/enable_withdrawl");

        // Check if withdrawal is enabled, if not, redirect to the affiliate home page
        if (!$enable_withdrawl) {
            $resultRedirect->setPath('affiliate/affiliate/home');
            return $resultRedirect;
        }

        // Get the parameters for currency code and payment method
        $currency_code = $this->getRequest()->getParam('currency_code');
        $payment_method = $this->getRequest()->getParam('type');
        
        try {
            // Save the withdrawal request and update the affiliate balance
            $this->_helper->saveWithdraw($request, $customer, $currency_code, $payment_method);
            $this->_accountModel->updateBalance($request, $customer);

            // Success message
            $this->messageManager->addSuccess(__('Your withdrawal request has been submitted successfully.'));

            // Prepare email details and send confirmation email
            $emailFrom = $this->_helper->getConfig('general_settings/sender_email_identity');
            $emailTo = $customer->getEmail();
            $templateVar = ['name' => $customer->getName(), 'currency_code' => $currency_code, 'payment_method' => $payment_method];

            // Send email to the customer
            $this->_helper->sendEmail($emailFrom, $emailTo, $templateVar, self::EMAILIDENTIFIER);

            // Redirect to the withdrawal page
            $resultRedirect->setPath('*/*/withdraw');
            return $resultRedirect;
        } catch (LocalizedException $e) {
            // Exception handling - show error message
            $this->messageManager->addErrorMessage(__('An error occurred while processing your request: ') . $e->getMessage());
        } catch (\Exception $e) {
            // Generic exception handler
            $this->messageManager->addErrorMessage(__('You cannot send a request at this time.'));
        }

        // Redirect to withdrawal page in case of an error
        $resultRedirect->setPath('*/*/withdraw');
        return $resultRedirect;
    }
}
