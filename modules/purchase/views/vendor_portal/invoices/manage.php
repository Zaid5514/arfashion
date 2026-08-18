<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('app_admin_head'); ?>
<div class="row">
	
	<div class="col-md-12">
		<div class="panel_s">
			<div class="panel-body dt-table-custom-invoices-wrapper">
				<h4><?php echo pur_html_entity_decode($title) ?></h4>
				<hr class="mtop5">
				<?php /*
				<a href="<?php echo site_url('purchase/vendors_portal/add_update_invoice'); ?>" class="btn btn-info"><?php echo _l('add_new'); ?></a>
				<br><br>
                */ ?>
				<table class="table dt-table-custom dt-table-custom-invoices">
			       <thead>
					<tr>
						<th colspan="2" class="text-right"><?php echo _l('total'); ?></th>
						<th id="total_invoice_amount"></th>
						<th id="total_paid_amount"></th>
						<th id="total_remaining_amount"></th>
						<th></th>
					</tr>
					<tr>
			       	<th><?php echo _l('invoice_code'); ?></th>
			          <th><?php echo _l('invoice_date'); ?></th>
			          <th><?php echo _l('invoice_amount'); ?></th>
			          <th><?php echo _l('Paid Amount'); ?></th>
			          <th><?php echo _l('Remaining Amount'); ?></th>
			          <th><?php echo _l('payment_status'); ?></th>
					</tr>
			       </thead>
			      <tbody>
			         <?php foreach($invoices as $inv) { ?>
			         	<?php 
			         		$base_currency = get_base_currency_pur(); 
			         		if($inv['currency'] != 0){
			         			$base_currency = pur_get_currency_by_id($inv['currency']);
			         		}
							$remaining_amount = purinvoice_left_to_pay($inv['id']);
							$paid_amount = $inv['total'] - $remaining_amount;
			         	?>
			         <tr class="inv_tr">
			         	<td class="inv_tr"><a href="<?php echo site_url('purchase/vendors_portal/invoice/'.$inv['id']); ?>"><?php echo pur_html_entity_decode($inv['invoice_number']); ?></a></td>
			         	<td class="inv_tr" data-order="<?php echo pur_html_entity_decode($inv['invoice_date']); ?>"><?php echo '<span class="label label-info">'._d($inv['invoice_date']).'</span>'; ?></td>
			         	<td class="inv_tr"><?php echo app_format_money($inv['subtotal'],$base_currency->symbol); ?></td>
			         	<td class="inv_tr">
							<?php echo app_format_money($paid_amount, $base_currency->symbol); ?>
						</td>
						<td class="inv_tr">
							<?php echo app_format_money($remaining_amount, $base_currency->symbol); ?>
						</td>
			         	<td class="inv_tr"><?php 
			         	$class = '';
			            if($inv['payment_status'] == 'unpaid'){
			                $class = 'danger';
			            }elseif($inv['payment_status'] == 'paid'){
			                $class = 'success';
			            }elseif ($inv['payment_status'] == 'partially_paid') {
			                $class = 'warning';
			            }

			            echo  '<span class="label label-'.$class.' s-status invoice-status-3">'._l($inv['payment_status']).'</span>';

			         	?>
			         	</td>
			         </tr>
			         <?php } ?>
					 <?php if (!empty($debit_notes)) {
						foreach ($debit_notes as $note) {
							$base_currency = get_base_currency_pur();
							if (!empty($note['currency']) && $note['currency'] != 0) {
								$base_currency = pur_get_currency_by_id($note['currency']);
							}
							$note_remaining = (float) $note['remaining_debits'];
							$note_status_html = function_exists('format_debit_note_status') ? format_debit_note_status($note['status']) : _l('debit_note');
					 ?>
			         <tr class="inv_tr payment-note-row">
			         	<td class="inv_tr"><?php echo pur_html_entity_decode(_l('debit_note') . ' ' . $note['formatted_number']); ?></td>
			         	<td class="inv_tr" data-order="<?php echo pur_html_entity_decode($note['date']); ?>"><?php echo '<span class="label label-info">'._d($note['date']).'</span>'; ?></td>
			         	<td class="inv_tr"><?php echo app_format_money(0, $base_currency->symbol); ?></td>
			         	<td class="inv_tr"><?php echo app_format_money($note_remaining, $base_currency->symbol); ?></td>
						<td class="inv_tr"><?php echo app_format_money(0, $base_currency->symbol); ?></td>
			         	<td class="inv_tr"><?php echo $note_status_html; ?></td>
			         </tr>
					 <?php }
					 } ?>
			      </tbody>
					<!-- <tfoot>
						<tr>
							<th colspan="3" class="text-right"><?php echo _l('total'); ?></th>
							<th id="total_invoice_amount"></th> -->
							<!--<th id="total_tax_value"></th>-->
							<!-- <th id="total_paid_amount"></th> -->
							<!-- <th id="total_remaining_amount"></th> -->
							<!--<th id="total_total_included_tax"></th>-->
							<!-- <th></th> -->
						<!-- </tr> -->
					<!-- </tfoot>				   -->
			   </table>	
			</div>
		</div>
	</div>
</div>
<?php hooks()->do_action('app_admin_footer'); ?>

<script>
jQuery(function () {
    let table = $('.dt-table-custom').DataTable({
        paging: false,
        order: [[1, 'desc']],
        orderCellsTop: false
    });

    table.on('draw', function () {
        let invoiceAmountTotal = 0;
        let paidAmountTotal = 0;
        let remainingAmountTotal = 0;

        table.rows({ page: 'current' }).every(function () {
            let row = $(this.node());
            let isPaymentNote = row.hasClass('payment-note-row');

            let amount = parseFloat(row.find('td:eq(2)').text().replace(/[^0-9.-]+/g, '')) || 0;
            let paid = parseFloat(row.find('td:eq(3)').text().replace(/[^0-9.-]+/g, '')) || 0;
            let remaining = parseFloat(row.find('td:eq(4)').text().replace(/[^0-9.-]+/g, '')) || 0;

            if (isPaymentNote) {
                paidAmountTotal += paid;
                remainingAmountTotal -= paid;
            } else {
                invoiceAmountTotal += amount;
                paidAmountTotal += paid;
                remainingAmountTotal += remaining;
            }
        });

        const formatMoney = (val) => {
            return Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val);
        };

        $('#total_invoice_amount').text(formatMoney(invoiceAmountTotal));
        // $('#total_tax_value').text(formatMoney(taxValueTotal));
        $('#total_paid_amount').text(formatMoney(paidAmountTotal));
        $('#total_remaining_amount').text(formatMoney(remainingAmountTotal));
        // $('#total_total_included_tax').text(formatMoney(totalIncludedTaxTotal));
    });

    // Trigger initial draw
    table.draw();
});
</script>

<style>
	.dt-table-custom-invoices {
  width: 100%;
  max-width: 100%;
  border-collapse: collapse;
}

.dt-table-custom-invoices-wrapper {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

</style>