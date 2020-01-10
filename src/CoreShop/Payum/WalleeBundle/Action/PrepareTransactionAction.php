<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Payum\WalleeBundle\Action;

use CoreShop\Component\Core\Model\CarrierInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Core\Model\OrderItemInterface;
use CoreShop\Component\Core\Model\PaymentInterface;
use CoreShop\Component\Order\Model\AdjustmentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Wallee\Sdk\Model\AddressCreate;
use Wallee\Sdk\Model\LineItemCreate;
use Wallee\Sdk\Model\LineItemType;
use WVision\Payum\Wallee\Request\PrepareTransaction;

class PrepareTransactionAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param $request PrepareTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getFirstModel();

        if (!$payment instanceof PaymentInterface) {
            return;
        }

        $order = $payment->getOrder();

        if (!$order instanceof OrderInterface) {
            return;
        }

        $orderLines = [];
        $lineItemId = 1;

        foreach ($order->getItems() as $item) {
            if (!$item instanceof OrderItemInterface) {
                continue;
            }

            $lineItem = new LineItemCreate();
            $lineItem->setName($item->getName());
            $lineItem->setUniqueId($lineItemId);
            $lineItem->setQuantity($item->getQuantity());
            $lineItem->setAmountIncludingTax($item->getTotal() / 100);
            $lineItem->setType(LineItemType::PRODUCT);

            $orderLines[] = $lineItem;

            $lineItemId++;
        }

        foreach ($order->getAdjustments(AdjustmentInterface::CART_PRICE_RULE) as $adjustment) {
            if (!$adjustment instanceof AdjustmentInterface) {
                continue;
            }

            $lineItem = new LineItemCreate();
            $lineItem->setName($adjustment->getLabel());
            $lineItem->setUniqueId($lineItemId);
            $lineItem->setQuantity($lineItemId);

            if ($adjustment->getAmount() < 0) {
                $lineItem->setDiscountIncludingTax($adjustment->getAmount() / 100);
                $lineItem->setType(LineItemType::DISCOUNT);
            }
            else {
                $lineItem->setAmountIncludingTax($adjustment->getAmount() / 100);
                $lineItem->setType(LineItemType::FEE);
            }

            $orderLines[] = $lineItem;

            $lineItemId++;
        }

        if ($order->getCarrier() instanceof CarrierInterface && $order->getShipping() > 0) {
            $shippingItem = new LineItemCreate();
            $shippingItem->setName($order->getCarrier()->getTitle());
            $shippingItem->setUniqueId($lineItemId);
            $shippingItem->setQuantity(1);
            $shippingItem->setAmountIncludingTax($order->getShipping() / 100);
            $shippingItem->setType(LineItemType::SHIPPING);

            $orderLines[] = $shippingItem;

            $lineItemId++;
        }

        $request->getTransaction()->setLineItems($orderLines);

        if ($order->getShippingAddress()) {
            $address = new AddressCreate();

            $address->setCity($order->getShippingAddress()->getCity());
            $address->setCountry($order->getShippingAddress()->getCountry()->getIsoCode());
            $address->setEmailAddress($order->getCustomer()->getEmail());
            $address->setFamilyName($order->getShippingAddress()->getLastname());
            $address->setGivenName($order->getShippingAddress()->getFirstname());
            $address->setOrganizationName($order->getShippingAddress()->getCompany());
            $address->setPhoneNumber($order->getShippingAddress()->getPhoneNumber());
            $address->setSalutation($order->getShippingAddress()->getSalutation());
            $address->setStreet($order->getShippingAddress()->getStreet() . ' ' . $order->getShippingAddress()->getNumber());

            $request->getTransaction()->setShippingAddress($address);
        }

        if ($order->getInvoiceAddress()) {
            $address = new AddressCreate();

            $address->setCity($order->getInvoiceAddress()->getCity());
            $address->setCountry($order->getInvoiceAddress()->getCountry()->getIsoCode());
            $address->setEmailAddress($order->getCustomer()->getEmail());
            $address->setFamilyName($order->getInvoiceAddress()->getLastname());
            $address->setGivenName($order->getInvoiceAddress()->getFirstname());
            $address->setOrganizationName($order->getInvoiceAddress()->getCompany());
            $address->setPhoneNumber($order->getInvoiceAddress()->getPhoneNumber());
            $address->setSalutation($order->getInvoiceAddress()->getSalutation());
            $address->setStreet($order->getInvoiceAddress()->getStreet() . ' ' . $order->getInvoiceAddress()->getNumber());

            $request->getTransaction()->setBillingAddress($address);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof PrepareTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
