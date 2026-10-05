<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $logs = db_prefix() . 'mrp_bom_production_inventory_logs';

        if ($CI->db->table_exists($logs) && !$CI->db->field_exists('movement_type', $logs)) {
            $CI->db->query('ALTER TABLE `' . $logs . '`
              ADD COLUMN `movement_type` VARCHAR(30) NOT NULL DEFAULT \'receive\' AFTER `qty_lost`,
              ADD INDEX `idx_movement_type` (`movement_type`)
            ;');
        }
    }
}
