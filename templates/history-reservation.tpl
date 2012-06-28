{box size=16 title="Reservation ID: $reservation_idx"}
{assign var=reserve value=$reservation[$reservation_idx]}
	<h2>Contact Information</h2>

	<ul class="label-left">
			<li><label>Name:</label>{$reserve.fname} {$reserve.lname}</li>
			<li><label>Phone:</label>{$reserve.phone}</li>
			{if $reserve.secondary_phone}
				<li><label>Secondary Phone:</label>{$reserve.secondary_phone}</li>
			{/if}
			<li><label>Email:</label>{$reserve.email}</li>
	</ul>
	<h2>Event Information</h2>
	<ul class="label-left">
			<li><label>Application date:</label>{$reserve.application_date|date_format:$date_format} <strong> at </strong> {$reserve.application_date|date_format:$time_format}</li>
			<li><label>Start Date:</label>{$reserve.start_date|date_format:$date_format}</li>		
			<li><label>Start Time:</label>{$reserve.start_time|date_format:$time_format}</li>		
			<li><label>End Date:</label>{$reserve.end_date|date_format:$date_format}</li>		
			<li><label>End Time:</label>{$reserve.end_time|date_format:$time_format}</li>		
			<li><label>Location:</label>{$locations[$reserve.building_idx]} <strong>in room</strong> {$reserve.room}</li>
			<li><label>Title:</label>{$reserve.title}</li>		
			<li><label>Status of Loan:</label>{$reserve.status}</li>
			<li><label>Comments:</label><p>{$reserve.memo}</p></li>
			<li><label>Requested Items:</label><p>{$reserve.request_items}</p></li>
	</ul>

	{/box}
