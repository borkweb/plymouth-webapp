{box size=8 title=$collection_title}
<table class="grid">
	<thead>
		<tr>
			<th>{$request_title}</th>
			{if 'saus' == $route || 'districts' == $route || 'school-types' == $route || 'school-approval-levels' == $route}
			<th>Schools</th>
			{/if}
			{if 'saus' == $route}
			<th>Constituents</th>
			{/if}
		</tr>
	</thead>
	<tbody>
	{foreach from=$collection item=item}
		<tr>
			<td><a href="{$PHP.BASE_URL}/admin/{$route}/{$item->id}" title="View/Edit {$item->name}">{$item->name}</a></td>
			{if 'saus' == $route || 'districts' == $route || 'school-types' == $route || 'school-approval-levels' == $route}
			<td class="center">
				{$item->schools()->count()}
			</td>
			{/if}
			{if 'saus' == $route}
			<td class="center">
				{$item->constituents()->count()}
			</td>
			{/if}
		</tr>
	{/foreach}
	</tbody>
</table>
{/box}

{box size=8 title="Add `$request_title`"}
	{include file="form.tpl" action="Add" what=$request_title edit=true model=$model}
{/box}
