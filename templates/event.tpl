<script>
var currentTime = new Date()
$(function(){
	$( "#startdate" ).datepicker({ minDate: currentTime });
	$( "#enddate" ).datepicker({ minDate: currentTime });
});
</script>
<form class="label-left" name ="event_request" method="POST">
          <ul>
            <li>
              <h4>Event Information: </h4>
            </li>
            <li>
              <label class="required">Event Title or Course Number and Section:<em>*</em></label>
              <input type="text" name="title" size="25">
            </li>
            <li>
              <label class="required">Location:<em>*</em></label>
              <select name="location" value="Please select a location">
				<option>Please select a location</option>
				<option>Lamson</option>
				<option>Hyde</option>

		    </select>
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
		  	<input id="startdate" type="text" name="start_date"> at <input type="text" name="start_time">
		  </li>
		  <li>
		  	<label class="required">Event End:<em>*</em></label>
		  	<input id="enddate" type="text" name="end_date"> at <input type="text" name="end_time">
		  </li>
            <li>
		   <div id="radio">
		  	<input type="radio" id="equipment" name="radio" />I will pick up and return the equipment to the learning Commons Information Desk in Lamson Library
		 <br>
			<input type="radio" id="sponsored" name="radio" />I will need the Classrom Technology Staff to deliver and retrieve the equipment at the location specified

			</div>
		  </li>
		  <li>
              <input type="Submit" name="submit" value="Next Step">
            </li>
  </ul>
</form>
