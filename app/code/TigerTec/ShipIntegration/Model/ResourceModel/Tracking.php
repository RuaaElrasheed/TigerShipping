<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 10/09/2019
 * Time: 09:13
 */
namespace TigerTec\ShipIntegration\Model\ResourceModel;

class Tracking extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            'sybertec_sybership_tracking', 'entity_id'
        );
        // TODO: Implement _construct() method.
    }
}