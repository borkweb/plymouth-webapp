{*
	messages.tpl -- display a configurable list of messages.
	make sure you escape the text first, as it's not done here.
*}
<script type="text/javascript">
{foreach from=$msg_messages item=message}
rfjs.{$msg_class}("{$message|escape:'javascript'}");
{/foreach}
</script>
<noscript>
	<div id="{$msg_class}" class="{$msg_class}">
		<ul>
		{foreach from=$msg_messages item=message}
			<li title="Click to dismiss message" onclick="rf_close_message(this);"><span class="badge"></span>{$message}</li>
		{/foreach}
		</ul>
	</div>
</noscript>
