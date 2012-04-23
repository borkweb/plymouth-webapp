{*
	messages.tpl -- display a configurable list of messages.
	make sure you escape the text first, as it's not done here.
*}
{if $msg_messages}
	<script type="text/javascript">
	$(document).ready(function(){ldelim}
		{foreach from=$msg_messages item=message}
			$.jGrowl("{$message|escape:'javascript'}", {ldelim}sticky:true, theme: "{$msg_class}"{rdelim});
		{/foreach}
	{rdelim});
	</script>
	<noscript>
		<ul>
		{foreach from=$msg_messages item=message}
			<li class="{$msg_class}">{$message}</li>
		{/foreach}
		</ul>
	</noscript>
{/if}
