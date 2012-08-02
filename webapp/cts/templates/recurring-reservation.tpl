{literal}
<script>
	$( "#startdate , #enddate" ).datepicker();
</script>
{/literal}
{assign var=reservation value=$reservation[$reservation_idx]}
{box size=16 title="Set Recurring Reservation for `$reservation_idx`" }
	{box size=8 title="Reservation Info"}
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
	<table class="table table-bordered table-striped" width="450">
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
	{box size=7 title="Recursion"}
	<form action = "{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/setrecurring" method="POST">
		<ul class="clean label-left">
			<li><label>From:</label><input id="startdate" type="text" name="start_date"
			value="{$reservation.start_date|date_format:'%m/%d/%Y'}"></li>
			<li><label>To:</label><input id="enddate" type="text" name="end_date"
			value="{$reservation.end_date|date_format:'%m/%d/%Y'}"></li>
			<li><label>Sunday:</label><input type="checkbox" name="day[]" value="0"></li>
			<li><label>Monday:</label><input type="checkbox" name="day[]" value="1"></li>
			<li><label>Tuesday:</label><input type="checkbox" name="day[]" value="2"></li>
			<li><label>Wednesday:</label><input type="checkbox" name="day[]" value="3"></li>
			<li><label>Thursday:</label><input type="checkbox" name="day[]" value="4"></li>
			<li><label>Friday:</label><input type="checkbox" name="day[]" value="5"></li>
			<li><label>Saturday:</label><input type="checkbox" name="day[]" value="6"></li>
					<li><input type="submit" name="submit" value="Submit" class="btn btn-primary"></li>
		</ul>
	</form>
	{/box}
{/box}
