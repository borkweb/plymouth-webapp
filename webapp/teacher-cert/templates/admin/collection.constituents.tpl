{box size=10 title=$collection_title}
<table class="grid">
	<thead>
		<tr>
			<th>Last Name</th>
			<th>First Name</th>
			<th>Middle Name</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$collection item=item}
		<tr>
			<td><a href="{$PHP.BASE_URL}/admin/{$route}/{$item->id}" title="View/Edit {$item->last_name}, {$item->first_name} {$item->mi}">{$item->last_name}</a></td>
			<td><a href="{$PHP.BASE_URL}/admin/{$route}/{$item->id}" title="View/Edit {$item->last_name}, {$item->first_name} {$item->mi}">{$item->first_name}</a></td>
			<td><a href="{$PHP.BASE_URL}/admin/{$route}/{$item->id}" title="View/Edit {$item->last_name}, {$item->first_name} {$item->mi}">{$item->mi}</a></td>
		</tr>
	{/foreach}
	</tbody>
</table>
{/box}

{box size=6 title="Add `$request_title`"}
	{include file="form.tpl" action="Add" what=$request_title edit=true model=$model}
{/box}
