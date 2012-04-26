{box title="Reservation ID: <a href=\"`$PHP.BASE_URL`/admin/reservation/search/id/`$item.reservation_idx`\">`$item.reservation_idx`</a>"}
<h2>Contact Information</h2>
	<ul class="label-left">
			<li><label>Name: </label>{$reservation.fname} {$reservation.lname}</li>
	</ul>
	<h2>Event Information</h2>
	<ul class="label-left">
			<li><label>Start Date: </label>{$reservation.start_date|date_format:$date_format}</li>		
			<li><label>Start Time: </label>{$reservation.start_time|date_format:$time_format}</li>		
			<li><label>End Date: </label>{$reservation.end_date|date_format:$date_format}</li>		
			<li><label>End Time: </label>{$reservation.end_time|date_format:$time_format}</li>		
			<li><label>Event Type: </label>
			{if $reservation.delivery_type=='1'}
				<span class="bold">CTS Sponsored Event</span>
			{else}
				<span>Equipment Pickup</span>
			{/if}
			<li><label>Location: </label>{$locations[$reservation.building_idx]} <strong>in room</strong> {$reservation.room}</li>
	</ul>

{if $reservation.equipment}
<table class="grid" width="450">
	<thead>
		<th>GLPI_ID</th>
		<th>Type</th>
		<th>Model</th>
	</thead>
	<tbody>
		{foreach from=$reservation.equipment item=row key=id}
			{foreach from=$reservation.equipment.$id item=equipment key=glpi_id}
			<tr>
				<td><a class="btn" target="blank" href="{$PHP.BASE_URL}/admin/equipment/item/{$glpi_id}">{$glpi_id|substr:-4}</a></td>
				<td>{$equipment.type}</td>
				<td>{$equipment.model}</td>
			</tr>
			{/foreach}
		{/foreach}
	</tbody>
</table>
{else}
<span>There is no equipment assigned to this reservation.</span>
{/if}

{/box}
