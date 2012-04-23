{capture name="title"}Accounts Pending Deletion ({$pending|@count}){/capture}

{box title=$smarty.capture.title}
<p>The following accounts have the "pending_deletion" role in Active Directory.</p>

<ul class="bullets">
	{foreach from=$pending item=person}
		<li><a href="{$PHP.BASE_URL}/user/{$person}">{$person}</a></li>
	{/foreach}
</ul>
{/box}
