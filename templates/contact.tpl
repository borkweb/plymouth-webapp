
<form class="label-left" name ="event_request" method="POST" action="{$PHP.BASE_URL}/reserve/contact">
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
          	<label>Secondary Phone Number(Cell):</label>
          	<input id="phone" type="text" name="secondary_phone" size="14">
          </li>

          <li>
          	<label class="required">Campus Email:<em>*</em></label>
          	<input type="email" value="{$user->wp_email}"> <!-- wp_email -->
		</li>

		<li>
			<input type="Submit" name="Submit_contact" value="Proceed to Event Information">
		</li>
	</ul>
</form>
