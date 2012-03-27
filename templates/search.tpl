<script>
	$(function(){
		$( "#fromdate, #todate" ).datepicker();
	});
</script>
{box title="Search"}
	<form action="{$PHP.BASE_URL}/admin/reservation/search" method="POST">
		<div>
			<label>From: </label><input type="text" id="fromdate" name="from_date">

			<label>To: </label><input type="text" id="todate" name="to_date">

			<label>First Name:</label><input type="text" name="first_name">

			<label>Last Name:</label><input type="text" name="last_name">

			<label>Location: </label>
				{html_options name=location options=$locations}

			<label>Reservation ID#:</label><input type="text" name="reservation_id">
			<input type="Submit" name="Search" value="Search">

		</div>
	</form>
{/box}
