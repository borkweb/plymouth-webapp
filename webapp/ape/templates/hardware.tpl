{PSU_JS src="/includes/js/jquery-plugins/jquery.jeditable.js"}
{capture name="title"}Hardware Information for <a href="{$PHP.BASE_URL}/user/{$user->username}">{$user->username}</a>{/capture}
{box title=$smarty.capture.title class="hardware"}
	{if count($user->hardware)}
		<table class="grid">
			<tr>
				<th>MAC Address</th>
				<th>Computer name</th>
				<th>Comments</th>
				<th>Actions</th>
			</tr>
			{foreach from=$user->hardware key=id item=device}
				<tr>
					<td {if $device.mac_count > 1} class="duplicate"{/if}><span id="mac-{$id}" class="edit mac-address">{$device.mac_address}</span></td>
					<td {if !empty($device.computer_name) && $device.name_count > 1} class="duplicate"{/if}><span id="name-{$id}" class="edit computer-name">{$device.computer_name}</span></td>
					<td><span id="comments-{$id}" class="edit comments">{$device.comments}</span></td>
					<td><a class="delete" href="{$PHP.BASE_URL}/actions/hardware-delete.php?pidm={$user->pidm}&amp;id={$id}">delete</a></td>
				</tr>
			{/foreach}
		</table>

		<small>Click to edit. "Enter" to save. Duplicate entries marked in <span class="duplicate">red</span>.</small>
	{else}
		<p>No hardware has been associated with this user.</p>
	{/if}

	{if $AUTHZ.permission.ape_hardware}
	<h3>Add Hardware</h3>
	<form action="{$PHP.BASE_URL}/actions/hardware-save.php" method="post">
		<input type="hidden" name="pidm" value="{$user->pidm}">
		<ul>
			<li><label>MAC address:</label> <input name="mac" type="text" value=""></li>
			<li><label>Computer name:</label> <input name="name" type="text" value=""></li>
			<li><label>Comments:</label> <input name="comments" type="text" value=""></li>
			<li><label>&nbsp;</label> <input type="submit" value="Add Hardware"></li>
		</ul>
	</form>
	{/if}
{/box}
