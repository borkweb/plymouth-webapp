<ul class="history">
{foreach from=$history item=call_history}
<li id="history-{$call_history.id}" class="update">
	<ul class="update-meta">
		<li class="pull-right"><label class="muted inline">Assigned To:</label> {$call_history.tlc_assigned_to}</li>
		<li><label class="muted inline">Updated by:</label> <a href="{$PHP.BASE_URL}/ticket/{$call_id}/#history-{$call_history.id}">{$call_history.updated_by} on {$call_history.date_assigned} @ {$call_history.time_assigned}</a></li>
		<li><label class="muted inline">Call Info:</label> <span class="priority-status status-{$call_history.call_priority}">{$call_history.call_priority}</span> ({$call_history.call_status})</li>
	</ul>

	<div class="update-details">{$call_history.comments}</div>
</li>
{/foreach}
