<?php

namespace Lof\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;
use Lof\Affiliate\Model\AccountAffiliateFactory;

class ReferCode extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $session;
    protected $accountAffiliateFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        AccountAffiliateFactory $accountAffiliateFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->accountAffiliateFactory = $accountAffiliateFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $modelAffiliateAccount = $this->accountAffiliateFactory->create();

        if ($this->session->isLoggedIn()) {
            $dataRequest = $this->getRequest()->getParams();
            $resultPage->getConfig()->getTitle()->set(__('Refer Code'));

            if ($dataRequest) {
                $accountAffiliateId = isset($dataRequest['account_affiliate_id']) ? $dataRequest['account_affiliate_id'] : null;
                $campaignCode = isset($dataRequest['campaign_code']) ? $dataRequest['campaign_code'] : null;

                if (!$accountAffiliateId || !$campaignCode) {
                    $this->messageManager->addError(__('Missing necessary data.'));
                    return $resultPage;
                }

                $affiliateAccount = $modelAffiliateAccount->load($accountAffiliateId);
                if ($affiliateAccount->getId()) {
                    try {
                        $affiliateAccount->setCampaignCode($campaignCode)->save();
                        $this->messageManager->addSuccessMessage(__('Campaign code saved successfully.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('Error saving campaign code.'));
                    }
                } else {
                    $this->messageManager->addError(__('Affiliate account not found.'));
                }
            }

            return $resultPage;
        } else {
            // Redirect to the login page and return after successful login
            $resultRedirect->setPath('affiliate/account/login', ['_secure' => true]);
            return $resultRedirect;
        }
    }
}
