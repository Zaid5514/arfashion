<?php
defined('BASEPATH') or exit('No direct script access allowed');

$bom_production_inventory_id = $bom_production_inventory_id;

$production_inventory = $this->db->select('*')
	->from('tblmrp_bom_production_inventory')
	->where('id', $bom_production_inventory_id)
	->get()
	->row_array();

$production_inventory_logs = $this->db->select('*')
	->from('tblmrp_bom_production_inventory_logs')
	->where('bom_production_inventory_id', $bom_production_inventory_id)
	->where('qty_received >', 0)
	->order_by('id', 'desc')
	->get()
	->result_array();

$manufacturing_order_code = '';
if (!empty($production_inventory['manufacturing_order_id'])) {
	$mo = $this->db->select('manufacturing_order_code')
		->from(db_prefix() . 'mrp_manufacturing_orders')
		->where('id', $production_inventory['manufacturing_order_id'])
		->get()
		->row();
	$manufacturing_order_code = $mo ? $mo->manufacturing_order_code : '';
}

$can_make_invoice = has_permission('manufacturing', '', 'view');

$uninvoiced_qty = 0;
$uninvoiced_lost = 0;
$uninvoiced_ids = [];
$invoiced_count = 0;
foreach ($production_inventory_logs as $log) {
	if ((int) ($log['pur_invoice_id'] ?? 0) > 0) {
		$invoiced_count++;
	} else {
		$uninvoiced_qty += (float) $log['qty_received'];
		$uninvoiced_lost += (float) $log['qty_lost'];
		$uninvoiced_ids[] = (int) $log['id'];
	}
}

$base_params = [
	'bom_production_inventory_id' => $production_inventory['id'],
	'manufacturing_order_id'      => $production_inventory['manufacturing_order_id'],
	'vendor'                      => $production_inventory['vendor_id'],
	'manufacturing_order_code'    => $manufacturing_order_code,
	'item_name'                   => $production_inventory['product_name'],
	'description'                 => $production_inventory['comments'],
	'qty_assigned'                => $production_inventory['qty_assigned'],
	'qty_pending'                 => $production_inventory['qty_pending'],
	'price'                       => $production_inventory['price'],
	'deduct_price'                => $production_inventory['deduct_price'],
];
?>

<div class="modal fade" id="commonModal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Production Invoices</h4>
			</div>
			<div class="modal-body">
				<p class="text-muted" style="margin-bottom:10px;">
					<?php echo htmlspecialchars($production_inventory['product_name']); ?>
					<?php if (!empty($production_inventory['comments'])): ?>
						— <?php echo htmlspecialchars($production_inventory['comments']); ?>
					<?php endif; ?>
				</p>

				<?php if ($can_make_invoice && count($uninvoiced_ids) > 0): ?>
					<div class="alert alert-info" style="padding:10px 12px; margin-bottom:12px;">
						<strong>Uninvoiced received:</strong>
						<?php echo rtrim(rtrim(number_format($uninvoiced_qty, 2, '.', ''), '0'), '.'); ?> qty
						<?php if (count($uninvoiced_ids) > 1): ?>
							across <?php echo count($uninvoiced_ids); ?> batches — select batches below to merge into one invoice, or invoice all pending.
						<?php else: ?>
							(1 batch)
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if (!empty($production_inventory_logs)): ?>
					<div style="max-height: 360px; overflow-y: auto;">
						<table class="table table-sm mb-0 customizable-table dataTable" style="font-size: 12px; margin-top: 5px;" id="bom_production_invoices_table">
							<thead class="thead-light">
								<tr>
									<?php if ($can_make_invoice && count($uninvoiced_ids) > 0): ?>
										<th width="5%">
											<input type="checkbox" id="bom_invoice_select_all" title="Select all uninvoiced">
										</th>
									<?php endif; ?>
									<th width="5%">#</th>
									<th width="18%">Date</th>
									<th width="10%">Received</th>
									<th width="10%">Lost</th>
									<th width="42%">Invoice</th>
								</tr>
							</thead>
							<tbody>
								<?php $x = 0; foreach ($production_inventory_logs as $log): ?>
									<?php
										$linked_invoice_id = (int) ($log['pur_invoice_id'] ?? 0);
										$is_uninvoiced = $linked_invoice_id <= 0;
									?>
									<tr data-log-id="<?php echo (int) $log['id']; ?>"
										data-qty-received="<?php echo (float) $log['qty_received']; ?>"
										data-qty-lost="<?php echo (float) $log['qty_lost']; ?>"
										data-uninvoiced="<?php echo $is_uninvoiced ? '1' : '0'; ?>">
										<?php if ($can_make_invoice && count($uninvoiced_ids) > 0): ?>
											<td>
												<?php if ($is_uninvoiced): ?>
													<input type="checkbox" class="bom-invoice-log-check" value="<?php echo (int) $log['id']; ?>" checked>
												<?php endif; ?>
											</td>
										<?php endif; ?>
										<td><?php echo ++$x; ?></td>
										<td><?php echo htmlspecialchars(date('d M Y H:iA', strtotime($log['created_at']))); ?></td>
										<td><?php echo htmlspecialchars($log['qty_received']); ?></td>
										<td><?php echo htmlspecialchars($log['qty_lost']); ?></td>
										<td>
											<?php if ($linked_invoice_id > 0):
												$inv_no = function_exists('get_pur_invoice_number') ? get_pur_invoice_number($linked_invoice_id) : $linked_invoice_id;
											?>
												<a target="_blank" href="<?php echo admin_url('purchase/purchase_invoice/' . $linked_invoice_id); ?>">
													View Invoice<?php echo $inv_no ? ' (' . $inv_no . ')' : ''; ?>
												</a>
											<?php elseif ($can_make_invoice):
												$single_params = array_merge($base_params, [
													'bom_production_inventory_log_ids' => $log['id'],
													'qty_received' => $log['qty_received'],
													'qty_lost' => $log['qty_lost'],
												]);
											?>
												<span class="text-warning">Pending</span>
												|
												<a class="bom-make-invoice-link" href="<?php echo admin_url('purchase/pur_invoice?' . http_build_query($single_params)); ?>">
													Make Invoice
												</a>
											<?php else: ?>
												—
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<p class="text-muted">No received batches yet. Use Receive first.</p>
				<?php endif; ?>
			</div>
			<div class="modal-footer" style="text-align:left;">
				<?php if ($can_make_invoice && count($uninvoiced_ids) > 0): ?>
					<span id="bom_invoice_selected_summary" class="text-muted" style="margin-right:12px;"></span>
					<a href="#" id="bom_make_merged_invoice" class="btn btn-info bom-make-invoice-link"
						data-base-url="<?php echo admin_url('purchase/pur_invoice'); ?>"
						data-params="<?php echo htmlspecialchars(json_encode($base_params), ENT_QUOTES, 'UTF-8'); ?>">
						Make Invoice for Selected
					</a>
					<?php
						$all_params = array_merge($base_params, [
							'bom_production_inventory_log_ids' => implode(',', $uninvoiced_ids),
							'qty_received' => $uninvoiced_qty,
							'qty_lost' => $uninvoiced_lost,
						]);
					?>
					<a href="<?php echo admin_url('purchase/pur_invoice?' . http_build_query($all_params)); ?>"
						class="btn btn-default bom-make-invoice-link">
						Invoice All Pending (<?php echo rtrim(rtrim(number_format($uninvoiced_qty, 2, '.', ''), '0'), '.'); ?>)
					</a>
				<?php elseif ($invoiced_count > 0): ?>
					<span class="text-success">All received batches are invoiced.</span>
				<?php endif; ?>
				<button type="button" class="btn btn-default close_btn pull-right" data-dismiss="modal"><?php echo _l('hr_close'); ?></button>
			</div>
		</div>
	</div>
</div>
