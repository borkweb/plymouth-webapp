<script>
var startTime= new Date();
	$(function(){
		$( "#fromdate, #todate" ).datepicker();

		$( "#fromdate" ).change(function (){
		var startTime = ($("#fromdate").val());
		$("#todate").datepicker('option','minDate',startTime);

	});

	});
</script>

{box title="Search"}
	<form class="label-left" action="{$PHP.BASE_URL}/admin/equipment/item/{$glpi_id}/daterange" >
		<ul>
			<li><label>From: </label><input type="text" id="fromdate" name="from_date"></li>

			<li><label>To: </label><input type="text" id="todate" name="to_date"></li>
			<li><input type="Submit" name="Search" value="Search"></li>
			<span class="note">Note: This will generate a gantt chart within this timeframe. More than two weeks can cause the graph to be unreadable.</span>

		</ul>
	</form>
{/box}
{box title=$title}
	<a href="{$PHP.BASE_URL}/admin/equipment/item/{$glpi_id}/lastweek">Last Week</a>|
	<a href="{$PHP.BASE_URL}/admin/equipment/item/{$glpi_id}/thisweek">This Week</a>|
	<a href="{$PHP.BASE_URL}/admin/equipment/item/{$glpi_id}/nextweek">Next Week</a>

{/box}
{box title="Equipment Availability for `$glpi_id`"}
	{$gantt_chart}
{if $reservation_idx}
<form action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/equipment" method="POST">
	<input type="hidden" name="GLPI_ID" value="{$glpi_id}">
	<input type="Submit" value="Add {$glpi_id} to reservation {$reservation_idx}">
</form>
{/if}

<h3>Statistics</h3>
<span>This item has been reserved {$count} time(s).</span>
{/box}
