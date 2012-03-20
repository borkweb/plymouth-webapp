<nav id="webapp-nav">
  <div class="container">
  </div>
</nav>

{include file='status.tpl'}
{box size="16" title="Confirmation"}
<form method="POST" action="{$PHP.BASE_URL}/reserve/success">
	<h1>Your request has not been sent yet!</h1>
	<p>Please make sure the information you have provided below is complete and accurate. Please 'Submit' below and your request will be processed for consideration. You will recieve a copy of your request via email. Classroom Technology does not recieve any information about this loan request until you press the Submit button below.</p>

	<ul class="clean">
		<h2>Your/Submitter Contact Information</h2>
		<li><strong>Name: </strong>{$reserve.submit_first_name} {$reserve.submit_last_name}</li>

		<h2>Event Contact Information</h2>
		<li><strong>Name: </strong>{$reserve.first_name} {$reserve.last_name}</li>
		<li><strong>Phone: </strong>{$reserve.phone}</li>

		<h2>Event Information</h2>
		<li><strong>Course Title or Event Name: </strong>{$reserve.title}</li>
		<li><strong>Location: </strong>{$locations[$reserve.location]}</li>
		<li><strong>Start Date and Time: </strong>{$reserve.start_date} at {$reserve.start_time}</li>
		<li><strong>End Date and Time: </strong>{$reserve.end_date} at {$reserve.end_time}</li>
		<li><strong>Pickup/Dropoff Method: </strong>
			{if $reserve.reserve_type == "equipment"}
				I will pickup/dropoff at the helpdesk.
			{else}
				The CTS 
			{/if}
		</li>
		<li><strong>Comments/Purpose: </strong>
			<p>{$reserve.comments}</p>
		</li>
		<h2>Equipment Requested</h2>
			{foreach from=$reserve.equipment item=item key=k}
				<li>{$categories[$item]} - <a href="{$PHP.BASE_URL}/reserve/confirm?remove_id={$k}"><em>Remove</em></a></li>
			{/foreach}
	</ul>

<input type="Submit" name="Submit" value="Submit">
</form>
{/box}
