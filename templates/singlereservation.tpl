{box title="Reservation ID: $reservation_idx"}
{psu_dbug var=$reservation}
{psu_dbug var=$messages}
{psu_dbug var=$equipment}
<ul class="clean">
{assign var=reserve value=$reservation[$reservation_idx]}
{psu_dbug var=$reserve}
	<h2>Contact Information</h2>
			<li><strong>Name: </strong>{$reserve.fname} {$reserve.lname}</li>
			<li><strong>Phone: </strong>{$reserve.phone}</li>
			<li><strong>Email: </strong>{$reserve.email}</li>
	<h2>Event Information</h2>
			<li><strong>Application date: </strong>{$reserve.application_date}</li>
			<li><strong>Start Date: </strong>{$reserve.start_date}</li>		
			<li><strong>Start Time: </strong>{$reserve.start_time}</li>		
			<li><strong>End Date: </strong>{$reserve.end_date}</li>		
			<li><strong>End Time: </strong>{$reserve.end_time}</li>		
			<li><strong>Location: </strong>{html_options name=location options=$locations selected=$reserve.building_idx} <strong>in room</strong> {$reserve.room}</li>
			<li><strong>Title: </strong>{$reserve.title}</li>		
			<li><strong>Status of Loan: </strong>{$reserve.status}</li>
			<li><strong>Comments: </strong><p>{$reserve.memo}</p></li>
			<li><strong>Requested Items: </strong><p>{$reserve.request_items}</p></li>

		<h2>Equipment Assigned</h2>
		<ul class="clean">
		{foreach from=$equipment item=equipment key=id}
				<li><strong>CTS Equipment #: </strong>{$equipment.equipment_idx}<br></li>
		{/foreach}
		</ul>

		<h2>Messages</h2>
		<ul class="clean">
<form method="POST" action="{$PHP.BASE_URL}/admin/reservation/addmessage/{$reservation_idx}">
			<li><textarea cols="60" name="message"></textarea></li>
			<li><input type="Submit" name="Submit" value="Add new message"></li>

			{foreach from=$messages item=message key=id}
				<li><strong>{$message.author} at {$message.time|date_format:"%l:%M %p"} on {$message.date|date:"%m-%d-%Y"}: </strong>{$message.message}<br></li>
			{/foreach}
		</ul>
	</ul>
{/box}
