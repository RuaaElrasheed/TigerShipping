<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 07/12/2020
 * Time: 10:15
 */
namespace TigerTec\ShipIntegration\Plugin\Block\Widget\Button;
use Magento\Shipping\Block\Adminhtml\View;

class ToolbarPlugin extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $url;
    protected $tracking;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \TigerTec\ShipIntegration\Model\Tracking $tracking
    ) {
        $this->url = $url;
        $this->tracking = $tracking;
    }

    public function beforePushButtons (
        \Magento\Backend\Block\Widget\Button\Toolbar $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $nameInLayout= $context->getNameInLayout();
        if ('sales_shipment_view' == $nameInLayout) {
            $shipment= $context->getShipment();
            $shipmentStatus= $shipment->getShipmentStatus();
            $shipmentIncrementId= $shipment->getIncrementId();
            $order= $shipment->getOrder();
            $orderIncrementId= $order->getIncrementId();
            $trackingCollection= $this->getTrackingId($shipmentIncrementId);

            if ($shipmentStatus == 1 && count($trackingCollection) == 0) {
                $url = $this->url->getUrl(
                    'sariIntegration/shipment/startshipment', 
                    [
                        'shipment_increment_id' => $shipmentIncrementId, 
                        'order_increment_id' => $orderIncrementId
                    ]
                );
                $stopUrl = $this->url->getUrl(
                    'sariIntegration/shipment/stopshipment', 
                    [
                        'shipment_increment_id' => $shipmentIncrementId, 
                        'order_increment_id' => $orderIncrementId
                    ]
                );
                $buttonList->add(
                    'start',
                    [
                        'label' => __('Start'),
                        'on_click' => "deleteConfirm('".__('Are you sure you want to start this shipment?')."','".$url."')",
                        'class' => 'primary',
                        'id' => 'start'
                    ]
                );
                $buttonList->add(
                    'stop',
                    [
                        'label' => __('Stop'),
                        'on_click' => "deleteConfirm('".__('Are you sure this shipping has an issue?')."','".$stopUrl."')",
                        'class' => 'primary',
                        'id' => 'stop'
                    ]
                );
            }
        }
        return [$context, $buttonList];
    }
    
    private function getTrackingId($incrementId)
    {
        $trackingColl = $this->tracking->getCollection()->addFieldToSelect('*')->addFieldToFilter('shipment', $incrementId);
        return $trackingColl;
    }
}