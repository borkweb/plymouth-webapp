{box title="" size=16}
	<p>
	{if $target->wpid == $user->wpid}You do{else}The user you're trying to view does{/if} not have any financial aid records for the {$aid_year->aidy_desc}.
	
	{if $user->myrelationships->get('confirmed', 'finaid')}
		Did you mean to view another user's information?
	{elseif $user->myrelationships->get('pending', 'finaid')}
		In addition, you've been granted financial aid view privileges as part of a pending relationship request. Do you need to
		<a href="http://go.plymouth.edu/familychannel">visit Family Access</a> and confirm the relationship?
	{/if}
	</p>

	{include file="block.aid-year-selection.tpl"}
{/box}
