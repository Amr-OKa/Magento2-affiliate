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

class CheckAccountExist extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Lof\Affiliate\Model\AccountAffiliate
     */
    protected $_accountAff;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Constructor
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Lof\Affiliate\Model\AccountAffiliate $accountAffiliate
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\Affiliate\Model\AccountAffiliate $accountAffiliate,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_accountAff = $accountAffiliate;
        $this->_jsonFactory = $jsonFactory;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Execute the action to check if the email is already registered
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $email_address = $this->getRequest()->getParam('email_address');
        $response = $this->_jsonFactory->create();

        if (!$email_address) {
            $response->setData([
                'message' => $this->_escaper->escapeHtml('Email address is required.'),
                'is_valid_email' => 0
            ]);
            return $response;
        }

        // Check if account exists with the provided email
        $account = $this->_accountAff->checkAccExist($email_address);

        if (!empty($account)) {
            $response->setData([
                'message' => $this->_escaper->escapeHtml('You can\'t use this email address.'),
                'is_valid_email' => 0
            ]);
        } else {
            $response->setData([
                'message' => $this->_escaper->escapeHtml('You can use this email address.'),
                'is_valid_email' => 1
            ]);
        }

        return $response;
    }
}
