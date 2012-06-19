{include file="status.tpl"}
{box size="16" title="Event Information"}
{if $step < 1 && $copy == false}
<form class="label-left" name ="event_info" method="POST" action="{$PHP.BASE_URL}/reserve/event">
          <ul>
			<li>
				<h4>Contact Information:</h4>
			</li>

			<li>
				<label class="required">First Name:<em>*</em></label>
				<input type="text" name="first_name" size="25" value="{$user->first_name}"></input>
			</li>

			<li>  
				<label class="required">Last Name:<em>*</em></label>
				<input type="text" name="last_name" size="25" value="{$user->last_name}">
			</li>

			<li>
				<label class="required">Phone Number:<em>*</em></label>
				<input id="phone" type="text" name="phone" size="14">
			</li>
			<li>
				<label class="required">Campus Email:<em>*</em></label>
				<input type="email" name ="email" value="{$user->wp_email}"> <!-- wp_email -->
			</li>
			  <li>
			    <h4>Event Information: </h4>
			  </li>
			  <li>
			    <label class="required">Event Title or Course Number and Section:<em>*</em></label>
			    <input type="text" name="title" size="25">
			  </li>
			  <li>
			    <label class="required">Location:<em>*</em></label>
				{html_options name=location options=$locations}
			  </li>
			  <li>
				<label class="required">Room Number:<em>*</em></label>
			    <input type="text" name="room" size="5">
			  </li>
			  <li>
			   <label>Purpose, Details or Comments:</label>
			   <textarea  name="comments" rows="5" cols="40"></textarea>
			  </li>
			  <li>
				<label class="required">Event Start:<em>*</em></label>
				<input id="startdate" type="text" name="start_date" readonly="true"> at 

				{html_options name=starthour options=$hours }
				:
				{html_options name=startminute options=$minutes|string_format:"%02d"}
			
				-
				{html_options name=startampm options=$ampm}
			  </li>
			  <li>
				<label class="required">Event End:<em>*</em></label>
				<input id="enddate" type="text" name="end_date" readonly="true" value="{$reserve.end_date}"> at
				{html_options name=endhour options=$hours}
			
				:
				{html_options name=endminute options=$minutes|string_format:"%02d"}
				-
				{html_options name=endampm options=$ampm }

			   </li>
			   <li>
				<input type="radio" id="equipment" name="radio" value="0" checked="true"/>I will pick up and return the equipment to the learning Commons Information Desk in Lamson Library
			  </li>
			  <li>
				<input type="radio" id="sponsored" name="radio" value="1" />I will need the Classrom Technology Staff to deliver and retrieve the equipment at the location specified
			  </li>
		  
{else}<!--ELSE STATEMENT -->
	<form class="label-left" name ="event_info" method="POST" action="{$PHP.BASE_URL}/reserve/event">
          <ul>
				<h4>Contact Information:</h4>
			<li>
				<label class="required">First Name:<em>*</em></label>
				<input type="text" name="first_name" size="25" value="{$reserve.first_name}"></input>
			</li>

			<li>  
				<label class="required">Last Name:<em>*</em></label>
				<input type="text" name="last_name" size="25" value="{$reserve.last_name}">
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
				{html_options name=location options=$locations selected=$reserve.location}
			  </li>
			  <li>
			    <label class="required">Room Number:<em>*</em></label>
			    <input type="text" name="room" size="5" value="{$reserve.room}">
			  </li>
			  <li>
			   <label>Purpose, Details or Comments:</label>
			   <textarea  name="comments" rows="5" cols="40">{$reserve.comments}</textarea>
			  </li>
			  <li>
				<label class="required">Event Start:<em>*</em></label>
				<input id="startdate" type="text" name="start_date" readonly="true" 
				value="{$reserve.start_date}"> at 
				{html_options name=starthour options=$hours selected=$reserve.starthour}
				:
				{html_options name=startminute options=$minutes|string_format:"%02d" selected=$reserve.startminute}
				
				-
				{html_options name=startampm options=$ampm selected=$reserve.startampm}
			  </li>
			  <li>
				<label class="required">Event End:<em>*</em></label>
				<input id="enddate" type="text" name="end_date" readonly="true" value="{$reserve.end_date}"> at
				{html_options name=endhour options=$hours selected=$reserve.endhour}
				
				:
				{html_options name=endminute options=$minutes|string_format:"%02d" selected=$reserve.endminute}
				-
				{html_options name=endampm options=$ampm selected=$reserve.startampm}
			   </li>
			   <li>
			<input type="radio" id="equipment" name="radio" value="equipment" checked="true"/>I will pick up and return the equipment to the learning Commons Information Desk in Lamson Library
			  </li>
			  <li>
				<input type="radio" id="sponsored" name="radio" value="sponsored" />I will need the Classrom Technology Staff to deliver and retrieve the equipment at the location specified
			  </li>
			  
	{/if}
				<li>
			<em>*required  </em><input type="checkbox" name="agreement"> I agree, as the individual requesting this equipment, to the terms of the <a href="{$PHP.BASE_URL}/reserve/agreement">Equipment Reservation Policy</a>.
			</li>
			<li class="form-actions">
			    <input type="Submit" name="Submit_event" value="Proceed to Equipment Choice">
			  </li>
 	 </ul>
	</form>

{/box}
