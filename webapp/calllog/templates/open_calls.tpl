{$displayOpenCalls}
{foreach from=$groups item=group}
	<a class="nav_link open_calls" href="calls.html?new_call=passed&amp;action=view_open_calls&amp;option={$group.type}&amp;group={$group.id}&amp;find_type={$group.open_call_type}" title="{$group.title}">
		{$group.my_group_name} (<span id="open_calls_num_rows">{$group.num}</span>)
	</a>
{/foreach}
