{foreach from=$collection item=gate_system}
	{capture assign=title}
		<a href="{$PHP.BASE_URL}/admin/gate-system/{$gate_system->id}" title="View/Edit {$gate_system->name}">{$gate_system->name}</a>
	{/capture}
	{capture assign=secondary_title}
		<a href="{$PHP.BASE_URL}/admin/gate-system/{$gate_system->id}" title="View/Edit {$gate_system->name}" class="btn">Edit</a>
	{/capture}
	{box size=8 title=$title title_size=6 secondary_title=$secondary_title}
			<ul>
			{foreach from=$gate_system->gates() item=gate}
				<li><a href="{$PHP.BASE_URL}/admin/gate-system/{$gate_system->id}" title="View/Edit {$gate_system->name}">{$gate->name}</a></li>
			{/foreach}
			</ul>
	{/box}
{/foreach}
