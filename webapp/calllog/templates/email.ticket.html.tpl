{if $caller.username == $call.call_log_username}{assign var='user_submitted' value=true}{/if}
<style>
{literal}
#message{ border:1px solid #ccc; font-family: Arial, Helvetica, Sans-Serif; }
#header, #body, #history, #footer{padding: 0.5em;}
#header{background: #fff;border-bottom: 1px solid #ccc;}
#header h1{ font-size: 1.4em; margin: 0; }
#history ul, #header ul{ margin: 0.3em 0 0 0; padding: 0; }
#history li, #header li{ clear: left; list-style-type: none; margin: 0 0 0.3em 0; }
#history label, #header label{ color: #999; }
#ticket-history li{ border-bottom: 1px solid #ddd; padding: 0.5em 0 1em 0; }
#ticket-history .history-header li{ border-bottom: none; margin-bottom: 0.3em; padding: 0; }
#ticket-num{ float:right; } 
#body{background: #fff;border-bottom: 1px solid #ccc;}
#history{background: #fff;border-top: 3px solid #f5f5f5;border-bottom: 1px solid #ccc;}
#history h2{font-size: 1.2em;margin: 0 0 0.5em 0;}
#footer{ background: #eee; color: #666; font-size: 0.8em; }
.clear{font-size: 0px; height: 0px; visibility: hidden; clear: both;}
.status-high{color: #cd0000;}
.status-medium{color: #ffff7e;}
.status-pending{color: #5da3ff;}
.status-delayed{color: #c0c0c0;}
.status-inprogress{color: #bd78c2;}
.status-install{color: #69CF58;}
.status-upgrade{color: #e88523;}
.status-collision{color: #208ec1;}
.status-,.status-normal{color: #eeffee;}
{/literal}
</style>
<div id="message">
	<div id="header">
		{capture name="type"}{if $is_caller}support{else}calllog{/if}{/capture}
		<div id="ticket-num">Ticket #<a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">{$call.call_id}</a></div>
		<h1>({$call.call_status|capitalize}) {$call.title|default:"Support Ticket #`$call.call_id`"}{if $call.call_priority!='normal'} [<span class="status-{$call.call_priority}">{$call.call_priority|@strtoupper}</span>]{/if}</h1>
		<ul style="float:left;width: 400px;">
			{if !$is_caller}
			{if $current.logger}
			<li><label>Updated by:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$current.logger]->username|default:"`$pcache[$current.logger]->wp_id`"}">{$pcache[$current.logger]->formatName('f m l')}</a> ({$pcache[$current.logger]->username|default:"`$pcache[$current.logger]->wp_id`"})</li>
			{/if}
			{else}
			{if $current.logger}
			<li><label>Updated by:</label> {$pcache[$current.logger]->formatName('f')}</li>
			{/if}
			{/if}
			{if $current.update_date}
			<li><label>Updated:</label> {$current.update_date|date_format:"%b %e, %Y %r"}</li>
			{/if}
			{if !$is_caller}
			{if $current.assigned_to}
			<li><label>Assigned to:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$current.assigned_to]->username|default:"`$pcache[$current.assigned_to]->wp_id`"}">{$pcache[$current.assigned_to]->formatName('f m l')}</a> ({$pcache[$current.assigned_to]->username|default:"`$pcache[$current.assigned_to]->wp_id`"})</li>
			{/if}
			{if $current.group_name}
			<li><label>Group:</label> {$current.group_name}</li>
			{/if}
			{/if}
			{if $call.feelings}<li><label>Feelings:</label> {$call.feelings}</li>{/if}
		</ul>
		{if !$is_caller}
		<ul style="float:left;">
			<li><label>Original Ticket Date:</label> {$call.call_date|date_format:"%b %e, %Y %r"}</li>
			<li>
				<label>Caller:</label>
				<a href="http://go.plymouth.edu/ape/{$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"}">{$pcache[$caller_id]->formatName('f m l')}</a> ({$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"})
			</li>
			{if $caller.dept}<li><label>Caller Department:</label> {$caller.dept}</li>{/if}
			{if $caller.location}<li><label>Caller Location:</label> {$caller.location}</li>{/if}
			{if $caller.phone_number}<li><label>Caller Phone Number:</label> {$caller.phone_number}</li>{/if}
		</ul>
		{/if}
		<div class="clear"></div>
	</div>
	<div id="body">
		{$current.comments|default:"<em>No details entered</em>"|nl2br}
		<br><br>
		{if $call.call_status == 'closed'}
		<strong>This ticket has been closed.  View the ticket <a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">here</a>.</strong>
		{else}
		<a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">Respond/Reply to this Ticket</a>
		{/if}
	</div>
	{if $history}
	<div id="history">
		<h2>Ticket History</h2>
		<ul id="ticket-history">
		{foreach from=$history item=item}
			<li>
				<ul class="history-header">
					<li><label>Updated by:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$item.logger]->username|default:"`$pcache[$item.logger]->wp_id`"}">{$pcache[$item.logger]->formatName('f m l')}</a> ({$pcache[$item.logger]->username|default:"`$pcache[$item.logger]->wp_id`"})</li>
					<li><label>Updated:</label> {$item.update_date|date_format:"%b %e, %Y %r"}</li>
					{if $item.assigned_to}
					<li><label>Assigned to:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$item.assigned_to]->username|default:"`$pcache[$item.assigned_to]->wp_id`"}">{$pcache[$item.assigned_to]->formatName('f m l')}</a> ({$pcache[$item.assigned_to]->username|default:"`$pcache[$item.assigned_to]->wp_id`"})</li>
					{/if}
					{if $item.group_name}
					<li><label>Group:</label> {$item.group_name}</li>
					{/if}
				</ul>
				{$item.comments|default:"<em>No details entered</em>"|nl2br}
			</li>
		{/foreach}
		</ul>
	</div>
	{/if}
	<div id="footer">
		You are receiving this email because you are attached to this Plymouth State University support ticket{if !$is_caller} or its assigned group{/if}.
		<br><br>
		If you wish to respond or add to this ticket, please do so <a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">here</a>.
		{if !$is_caller}
		<br><br>
		This ticket is attached to <a href="http://go.plymouth.edu/ape/{$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"}">{$pcache[$caller_id]->formatName('f m l')}</a>.
		{/if}
	</div>
</div>
