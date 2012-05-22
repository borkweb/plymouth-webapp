{foreach from=$history item=call_history}
<ul id="history-{$call_history.id}">
	<li><label class="inline">Updated by:</label> <a href="{$PHP.BASE_URL}/ticket/{$call_id}/#history-{$call_history.id}">{$call_history.updated_by} on {$call_history.date_assigned} @ {$call_history.time_assigned}</a></li>
	<li><label class="inline">Call Status:</label> {$call_history.call_status}</li>
	<li><label class="inline">Call Priority:</label> {$call_history.call_priority}</li>
	<li><label class="inline">Assigned To:</label> {$call_history.tlc_assigned_to}</li>
	<li><label class="inline">Details:</label> {$call_history.comments}</li>
</ul>
<hr style="width: 75%;" align="left">
{/foreach}
