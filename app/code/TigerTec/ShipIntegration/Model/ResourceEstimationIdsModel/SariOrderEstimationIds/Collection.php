<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 07/12/2020
 * Time: 09:29
 */
namespace TigerTec\ShipIntegration\Model\ResourceEstimationIdsModel\SariOrderEstimationIds;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'TigerTec\ShipIntegration\Model\SariOrderEstimationIds',
            'TigerTec\ShipIntegration\Model\ResourceEstimationIdsModel\SariOrderEstimationIds'
        );
        //parent::_construct(); // TODO: Change the autogenerated stub
    }
}