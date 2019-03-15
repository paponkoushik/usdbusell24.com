<?php


use Phinx\Migration\AbstractMigration;

/**
 * Class ImportantTables
 */
class ImportantTables extends AbstractMigration
{
    public function change()
    {
        $this->table('payment_providers')
            ->addColumn("uuid", "uuid")
            ->addColumn("name", "string", ['null' => false, 'limit' => 80])
            ->addColumn("slug", "string", ['null' => false, 'limit' => 80])
            ->addColumn("referer_field_name", "string", ['null' => false, 'limit' => 120])
            ->addColumn("icon_url", "text", ['null' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn("description", "text", ['null' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn("exchanged_request", "string", ['null' => false, 'limit' => 80])
            ->addColumn("buy_rate", 'decimal', ['null' => false, 'default' => 0.00, 'precision' => 10, 'scale' => 2])
            ->addColumn("sell_rate", 'decimal', ['null' => false, 'default' => 0.00, 'precision' => 10, 'scale' => 2])
            ->addColumn("total_reserves", 'integer', ['null' => false, 'limit' => 20])
            ->addColumn("status", "integer", ['null' => false, 'default' => 1, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex("uuid", ['unique' => true])
            ->create();

        $this->table('page_content')
            ->addColumn('uuid', 'uuid')
            ->addColumn('content', 'text', ['null' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn('slug', 'text', ['null' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex("uuid", ['unique' => true])
            ->create();

        $this->table('site_menu')
            ->addColumn('uuid', 'uuid')
            ->addColumn('name', 'string', ['null' => false, 'limit' => 80])
            ->addColumn('url', 'text', ['null' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn('order', 'string', ['null' => false, 'limit' => 10])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex("uuid", ['unique' => true])
            ->create();

        $this->table('settings')
            ->addColumn('uuid', 'uuid')
            ->addColumn('logo_url', 'text', ['null' => false, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
            ->addColumn("contact_email_address", "string", ['null' => false, 'limit' => 120])
            ->addColumn("contact_email_address_two", "string", ['null' => true, 'limit' => 120])
            ->addColumn("contact_phone", "string", ['null' => false, 'limit' => 120])
            ->addColumn("contact_phone_two", "string", ['null' => true, 'limit' => 120])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex("uuid", ['unique' => true])
            ->create();
    }
}
