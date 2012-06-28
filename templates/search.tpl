{box size=16 title="Search"}
	<form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/search/detailed" method="POST">
		<ul>
			<li><label>From: </label><input type="text" id="fromdate" name="from_date"></li>

			<li><label>To: </label><input type="text" id="todate" name="to_date"></li>

			 <li><label>First Name:</label><input type="text" name="first_name"></li>

			<li><label>Last Name:</label><input type="text" name="last_name"></li>

			<li><label>Location: </label>
				{html_options name=location options=$locations}</li>

			<li><label>Reservation ID#:</label><input type="text" name="reservation_id"></li>
			<li><input type="Submit" name="Search" value="Search"></li>
		<li><span class="note">Note: Queries are limited to 100 results.</span></li>
		</ul>
	</form>
{/box}
