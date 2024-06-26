<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Observer;

use Magento\Framework\Event\ObserverInterface;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    public function __construct(\Magento\Customer\Api\GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * Check the current customer group to see if any payment methods
     * have been disabled. We should add some caching in here.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if(!$quote){
            return;
        }
        $customerGroupId = $quote->getCustomerGroupId();
        $customerGroup = $this->groupRepository->getById($customerGroupId);
        $customerGroupDisabledPaymentMethods = $customerGroup->getExtensionAttributes()
            ->getDisallowedPaymentOptions()
            ->getDisallowedPaymentOptions();

        if(in_array(
            $observer->getEvent()->getMethodInstance()->getCode(),
            $customerGroupDisabledPaymentMethods)
        ){
            $paymentMethod = $observer->getEvent()->getResult();
            $paymentMethod->setData('is_available', false);
        }
    }
}
