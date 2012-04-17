{box title="Reservation ID: $reservation_idx"}
<ul class="clean">
{assign var=reserve value=$reservation[$reservation_idx]}
	<h2>Contact Information</h2>
			<li><strong>Name: </strong>{$reserve.fname} {$reserve.lname}</li>
			<li><strong>Phone: </strong>{$reserve.phone}</li>
			{if $reserve.secondary_phone}
				<li><strong>Secondary Phone: </strong>{$reserve.secondary_phone}</li>
			{/if}
			<li><strong>Email: </strong>{$reserve.email}</li>
	<h2>Event Information</h2>
			<li><strong>Application date: </strong>{$reserve.application_date|date_format:$date_format} <strong> at </strong> {$reserve.application_date|date_format:$time_format}</li>
			<li><strong>Start Date: </strong>{$reserve.start_date|date_format:$date_format}</li>		
			<li><strong>Start Time: </strong>{$reserve.start_time|date_format:$time_format}</li>		
			<li><strong>End Date: </strong>{$reserve.end_date|date_format:$date_format}</li>		
			<li><strong>End Time: </strong>{$reserve.end_time|date_format:$time_format}</li>		
			<li><strong>Location: </strong>{$locations[$reserve.building_idx]} <strong>in room</strong> {$reserve.room}</li>
			<li><strong>Title: </strong>{$reserve.title}</li>		
			<li><strong>Status of Loan: </strong>{$reserve.status}</li>
			<li><strong>Comments: </strong><p>{$reserve.memo}</p></li>
			<li><strong>Requested Items: </strong><p>{$reserve.request_items}</p></li>

		<h2>Equipment Assigned</h2>
		<table class="grid" width="450">
		<thead>
			<tr>
				<th>GLPI ID</th>
				<th>CTS ID</th>
				<th>Type</th>
				<th>Model</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$equipment item=equipment key=id}
			<tr>
				<td>GLPI_ID<!--|substr:-4}--></td>
				<td>{$equipment.equipment_idx}</td>
				<td>Type</td>
				<td>Model</td>
			</tr>
		{/foreach}
		</tbody>
		</table>
		
		<h3>Subitems</h3>
		<table class="grid" width="300">
		<thead>
			<tr>
				<th>Subitem ID</th>
				<th>Subitem</th>
				<th>Remove</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$subitems item=subitem key=id}
			<tr>
				<td>{$subitem.subitem_id}</td>
				<td>{$subitem.name}</td>
				<td><a href="{$PHP.BASE_URL}/admin/reservation/subitem/remove/{$id}/{$reservation_idx}">Remove</a>
			</tr>
		{/foreach}
		</tbody>
		</table>
	<h2>Messages</h2>
{foreach from=$messages item=message key=id}
				<li><strong>{$message.author} at {$message.time|date_format:$time_format} on {$message.date|date_format:$date_format}: </strong>{$message.message}<br></li>
			{/foreach}
		<!--
		<script type="text/javascript">
$(document).ready(function(){

window.print();
});
</script>
-->
<a href="javascript:window.print()" class="button">Print Reservation</a></li>
		{/box}


