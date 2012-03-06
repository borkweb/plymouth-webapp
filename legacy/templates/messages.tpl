{*
	messages.tpl -- display a configurable list of messages.
	make sure you escape the text first, as it's not done here.
*}
<div id="{$msg_class}" class="{$msg_class}">
	<ul>
	{foreach from=$msg_messages item=message}
		<li>{$message}</li>
	{/foreach}
	</ul>
</div>
