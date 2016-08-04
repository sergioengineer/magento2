<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Constraint\AssertFieldsAreActive;
use Magento\Payment\Test\Constraint\AssertFieldsAreDisabled;
use Magento\Payment\Test\Constraint\AssertFieldsAreEnabled;
use Magento\Payment\Test\Constraint\AssertFieldsArePresent;

/**
 * Check PayPal Payments Pro configuration.
 */
class CheckPaymentsProConfigStep implements TestStepInterface
{
    /**
     * @var SystemConfigEditSectionPayment
     */
    private $systemConfigEditSectionPayment;

    /**
     * @var AssertFieldsAreDisabled
     */
    private $assertFieldsAreDisabled;

    /**
     * @var AssertFieldsArePresent
     */
    private $assertFieldsArePresent;

    /**
     * @var AssertFieldsAreActive
     */
    private $assertFieldsAreActive;

    /**
     * @var AssertFieldsAreEnabled
     */
    private $assertFieldsAreEnabled;

    /**
     * @var
     */
    private $countryCode;

    /**
     * @var
     */
    private $sections;

    /**
     * @var \Magento\Paypal\Test\Block\System\Config\PaymentsPro
     */
    private $paymentsProConfigBlock;

    /**
     * @param SystemConfigEditSectionPayment $systemConfigEditSectionPayment
     * @param AssertFieldsAreDisabled $assertFieldsAreDisabled
     * @param AssertFieldsArePresent $assertFieldsArePresent
     * @param AssertFieldsAreActive $assertFieldsAreActive
     * @param AssertFieldsAreEnabled $assertFieldsAreEnabled
     * @param $countryCode
     * @param $sections
     */
    public function __construct(
        SystemConfigEditSectionPayment $systemConfigEditSectionPayment,
        AssertFieldsAreDisabled $assertFieldsAreDisabled,
        AssertFieldsArePresent $assertFieldsArePresent,
        AssertFieldsAreActive $assertFieldsAreActive,
        AssertFieldsAreEnabled $assertFieldsAreEnabled,
        $countryCode,
        $sections
    ) {
        $this->systemConfigEditSectionPayment = $systemConfigEditSectionPayment;
        $this->assertFieldsAreDisabled = $assertFieldsAreDisabled;
        $this->assertFieldsArePresent = $assertFieldsArePresent;
        $this->assertFieldsAreActive = $assertFieldsAreActive;
        $this->assertFieldsAreEnabled = $assertFieldsAreEnabled;
        $this->countryCode = $countryCode;
        $this->sections = $sections;
        $this->paymentsProConfigBlock = $this->systemConfigEditSectionPayment->getPaymentsProConfigBlock();
    }

    /**
     * Run step for checking Payments Pro configuration.
     *
     * @return void
     */
    public function run()
    {
        $this->systemConfigEditSectionPayment->getPaymentsConfigBlock()->expandPaymentSections($this->sections);
        $this->enablePaymentsPro();
        $this->disablePaymentsPro();
    }

    /**
     * Enables Payments Pro and makes assertions for fields.
     *
     * @return void
     */
    private function enablePaymentsPro()
    {
        $this->paymentsProConfigBlock->clickConfigureButton();
        $this->paymentsProConfigBlock->clearCredentials();
        $enablers = $this->paymentsProConfigBlock->getEnablerFields();
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable this Solution'], $enablers['Enable PayPal Credit']]
        );
        $this->paymentsProConfigBlock->specifyCredentials();
        $this->assertFieldsAreActive->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable this Solution']]
        );
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable PayPal Credit']]
        );
        $this->paymentsProConfigBlock->enablePaymentsPro();
        $this->assertFieldsAreActive->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable this Solution'], $enablers['Enable PayPal Credit'], $enablers['Vault enabled']]
        );
        $this->assertFieldsAreEnabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable this Solution'], $enablers['Enable PayPal Credit']]
        );
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Disables Payments Pro and makes assertions for fields.
     *
     * @return void
     */
    private function disablePaymentsPro()
    {
        $enablers = $this->paymentsProConfigBlock->getEnablerFields();
        $this->paymentsProConfigBlock->clickConfigureButton();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->assertFieldsAreEnabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable this Solution'], $enablers['Enable PayPal Credit']]
        );
        $this->paymentsProConfigBlock->disablePaymentsPro();
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable PayPal Credit']]
        );
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessage();
    }
}
