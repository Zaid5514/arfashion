<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $assignments_table = db_prefix() . 'mrp_bom_production_inventory';
        $logs_table = db_prefix() . 'mrp_bom_production_receipt_print_logs';

        if ($CI->db->table_exists($assignments_table)
            && !$CI->db->field_exists('receipt_print_count', $assignments_table)) {
            $CI->db->query('ALTER TABLE `' . $assignments_table . '`
              ADD COLUMN `receipt_print_count` INT(11) NOT NULL DEFAULT 0
            ;');
        }

        if (!$CI->db->table_exists($logs_table)) {
            $CI->db->query('CREATE TABLE `' . $logs_table . '` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `bom_production_inventory_id` INT(11) NOT NULL,
              `staff_id` INT(11) NOT NULL,
              `printed_at` DATETIME NOT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_bom_production_inventory_id` (`bom_production_inventory_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }
}
