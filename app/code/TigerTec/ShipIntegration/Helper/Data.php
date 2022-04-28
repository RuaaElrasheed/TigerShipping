<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 10/09/2019
 * Time: 10:05
 */
namespace TigerTec\ShipIntegration\Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    private $_orderRepository;
    private $_searchCriteriaBuilder;
    private $_productloader;
    private $_shipment;

    public function __construct(
        Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Sales\Model\Order\Shipment $shipment
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_productloader=$_productloader;
        $this->_shipment = $shipment;
        parent::__construct($context);
    }
    
    public function getGatewayUrl()
    {
        $url = $this->scopeConfig->getValue('carriers/sarishipping/urlPro', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $url;
    }
    
    public function getGatewayTitle()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getCategoryId()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/categoryId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getServiceId()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/serviceId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getOrderByIncrementId($incrementId)
    {
        $searchCriteria= $this->_searchCriteriaBuilder->addFilter('increment_id', $incrementId, 'eq')->create();
        $orderList= $this->_orderRepository->getList($searchCriteria)->getItems();
        return reset($orderList);
    }

    public function getShipmentByIncrementId($incrementId)
    {
        return $this->_shipment->loadByIncrementId($incrementId);
    }
    
    public function getMaxiumumSariShippingPricePerOrder()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/costPerOrder', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductbyId($id)
    {
        return $this->_productloader->create()->load($id);
    }

    public function getSariFreeCouponCodeId()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/SariFreeCouponCodeId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPerBookingCouponCodeId()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/PerBookingCouponCodeId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSyberAddressCouponCodeId()
    {
        return $this->scopeConfig->getValue('carriers/sarishipping/SyberAddressCouponCodeId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function callAPI($apiURL, $requestParamList)
    {
        $headers= array(
            'Content-Type: application/json',
            'Accept-Language: en-US',
            'x-secret-token-key: abcdefghijklmnopq',
            'business-code: UAAABB',
        );
        $ch= curl_init();
        $JsonData= json_encode($requestParamList);
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $JsonData);
        $result= curl_exec($ch);
        if (curl_error($ch)) {
            throw new LocalizedException(new Phrase('External System Error.'));
        }
        curl_close($ch);
        $responseParamList= json_decode($result, true);
        return $responseParamList;
    }
    
    public function getApi($apiURL)
    {
        $headers = array(
            'Content-Type: application/json',
            'Accept-Language: en-US',
            'x-secret-token-key: abcdefghijklmnopq',
            'business-code: UAAABB',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiURL);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            throw new LocalizedException(new Phrase('External System Error.'));
        }
        curl_close($ch);
        $responseParamList = json_decode($result, true);
        return $responseParamList;
    }
}
