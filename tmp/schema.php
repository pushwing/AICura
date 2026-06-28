<?php return \CodeIgniter\PHPStan\Database\Schema\Schema::__set_state(array(
   'hash' => 'cdde37dc1e7b5f202dd54b9a9d090cb6c398885df8db58a5885cb1ee650c353f',
   'tables' => 
  array (
    'contracts' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'contracts',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'title',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'pay_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'pay_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_type2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type2',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'main_contract' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'main_contract',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'use_status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'use_status',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_deleted' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_deleted',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'term_list_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'term_list_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'channel_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'channel_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'manage_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'manage_user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_type',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '1',
        )),
        'hospital_charge_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_charge_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_charge_phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_charge_phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_charge_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_charge_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_charge_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_charge_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_business_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_business_no',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_charge_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_charge_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'contract_orders' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'contract_orders',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_type2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type2',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_price' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_price',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'contract_status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'deposit_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deposit_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'parent_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'parent_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'manage_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'manage_user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_charge_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_charge_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_charge_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_charge_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_business_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_business_no',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_issue_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_issue_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_fee_rate' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_fee_rate',
           'type' => 'DECIMAL',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'memo',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'title',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_agree_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_agree_date',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agree' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agree',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'deposit_check_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deposit_check_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'main_contract' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'main_contract',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'purchase_owner_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'purchase_owner_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_network' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_network',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'hospital_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_type',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '1',
        )),
        'hospital_charge_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_charge_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_charge_phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_charge_phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_charge_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_charge_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_charge_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_charge_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_charge_phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_charge_phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_company_charge_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_company_charge_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tax_issue_request_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tax_issue_request_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ads_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ads_count',
           'type' => 'SMALLINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ads_count_bonus' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ads_count_bonus',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'pay_method' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'pay_method',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'contract_order_connects' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'contract_order_connects',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'deposits' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'deposits',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '2',
        )),
        'is_minus' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_minus',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'price' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'price',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'users_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'users_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'note' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'note',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'users' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'users',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'email',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'password' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'password',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'username' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'username',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_type',
           'type' => 'SMALLINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'where_from' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'where_from',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'provider' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'provider',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '9',
        )),
        'is_agency_account' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_agency_account',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'picture' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'picture',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'age' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'age',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'sex' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sex',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'job' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'job',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'health_point' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'health_point',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'note' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'note',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_dormant' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_dormant',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'dormant_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'dormant_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'last_login_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'last_login_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'last_logout_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'last_logout_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'last_activity_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'last_activity_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'oauth_token' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'oauth_token',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'uid' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'uid',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'group_auth_code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'group_auth_code',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_active' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_active',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'deleted_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deleted_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'hospitals' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'hospitals',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'address' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'address',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'active\'',
        )),
        'is_deleted' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_deleted',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'campaign_histories' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'campaign_histories',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'action' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'action',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status_from' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status_from',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status_to' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status_to',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'memo',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'admin_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'admin_user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'campaign_packages' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'campaign_packages',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'title',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'banner_view_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'banner_view_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'view_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'view_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'start_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'start_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'end_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'end_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'banner' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'banner',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'detail_info' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'detail_info',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'active\'',
        )),
        'is_deleted' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_deleted',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'campaign_package_map' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'campaign_package_map',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_package_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_package_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'sort_order' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sort_order',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'campaign_temps' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'campaign_temps',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_title',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_start_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_start_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_end_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_end_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'cost_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'cost_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'general_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'general_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'discount_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'discount_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'text_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'text_cost',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'db_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'db_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'category' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'exposure' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'exposure',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'region' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'region',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'keyword' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'keyword',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'deliberation_code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deliberation_code',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'channel' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'channel',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        't1_image_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 't1_image_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        't2_image_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 't2_image_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'd_image_json' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'd_image_json',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'admin_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'admin_user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_deleted' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_deleted',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'payments' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'payments',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'amount' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'amount',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'result_code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'result_code',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'trans_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'trans_no',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'auth_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'auth_date',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'auth_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'auth_no',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'fn_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'fn_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'vbank_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'vbank_no',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'vbank_expire' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'vbank_expire',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'pending\'',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'advertisers' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'advertisers',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contact_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contact_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contact_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contact_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contact_phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contact_phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'business_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'business_no',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_network' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_network',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'network_parent_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'network_parent_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'owner_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'owner_user_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_agreed_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_agreed_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'campaign_review_requests' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'campaign_review_requests',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'request_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'request_type',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'update\'',
        )),
        'ad_title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_title',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_start_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_start_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_end_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_end_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'cost_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'cost_type',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'general_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'general_cost',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'discount_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'discount_cost',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'text_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'text_cost',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'db_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'db_cost',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'category' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'exposure' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'exposure',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'region' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'region',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'keyword' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'keyword',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'deliberation_code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deliberation_code',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'channel' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'channel',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        't1_image_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 't1_image_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        't2_image_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 't2_image_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'd_image_json' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'd_image_json',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'review_status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'review_status',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'pending\'',
        )),
        'review_memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'review_memo',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'reviewed_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reviewed_by',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'reviewed_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reviewed_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_by',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_detail_info' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_detail_info',
           'type' => 'MEDIUMTEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'ad_mains' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'ad_mains',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_title',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'ad_main_maps' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'ad_main_maps',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'ad_main_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_main_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_main' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_main',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_inspect' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_inspect',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '2',
        )),
        'url' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'url',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'ad_recommend_maps' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'ad_recommend_maps',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'admin_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'admin_user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ads_order' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ads_order',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'call_requests' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'call_requests',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'device' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'device',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'confirm_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'confirm_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'phone',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'content' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'content',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'call_time' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'call_time',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'age' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'age',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'sex' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sex',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'privacy_agree' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'privacy_agree',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'supply_third_party_agree' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'supply_third_party_agree',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'event_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'event_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'funnel' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'funnel',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'region' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'region',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'finger_print' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'finger_print',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'parent_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'parent_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_save_phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_save_phone',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_charged' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_charged',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'reserved_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reserved_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'call_memos' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'call_memos',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'call_request_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'call_request_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'memo',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'refunds' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'refunds',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'payment_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'payment_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'trans_no' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'trans_no',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'term_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'term_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'amount' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'amount',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'result_code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'result_code',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '2',
        )),
        'result1' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'result1',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'result2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'result2',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'bookings' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'bookings',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'call_request_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'call_request_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'phone' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'phone',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'book_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'book_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'confirm_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'confirm_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'event_categories' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'event_categories',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'parent_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'parent_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'title',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'category_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'sort' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sort',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_visible' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_visible',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'image' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'image',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'coocha_tags' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'coocha_tags',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'coocha_category' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'coocha_category',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'mapping_categories' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'mapping_categories',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'v1' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'v1',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'v2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'v2',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
      ),
    )),
    'codes' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'codes',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'code',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'description' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'description',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_use' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_use',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'sort' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sort',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
      ),
    )),
    'boards' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'boards',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_email',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'board_pid' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_pid',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'target_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'target_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'subject' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'subject',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contents' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contents',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_notice' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_notice',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'is_member' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_member',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_secret' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_secret',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'files_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'files_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'hit' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hit',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'comment_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'comment_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'reply_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reply_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'like_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'like_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'complain_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'complain_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'help_yes_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'help_yes_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'help_no_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'help_no_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'info_request_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'info_request_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'category' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'category_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ip' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ip',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_list' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_list',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'delete_memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'delete_memo',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'delete_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'delete_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'password' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'password',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'reply_order' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reply_order',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'general_setting' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'general_setting',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'rate_sum' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate_sum',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'rate1' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate1',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'rate2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate2',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'rate3' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate3',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'survey_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey_type',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey1' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey1',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey2',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey3' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey3',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey4' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey4',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey5' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey5',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey6' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey6',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'call_request_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'call_request_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'not_event_user' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'not_event_user',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '2',
        )),
        'device' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'device',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'funnel' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'funnel',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'board_comments' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'board_comments',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'contents' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contents',
           'type' => 'TEXT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_secret' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_secret',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'is_list' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_list',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'recommend_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'recommend_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'files_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'files_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'ip' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ip',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'password' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'password',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'comment_order' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'comment_order',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'board_files' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'board_files',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'original_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'original_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'file_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'file_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'file_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'file_type',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'order_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'order_by',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'board_estimations' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'board_estimations',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'board_ranks' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'board_ranks',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'order_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'order_date',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'order_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'order_by',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'board_summaries' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'board_summaries',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'target_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'target_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'rate_sum' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate_sum',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'rate1' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate1',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'rate2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate2',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'rate3' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'rate3',
           'type' => 'FLOAT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'survey_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'survey11' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey11',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey12' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey12',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0|0\'',
        )),
        'survey13' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey13',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0\'',
        )),
        'survey14' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey14',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey15' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey15',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey21' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey21',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey22' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey22',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0|0\'',
        )),
        'survey23' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey23',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey31' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey31',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey32' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey32',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0|0\'',
        )),
        'survey33' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey33',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0\'',
        )),
        'survey34' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey34',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey35' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey35',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey41' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey41',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey42' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey42',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0\'',
        )),
        'survey43' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey43',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0|0\'',
        )),
        'survey44' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey44',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey45' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey45',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey46' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey46',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0\'',
        )),
        'survey51' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey51',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey52' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey52',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'survey53' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey53',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'survey54' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey54',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0|0|0\'',
        )),
        'survey55' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'survey55',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'0|0|0\'',
        )),
        'reg_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reg_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'mod_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'mod_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'board_tags' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'board_tags',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'tag_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'tag_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'advertiser_boards' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'advertiser_boards',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'board_pid' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_pid',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'title',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contents' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contents',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_notice' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_notice',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'is_secret' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_secret',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'files_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'files_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'hit' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hit',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'comment_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'comment_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'like_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'like_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'complain_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'complain_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'category' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'category_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ip' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ip',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_list' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_list',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'delete_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'delete_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'password' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'password',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'reply_order' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reply_order',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'general_setting' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'general_setting',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'advertiser_board_comments' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'advertiser_board_comments',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'contents' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contents',
           'type' => 'TEXT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_secret' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_secret',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'is_list' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_list',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'recommend_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'recommend_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'files_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'files_count',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'ip' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ip',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'password' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'password',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'comment_order' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'comment_order',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'advertiser_board_files' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'advertiser_board_files',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'board_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'board_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'original_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'original_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'file_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'file_name',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'\'',
        )),
        'file_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'file_type',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'order_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'order_by',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'black_lists' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'black_lists',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'users_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'users_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'advertiser_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'advertiser_user_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'reg_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reg_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_delete' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_delete',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'memos' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'memos',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'memo_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'memo_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'target_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'target_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'target_id2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'target_id2',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'memo',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'customer_memo' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'customer_memo',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'user_actions' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'user_actions',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'sum_point' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sum_point',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'advertiser_owner_invites' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'advertiser_owner_invites',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'advertiser_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'advertiser_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'invitee_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'invitee_user_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'invitee_email' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'invitee_email',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'expires_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'expires_at',
           'type' => 'DATETIME',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'responded_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'responded_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'campaigns' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'campaigns',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'ad_title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_title',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'ad_start_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_start_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_end_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_end_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'cost_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'cost_type',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'general_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'general_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'discount_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'discount_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'text_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'text_cost',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'db_cost' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'db_cost',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'category' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'category',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'exposure' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'exposure',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'contract_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_order_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_order_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'region' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'region',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'keyword' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'keyword',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'deliberation_code' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deliberation_code',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '\'pending\'',
        )),
        'channel' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'channel',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'is_deleted' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_deleted',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '0',
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        't1_image_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 't1_image_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        't2_image_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 't2_image_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'd_image_json' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'd_image_json',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'agency_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'agency_user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'ad_date_extend' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_date_extend',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'where_image' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'where_image',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'model_image_count' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'model_image_count',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '0',
        )),
        'ad_detail_info' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'ad_detail_info',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'inspect_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'inspect_date',
           'type' => 'DATE',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'is_view_board' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'is_view_board',
           'type' => 'TINYINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '1',
        )),
        'custom_randing' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'custom_randing',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'option_ad_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'option_ad_id',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'custom1' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'custom1',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'custom2' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'custom2',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'custom3' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'custom3',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'cooperation' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'cooperation',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'sub_hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'sub_hospital_id',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'contract_name' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'contract_name',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'del_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'del_date',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'delete_user_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'delete_user_id',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'review_status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'review_status',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '\'pending\'',
        )),
      ),
    )),
    'refund_requests' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'refund_requests',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'call_request_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'call_request_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'hospital_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'hospital_id',
           'type' => 'INT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'requested_status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'requested_status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'reason' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reason',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'status' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'status',
           'type' => 'TINYINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => '1',
        )),
        'reject_reason' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'reject_reason',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'deposit_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'deposit_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'requested_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'requested_by',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'processed_by' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'processed_by',
           'type' => 'INT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'processed_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'processed_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'ai_reports' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'ai_reports',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'type',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'title' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'title',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'content' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'content',
           'type' => 'MEDIUMTEXT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'report_date' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'report_date',
           'type' => 'DATE',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'meta' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'meta',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'scope_type' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'scope_type',
           'type' => 'VARCHAR',
           'nullable' => true,
           'primaryKey' => false,
           'default' => '\'global\'',
        )),
        'scope_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'scope_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'settings' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'settings',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'setting_key' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'setting_key',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'setting_value' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'setting_value',
           'type' => 'TEXT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
    'creative_compliance_checks' => 
    \CodeIgniter\PHPStan\Database\Schema\Table::__set_state(array(
       'name' => 'creative_compliance_checks',
       'columns' => 
      array (
        'id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'id',
           'type' => 'INTEGER',
           'nullable' => true,
           'primaryKey' => true,
           'default' => NULL,
        )),
        'campaign_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'campaign_id',
           'type' => 'BIGINT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'review_request_id' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'review_request_id',
           'type' => 'BIGINT',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'risk_level' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'risk_level',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'flags' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'flags',
           'type' => 'JSON',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'checked_text' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'checked_text',
           'type' => 'MEDIUMTEXT',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'model' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'model',
           'type' => 'VARCHAR',
           'nullable' => false,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'created_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'created_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
        'updated_at' => 
        \CodeIgniter\PHPStan\Database\Schema\Column::__set_state(array(
           'name' => 'updated_at',
           'type' => 'DATETIME',
           'nullable' => true,
           'primaryKey' => false,
           'default' => NULL,
        )),
      ),
    )),
  ),
));
