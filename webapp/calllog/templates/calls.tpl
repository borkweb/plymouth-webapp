{include file="sidebar.tpl"}
{col size=12}
	{capture name="secondary"}
		<a href="{$PHP.BASE_URL}" class="btn">+ New Call</a>
	{/capture}
	{box title="Open Calls &raquo; `$find_type`" title_size=10 secondary_title=$smarty.capture.secondary}
		{if ! $calls}
			There are currently no <em>{$open_call_type}</em> open calls.
		{else}
		<div id="open_calls_main_div">
			<table class="table table-bordered table-striped table-condensed">
				<thead>
					<tr>
						<th><a href="{$PHP.BASE_URL}/calls.html?option={$smarty.get.option}&amp;group={$smarty.get.group}&amp;open_call_type={$smarty.get.open_call_type}&amp;sort_by=call_date">Opened</a></th>
						<th><a href="{$PHP.BASE_URL}/calls.html?option={$smarty.get.option}&amp;group={$smarty.get.group}&amp;open_call_type={$smarty.get.open_call_type}&amp;sort_by=call_updated">Updated</a></th>
						<th><a href="{$PHP.BASE_URL}/calls.html?option={$smarty.get.option}&amp;group={$smarty.get.group}&amp;open_call_type={$smarty.get.open_call_type}&amp;sort_by=caller_last_name">Caller</a></th>
						<th><a href="{$PHP.BASE_URL}/calls.html?option={$smarty.get.option}&amp;group={$smarty.get.group}&amp;open_call_type={$smarty.get.open_call_type}&amp;sort_by=call_priority">Priority</a></th>
						<th><a href="{$PHP.BASE_URL}/calls.html?option={$smarty.get.option}&amp;group={$smarty.get.group}&amp;open_call_type={$smarty.get.open_call_type}&amp;sort_by=its_assigned_group ASC, tlc_assigned_to">Assigned To</a></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$calls item=row}
					<tr class="call">
						<td class="call-age-status-{$row.call_age_status}" style="text-align: center; white-space:nowrap;">{$row.call_date}<br/>{$row.call_time}</td>
						<td class="activity-age-status-{$row.activity_age_status}" style="text-align: center; white-space:nowrap;">{$row.date_assigned}<br/>{$row.time_assigned}</td>
						<td>
							<div class="call-title">{$row.call_title}</div>
							<div>
								<a href="{$PHP.BASE_URL}/ticket/{$row.call_id}/?action=view_open_calls&option={$open_call_option}&group={$group_number}&find_type={$find_type}" class="view">{$row.name_full} <em>({$row.caller_username})</em></a> 
								<span class="fade">[#{$row.call_id}]</span>
							</div>
							<div class="summary">{$row.call_summary}</div>
						</td>
						<td class="priority-status status-{$row.call_priority}">{$row.call_priority}{$row.feelings_face}</td>
						<!-- BEGIN: assigned_open_call -->
						<td style="text-align: center">{$row.assigned_to}</td>
						<!-- END: assigned_open_call -->
					</tr>
					{/foreach}
				</tbody>
			</table>
			<small class="average-open-time">Average Open Call Time: {$average_open_call_time}</small>
		</div>
		{/if}
	{/box}
{/col}
