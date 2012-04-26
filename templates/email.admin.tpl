
<h2>Reservation Index: </h2>{$insert_id}
<h2>Submitter Contact Information</h2>
	<ul>
		<li><strong>Name: </strong>{$reserve.submit_first_name} {$reserve.submit_last_name}</li>
	</ul>

<h2>Event Contact Information</h2>
	<ul>
		<li><strong>Name: </strong>{$reserve.first_name} {$reserve.last_name}</li>
		<li><strong>Phone: </strong>{$reserve.phone}</li>
	</ul>

<h2>Event Information</h2>
	<ul>
		<li><strong>Course Title or Event Name: </strong>{$reserve.title}</li>
		<li><strong>Location: </strong>{$locations[$reserve.location] in room {$reserve.room}</li>
		<li><strong>Start Date and Time: </strong>{$reserve.start_date} at {$reserve.start_time}</li>
		<li><strong>End Date and Time: </strong>{$reserve.end_date} at {$reserve.end_time}</li>
		<li><strong>Pickup/Dropoff Method: </strong>

			{if $reserve.reserve_type == "equipment" }
				I will pickup/dropoff at the helpdesk.
			{else}
				The CTS department will dropoff the equipment at the location specified.
			{/if}
			
		</li>
		<li><strong>Comments/Purpose: </strong>
			<p>{$reserve.comments}</p>
		</li>
		<h2>Equipment Requested</h2>
			{foreach from=$reserve.equipment item=equipment}
				<li>{$categories[$equipment]}</li>
			{/foreach}
		
	</ul>
