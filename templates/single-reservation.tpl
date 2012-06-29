<script>
// jQuery dependent
$(function(){
	$( "#startdate , #enddate" ).datepicker();

	// Confirm the deletion
	$( "#reservation_delete" ).click( function(event) {
		// Let's prevent the default action, so we can confirm it first
		event.preventDefault();

		// Let's grab the link's url
		var linkUrl = $(this).attr('href');

		var confirmation = confirm("Are you sure you would like to delete this reservation?");
		if( confirmation ){ //if the user confirmed
			window.location.href= linkUrl; //send the window href to the link url that we grabbed previously
		}

	});
});
</script>
{box title="Reservation ID: $reservation_idx"}
<ul class="clean">
{assign var=reserve value=$reservation[$reservation_idx]}
{if $editable}
<form class="label-left" name ="event_info" method="POST" action="{$PHP.BASE_URL}/admin/reservation/{$reservation_idx}/edit">
          <ul>
		<li>
          	<h4>Contact Information:</h4>
          </li>

          <li>
          	<label class="required">First Name:<em>*</em></label>
          	<input type="text" name="first_name" size="25" value="{$reserve.fname}"></input>
          </li>

          <li>  
          	<label class="required">Last Name:<em>*</em></label>
          	<input type="text" name="last_name" size="25" value="{$reserve.lname}">
          </li>

          <li>
          	<label class="required">Phone Number:<em>*</em></label>
          	<input id="phone" type="text" name="phone" size="14" value="{$reserve.phone}">
          </li>
          <li>
          	<label class="required">Campus Email:<em>*</em></label>
          	<input type="email" name ="email" value="{$reserve.email}"> <!-- wp_email -->
		</li>
            <li>
              <h4>Event Information: </h4>
            </li>
            <li>
              <label class="required">Event Title or Course Number and Section:<em>*</em></label>
              <input type="text" name="title" size="25" value="{$reserve.title}">
            </li>
            <li>
              <label class="required">Location:<em>*</em></label>
			{html_options name=location options=$locations selected=$reserve.building_idx}
		  </li>
		  <li>
		    <label class="required">Room Number:<em>*</em></label>
              <input type="text" name="room" size="5" value="{$reserve.room}">
            </li>
            <li>
             <label>Purpose, Details or Comments:</label>
             <textarea  name="comments" rows="5" cols="40">{$reserve.memo}</textarea>
            </li>
		  <li>
		  	<label class="required">Event Start:<em>*</em></label>
		  	<input id="startdate" type="text" name="start_date"
			value="{$reserve.start_date|date_format:'%m/%d/%Y'}"> at 
			{html_options name=starthour options=$hours selected=$starthour class="time"}
			:
			{html_options name=startminute options=$minutes|string_format:"%02d" selected=$startminute class="time"}
			
			-
			{html_options name=startampm options=$ampm selected=$startampm class="time"}
		  </li>
		  <li>
		  	<label class="required">Event End:<em>*</em></label>
		  	<input id="enddate" type="text" name="end_date" value="{$reserve.end_date|date_format:'%m/%d/%Y'}"> at
			{html_options name=endhour options=$hours selected=$endhour class="time"}
			
			:
			{html_options name=endminute options=$minutes|string_format:"%02d" selected=$endminute class="time"}
			-
			{html_options name=endampm options=$ampm selected=$endampm class="time"}
		   </li>
		   <li>
		<input type="radio" id="equipment" name="radio" value="0" checked="true"/>I will pick up and return the equipment to the learning Commons Information Desk in Lamson Library
		  </li>
		  <li>
			<input type="radio" id="sponsored" name="radio" value="1" />I will need the Classrom Technology Staff to deliver and retrieve the equipment at the location specified
		  </li>
		  <li class="form-actions">
              <input type="Submit" name="Edit_event" value="Submit Changes">
            </li>
 	 </ul>
	</form>

{else}
	<h2>Contact Information</h2>
	<ul class="label-left clean">
			<li><label>Name: </label>{$reserve.fname} {$reserve.lname}</li>
			<li><label>Phone: </label>{$reserve.phone}</li>
			<li><label>Email: </label>{$reserve.email}</li>
	</ul>
	<h2>Event Information</h2>
	<ul class="label-left clean">
			<li><label>Start Date: </label>{$reserve.start_date|date_format:$date_format}</li>		
			<li><label>Start Time: </label>{$reserve.start_time|date_format:$time_format}</li>		
			<li><label>End Date: </label>{$reserve.end_date|date_format:$date_format}</li>		
			<li><label>End Time: </label>{$reserve.end_time|date_format:$time_format}</li>		
			<li><label>Event Type: </label>

			{if $reserve.delivery_type==1}
				<span class="bold">CTS Supported Event</span>
			{else}
				<span>Equipment Pickup</span>
			{/if}
			<li><label>Location: </label>{$locations[$reserve.building_idx]} <strong>in room</strong> {$reserve.room}</li>
			<li><label>Title: </label>{$reserve.title}</li>
			<li><label>Application date: </label>{$reserve.application_date|date_format:$date_format} <strong> at </strong> {$reserve.application_date|date_format:$time_format}</li>

			<li><form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/status"><label>Status of Loan: </label>{html_options name="status" options=$status selected=$reserve.status} <input type="submit" name="Status" class="btn btn-primary" value="Change Status"></form></li>

			{if $reserve.user_pickup == "000000000"}
			<li><form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/userpickup" method="POST">
			<label>Add Pickup User:</label><input type="text" name="USER_ID"><input type="submit" name="add_user_pickup" value="Add Pickup User"></li></form>
			{else}
			<li><label>Pickup User: </label>{$reserve.user_pickup}</li>
			{/if}

			{if $reserve.user_dropoff == "000000000"}
			<li><form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/userdropoff" method="POST">
			<label>Add Dropoff User:</label><input type="text" name="USER_ID"><input type="submit" name="add_user_dropoff" value="Add Dropoff User"></li></form>
			{else}
			<li><label>Dropoff User: </label>{$reserve.user_dropoff}</li>
			{/if}


			{if $user_level <= 2}
				<li><form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/priority"><label>Priority of Loan: </label>{html_options name="priority" options=$priority selected=$reserve.priority} <input type="submit" name="Priority" value="Change Priority"></form></li>
			{else}
				<li><label>Priority of Loan: </label>{html_options name="priority" options=$priority selected=$reserve.priority disabled=true}</li>
			{/if}

			<li><label>Comments: </label><p>{$reserve.memo}</p></li>
			<li><label>Requested Items: </label><p>{$reserve.request_items}</p></li>
	</ul>
		<h2>Equipment Assigned</h2>
		{if $equipment_info}
			<table class="table table-bordered table-striped" width="450">
			<thead>
				<tr>
					<th>GLPI ID</th>
					<th>Type</th>
					<th>Model</th>
					{if $user_level <=2}
						<th>Remove</th>
					{/if}
				</tr>
			</thead>
			<tbody>
			<!--need to access the data that is saved in equipment info -->
			{foreach from=$equipment_info item=row key=id}
				{foreach from=$equipment_info.$id item=equipment key=glpi_id}
				<tr>
					<td>{$glpi_id|substr:-4}</td>
					<td>{$equipment.type}</td>
					<td>{$equipment.model}</td>
					{if $user_level <= 2}
						<td><a class="btn btn-danger" href="{$PHP.BASE_URL}/admin/reservation/equipment/{$reservation_idx}/remove/{$equipment.reservation_equipment_idx}">Remove</a></td>
					{/if}
				</tr>
				{/foreach}
			{/foreach}
			</tbody>
			</table>
		{else}
			<span class="bold">There is no equipment assigned to this request.</span>
		{/if}
		
	<ul>
		<li><form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/equipment" method="POST">
			<label>Add Item by ID:</label><input type="text" name="GLPI_ID"><input type="submit" name="add_equipment" value="Add Equipment">

		</form>
		<li><a class="btn" href="{$PHP.BASE_URL}/admin/equipment/{$reservation_idx}">Use equipment book to add equipment</a></li>
		</li>

		<h3>Subitems</h3>
		{if $subitems}
			<table class="table table-bordered table-striped" width="300">
			<thead>
				<tr>
					<th>Subitem</th>
					{if $user_level <=2}
					<th>Remove</th>
					{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$subitems item=subitem key=id}
				<tr>
					<td>{$subitem.name}</td>
					{if $user_level <= 2}
					<td><a class="btn btn-danger" href="{$PHP.BASE_URL}/admin/reservation/subitem/remove/{$id}/{$reservation_idx}">Remove</a>
					{/if}
				</tr>
			{/foreach}
			</tbody>
			</table>
		{else}
			<span class="bold">There are no subitems assigned to this request.</span>
		{/if}

		
		<form action="{$PHP.BASE_URL}/admin/reservation/{$reservation_idx}/subitem/add" method="POST">
			{html_options name="subitems" options=$subitemlist}
		<input type="submit" name="Subitemsubmit" value="Assign Sub Item">
		</form>
	</ul>
		{if $reserve.delivery_type == 1}
		<h2>Technician Assigned</h2>
		<ul class="label-left clean">
			{if $user_level == 1} <!--if the user is the manager -->
				<form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/dropoff"<li>{html_options name=assigned_tech_dropoff options=$cts_technicians selected=$reserve.delivery_user}</li>
				<input type="submit" name="dropoff" value="Assign Delivery & Support Technician"></form></li>
			{elseif $user_level == 2}<!--if the user is cts staff -->

				{if $reserve.delivery_user == NULL}<!--if there is no delivery user, show the assign myself button -->
					<form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/dropoff"<li><input type="hidden" value="{$user->wpid}" name="assigned_tech_dropoff"></li><input type="submit" name="dropoff" value="Assign Myself to Dropoff"></form></li>
				{else}<!--If there is a delivery user, show the technician that is assigned -->
					<li><label>Dropoff: </label>{html_options name=assigned_tech_dropoff options=$cts_technicians selected=$reserve.delivery_user disabled=true}</li>
				{/if}
			{else}<!-- if the user is helpdesk -->
<!--this select box is to change between Manager, CTS Staff and Helpdesk -->
					<li><label>Dropoff: </label>{html_options name=assigned_tech_dropoff options=$cts_technicians selected=$reserve.delivery_user disabled=true}</li>

			{/if}

			{if $user_level == 1}<!--if the user is the manager -->
				<form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/pickup"<li>{html_options name=assigned_tech_pickup options=$cts_technicians selected=$reserve.retrieval_user}</li>
				<input type="submit" name="pickup" value="Assign Pickup Technician"></form></li>
			{elseif $user_level == 2}<!--if the user is a CTS staff -->
				{if $reserve.retrieval_user == NULL}
					<form class="label-left" action="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/pickup"<li><input type="hidden" value="{$user->wpid}" name="assigned_tech_pickup"></li><input type="submit" name="pickup" value="Assign Myself to Pickup"></form></li>

				{else}
					<li><label>Pickup: </label>{html_options name=assigned_tech_pickup options=$cts_technicians selected=$reserve.retrieval_user disabled=true}</li>

				{/if}
			{else}<!--if the user is helpdesk -->
<!--this select box is to change between Manager, CTS Staff and Helpdesk -->
					<li><label>Pickup: </label>{html_options name=assigned_tech_pickup options=$cts_technicians selected=$reserve.retrieval_user disabled=true}</li>

			{/if}
		{/if}
		</ul>


	</ul>
{/if}
	<h2>Messages</h2>
	<ul class="clean">
		<form method="post" action="{$PHP.BASE_URL}/admin/reservation/addmessage/{$reservation_idx}">
			<li><textarea cols="60" name="message"></textarea></li>
			<li><input type="submit" name="submit" value="Add New Message"></li>

			{foreach from=$messages item=message key=id}
				<li><label>{$message.author} at {$message.time|date_format:$time_format} on {$message.date|date_format:$date_format}: </label><span class="cts-message">{$message.message}</span></li>
			{/foreach}
		<li>
		{if $user_level<=2}<!--If the user is CTS staff or manager -->
		<a href="{$PHP.BASE_URL}/admin/reservation/search/id/{$reservation_idx}/edit" class="btn btn-warning">Edit Reservation</a>
		{/if}
		{if $user_level==1}<!--Don't show the delete button to anyone other than the manager -->
		<a id="reservation_delete" href="{$PHP.BASE_URL}/admin/reservation/search/id/{$reservation_idx}/delete" class="btn btn-danger">Delete Reservation</a>
		{/if}
		<a href="{$PHP.BASE_URL}/admin/reservation/id/{$reservation_idx}/print" class="btn btn-primary">Print Reservation</a></li>
	</ul>
{/box}
