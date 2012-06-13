<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
{if $caller.username == $call.call_log_username}{assign var='user_submitted' value=true}{/if}
<style type="text/css">
{assign var=ul_plain value="list-style-type: none;padding: 0;margin: 0 0 0.3em 0;"}
{literal}
body {
	font-family: Arial, Helvetica, sans-serif;
}
td {
	padding: 0.5em;
}
#header, #body, #history, #footer{padding: 0.5em;}
#header td{background: #fff;border-bottom: 1px solid #ccc;}
#header h1{ font-size: 1.4em; margin: 0; }
#history ul, #header ul{ margin: 0.3em 0 0 0; padding: 0; }
#history li, #header li{ clear: left; list-style-type: none; margin: 0 0 0.3em 0; }
.ticket-history li{ border-bottom: 1px solid #ddd; padding: 0.5em 0 1em 0; }
.ticket-history .history-header li{ border-bottom: none; margin-bottom: 0.3em; padding: 0; }
#ticket-num{ float:right; } 
#body td{background: #fff;border-bottom: 1px solid #ccc;}
#history td{background: #fff;border-top: 3px solid #f5f5f5;border-bottom: 1px solid #ccc;}
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
</head>
<body bgcolor="#FFFFFF" bottommargin="0" topmargin="0" leftmargin="10" rightmargin="10" text="#000000">
<br>
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="backgroundTable" style="border: 1px solid #cccccc;">
	<tr id="header">
		<td style="padding: 0.5em;border-bottom: 1px solid #cccccc;">
		{capture name="type"}{if $is_caller}support{else}calllog{/if}{/capture}
		<div id="ticket-num" style="float:right">Ticket #<a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">{$call.call_id}</a></div>
		<h1 style="margin: 0 0 0.5em 0;">({$call.call_status|capitalize}) {$call.title|default:"Support Ticket #`$call.call_id`"}{if $call.call_priority!='normal'} [<span class="status-{$call.call_priority}">{$call.call_priority|@strtoupper}</span>]{/if}</h1>
		<ul style="float:left;width: 400px;{$ul_plain}">
			{if !$is_caller}
				{if $current.logger}
					<li style="margin-left:0;"><label style="color:#999999">Updated by:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$current.logger]->username|default:"`$pcache[$current.logger]->wp_id`"}">{$pcache[$current.logger]->formatName('f l')}</a> ({$pcache[$current.logger]->username|default:"`$pcache[$current.logger]->wp_id`"})</li>
				{/if}
			{else}
				{if $current.logger}
					<li style="margin-left:0;"><label style="color:#999999">Updated by:</label> {$pcache[$current.logger]->formatName('f')}</li>
				{/if}
			{/if}
			{if $current.update_date}
				<li style="margin-left:0;"><label style="color:#999999">Updated:</label> {$current.update_date|date_format:"%b %e, %Y %r"}</li>
			{/if}
			{if !$is_caller}
				{if $current.assigned_to}
					<li style="margin-left:0;"><label style="color:#999999">Assigned to:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$current.assigned_to]->username|default:"`$pcache[$current.assigned_to]->wp_id`"}">{$pcache[$current.assigned_to]->formatName('f l')}</a> ({$pcache[$current.assigned_to]->username|default:"`$pcache[$current.assigned_to]->wp_id`"})</li>
				{/if}
				{if $current.group_name}
					<li style="margin-left:0;"><label style="color:#999999">Group:</label> {$current.group_name}</li>
				{/if}
			{/if}
			{if $call.feelings}<li style="margin-left:0;"><label>Feelings:</label> {$call.feelings}</li>{/if}
		</ul>
		{if !$is_caller}
		<ul style="float:left;{$ul_plain}">
			<li style="margin-left:0;"><label style="color:#999999">Original Ticket Date:</label> {$call.call_date|date_format:"%b %e, %Y %r"}</li>
			<li style="margin-left:0;">
				<label style="color:#999999">Caller:</label>
				<a href="http://go.plymouth.edu/ape/{$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"}">{$pcache[$caller_id]->formatName('f l')}</a> ({$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"})
			</li>
			{if $caller.dept}<li style="margin-left:0;"><label style="color:#999999">Caller Department:</label> {$caller.dept}</li>{/if}
			{if $caller.location}<li style="margin-left:0;"><label style="color:#999999">Caller Location:</label> {$caller.location}</li>{/if}
			{if $caller.phone_number}<li style="margin-left:0;"><label style="color:#999999">Caller Phone Number:</label> {$caller.phone_number}</li>{/if}
		</ul>
		{/if}
		<div class="clear"></div>
		</td>
	</tr>
	<tr id="body">
		<td style="border-bottom:1px solid #cccccc;padding:0.5em;">
		{$current.comments|default:"<em>No details entered</em>"|nl2br}
		<br><br>
		{if $call.call_status == 'closed'}
			<strong>This ticket has been closed.  View the ticket <a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">here</a>.</strong>
		{else}
			<a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">Respond/Reply to this Ticket</a>
		{/if}
		</td>
	</tr>
	{if $history}
	<tr id="history">
		<td style="border-bottom:1px solid #cccccc;padding: 0.5em;">
			<h2 style="margin: 0;">Ticket History</h2>
		</td>
	</tr>
	{foreach from=$history item=item}
	<tr class="ticket-history">
		<td style="border-bottom:1px solid #cccccc;padding: 0.5em;">
			<ul style="{$ul_plain}padding-bottom:1em;" class="history-header">
				<li style="margin-left:0;"><label style="color:#999999;">Updated by:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$item.logger]->username|default:"`$pcache[$item.logger]->wp_id`"}">{$pcache[$item.logger]->formatName('f l')}</a> ({$pcache[$item.logger]->username|default:"`$pcache[$item.logger]->wp_id`"})</li>
				<li style="margin-left:0;"><label style="color:#999999;">Updated:</label> {$item.update_date|date_format:"%b %e, %Y %r"}</li>
				{if $item.assigned_to}
					<li style="margin-left:0;"><label style="color:#999999;">Assigned to:</label> <a href="http://go.plymouth.edu/ape/{$pcache[$item.assigned_to]->username|default:"`$pcache[$item.assigned_to]->wp_id`"}">{$pcache[$item.assigned_to]->formatName('f l')}</a> ({$pcache[$item.assigned_to]->username|default:"`$pcache[$item.assigned_to]->wp_id`"})</li>
				{/if}
				{if $item.group_name}
					<li style="margin-left:0;"><label style="color:#999999;">Group:</label> {$item.group_name}</li>
				{/if}
			</ul>
			{$item.comments|htmlentities|default:"<em>No details entered</em>"|nl2br}
		</td>
	</tr>
	{/foreach}
	{/if}
	<tr id="footer">
		<td style="padding:0.5em;">
		You are receiving this email because you are attached to this Plymouth State University support ticket{if !$is_caller} or its assigned group{/if}.
		<br><br>
		If you wish to respond or add to this ticket, please do so <a href="https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}">here</a>.
		{if !$is_caller}
		<br><br>
		This ticket is attached to <a href="http://go.plymouth.edu/ape/{$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"}">{$pcache[$caller_id]->formatName('f l')}</a>.
		{/if}
		</tr>
	</tr>
</table>
</body>
</html>
