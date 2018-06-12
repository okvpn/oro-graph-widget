<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OkvpnAppBundleMigration implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('okvpn_database_username', Type::STRING, ['length' => 100, 'notnull' => false]);
        $table->addColumn('okvpn_database_password', Type::STRING, ['length' => 1024, 'notnull' => false]);
        $table->addColumn('okvpn_database_host', Type::STRING, ['length' => 100, 'notnull' => false]);
        $table->addColumn('okvpn_database_port', Type::INTEGER, ['notnull' => false]);
        $table->addColumn('okvpn_database_driver', Type::STRING, ['length' => 64, 'notnull' => false]);
        $table->addColumn('okvpn_database_name', Type::STRING, ['length' => 64, 'notnull' => false]);
    }
}
