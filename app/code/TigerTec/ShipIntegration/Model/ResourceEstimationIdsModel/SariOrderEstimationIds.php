<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 07/12/2020
 * Time: 09:28
 */
namespace TigerTec\ShipIntegration\Model\ResourceEstimationIdsModel;

class SariOrderEstimationIds extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            'sybertec_sari_order_estimationIds', 'entity_id'
        );
        //TODO: Implement _construct() method.
    }
}