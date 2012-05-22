{capture name="search_type}
	<select name="search_type" class="search_type"> 
		<option value="all" {if $smarty.get.search_string == 'all'}selected{/if}>Everybody</option>
		<option value="wp_id" {if $smarty.get.search_string == 'wp_id'}selected{/if}>WordPress/Family Portal</option>
		<option value="ticket" {if $smarty.get.search_string == 'ticket'}selected{/if}>Ticket Number</option>
		<option value="computer" {if $smarty.get.search_string == 'computer'}selected{/if}>Computer Name</option>
		<option value="mac" {if $smarty.get.search_string == 'mac'}selected{/if}>MAC Address</option>
		<option value="ip" {if $smarty.get.search_string == 'ip'}selected{/if}>IP Address</option>
		<option value="closed" {if $smarty.get.search_string == 'closed'}selected{/if}>Closed Calls</option>
		<option value="user" {if $smarty.get.search_string == 'user'}selected{/if}>Call Log User</option>
	</select>
{/capture}
<form method="get" action="{$PHP.BASE_URL}" class="search">
	{if $search_body}
		<ul>
			<li>
				{$smarty.capture.search_type}
			</li>
			<li>
				<input type="search" size="19" class="search_string" name="search_string" value="{$smarty.get.search_string}{$option}"/>
			</li>
			<li class="form-actions">
				<button class="btn {$button_class}">Search &raquo;</button>
			</li>
		</ul>
	{else}
		{$smarty.capture.search_type}
		<input type="search" size="19" id="search_string" name="search_string" onKeyDown="javascript: keyCheck(event);" value="{$smarty.get.search_string}{$option}"/>
		<button class="btn {$button_class}">Search &raquo;</button>
	{/if}
</form>
