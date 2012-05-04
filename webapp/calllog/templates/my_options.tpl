<!-- BEGIN: main -->
	<h1 align="center">Search Settings</h1>

	<form name="my_options_search" id="my_options_search" method="POST" action="?action=search_setting">
	<div style="text-align:center;">
		Use Rank Based Searching? <input type="radio" name="search_setting" value="full" {search_setting_full}/> Yes <input type="radio" name="search_setting" value="split" {search_setting_split}/> No <button type="submit">Save</button>
	</div>
	</form>
	
	<h1 align="center">Subscribe To Groups</h1>

	<form name="my_options_group" id="my_options_group" method="POST">
	<table width="75%" align="center" id="my_options_table">
	<tr>
	<th>Group Name</th>
	<th width="10%">Not Subscribed</th>
	<th width="10%">Show Group</th>
	<th width="10%">Show &amp; Email</th>
	</tr>
	<!-- BEGIN: group_listing -->
	<tr>
	<td style="border: solid 1px #E3BEAC;">{group_listing.subgroupName}</td>
	<td style="border: solid 1px #E3BEAC;" align="center"><input type="radio" name="group[{group_listing.itsgroupid}]" id="{group_listing.itsgroupid}" class="radio" value="0" {0_is_checked}></td>
	<td style="border: solid 1px #E3BEAC;" align="center"><input type="radio" name="group[{group_listing.itsgroupid}]" id="{group_listing.itsgroupid}" class="radio" value="1" {1_is_checked}></td>
	<td style="border: solid 1px #E3BEAC;" align="center"><input type="radio" name="group[{group_listing.itsgroupid}]" id="{group_listing.itsgroupid}" class="radio" value="2" {2_is_checked}></td>
	</tr>
	<!-- END: group_listing -->
	<tr><th colspan="4" align="center"><input type="submit" name="my_options_page" id="my_options_page" value="Apply Changes" class="action"></th></tr>
	</table>
	</form>
<!-- END: main -->
