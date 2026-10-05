<?php defined('BASEPATH') or exit('No direct script access allowed');

$bom_production_inventory_id = (int) $bom_production_inventory_id;
$production_inventory = $this->db->where('id', $bom_production_inventory_id)->get('tblmrp_bom_production_inventory')->row_array();
$qty_lost = $production_inventory ? (float) $production_inventory['qty_lost'] : 0;
$price = $production_inventory ? (float) $production_inventory['price'] : 0;
$deduct_price = $production_inventory ? (float) $production_inventory['deduct_price'] : 0;
?>

<div class="modal fade" id="commonModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Recover lost quantity</h4>
			</div>
			<?php echo form_open(admin_url('manufacturing/recover_lost_quantity'), ['id' => 'recover_lost_form']); ?>
			<div class="modal-body">
				<?php if (!$production_inventory): ?>
					<p class="text-danger">Production assignment was not found.</p>
				<?php else: ?>
					<p class="text-muted">Use this when pieces already marked lost come back fixed. The original invoice stays as it is. The new invoice pays the receive price and returns the lost deduction.</p>
					<input type="hidden" name="bom_production_inventory_id" value="<?php echo $bom_production_inventory_id; ?>">
					<div class="row">
						<div class="col-md-4 form-group">
							<label>Lost quantity</label>
							<input type="number" class="form-control" value="<?php echo htmlspecialchars($qty_lost); ?>" readonly>
						</div>
						<div class="col-md-4 form-group">
							<label>Receive price</label>
							<input type="text" class="form-control" value="<?php echo htmlspecialchars($price); ?>" readonly>
						</div>
						<div class="col-md-4 form-group">
							<label>Lost price</label>
							<input type="text" class="form-control" value="<?php echo htmlspecialchars($deduct_price); ?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label>Quantity to recover</label>
						<input type="number" class="form-control" name="qty_recovered" min="0.01" max="<?php echo htmlspecialchars($qty_lost); ?>" step="0.01" value="<?php echo htmlspecialchars($qty_lost); ?>" required data-price="<?php echo htmlspecialchars($price); ?>" data-deduct-price="<?php echo htmlspecialchars($deduct_price); ?>">
					</div>
					<p id="recover_lost_preview" class="text-info"></p>
					<div class="form-group">
						<label>Comment</label>
						<textarea class="form-control" name="comments" rows="3" required placeholder="Vendor returned pieces that were marked lost"></textarea>
					</div>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<?php if ($production_inventory && $qty_lost > 0): ?>
					<button type="submit" class="btn btn-info">Recover</button>
				<?php endif; ?>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
