{*
	messages.tpl -- display a configurable list of messages.
	make sure you escape the text first, as it's not done here.
*}
<div class="message-container">
<div class="message message-{$msg_class}" {if count($msg_messages) == 0}style="display:none;"{/if}>
	<ul>
	{foreach from=$msg_messages item=message}
		<li>{$message}</li>
	{foreachelse}
		<li style="display:none;"></li>
	{/foreach}
	</ul>
</div>
</div>
