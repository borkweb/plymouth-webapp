{include file="status.tpl"}
{box size="16" title="Event Information"}
	<form class="label-left" name ="event_info" method="POST" action="{$PHP.BASE_URL}/reserve/event">
          <ul>
				<h4>Contact Information:</h4>
			<li>
				<label class="required">First Name:<em>*</em></label>
				{if $reserve.first_name} 
					{assign var=first_name value=$reserve.first_name}
				{else}
					{assign var=first_name value=$user->first_name}
				{/if}

				<input type="text" name="first_name" size="25" value="{$first_name}"></input>
			</li>

			<li>  
				<label class="required">Last Name:<em>*</em></label>
				{if $reserve.last_name} 
					{assign var=last_name value=$reserve.last_name}
				{else}
					{assign var=last_name value=$user->last_name}
				{/if}
				<input type="text" name="last_name" size="25" value="{$last_name}">
			</li>

			<li>
				<label class="required">Phone Number:<em>*</em></label>
				<input id="phone" type="text" name="phone" size="14" value="{$reserve.phone}">
			</li>
			<li>
				<label class="required">Campus Email:<em>*</em></label>
				{if $reserve.email} 
					{assign var=email value=$reserve.email}
				{else}
					{assign var=email value=$user->wp_email}
				{/if}

				<input type="email" name ="email" value="{$email}"> 
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
				<input id="startdate" class="date-readonly" type="text" name="start_date" readonly="true" 
				value="{$reserve.start_date}"> at 
				{html_options name=starthour options=$hours selected=$reserve.starthour class="time"}
				:
				{html_options name=startminute options=$minutes|string_format:"%02d" selected=$reserve.startminute class="time"}
				
				-
				{html_options name=startampm options=$ampm selected=$reserve.startampm class="time"}
			  </li>
			  <li>
				<label class="required">Event End:<em>*</em></label>
				<input id="enddate" class="date-readonly" type="text" name="end_date" readonly="true" value="{$reserve.end_date}"> at
				{html_options name=endhour options=$hours selected=$reserve.endhour class="time"}
				
				:
				{html_options name=endminute options=$minutes|string_format:"%02d" selected=$reserve.endminute class="time"}
				-
				{html_options name=endampm options=$ampm selected=$reserve.startampm class="time"}
			   </li>
			   <li>
			<input type="radio" id="equipment" name="radio" value="0" {if $reserve.reserve_type == 0} checked="true"{/if}/>I will pick up and return the equipment to the learning Commons Information Desk in Lamson Library
			  </li>
			  <li>
				<input type="radio" id="sponsored" name="radio" value="1" {if $reserve.reserve_type == 1} checked="true"{/if} />I will need the Classrom Technology Staff to deliver and retrieve the equipment at the location specified
			  </li>
			  
				<li>
			<em>*required  </em><input type="checkbox" name="agreement"> I agree, as the individual requesting this equipment, to the terms of the <a href="{$PHP.BASE_URL}/reserve/agreement" target="_blank">Equipment Reservation Policy</a>.
			</li>
			<li class="form-actions">
			    <input type="Submit" name="Submit_event" value="Proceed to Equipment Choice">
			  </li>
 	 </ul>
	</form>

{/box}
