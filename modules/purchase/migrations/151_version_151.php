<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_151 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->field_exists('vendor_machine', db_prefix() . 'pur_vendor')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'pur_vendor`
                ADD COLUMN `vendor_machine` INT(11) NOT NULL DEFAULT 0
            ;');
        }
    }
}
