<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 11/09/2019
 * Time: 13:00
 */
namespace TigerTec\ShipIntegration\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterPlaceOrder implements ObserverInterface
{
    const FB3_VENDOR_ID = 20;
    protected $session;
    protected $initShip;
    protected $model;
    protected $sariLogger;

    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \TigerTec\ShipIntegration\Observer\InitShip $initShip,
        \TigerTec\ShipIntegration\Model\SariOrderEstimationIds $model,
        \TigerTec\ShipIntegration\Logger\Logger $sariLogger
    ) {
        $this->session= $session;
        $this->initShip=  $initShip;
        $this->model= $model;
        $this->sariLogger= $sariLogger;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order= $observer->getEvent()->getOrder();
        $orderId= $order->getIncrementId();
        $payment= $order->getPayment();
        $method= $payment->getMethodInstance();
        $methodCode= $method->getCode();
        $this->session->start();
        $vendorEstimationArr=$this->session->getVendorEstimationIdsArr();
        $this->session->start();
        $SyberStaffCouponId=$this->session->getSyberStaffCoupon();
        $this->session->start();
        $PerBookingCouponId=$this->session->getPerBookingCouponId();
        $this->session->start();
        $discountAmountPerBooking=$this->session->getDiscountPerBooking();
        foreach ($vendorEstimationArr as $vendorEstimationId) {
            $this->model->setOrderId($orderId);
            $this->model->setVendorId($vendorEstimationId['vendorId']);
            $this->model->setEstimationId($vendorEstimationId['estimationId']);
            if ($SyberStaffCouponId != "" && $vendorEstimationId['vendorId'] == self::FB3_VENDOR_ID) {
                $model->setCouponId($SyberStaffCouponId);  
            } else {
                $model->setCouponId($PerBookingCouponId);
            }
            $model->setStatusId(0);
            $model->setEstimateFare($vendorEstimationId['estimateFare']);
            $model->setDiscountAmount($discountAmountPerBooking);
            $model->save();
        }
        if ($methodCode != "syberpay") {
            $this->initShip->ship($order);
        } else {
            $this->sariLogger->info('online payment method');
        }
    }
}