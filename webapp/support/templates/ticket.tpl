{capture name="status_image"}{strip}
	{if $call_status == 'open'}
		<img src='/images/icons/22x22/apps/utilities-system-monitor.png'/> 
	{else}
		<img src='/images/icons/22x22/mimetypes/text-x-generic.png'/> 
	{/if}
{/strip}{/capture}
{capture name="fancy_status"}{$call_status|capitalize}{/capture}
{box title="`$smarty.capture.status_image` `$smarty.capture.fancy_status` Ticket: `$ticket`" class="grid_16"}
{if $details}
<table class="grid" width="100%">
	<thead>
		<tr>
			<th width="200">Updated By</th>
			<th>Details</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$details item=data}
		<tr>
			<td>
				{$data.updated_by_name}
				<br/>
				<small style="color: #555;">{$data.update_date|date_format:"%b %e, %Y @ %r"}</small>
			</td>
			<td>{if $data.comments}{$data.comments|nl2br}{else}<em>No details provided.</em>{/if}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{else}
<em>This ticket does not have any viewable details at this time.  {if $call_status == 'open'}Please check back later.{/if}</em>
{/if}
{if $details && $call_status != 'closed'}
{capture name="redirect"}{$PHP.BASE_URL}/ticket/{$ticket}{/capture}
<form id="edit_call" action="/webapp/calllog/update_call_details.html?ticket={$ticket}&amp;call_source=support&amp;redirect={$smarty.capture.redirect|@urlencode}" enctype="multipart/form-data" method="post">
{$form}
<br/>
<button type="submit">Update Ticket</button> <input type="checkbox" name="call_status" value="closed"/> Close this ticket <small>(Marks this ticket as complete and will allow no further action)</small>
<div class="clear"></div>
</form>
{/if}
{/box}
