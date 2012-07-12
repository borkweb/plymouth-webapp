{foreach from=$announcements item=announcement}
	{box title="Announcement"}
		{$announcement.message}
	{/box}
{/foreach}
{box size="5" title="New Equipment Request"}
	{if $step != NULL}
		You have an <a href="{$PHP.BASE_URL}/reserve/equipment">Unfinished Request</a> - <a href="{$PHP.BASE_URL}/user/delete">Delete this request</a>
	{/if}
		<p class="center"><a class="button" href="{$PHP.BASE_URL}/reserve/new">Start New Loan</a></p>

		<p>This is the ONLY way to request any equipment from Classroom Technology Services. Requests made via this form <strong>must</strong> be submitted 72 hours in advance.</p>
{/box}
{box size ="5" title="Pending Reservations"}
		<p class="center"><a class="button" href="{$PHP.BASE_URL}/history/pending">View Pending</a></p>

		<p>After submitting a request you can view the status of the pending equipment reservation to see if it has been approved and if the equipment you need has been assigned.</p>
{/box}

{box size="5" title="Reservation History"}

		<p class="center"><a class="button" href="{$PHP.BASE_URL}/history/">View History</a></p>

		<p>View your equipment Reservation History to see all of your completed reservations since July 7th, 2012.</p>

{/box}
