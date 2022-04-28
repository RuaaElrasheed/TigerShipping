<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ruaa Elrasheed
 * Date: 10/09/2019
 * Time: 09:00
 */
namespace TigerTec\ShipIntegration\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $table = $connection
            ->newTable($setup->getTable('sybertec_sybership_tracking'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'shipping_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Shipping Id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Order Id'
            )
            ->addColumn(
                'tracking_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Tracking Id'
            )
            ->addColumn(
                'shipping_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Shipping Status'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Status'
            )
            ->addColumn(
                'items',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2255,
                [],
                'Items'
            )
            ->addColumn(
                'shipment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2255,
                [],
                'Shipment'
            )
            ->addColumn(
                'vendor',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2255,
                [],
                'Vendor'
            )
            ->addColumn(
                'shipping_method_name', 
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                0,
                [],
                'shipping Method Name'
            )
            ->addColumn(
                'shipping_cost', 
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                0,
                [],
                'shipping Cost'
            )
            ->addColumn(
                'amir_shipping_cost',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                0,
                [],
                '3amir Shipping Cost'
            )
            ->setComment('Delivery Tracking Table');
        $connection->createTable($table);

        $estimationTable = $connection
            ->newTable($setup->getTable('sybertec_sari_order_estimationIds'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Order Id'
            )
            ->addColumn(
                'vendor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Vendor ID'
            )
            ->addColumn(
                'estimation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Estimation Id'
            )
            ->addColumn(
                'coupon_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Coupon Id'
            )
            ->addColumn(
                'status_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Status ID'
            )
            ->addColumn(
                'estimate_fare',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                0,
                [],
                'Estimate Fare'
            )
            ->addColumn(
                'shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                0,
                [],
                'Shipment Id'
            )
            ->addColumn(
                'discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                0,
                [],
                'Discount Amount'
            )
            ->setComment('Syber Delivery Estimations Table');
        $connection->createTable($estimationTable);
        $installer->endSetup();
    }
}