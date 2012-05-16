<!-- BEGIN: main -->

	<form name="my_options_search" id="my_options_search" method="POST" action="?action=search_setting">
	<fieldset>
		<legend>Search Settings</legend>
		Use Rank Based Searching? <input type="radio" name="search_setting" value="full" {search_setting_full}/> Yes <input type="radio" name="search_setting" value="split" {search_setting_split}/> No <button type="submit" class="btn">Save</button>
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
			</tr>
		</thead>
		<tbody>
			<!-- BEGIN: group_listing -->
			<tr>
				<td>{group_listing.subgroupName}</td>
				<td align="center"><input type="radio" name="group[{group_listing.itsgroupid}]" id="{group_listing.itsgroupid}" class="radio" value="0" {0_is_checked}></td>
				<td align="center"><input type="radio" name="group[{group_listing.itsgroupid}]" id="{group_listing.itsgroupid}" class="radio" value="1" {1_is_checked}></td>
				<td align="center"><input type="radio" name="group[{group_listing.itsgroupid}]" id="{group_listing.itsgroupid}" class="radio" value="2" {2_is_checked}></td>
			</tr>
			<!-- END: group_listing -->
		</tbody>
		<tfoot>
			<tr><th colspan="4" align="center" class="well"><input type="submit" name="my_options_page" id="my_options_page" value="Apply Changes" class="btn primary"></th></tr>
		</tfoot>
	</table>
	</fieldset>
	</form>
<!-- END: main -->
