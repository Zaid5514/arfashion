<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * List AR Reports permissions
 * @return array
 */
function list_nex_reports_permission()
{
    $nex_reports_permissions = [];
    $nex_reports_permissions[] = 'ar_reports';
    
    return $nex_reports_permissions;
}

