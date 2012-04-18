{if 'success' == $msg_class}
	{assign var=msg_class value='successes'}
{elseif 'message' == $msg_class}
	{assign var=msg_class value='messages'}
{elseif 'error' == $msg_class}
	{assign var=msg_class value='errors'}
{elseif 'warning' == $msg_class}
	{assign var=msg_class value='warnings'}
{/if}
<div class="message-container">
<div class="message message-{$msg_class}">
	<ul>
		<li>{$content}</li>
	</ul>
</div>
</div>

