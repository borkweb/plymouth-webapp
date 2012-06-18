{$displayOpenCalls}
{foreach from=$groups item=group}
	<a class="nav_link open_calls" href="{$PHP.BASE_URL}/calls/{$group.type}/" title="{$group.title}">
		{$group.my_group_name} (<span id="open_calls_num_rows">{$group.num}</span>)
	</a>
{/foreach}
