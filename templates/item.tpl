{box size="16" title="<a href=\"`$PHP.BASE_URL`/admin/equipment/`$reservation_idx`/item/`$item.psu_name`\">`$item.psu_name`</a>" title_size="12"  class="item-box"}
		{if $reservation_idx}
			<form action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/equipment" method="POST">
				<input type="hidden" name="GLPI_ID" value="{$item.psu_name}">
				<input type="Submit" value="Add {$item.psu_name} to reservation {$reservation_idx}">
			</form>
		{/if}
		<div class="statistics">
			<h2>Statistics</h2>
			<label>This item has been reserved {$item.count} time(s).</label>
		</div>
{/box}
