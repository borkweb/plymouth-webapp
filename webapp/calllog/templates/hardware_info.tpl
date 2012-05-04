<div id="hardwareInnerDiv">
	{if count($person->hardware)}
		<table width="95%">
		<tr>
			<th>Comp Name</th>
			<th>Mac Address</th>
			<th>IP Address</th>
		</tr>
		{foreach from=$person->hardware item=hardware}
			<tr>
			<td>{$hardware.computer_name}</td>
			<td>{$hardware.mac_address}</td>
			<td>{$hardware.ip_address}</td>
			</tr>
		{/foreach}
		</table>
	{else}
		<p align="center">This user does not have any associated hardware.</p>
	{/if}

	<div align="center"><a href="/webapp/ape/hardware/u/{$person->username}/"><img src="https://www.plymouth.edu/images/icons/16x16/emotes/face-monkey.png" style="vertical-align: middle;"/> Add or Edit Hardware</a></div>
</div>
