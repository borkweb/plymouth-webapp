{box}
	<form name="my_options_search" id="my_options_search" method="POST" action="?action=search_setting">
	<fieldset>
		<legend>Search Settings</legend>
		Use Rank Based Searching? <input type="radio" name="search_setting" value="full" {if $search_setting == 'full'}checked="checked"{/if}/> Yes <input type="radio" name="search_setting" value="split" {if $search_setting == 'split'}checked="checked"{/if}/> No <button type="submit" class="btn">Save</button>
	</fieldset>
	</form>
	
	<form name="my_options_reminder" id="my_options_reminder" method="POST" action="?action=reminder_setting">
	<fieldset>
		<legend>Daily Open Call Reminder Settings</legend>
		Receive a daily reminder of Open Calls? <input type="radio" name="reminder_setting" value="yes" {if $reminder_setting == 'yes'}checked="checked"{/if}/> Yes <input type="radio" name="reminder_setting" value="no" {if $reminder_setting == 'no'}checked="checked"{/if}/> No <button type="submit" class="btn">Save</button>
	</fieldset>
	</form>

	<form name="my_options_group" id="my_options_group" method="POST">
	<fieldset>
	<legend>Subscribe To Groups</legend>
	<table id="my_options_table" class="grid" width="100%">
		<thead>
			<tr>
				<th>Group Name</th>
				<th>Not Subscribed</th>
				<th>Show Group</th>
				<th>Show &amp; Email</th>
				<th>High Priority</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$groups item=group key=group_id}
			<tr>
				<td>{$group.subgroupName}</td>
				<td align="center"><input type="radio" name="group[{$group_id}]" id="{$group_id}" class="radio" value="0" {if $my_groups.$group_id.option_id == 0}checked="checked"{/if} /></td>
				<td align="center"><input type="radio" name="group[{$group_id}]" id="{$group_id}" class="radio" value="1" {if $my_groups.$group_id.option_id == 1}checked="checked"{/if} /></td>
				<td align="center"><input type="radio" name="group[{$group_id}]" id="{$group_id}" class="radio" value="2" {if $my_groups.$group_id.option_id == 2}checked="checked"{/if} /></td>
				<td align="center"><input type="checkbox" name="high_priority[{$group_id}]" value="1" {if $high_priorities.$group_id}checked="checked"{/if} /></td>
			</tr>
			{/foreach}
		</tbody>
		<tfoot>
			<tr><th colspan="4" align="center" class="well"><input type="submit" name="my_options_page" id="my_options_page" value="Apply Changes" class="btn primary"></th></tr>
		</tfoot>
	</table>
	</fieldset>
	</form>
{/box}