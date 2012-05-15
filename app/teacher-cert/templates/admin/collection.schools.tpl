{* this template does not use the generic admin.collection.tpl due to its more complex nature *}
{col size=10}
	{box size="10" title="Schools"}
	<table class="grid">
		<thead>
			<tr>
				<th>School</th>
				<th>Constituents</th>
				<th>SAU</th>
				<th>District</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$collection item=school}
			<tr title="{if $school->school_type()}{$school->school_type()->name}{/if}">
				<td>
					<a href="{$PHP.BASE_URL}/admin/schools/{$school->id}" title="View/Edit {$school->name}{if $school->school_type()} ({$school->school_type()->name}){/if}">
						{$school->name}
					</a>
				</td>
				<td class="center">
					{$school->cooperating_teachers()->count()}
				</td>
				<td>{if $school->sau()}<a href="{$PHP.BASE_URL}/admin/saus/{$school->sau()->id}">{$school->sau()->name}</a>{/if}</td>
				<td>{if $school->district()}<a href="{$PHP.BASE_URL}/admin/districts/{$school->district()->id}">{$school->district()->name}</a>{/if}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	{/box}
{/col}
{col size=6}
	{box size=6 title="Add School"}
		{include file="form.tpl" action="Add" what="School" edit=true model=$model}
	{/box}
{/col}
