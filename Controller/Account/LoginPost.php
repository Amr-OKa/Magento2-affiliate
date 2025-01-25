<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\UrlFactory;

class LoginPost extends \Magento\Customer\Controller\AbstractAccount
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Validator */
    protected $formKeyValidator;

    /** @var AccountRedirect */
    protected $accountRedirect;

    /** @var Session */
    protected $session;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /** @var CustomerUrl */
    protected $customerUrl;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        UrlFactory $urlFactory
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context);
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        // Check if the user is already logged in or if the form key is invalid
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/'); // Redirect to homepage if already logged in or invalid form key
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    // Authenticate the user with the provided username and password
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId(); // Regenerate session ID for security
                } catch (EmailNotConfirmedException $e) {
                    // Handle unconfirmed email scenario
                    $confirmationUrl = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed.' . 
                        ' <a href="%1">Click here</a> to resend confirmation email.',
                        $confirmationUrl
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    // Handle invalid login or password
                    $this->messageManager->addError(__('Invalid login or password.'));
                    $this->session->setUsername($login['username']);
                } catch (\Exception $e) {
                    // General error handling
                    $this->messageManager->addError(__('Invalid login or password.'));
                }
            } else {
                // Handle missing username or password
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }

        // Redirect to the desired page after login attempt
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->urlModel->getUrl('affiliate/affiliate/home', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->success($url));
        return $resultRedirect;
    }
}
