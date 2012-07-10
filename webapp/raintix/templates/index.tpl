<script type="text/javascript">
$(function(){
	$('#submit-form').submit(function(e) {
		if( $('#alcohol-free:checked').length > 0 && $('#alcohol-possession:checked').length > 0 ) {
			return confirm('Are you SURE you have entered the correct information? NO changes will be allowed after you click OK');
		} else {
			e.preventDefault();	
			alert('You must acknowledge PSU\'s stance on alcohol for the Commencement ceremony before submitting');
		}//end else
	});
});
</script>
{box title="Ticket Sign-Up" class="grid_12 prefix_2 suffix_2"}
{if $now < $start}
<div class="message-container">
<div class="message message-messages">
	Inclement Weather Ticket Sign-Up will begin at 8:00 AM on Monday, February 14th.
</div>
</div>
{elseif $now > $end}
<div class="message-container">
<div class="message message-messages">
	The sign-up period for Inclement Weather Tickets has ended.
</div>
</div>
{elseif !$can_signup}
<div class="message-container">
<div class="message message-errors">
	You are not listed as a senior who has applied and qualified to participate in commencement.  Please contact Brenda Clayton at the Registrar's Office via <a href="mailto:blcayton@plymouth.edu">email</a> or via phone at 603-535-2847.
</div>
</div>
{elseif $smarty.post.who || $signed_up}
	{if $smarty.post.who}
<div class="message-container">
	<div class="message message-successes">
	Thank you, {$person->formatName('f m l')}!
	</div>
	</div>
	You have signed up for {$user_location.quantity} Inclement Weather Ticket{if $user_location.quantity != 1}s{/if} at the location designated by your major. (for more info <a href="{$PHP.BASE_URL}/tickets_by_major.pdf">see here</a>)
	{else}
<div class="message-container">
	<div class="message message-messages">
		You have already signed up for {$user_location.quantity} Inclement Weather Ticket{if $user_location.quantity != 1}s{/if} at the location designated by your major. (for more info <a href="{$PHP.BASE_URL}/tickets_by_major.pdf">see here</a>)
	</div>
	</div>
	{/if}
	Your tickets will be available on Senior's Day.  {if $signed_up}Your reservation included the following:{else}Please print this page as confirmation of your request.{/if}<br /><br />
	<p>
	<strong>Q:</strong> How many tickets would you like to reserve?<br/>
	<strong>A:</strong> {$user_location.quantity}
	</p>
	<p>
	<strong>Q:</strong> Do you or any of your guests require special assistance, such as wheel chair seating, sign language interpretation, etc?<br/>
	<strong>A:</strong> {if $user_location.assistance == 'Y'}Yes{else}No{/if}
	</p>
	<p>
	<strong>Q:</strong> This special assistance request is for:<br/>
	<strong>A:</strong> {if $user_location.who == 'me'}Me{elseif $user_location.who == 'guests'}My Guests{else}Both Me and my Guests{/if}
	</p>
	<p>
	<strong>Q:</strong> How many of your guests do you expect will need accomodations for mobility? <br />
	<strong>A:</strong> {$user_location.mobility}
	</p>
	<p>
	<strong>Q:</strong> Please describe any special assistance that you or your guests require:<br/>
	<strong>A:</strong> {$user_location.details|strip_tags|nl2br}
	</p>

	<p>
		If you have any general questions, please contact Brenda Clayton at the Registrar's Office via <a href="mailto:blcayton@plymouth.edu">email</a> or via phone at 603-535-2847.
	</p>
	<p>
		If you have any questions or would like to discuss any special assistance requirements in detail, please call Tammy Hill, Facilities ADA, at 603-535-2409.  If necessary, you may be contacted for further information.
	</p>
	<strong>Please note: Ticket exchanges are not allowed, all selections are <em>final</em>.</strong>
{else}
	<p>
	You may reserve up to 4 tickets for the building that corresponds to your major. For more information, <a href="{$PHP.BASE_URL}/tickets_by_major.pdf">see here</a>.
	</p>
	<form method="post" id="submit-form" onsubmit="return confirm('Are you SURE you have entered the correct information? NO changes will be allowed after you click OK');">
	<ul>
		<li>
			<label for="num">How many tickets would you like to reserve?</label>
			<select name="num">
				<option value="" selected></option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
			</select>
		</li>
		<li>
			<label for="assistance">Do you or any of your guests require special assistance, such as wheel chair seating, sign language interpretation, etc?</label>
			<select name="assistance">
				<option value="N">No</option>
				<option value="Y">Yes</option>
			</select>
		</li>
		<li>
			<label for="who">This special assistance request is for:</label>
			<select name="who">
				<option value="me">Me</option>
				<option value="guests">My Guests</option>
				<option value="both">Both Me and my Guests</option>
			</select>
		</li>
		<li>
			<label for="mobility">How many of your guests do you expect will need accomodations for mobility?</label>
			<select name="mobility">
				<option value="0">0</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
			</select>
		</li>
		<li>
			<label for="desc">Please describe any special assistance that you or your guests require:</label>
			<textarea name="desc" cols="20" rows="5"></textarea>
			<br/>
			<span style="help" style="display:block;">
				If you have any questions or would like to discuss any special assistance requirements in detail, please call Tammy Hill, Facilities ADA, at 603-535-2409.  If necessary, you may be contacted for further information.
			</span>
			<br/><br/>
		</li>
		<li>
			<input type="checkbox" name="alcohol-free" id="alcohol-free" value="1"/> <label for="alcohol-free" style="display:inline;float:none;font-weight:normal;">I am aware that Commencement is an alcohol-free event.</label><br/>
			<input type="checkbox" name="alcohol-possession" id="alcohol-possession" value="1"/> <label for="alcohol-possession" style="display:inline;float:none;font-weight:normal;">I understand that anyone found to be under the influence or in possession of alcohol may be prohibited from participating in the ceremony. </label>
		</li>
		<li>
			<input type="submit" value="Signup" />
		</li>
	<ul>
	<p>
		If you have any general questions, please contact Brenda Clayton at the Registrar's Office via <a href="mailto:blcayton@plymouth.edu">email</a> or via phone at 603-535-2847.
	</p>
	</form>
{/if}
{/box}
