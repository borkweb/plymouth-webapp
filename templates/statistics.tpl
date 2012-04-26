{box title="Statistics"}
<ul class="bullet">
	<li><span class="stats">There have been {$statistics.count_of_reservations|number_format:0:".":","} equipment reservations.</span></li>
	<li><span class="stats"> In the {$statistics.count_of_reservations|number_format:0:".":","} reservations there have been {$statistics.count_of_equipment|number_format:0:".":","} items reserved. </span></li>

	<li><span class="stats">The first reservation was made by {$statistics.first_reservation.fname} {$statistics.first_reservation.lname} on {$statistics.first_reservation.application_date}</span></li>

	<li><span class="stats">The last reservation was made by {$statistics.last_reservation.fname} {$statistics.last_reservation.lname} on {$statistics.last_reservation.application_date}</span></li>

	<li><span class="stats">The most reserved item is {$statistics.equipment_use.0.glpi_id}, which has been reserved {$statistics.equipment_use.0.count|number_format:0:".":","} times. </span></li>

</ul>
<h3>Item Counts</h3>
<table class="grid">
	<thead>
		<th>GLPI ID</th>
		<th>Count</th>
	</thead>
	<tbody>
		{foreach from=$statistics.equipment_use item=item }
			<tr>
				<td><a target="blank" href="{$PHP.BASE_URL}/admin/equipment/item/{$item.glpi_id}">{$item.glpi_id}</a></td>
				<td>{$item.count}</td>
			</tr>
		{/foreach}
		<tr>
		</tr>
	</tbody>
</table>
{/box}
