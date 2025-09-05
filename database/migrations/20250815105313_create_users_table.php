<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUsersTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // Crear la tabla con engine InnoDB
        $table = $this->table('users', ['engine' => 'InnoDB']);
        $table->addColumn('pid', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '父ID'])
              ->addColumn('username', 'string', ['limit' => 50, 'default' => '', 'comment' => '用户名，登陆使用'])
              ->addColumn('password', 'string', ['limit' => 255, 'comment' => '用户密码'])
              ->addColumn('nickname', 'string', ['limit' => 50, 'default' => '', 'comment' => '昵称'])
              ->addColumn('lastip', 'string', ['limit' => 45, 'default' => '', 'comment' => '最后登录IP'])
              ->addColumn('loginnum', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '登录次数'])
              ->addColumn('email', 'string', ['limit' => 100, 'default' => '', 'comment' => '邮箱'])
              ->addColumn('mobile', 'string', ['limit' => 20, 'default' => '', 'comment' => '手机号'])
              ->addColumn('islock', 'boolean', ['limit' => 1, 'default' => 0, 'comment' => '锁定状态'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['username'], ['unique' => true])
              ->create();
    }
}
