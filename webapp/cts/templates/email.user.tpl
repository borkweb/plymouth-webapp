<p>
	Below you will find your copy of the media equipment request which you or someone you authorized
	submitted via the on-line request form. This loan is subject to the Equipment Reservation
	Agreement (ERA).  If approved, you are to abide by the terms of the ERA.  The full text of the
	agreement can be found in myPlymouth under the Computing Resources Channel and in the Equipment 
	Reservations link.
</p>
<p>
	If you did not authorize this loan or you do not agree to the Equipment Reservation Agreement,
	you must e-mail itsmedia@plymouth.edu and request cancellation of this loan. 
</p>
<p>
	You will be contacted by a member of Classroom Technology Services ONLY if there is a need for 
	further clarification or if there is a conflict with equipment availability.
</p>
<p>Thank you.</p>


<h2>Your/Submitter Contact Information</h2>
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

			{if $reserve.reserve_type == 0 }
				I will pickup/dropoff at the helpdesk.
			{else}
				The CTS department will dropoff the equipment at the location specified.
			{/if}
			
		</li>
		<li><strong>Comments/Purpose: </strong>
			<p>{$reserve.comments}</p>
		</li>
	</ul>
		<h2>Equipment Requested</h2>
		<ul>
			{foreach from=$reserve.equipment item=equipment}
				<li>{$categories[$equipment]}</li>
			{/foreach}
		
	</ul>
