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

	{/box}
