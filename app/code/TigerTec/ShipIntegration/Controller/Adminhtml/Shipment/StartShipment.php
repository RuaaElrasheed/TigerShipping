<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 07/12/2020
 * Time: 10:29
 */
namespace TigerTec\ShipIntegration\Controller\Adminhtml\Shipment;

class StartShipment extends \Magento\Backend\App\Action
{
    protected $resultFactory;
    protected $callSariShip;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \TigerTec\ShipIntegration\Observer\InitShip $callSariShip
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->callSariShip = $callSariShip;
    }

    public function execute()
    {
        $shipmentIncrementId = $this->getRequest()->getParam('shipment_increment_id');
        $orderIncrementId = $this->getRequest()->getParam('order_increment_id');
        $this->callSariShip->callSari($orderIncrementId, $shipmentIncrementId);
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }
}
