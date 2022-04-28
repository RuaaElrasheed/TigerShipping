<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 10/09/2019
 * Time: 11:00
 */
namespace TigerTec\ShipIntegration\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class TigerShipping extends AbstractCarrier implements CarrierInterface
{
    protected $items_lead_time;
    /**
     * @var string
     */
    protected $code = 'sarishipping';
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;
    const FB3_VENDOR_ID = 20;
    protected $state;
    protected $sariLogger;
    protected $customerFactory;
    protected $addressFactory;
    protected $messageManager;
    protected $vProducts;
    protected $vendor;
    protected $helper;
    protected $estimateHelper;
    protected $metadataHelper;
    protected $session;
    protected $checkoutSessionFactory;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Ced\CsMarketplace\Model\Vproducts $vProducts,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \TigerTec\ShipIntegration\Helper\Data $helper,
        \TigerTec\ShipIntegration\Helper\bookingEstimationsData $estimateHelper,
        \TigerTec\ShipIntegration\Helper\bookingMetaData $metadataHelper,
        \Magento\Checkout\Model\Session $session,
        \Magento\Checkout\Model\SessionFactory $sessionFactory,
        \Magento\Framework\App\State $state,
        \TigerTec\ShipIntegration\Logger\Logger $sariLogger,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->rateResultFactory= $rateResultFactory;
        $this->rateMethodFactory= $rateMethodFactory;
        $this->vProducts= $vProducts;
        $this->vendor= $vendor;
        $this->helper= $helper;
        $this->estimateHelper= $estimateHelper;
        $this->metadataHelper= $metadataHelper;
        $this->session= $session;
        $this->checkoutSessionFactory= $sessionFactory;
        $this->state= $state;
        $this->sariLogger= $sariLogger;
        $this->customerFactory= $customerFactory;
        $this->addressFactory= $addressFactory;
        $this->messageManager= $messageManager;
        parent::__construct($scopeConfig, $rateErrorFactory, $sariLogger, $data);
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        /** 
         * @var \Magento\Shipping\Model\Rate\Result $result 
         */
        $result = $this->rateResultFactory->create();
        if (empty($this->_express_cost)&&empty($this->_standard_cost)) {
            try
            {
                $this->_collectRateExternally();
            }
            catch(\Exception $e)
            {
                $error = $this->_rateErrorFactory->create(
                    [
                        'data' => [
                            'carrier' => $this->code,
                            'carrier_title' => $this->helper->getGatewayTitle(),
                            'error_message' => __('Sorry, this method is not available at the moment.'),
                        ],
                    ]
                );
                $result->append($error);
                return false;
            }
        }
        $result->append($this->_getStandardRate());
        return $result;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\SessionException
     */
    protected function _getStandardRate()
    {
        $customer2= $this->getQoute()->getCustomer();
        $vendorIdsPerOrder= array();
        $vendorEstimationIdsArrAll= array();
        $shipping_method_title= "";
        $syberFreeCouponID= "";
        $lastCost= 0;
        $shippingPricePerOder= 0;
        $maximumShippingPricePerOrder= $this->helper->getMaxiumumSariShippingPricePerOrder();
        $freeCouponCodeId= $this->helper->getSariFreeCouponCodeId();
        $perBookingCouponCodeId= $this->helper->getPerBookingCouponCodeId();
        $vendorIds = $this->estimateHelper->getVendorIdsArray();
        $vendorProductsArr = $this->estimateHelper->getVendorProductsArray();
        $sariDataArr = $this->estimateHelper->getData($vendorIds, $vendorProductsArr);
        $url = $this->estimateHelper->getUrl();
        
        foreach ($sariDataArr as $sariData) {   
            $bookingEstimationsResponse = $this->helper->callAPI($url, $sariData['data']);
            $bookingEstimationsCost = 0;
            $vendorID = $sariData['vendor'];
            foreach ($bookingEstimationsResponse["estimationDetails"] as $estDet) {
                if ($estDet["itemKey"] == "base_fare") {
                    $bookingEstimationsCost += $estDet["itemRate"];
                }
            }
            $vendorEstimationIdsArr = array("vendorId" => $sariData['vendor'], "estimationId" => $bookingEstimationsResponse['estimationId'], "goodsTypesIdList" => $sariData['data']['goodsTypesIdList'], "estimateFare" => $bookingEstimationsCost);
            if ($this->getProductData($vendorID) == true) {
                if ($shippingPricePerOder == 0) {
                    $shippingPricePerOder += 0;
                } else {
                    $shippingPricePerOder += 0;
                }
            } elseif ($this->getProductData($vendorID) == false) {
                if ($this->isInCorporate($customer2) && $this->isCorporateAddressSelected($customer2) && $vendorID == self::FB3_VENDOR_ID) {
                    $syberFreeCouponID= $this->helper->getSyberAddressCouponCodeId();
                    $syberFreeCouponAmount= $this->getCouponData($syberFreeCouponID);
                    $plusPerSariBooking= $bookingEstimationsCost - $syberFreeCouponAmount;
                    $shippingPricePerOder += $plusPerSariBooking;
                } else {
                    $freeCouponModelAmount= $this->getCouponData($perBookingCouponCodeId);
                    $plusPerSariBooking= $bookingEstimationsCost - $freeCouponModelAmount;
                    if ($shippingPricePerOder == 0) {
                        $shippingPricePerOder += $bookingEstimationsCost;
                    } else {
                        $shippingPricePerOder += $plusPerSariBooking;
                    }
                }
            }
            array_push($vendorIdsPerOrder, $vendorID);
            array_push($vendorEstimationIdsArrAll, $vendorEstimationIdsArr);
        }
        if ($shippingPricePerOder <= $maximumShippingPricePerOrder) {
            $lastCost += $shippingPricePerOder;
        } else {
            $lastCost += $maximumShippingPricePerOrder;
        }
        try
        {
            $min = min($this->items_lead_time);
            $max = max($this->items_lead_time);
            if ($min == $max) {
                if ($min == 0) {
                    $shipping_method_title .= __('Standard 0-3 days');
                } elseif ($min == 1) {
                    $shipping_method_title .= __('Next Day Delivery');
                } else {
                    $shipping_method_title .= __('%1 Days Delivery', $min);
                }
            } else {
                $shipping_method_title .= __('From  %1 Days Delivery', $min . '-' . $max);
            }
        }
        catch(\Exception $e)
        {
            $this->sariLogger->error("no items lead time" . $e->getTraceAsString());
            $shipping_method_title = __('Standard 0-3 days');
        }
        $this->session->start();
        $this->session->setVendorEstimationIdsArr($vendorEstimationIdsArrAll);
        $this->session->start();
        $this->session->getVendorEstimationIdsArr();
        $this->session->start();
        $this->session->setSyberStaffCoupon($syberFreeCouponID);
        $this->session->start();
        $this->session->getSyberStaffCoupon();
        $this->session->start();
        $this->session->setPerBookingCouponId($perBookingCouponCodeId);
        $this->session->start();
        $this->session->getPerBookingCouponId();
        $this->session->start();
        $this->session->setDiscountPerBooking($plusPerSariBooking);
        $this->session->start();
        $this->session->getDiscountPerBooking();
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->code);
        $method->setCarrierTitle($this->helper->getGatewayTitle());
        $method->setMethod('standand');
        $method->setMethodTitle($shipping_method_title);
        $method->setCost($lastCost);
        $method->setPrice($lastCost);
        return $method;
    }
    
    protected function getProductData($vendorID)
    {
        $quote = $this->getQoute();
        $cartItems = $quote->getAllItems();
        $productTypesArr = array();
        foreach ($cartItems as $item) {
            $productId = $item->getProductId();
            $productOfVendor = $this->vProducts->load($productId, 'product_id');
            $vendor = $this->vendor->load($productOfVendor->getVendorId());
            $vendorId=$vendor->getId();
            if ($vendorID == $vendorId) {
                $productType=$item->getProductType(); 
                array_push($productTypesArr, $productType);
            }
            if (in_array('simple', $productTypesArr) || in_array('configurable', $productTypesArr) || in_array('bundle', $productTypesArr) || in_array('grouped', $productTypesArr)) {
                $hasVirtual = false;
            } else {
                $hasVirtual = true;
            }
        }
        return $hasVirtual;
    }

    protected function _collectRateExternally()
    {
        $quote= $this->getQoute();
        $cartItems= $quote->getAllItems();
        $this->items_lead_time= array();
        foreach ($cartItems as $item) {
            $productId= $item->getProductId();
            $productOfVendor= $this->vProducts->load($productId, 'product_id');
            $vendor= $this->vendor->load($productOfVendor->getVendor_id());
            $product= $this->helper->getProductbyId($productId);
            $vendorId= $productOfVendor->getVendor_id();
            $leadTime= $product->getLeadTime();
            if (!$leadTime) {
                $leadTime= 0;
            }
            $this->items_lead_time[]= $leadTime;
        }
        return ;
    }

    protected function getCouponData($couponID)
    {
        $metaUrl= $this->metadataHelper->getUrl();
        $bookingMetaDataResponse= $this->helper->getApi($metaUrl);
        $promocodeListArr= $bookingMetaDataResponse['promocodeList'];
        foreach ($promocodeListArr as $promocodeList) {
            if ($promocodeList['id'] == $couponID) {
                $couponDiscountAmount= $promocodeList['discount'];
            }
        }
        return $couponDiscountAmount;
    }

    protected function getQoute()
    {
        if ($this->getArea() == 'adminhtml') {
            $qoute = $this->backecndSession->getQuote();
        } else {
            $qoute = $this->session->getQuote();
        }
        return $qoute;
    }

    protected function getArea()
    {
        try
        {
            return $this->state->getAreaCode();
        }
        catch(LocalizedException $e)
        {
            $this->sariLogger->info("exception " . $e);
        }
        return -1;
    }

    private function isFreeShippingDay($customer)
    {
        $today= date('l');
        $customerCollection= $this->customerFactory->create()->getCollection()->addFieldToFilter('entity_id', $customer->getId())->addAttributeToSelect('*');
        foreach ($customerCollection as $coll) {
            if (!is_null($coll->getFreeShippingDays())) {
                if (in_array($today, json_decode($coll->getFreeShippingDays()))) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $this->sariLogger->info('free customer ====== FALSE');
            }
        }
    }

    private function isCorporateAddressSelected($customer)
    {
        $quote = $this->checkoutSessionFactory->create()->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $customerCorporateAddress = $this->getCorporateAddress($customer->getId());
        if (!$customerCorporateAddress) {
            //return false if the customer doesnt have corporate address
            return false;
        }
        //check if both adresses having same data
        if ($shippingAddress->getCity() == $customerCorporateAddress->getCity() && $shippingAddress->getStreetFull() == $customerCorporateAddress->getStreetFull()) {
            return true;
        } else {
            return false;
        }
    }

    private function getCorporateAddress($customerId)
    {
        $customerModel = $this->customerFactory->create()->load($customerId);
        $customerData = $customerModel->getDataModel();
        $customerGroupAddressId = $customerData->getCustomAttribute('group_address_id');
        if (!empty($customerGroupAddressId)) {
            $corporateAddressModel = $this->addressFactory->create()->load($customerGroupAddressId->getValue());
            return $corporateAddressModel;
        } else {
            return false;
        }
    }

    private function isInCorporate($customer)
    {
        if ($customer->getGroupId()== 4) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['sarishipping' => $this->helper->getGatewayTitle()];
    }
}
