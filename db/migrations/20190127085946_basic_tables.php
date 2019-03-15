<?php


use Phinx\Migration\AbstractMigration;

/**
 * Class BasicTables
 */
class BasicTables extends AbstractMigration
{
    public function change()
    {
        $this->table('users')
            ->addColumn("uuid", "uuid")
            ->addColumn("first_name", "string", ['null' => false, 'limit' => 80])
            ->addColumn("last_name", "string", ['null' => false, 'limit' => 80])
            ->addColumn("email_address", "string", ['null' => false, 'limit' => 128])
            ->addColumn("password", "string", ['null' => false, 'limit' => 80])
            ->addColumn("role", "string", ['null' => false, 'default' => 'general-user', 'limit' => 40])
            ->addColumn("email_verified", "integer", ['null' => false, 'default' => 0, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY])
            ->addColumn("profile_pic", "string", ['null' => true, 'default' => null, 'limit' => 120])
            ->addColumn("pwd_reset_token", "string", ['null' => true, 'default' => null, 'limit' => 120])
            ->addColumn("status", "integer", ['null' => false, 'default' => 1, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY])
            ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex("uuid", ['unique' => true])
            ->addIndex("email_address", ['unique' => true])
            ->addIndex("pwd_reset_token", ['unique' => true])
            ->create();
    }
}
