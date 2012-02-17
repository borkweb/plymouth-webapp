<script type="text/javascript">
jQuery(function($){
	$("#phone").mask("(999) 999-9999");
});
</script>
<form class="label-left" name ="event_request" method="POST">
          <ul>
            <li>
            <h4>Contact Information</h4>
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
              <div class="input-append">
              <input type="email" value="{$user->username}"> <!-- wp_email -->
              <span class="add-on active">@plymouth.edu</span>
            </li>
            <li>
              <h4>Event Information: </h4>
            </li>
            <li>
              <label class="required">Event Title or Course Number and Section:</label>
              <input type="text" name="title" size="25">
            </li>
            <li>
              <label class="required">Room Number:</label>
              <input type="text" name="last_name" size="5">
            </li>
            <li>
             <label>Purpose, Details or Comments:</label>
             <textarea  name="comments" rows="5" cols="40"></textarea>
            </li>
            <li>
              <input type="Submit" name="submit" value="Next Step">
            </li>
  </div>
</form>
