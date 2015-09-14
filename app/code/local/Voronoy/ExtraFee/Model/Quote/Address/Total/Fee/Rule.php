<?php
/**
 * Magento Extra Fee Extension
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright (c) 2015 by Yaroslav Voronoy (y.voronoy@gmail.com)
 * @license   http://www.gnu.org/licenses/
 */

class Voronoy_ExtraFee_Model_Quote_Address_Total_Fee_Rule extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Discount calculation object
     *
     * @var Mage_SalesRule_Model_Validator
     */
    protected $_calculator;

    /**
     * Initialize discount collector
     */
    public function __construct()
    {
        $this->_calculator = Mage::getSingleton('voronoy_extrafee/salesRule_validator');
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());
        $this->_calculator->reset($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $this->_calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->_calculator->initTotals($items, $address);

        $address->setDiscountDescription(array());
        $items = $this->_calculator->sortItemsByPriority($items);

        foreach ($items as $item) {
            $this->_calculator->process($item);
        }

        $this->_addAmount($item->getExtraFeeRuleAmount());
        $this->_addBaseAmount($item->getBaseExtraFeeRuleAmount());
    }

    /**
     * Fetch Totals
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return Voronoy_ExtraFee_Model_Quote_Address_Total_Fee_Payment
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getExtraFeeRuleAmount();
        $address->addTotal(array(
            'code'  => $this->getCode(),
            'title' => Mage::helper('voronoy_extrafee')->__('Rule Extra Fee'),
            'value' => $amount
        ));
        return $this;
    }
}