<h2>Your request has been cancelled!</h2>

<p>Below you will see the information for the reservation.</p>

<h2>Event Contact Information</h2>
	<ul>
		<li><strong>Name: </strong>{$reserve.fname} {$reserve.lname}</li>
		<li><strong>Phone: </strong>{$reserve.phone}</li>
	</ul>

<h2>Event Information</h2>
	<ul>
		<li><strong>Course Title or Event Name: </strong>{$reserve.title}</li>
		<li><strong>Location: </strong>{$locations[$reserve.building_idx]} in room {$reserve.room}</li>
		<li><strong>Start Date and Time: </strong>{$reserve.start_date|date_format:'%m-%d-%Y'} <strong>at</strong> {$reserve.start_time|date_format:'%l:%M %p'}</li>
		<li><strong>End Date and Time: </strong>{$reserve.end_date|date_format:'%m-%d-%Y'} <strong>at</strong> {$reserve.end_time|date_format:'%l:%M %p'}</li>
		<li><strong>Pickup/Dropoff Method: </strong>

			{if $reserve.delivery_type == "0" }
				I will pickup/dropoff at the helpdesk.
			{else}
				The CTS department will dropoff the equipment at the location specified.
			{/if}
			
		</li>
		<li><strong>Comments/Purpose: </strong>
			<p>{$reserve.memo}</p>
		</li>
		<h2>Equipment Requested</h2>
			<p>{$reserve.request_items}</p>
	</ul>
