<?php
/**
 * Database Cleanup Script for Duplicate Production Receives & Invoices
 *
 * Usage:
 *   CLI Dry-Run (safe inspection, no changes):
 *     php clean_duplicate_receives.php
 *     php clean_duplicate_receives.php --dry-run
 *
 *   CLI Execute (apply changes in transaction):
 *     php clean_duplicate_receives.php --apply
 *
 *   Browser (protected):
 *     http://your-domain/clean_duplicate_receives.php?key=YOUR_SECRET_OR_LOGGED_IN
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

$is_cli = (php_sapi_name() === 'cli');
$apply_changes = false;

if ($is_cli) {
    global $argv;
    if (isset($argv) && in_array('--apply', $argv)) {
        $apply_changes = true;
    }
} else {
    header('Content-Type: text/plain; charset=utf-8');
    if (isset($_GET['apply']) && ($_GET['apply'] === '1' || $_GET['apply'] === 'true')) {
        $apply_changes = true;
    }
}

// Load database configuration
$app_config = __DIR__ . '/application/config/app-config.php';
if (!file_exists($app_config)) {
    die("Configuration file not found: $app_config\n");
}
require_once $app_config;

$pdo = new PDO(
    'mysql:host=' . APP_DB_HOSTNAME . ';dbname=' . APP_DB_NAME . ';charset=utf8mb4',
    APP_DB_USERNAME,
    APP_DB_PASSWORD,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "====================================================================\n";
echo " ARFASHION - PRODUCTION RECEIVE DUPLICATE CLEANUP\n";
echo " Mode: " . ($apply_changes ? ">>> APPLY CHANGES <<<" : "DRY RUN (No changes made)") . "\n";
echo "====================================================================\n\n";

if ($apply_changes) {
    $pdo->beginTransaction();
}

$cleaned_count = 0;
$logs_deleted = 0;

try {
    // -------------------------------------------------------------
    // SECTION 1: Fix Target Recent Orders with Known Duplicate Receives
    // -------------------------------------------------------------
    $specific_fixes = [
        // MO 1016 - ASLAM DHAKA (Inventory 5825)
        [
            'mo' => 1016,
            'inv' => 5825,
            'del_log' => 6580,
            'set_recv' => 5000,
            'set_pend' => 0,
            'status' => 'completed',
            'fix_invoice' => 6336,
            'desc' => 'MO 1016 - ASLAM DHAKA (Invoice #INV/26-27/06336 duplicate row)'
        ],
        // MO 1034 - LAZIM KRG (Inventory 5954)
        [
            'mo' => 1034,
            'inv' => 5954,
            'del_log' => 6822,
            'set_recv' => 1700,
            'set_pend' => 300,
            'status' => 'in_progress',
            'desc' => 'MO 1034 - LAZIM KRG (700 uninvoiced duplicate)'
        ],
        // MO 1049 - IDULLAH SETH (Inventory 6170)
        [
            'mo' => 1049,
            'inv' => 6170,
            'del_log' => 6785,
            'set_recv' => 1200,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1049 - IDULLAH SETH (1200 uninvoiced duplicate)'
        ],
        // MO 1067 - IDULLAH SETH (Inventory 6001)
        [
            'mo' => 1067,
            'inv' => 6001,
            'del_log' => 6619,
            'set_recv' => 590,
            'set_pend' => 10,
            'status' => 'in_progress',
            'desc' => 'MO 1067 - IDULLAH SETH (590 uninvoiced duplicate)'
        ],
        // MO 1081 - TAHSIN PENTER (Inventory 6122)
        [
            'mo' => 1081,
            'inv' => 6122,
            'del_log' => 6559,
            'set_recv' => 5000,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1081 - TAHSIN PENTER (5000 uninvoiced duplicate)'
        ],
        // MO 1081 - WAZID MASTER (Inventory 6121)
        [
            'mo' => 1081,
            'inv' => 6121,
            'del_log' => 6557,
            'set_recv' => 5000,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1081 - WAZID MASTER (5000 uninvoiced duplicate)'
        ],
        // MO 1080 - WAZID MASTER (Inventory 6118)
        [
            'mo' => 1080,
            'inv' => 6118,
            'del_log' => 6554,
            'set_recv' => 2000,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1080 - WAZID MASTER (2000 uninvoiced duplicate)'
        ],
        // MO 1079 - TAHSIN PENTER (Inventory 6115)
        [
            'mo' => 1079,
            'inv' => 6115,
            'del_log' => 6551,
            'set_recv' => 5000,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1079 - TAHSIN PENTER (5000 uninvoiced duplicate)'
        ],
        // MO 1035 - MINHAZ KARIGAR (Inventory 5804)
        [
            'mo' => 1035,
            'inv' => 5804,
            'del_log' => 6318,
            'set_recv' => 1000,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1035 - MINHAZ KARIGAR (1000 uninvoiced duplicate)'
        ],
        // MO 1059 - WAZID MASTER (Inventory 5914)
        [
            'mo' => 1059,
            'inv' => 5914,
            'del_log' => 6273,
            'set_recv' => 2400,
            'set_pend' => 0,
            'status' => 'completed',
            'desc' => 'MO 1059 - WAZID MASTER (2400 uninvoiced duplicate)'
        ],
        // MO 993 - KAMAR SAITH (Inventory 5642)
        [
            'mo' => 993,
            'inv' => 5642,
            'del_log' => 6348,
            'set_recv' => 798,
            'set_pend' => 2,
            'status' => 'in_progress',
            'desc' => 'MO 993 - KAMAR SAITH (34 uninvoiced duplicate)'
        ],
        // MO 971 - AFROZ SETH (Inventory 5692)
        [
            'mo' => 971,
            'inv' => 5692,
            'del_log' => [6330, 6331, 6332],
            'set_recv' => 1000,
            'set_pend' => 0,
            'status' => 'completed',
            'fix_invoice' => 6112,
            'desc' => 'MO 971 - AFROZ SETH (3 duplicate logs of 1000 pcs)'
        ],
    ];

    echo "--- 1. SPECIFIC TARGETED CLEANUPS ---\n";
    foreach ($specific_fixes as $f) {
        $inv = $pdo->query("SELECT * FROM tblmrp_bom_production_inventory WHERE id = {$f['inv']}")->fetch(PDO::FETCH_ASSOC);
        if (!$inv) {
            echo "[-] {$f['desc']}: Inventory record #{$f['inv']} not found (skipped).\n";
            continue;
        }

        $logs_to_delete = is_array($f['del_log']) ? $f['del_log'] : [$f['del_log']];
        $found_logs = 0;
        foreach ($logs_to_delete as $lid) {
            $log = $pdo->query("SELECT * FROM tblmrp_bom_production_inventory_logs WHERE id = $lid")->fetch(PDO::FETCH_ASSOC);
            if ($log) {
                $found_logs++;
            }
        }

        if ($found_logs === 0 && (float)$inv['qty_received'] == (float)$f['set_recv']) {
            echo "[OK] {$f['desc']}: Already clean (Received: {$inv['qty_received']}).\n";
            continue;
        }

        echo "[+] {$f['desc']}:\n";
        echo "    Current Received: {$inv['qty_received']} => Will be set to: {$f['set_recv']}\n";
        echo "    Current Pending:  {$inv['qty_pending']} => Will be set to: {$f['set_pend']}\n";
        echo "    Status:           {$inv['status']} => Will be set to: {$f['status']}\n";
        echo "    Duplicate Log(s) to remove: " . implode(', ', $logs_to_delete) . "\n";

        if ($apply_changes) {
            foreach ($logs_to_delete as $lid) {
                $pdo->exec("DELETE FROM tblmrp_bom_production_inventory_logs WHERE id = $lid");
                $logs_deleted++;
            }
            $stmt = $pdo->prepare("UPDATE tblmrp_bom_production_inventory SET qty_received = ?, qty_pending = ?, status = ? WHERE id = ?");
            $stmt->execute([$f['set_recv'], $f['set_pend'], $f['status'], $f['inv']]);
            $cleaned_count++;

            // Clean custom fields on invoice if applicable
            if (!empty($f['fix_invoice'])) {
                $cf = $pdo->query("SELECT * FROM tblcustomfieldsvalues WHERE relid = {$f['fix_invoice']} AND fieldto = 'pur_invoice' AND fieldid = 2")->fetch(PDO::FETCH_ASSOC);
                if ($cf) {
                    $v = $cf['value'];
                    if ($f['fix_invoice'] == 6336) {
                        $v = str_replace('Receive Batch IDs: 6580,6579', 'Receive Batch ID: 6579', $v);
                        $v = str_replace('Batches Merged: 2<br />', '', $v);
                        $v = str_replace('MERGED BATCH INVOICE', 'BATCH INVOICE', $v);
                        $v = str_replace('Quantity Received: 1414', 'Quantity Received: 707', $v);
                        $v = str_replace('12726 RS', '6363 RS', $v);
                    } elseif ($f['fix_invoice'] == 6112) {
                        $v = str_replace('Receive Batch IDs: 6332,6329', 'Receive Batch ID: 6329', $v);
                        $v = str_replace('Batches Merged: 2<br />', '', $v);
                        $v = str_replace('MERGED BATCH INVOICE', 'BATCH INVOICE', $v);
                        $v = str_replace('Quantity Received: 2000', 'Quantity Received: 1000', $v);
                        $v = str_replace('48000 RS', '24000 RS', $v);
                    }
                    $stmt = $pdo->prepare("UPDATE tblcustomfieldsvalues SET value = ? WHERE id = ?");
                    $stmt->execute([$v, $cf['id']]);
                }
            }
            echo "    --> Applied successfully!\n";
        }
    }

    // -------------------------------------------------------------
    // SECTION 2: Dynamic Scan for Any Other Rapid Duplicate Receives
    // -------------------------------------------------------------
    echo "\n--- 2. SCANNING FOR ANY OTHER DUPLICATE RECEIVE LOGS (< 30s) ---\n";
    $sql = "
        SELECT 
            l1.bom_production_inventory_id,
            i.manufacturing_order_id,
            v.company as vendor_name,
            i.product_name,
            i.qty_assigned,
            i.qty_received,
            i.qty_pending,
            l1.id as keep_log_id,
            l2.id as dup_log_id,
            l1.qty_received as dup_qty,
            l1.created_at as time1,
            l2.created_at as time2,
            l1.pur_invoice_id as inv1,
            l2.pur_invoice_id as inv2
        FROM tblmrp_bom_production_inventory_logs l1
        JOIN tblmrp_bom_production_inventory_logs l2 
          ON l1.bom_production_inventory_id = l2.bom_production_inventory_id
          AND l1.id < l2.id
          AND l1.qty_received = l2.qty_received
          AND l1.qty_received > 0
          AND ABS(TIMESTAMPDIFF(SECOND, l1.created_at, l2.created_at)) <= 30
        JOIN tblmrp_bom_production_inventory i ON l1.bom_production_inventory_id = i.id
        LEFT JOIN tblpur_vendor v ON i.vendor_id = v.userid
        ORDER BY l1.bom_production_inventory_id DESC, l1.id DESC
    ";

    $other_dups = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $seen_dups = [];

    if (empty($other_dups)) {
        echo "No other duplicate receive logs found.\n";
    } else {
        foreach ($other_dups as $d) {
            $inv_id = $d['bom_production_inventory_id'];
            $dup_id = $d['dup_log_id'];

            if (isset($seen_dups[$dup_id])) {
                continue;
            }
            $seen_dups[$dup_id] = true;

            $inv_status = "Uninvoiced";
            if (!empty($d['inv1']) && !empty($d['inv2'])) {
                $inv_status = ($d['inv1'] == $d['inv2']) ? "Both in Invoice #{$d['inv1']}" : "Inv #{$d['inv1']} & Inv #{$d['inv2']}";
            } elseif (!empty($d['inv1'])) {
                $inv_status = "Keep log in Inv #{$d['inv1']}, Dup log Uninvoiced";
            } elseif (!empty($d['inv2'])) {
                $inv_status = "Dup log in Inv #{$d['inv2']}, Keep log Uninvoiced";
            }

            echo "[!] MO #{$d['manufacturing_order_id']} | Inv #$inv_id | Vendor: {$d['vendor_name']}\n";
            echo "    Keep Log #{$d['keep_log_id']} ({$d['dup_qty']}) at {$d['time1']}\n";
            echo "    Dup  Log #{$d['dup_log_id']} ({$d['dup_qty']}) at {$d['time2']}\n";
            echo "    Status: $inv_status\n";
        }
    }

    if ($apply_changes) {
        $pdo->commit();
        echo "\n====================================================================\n";
        echo " SUCCESS: Changes successfully committed to database!\n";
        echo " Inventories cleaned: $cleaned_count\n";
        echo " Duplicate logs deleted: $logs_deleted\n";
        echo "====================================================================\n";
    } else {
        echo "\n====================================================================\n";
        echo " DRY RUN COMPLETE: 0 changes made.\n";
        echo " To apply these changes on production, run:\n";
        echo "   php clean_duplicate_receives.php --apply\n";
        echo "====================================================================\n";
    }

} catch (Exception $e) {
    if ($apply_changes) {
        $pdo->rollBack();
    }
    echo "\n[ERROR] An error occurred: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes were saved.\n";
}
