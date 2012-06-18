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
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th><a href="?sort_by=call_updated" title="Activity">{icon id=clock size=tiny flat=true}</a></th>
						<th><a href="?sort_by=call_date">Ticket</a></th>
						<th><a href="?sort_by=caller_last_name">Caller</a></th>
						<th><a href="?sort_by=call_priority">Priority</a></th>
						<th><a href="?sort_by=its_assigned_group ASC, tlc_assigned_to">Assigned To</a></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$calls item=row}
					<tr class="call {foreach from=$row.assigned_to item=assignee}assigned-to_{$assignee} {if $assignee == $smarty.session.username}assigned-to_me{/if} {/foreach}">
						{capture name="info"}
							Last update: {$row.date_assigned} @ {$row.time_assigned}<br>
							Opened: {$row.call_date} @ {$row.call_time}
						{/capture}
						<td class="call-age-status activity-age-status-{$row.activity_age_status}" title="Activity" data-content="{$smarty.capture.info}">&bull;</td>
						<td class="call-id muted">#{$row.call_id}</td>
						<td>
							<div class="call-title">{$row.call_title}</div>
							<div>
								<a href="{$PHP.BASE_URL}/ticket/{$row.call_id}/{if $find_in}in/{$find_in}/{/if}" class="view">{$row.name_full} <em>({$row.caller_username})</em></a> 
							</div>
							<div class="summary">
								{$row.call_summary}
								- <span class="activity-age-status-{$row.activity_age_status}">Updated by {$row.updated_by} {$row.call_activity_diff} ago</span>
							</div>
						</td>
						<td class="priority-status status-{$row.call_priority}">{$row.call_priority}{$row.feelings_face}</td>
						<td class="assignees">
							<ul class="unstyled">
							{foreach name=assignees from=$row.assigned_to item=assignee key=key}
							<li class="{if ! is_numeric( $key )}{$key}{/if} {if $assignee == $smarty.session.username}me{/if}">{$assignee}</li>
							{/foreach}
							</ul>
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<small class="average-open-time">Average Open Call Time: {$average_open_call_time}</small>
		</div>
		{/if}
	{/box}
{/col}
