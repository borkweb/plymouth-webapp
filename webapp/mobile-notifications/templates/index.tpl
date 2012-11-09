{box size="16" title="PSU Mobile Notifications"}
{if $rave_user}
	<form method="post">
		<table class="grid">
			<thead>
				<tr>
					<th></th>
					<th>Alert</th>
					<th>Description</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$groups item=group}
				<tr>
					<td><input type="checkbox" id="group-{$group.id}" name="group[]" {$group.subscribed} {$group.disabled} value="{$group.id}" /></td>
					<td><label for="group-{$group.id}">{$group.name}</label></td>
					<td>{$group.description|default:'No Description Available'}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
		<input type="submit" value="Update Subscriptions" />
	</form>
{else}
	<p>It appears that you are not currently registered with Plymouth
	State University's Text notification system. Please click the button
	below, and you will be taken to where you can first register your
	mobile phone number before being returned here to subscribe to
	different types of mobile notifications.</p>
	<div style="text-align:center;" id="alert-btn-container">
		<a href="https://go.plymouth.edu/mobileinterrupt" class="btn btn-primary">Register</a>
	</div>
{/if}
{/box}
