{box title="Debug"}
{foreach from=$portal->tabs() item=tab}
	<h3>{$tab->base->name} ({$tab->slug})</h3>

	<ul>
		<li>tab.id = {$tab->id}</li>
		<li>tab.base.name = {$tab->base->name}</li>
		<li>tab.base.id = {$tab->base->id}</li>
	{foreach from=$tab->channels() item=channel}
		<li>{$channel->name} (userchannel.id = {$channel->id})
			<ul>
				<li>channel_id = {$channel->channel_id}</li>
				<li>Meta
					<ul>
						{foreach from=$channel->meta()->get() item=meta}
							<li>{$meta->key} = {$meta->value} (id = {$meta->id|default:'<em>none</em>'}, changed = {$meta->changed|bool2str})</li>
						{/foreach}
					</ul>
				</li>
			</ul>
		</li>
	{/foreach}
	</ul>
{/foreach}
<p>Done.</p>
{/box}
