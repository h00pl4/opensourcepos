<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/save/'.$item_info->item_id, array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
	<fieldset id="item_basic_info">

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_name'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'name',
						'id'=>'name',
						'class'=>'form-control input-sm',
						'value'=>$item_info->name)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_category'), 'category', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'category',
							'id'=>'category',
							'class'=>'form-control input-sm',
							'value'=>$item_info->category)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_unit_price'), 'unit_price', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-4'>
				<div class="input-group input-group-sm">
					<?php if (!currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'unit_price',
							'id'=>'unit_price',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($item_info->unit_price))
							);?>
					<?php if (currency_side()): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		
	<?php
		foreach($stock_locations as $key=>$location_detail)
		{
		?>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('items_quantity').' '.$location_detail['location_name'], 'quantity_' . $key, array('class'=>'required control-label col-xs-3')); ?>
				<div class='col-xs-4'>
					<?php echo form_input(array(
							'name'=>'quantity_' . $key,
							'id'=>'quantity_' . $key,
							'class'=>'required quantity form-control',
							'value'=>isset($item_info->item_id) ? to_quantity_decimals($location_detail['quantity']) : to_quantity_decimals(0))
							);?>
				</div>
			</div>
		<?php
		}
		?>
		

		<?php
		for ($i = 1; $i <= 10; ++$i)
		{
		?>
			<?php
			if($this->config->item('custom'.$i.'_name') != NULL)
			{
				$item_arr = (array)$item_info;
			?>
				<div class="form-group form-group-sm">
					<?php echo form_label($this->config->item('custom'.$i.'_name'), 'custom'.$i, array('class'=>'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<?php echo form_input(array(
								'name'=>'custom'.$i,
								'id'=>'custom'.$i,
								'class'=>'form-control input-sm',
								'value'=>$item_arr['custom'.$i])
								);?>
					</div>
				</div>
		<?php
			}
		}
		?>


		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_description'), 'description', array('class'=>'control-label col-xs-3')); ?>
				<div class='col-xs-8'>		
		
            	<?php 

			  if($item_info->category == 'Laptop' || $item_info->category == 'Desktop' || $item_info->category == 'Tower' || $item_info->category == 'All-in-One') //Check if item is a computer then concatenate a description, if not show category
			  { 
                                	echo form_textarea(array(
						'name'=>'description',
						'id'=>'description',
						'class'=>'form-control input-sm',
						'value'=>$item_info->category.', '.$item_info->custom2.', '.$item_info->custom3.', '.$item_info->custom4.' GHz, '.$item_info->custom5.' GB RAM, '.$item_info->custom6.' GB HDD, '.$item_info->custom8.', '.$item_info->custom7.'" Monitor') 
						);
                    		} else { // not a computer so enter category for description
                    			echo form_textarea(array(
						'name'=>'description',
						'id'=>'description',
						'class'=>'form-control input-sm',
						'value'=>$item_info->category)
                                                 );
                        		}
                	?>
				</div>
		</div>
		
	</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("#new").click(function() {
		stay_open = true;
		$("#item_form").submit();
	});

	$("#submit").click(function() {
		stay_open = false;
	});

	var no_op = function(event, data, formatted){};
	$("#category").autocomplete({source: "<?php echo site_url('items/suggest_category');?>",delay:10,appendTo: '.modal-content'});

	<?php for ($i = 1; $i <= 10; ++$i)
	{
	?>
		$("#custom" + <?php echo $i; ?>).autocomplete({
			source:function (request, response) {
				$.ajax({
					type: "POST",
					url: "<?php echo site_url('items/suggest_custom');?>",
					dataType: "json",
					data: $.extend(request, $extend(csrf_form_base(), {field_no: <?php echo $i; ?>})),
					success: function(data) {
						response($.map(data, function(item) {
							return {
								value: item.label
							};
						}))
					}
				});
			},
			delay: 10,
			appendTo: '.modal-content'});
	<?php
	}
	?>

	$("a.fileinput-exists").click(function() {
		$.ajax({
			type: "GET",
			url: "<?php echo site_url("$controller_name/remove_logo/$item_info->item_id"); ?>",
			dataType: "json"
		})
	});

	$('#item_form').validate($.extend({
		submitHandler: function(form, event) {
			$(form).ajaxSubmit({
				success: function(response) {
					var stay_open = dialog_support.clicked_id() != 'submit';
					if (stay_open)
					{
						// set action of item_form to url without item id, so a new one can be created
						$("#item_form").attr("action", "<?php echo site_url("items/save/")?>");
						// use a whitelist of fields to minimize unintended side effects
						$(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1,' +
							'#tax_percent_name_1, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price').val('');
						// de-select any checkboxes, radios and drop-down menus
						$(':input', '#item_form').not('#item_category_id').removeAttr('checked').removeAttr('selected');
					}
					else
					{
						dialog_support.hide();
					}
					table_support.handle_submit('<?php echo site_url('items'); ?>', response, stay_open);
				},
				dataType: 'json'
			});
		},

		rules:
		{
			name: "required",
			category: "required",
			item_number:
			{
				required: false,
				remote:
				{
					url: "<?php echo site_url($controller_name . '/check_item_number')?>",
					type: "post",
					data: $.extend(csrf_form_base(),
					{
						"item_id": "<?php echo $item_info->item_id; ?>",
						"item_number": function()
						{
							return $("#item_number").val();
						},
					})
				}
			},
			cost_price:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			},
			unit_price:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			},
			<?php
			foreach($stock_locations as $key=>$location_detail)
			{
			?>
				<?php echo 'quantity_' . $key ?>:
				{
					required: true,
					remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
				},
			<?php
			}
			?>
			receiving_quantity:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			},
			reorder_level:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			},
			tax_percent:
			{
				required: true,
				remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
			}
		},

		messages:
		{
			name: "<?php echo $this->lang->line('items_name_required'); ?>",
			item_number: "<?php echo $this->lang->line('items_item_number_duplicate'); ?>",
			category: "<?php echo $this->lang->line('items_category_required'); ?>",
			cost_price:
			{
				required: "<?php echo $this->lang->line('items_cost_price_required'); ?>",
				number: "<?php echo $this->lang->line('items_cost_price_number'); ?>"
			},
			unit_price:
			{
				required: "<?php echo $this->lang->line('items_unit_price_required'); ?>",
				number: "<?php echo $this->lang->line('items_unit_price_number'); ?>"
			},
			<?php
			foreach($stock_locations as $key=>$location_detail)
			{
			?>
				<?php echo 'quantity_' . $key ?>:
				{
					required: "<?php echo $this->lang->line('items_quantity_required'); ?>",
					number: "<?php echo $this->lang->line('items_quantity_number'); ?>"
				},
			<?php
			}
			?>
			receiving_quantity:
			{
				required: "<?php echo $this->lang->line('items_quantity_required'); ?>",
				number: "<?php echo $this->lang->line('items_quantity_number'); ?>"
			},
			reorder_level:
			{
				required: "<?php echo $this->lang->line('items_reorder_level_required'); ?>",
				number: "<?php echo $this->lang->line('items_reorder_level_number'); ?>"
			},
			tax_percent:
			{
				required: "<?php echo $this->lang->line('items_tax_percent_required'); ?>",
				number: "<?php echo $this->lang->line('items_tax_percent_number'); ?>"
			}
		}
	}, form_support.error));
});
</script>
