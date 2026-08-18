<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'mrp_bom_production_inventory_logs';

        // One purchase invoice per receive batch
        if ($CI->db->table_exists($table) && !$CI->db->field_exists('pur_invoice_id', $table)) {
            $CI->db->query('ALTER TABLE `' . $table . '`
              ADD COLUMN `pur_invoice_id` INT(11) NULL DEFAULT NULL,
              ADD INDEX `idx_pur_invoice_id` (`pur_invoice_id`)
            ;');
        }

        // Backfill: lock existing receive logs that already have a production invoice (custom field 3)
        if ($CI->db->table_exists($table) && $CI->db->field_exists('pur_invoice_id', $table)) {
            $CI->db->query('
                UPDATE `' . $table . '` l
                INNER JOIN (
                    SELECT `value` AS production_id, MIN(`relid`) AS invoice_id
                    FROM `' . db_prefix() . 'customfieldsvalues`
                    WHERE `fieldid` = 3 AND `fieldto` = \'pur_invoice\'
                    GROUP BY `value`
                ) cf ON cf.production_id = l.bom_production_inventory_id
                SET l.pur_invoice_id = cf.invoice_id
                WHERE l.pur_invoice_id IS NULL
            ');
        }
    }
}
