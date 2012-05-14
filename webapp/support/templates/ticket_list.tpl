<tr>
	<td><a href="ticket/{$call.call_id}">{$call.call_id}</a></td>
	<td>
		{if $call.original_submitter == $smarty.session.username}You{else}ITS{/if}
		<br/>
		<small style="color: #555;">{$call.call_date|date_format:"%b %e, %Y <br/>@ %r"}</small>
	</td>
	<td {if $call.status == 'open' && $call.tlc_assigned_to == $smarty.session.username && $call.updated_by != $call.tlc_assigned_to}class="action-required"{/if}>
		<a href="{$PHP.BASE_URL}/ticket/{$call.call_id}">{strip}
		{if $call.status == 'open' && $call.tlc_assigned_to == $smarty.session.username && $call.updated_by != $call.tlc_assigned_to}
			Action Required
		{elseif $call.status == 'open'}
			Pending
		{else}
			Closed
		{/if}
		{/strip}</a>
	</td>
	<td>
		{if $call.tlc_assigned_to == $smarty.session.username}
			{$call.comments|nl2br}
		{else}
			{if $call.first_assigned_to == $smarty.session.username || $call.original_submitter == $smarty.session.username}
				{$call.original_comment|nl2br}
			{else}
				<em>Protected</em>
			{/if}
		{/if}
	</td>
</tr>
