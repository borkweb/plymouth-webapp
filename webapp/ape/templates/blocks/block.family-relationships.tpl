<div id="ape_confirmed_relationships" class="ape-section {if $myuser->go_states.ape_confirmed_relationships === '0'}ape-section-hidden{/if}">
<h3>Confirmed Relationships</h3>
<ul class="apedata">
	{assign var='person_wpid' value=$person->wpid}
	{foreach from=$person->myrelationships->get('confirmed') key=wpid item=rels}
	<li>
	{assign var='rel_person' value=$rels->$wpid}
		<h4>
			{$rel_person->data.person->formatName('f m l')} (<a href="{$PHP.BASE_URL}/user/family/{$wpid}">{$wpid}</a>) Has Been Granted:
		</h4>
		<ul>
		{foreach from=$rels->$person_wpid->grants() item=perm}
			<li>{$perm->permission->name} (granted {$perm->date_granted|date_format})</li>
		{foreachelse}
			<li> No grants assigned </li>
		{/foreach}
		</ul>
	</li>
	{/foreach}
</ul>
</div>
<div id="ape_unconfirmed_relationships" class="ape-section {if $myuser->go_states.ape_unconfirmed_relationships === '0'}ape-section-hidden{/if}">
<h3>Pending Relationships</h3>
<ul class="apedata">
	{foreach from=$person->myrelationships->get('pending') key=wpid item=rels}
	<li>
		{assign var='rel_person' value=$rels->$wpid}
		<h4>
			{$rel_person->data.person->formatName('f m l')} (<a href="{$PHP.BASE_URL}/user/family/{$wpid}/{$rels->id}">{$wpid}</a>) Has Been Granted:
		</h4>
		<ul>
		{foreach from=$rels->$person_wpid->grants() item=perm}
			<li>{$perm->permission->name}</li>
		{foreachelse}
			<li> No grants assigned </li>
		{/foreach}
		</ul>
	</li>
{/foreach}
</ul>
</div>
