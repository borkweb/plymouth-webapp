{capture name="create_ticket_image"}<img src='/images/icons/22x22/apps/accessories-text-editor.png'/>{/capture}
{capture name="open_ticket_image"}<img src='/images/icons/22x22/apps/utilities-system-monitor.png'/>{/capture}
{capture name="closed_ticket_image"}<img src='/images/icons/22x22/mimetypes/text-x-generic.png'/>{/capture}
{box title="`$smarty.capture.open_ticket_image` Open Tickets" class="grid_16" title_size="8" secondary_title="<a href='`$PHP.BASE_URL`/submit/'>`$smarty.capture.create_ticket_image` Create A Ticket</a>"}
{if $open_calls}
{assign var="page_name" value="open_page"}
{assign var="pages" value=$open_calls}
{include file="paging.tpl"}
<table class="grid" width="100%">
	<thead>
		<tr>
			<th>Ticket</th>
			<th width="100">Opened By</th>
			<th>Status</th>
			<th>Details</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$open_calls.items item=call}
		{include file="ticket_list.tpl"}
		{/foreach}
	</tbody>
</table>
{else}
	<em>You do not have any open support tickets.  Would you like to <a href="{$PHP.BASE_URL}/submit/">create one</a>?</em>
{/if}
{/box}
{box title="`$smarty.capture.closed_ticket_image` Closed Tickets" class="grid_16"}
{if $closed_calls}
{assign var="page_name" value="closed_page"}
{assign var="pages" value=$closed_calls}
{include file="paging.tpl"}
<table class="grid" width="100%">
	<thead>
		<tr>
			<th>Ticket</th>
			<th width="100">Opened By</th>
			<th>Status</th>
			<th>Details</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$closed_calls.items item=call}
		{include file="ticket_list.tpl"}
		{/foreach}
	</tbody>
</table>
{else}
	<em>You do not have any tickets closed tickets.</em>
{/if}
{/box}
