{foreach from=$categories item=category }
	{assign var=permission value="ape_checklist_employee_exit_`$category.slug`"}
	{if $AUTHZ.permission.ape_checklist_employee_exit_hr || $AUTHZ.permission.$permission}
	<tr class="category {$category.is_complete}" data-category="{$category.id}">
		<td>
			<input type="checkbox" disabled="disabled" {if $category.is_complete } checked="checked" {/if } />
		</td>
		<td>
			{$category.name}
		</td>
		<td>
			<label>Updated:</label>
			{if $category.updated }
				{$category.updated|date_format}
			{else}
				Never
			{/if}
		</td>
		<td class="reminder-sent">
			<label>Reminder Sent:</label> <span>{if $category.reminder}{$category.reminder.meta_value|date_format}{else}Never{/if}</span>
		</td>
		<td>
			{if $AUTHZ.permission.ape_checklist_employee_exit_hr}
			<a href="{$PHP.BASE_URL}/checklist-admin.html?checklist_id={$checklist.id}&category={$category.slug}&subject={$checklist.pidm}&end_date={$checklist.meta.end_date.meta_value}">Send Reminder</a>
			{/if}
		</td>
	</tr>
	<tr class="category {$category.is_complete} category-items" data-category="{$category.id}">
		<td colspan="4">
			<ul style="margin-left: 25px;">
				{foreach from=$category.items item=item}
				<li>
					<input type="checkbox" disabled="disabled" {if $item.response.response && $item.response.response != 'incomplete' } checked="checked" {/if } />
					<span>{$item.description}</span>
					{if $item.response.notes}
						<div class="notes">
						{$item.response.notes}
						</div>
					{/if}
				</li>
				{/foreach}
			</ul>
		</td>
	</tr>
	{/if}
{/foreach } 
