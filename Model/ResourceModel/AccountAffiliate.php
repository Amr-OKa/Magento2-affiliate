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

namespace Lof\Affiliate\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AccountAffiliate extends AbstractDb
{
    /**
     * @var \Lof\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Lof\Affiliate\Helper\Data $dataHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Lof\Affiliate\Helper\Data $dataHelper,
        $connectionName = null
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_affiliate_account', 'accountaffiliate_id');
    }

    /**
     * Perform operations before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            for ($i = 1; $i <= 10; $i++) {
                if ($this->checkTrackingCodeExists($object->getTrackingCode())) {
                    $tracking_code = $this->_dataHelper->getAffiliateTrackingCode();
                    $object->setTrackingCode($tracking_code);
                } else {
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Check if tracking code already exists
     *
     * @param string $tracking_code
     * @return bool
     */
    public function checkTrackingCodeExists($tracking_code = '')
    {
        if ($tracking_code) {
            $table_name = $this->getTable('lof_affiliate_account');
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                $table_name
            )->where(
                'tracking_code = :tracking_code'
            );

            $binds = [':tracking_code' => $tracking_code];
            $resultData = $connection->fetchRow($select, $binds);
            if ($resultData) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if account exists by email
     *
     * @param string $email
     * @return array
     */
    public function checkAccountExist($email)
    {
        $table_name = $this->getTable('customer_entity');
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $table_name
        )->where(
            'email = :email'
        );

        $binds = [':email' => $email];
        $collection = $connection->fetchCol($select, $binds);
        return $collection;
    }

    /**
     * Load affiliate account by customer ID
     *
     * @param \Lof\Affiliate\Model\AccountAffiliate $accountAffiliate
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId(\Lof\Affiliate\Model\AccountAffiliate $accountAffiliate, $customerId)
    {
        $connection = $this->getConnection();
        $bind = ['customer_id' => $customerId];
        $select = $connection->select()->from($this->getMainTable())
            ->where('customer_id = :customer_id');

        $data = $connection->fetchRow($select, $bind);
        if ($data) {
            $accountAffiliate->setData($data);
        }
        return $this;
    }
}