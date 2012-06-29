{PSU_JS src="/webapp/training-tracker/js/admin.js"}

{box size="16" title="Table Of Fate"}
	{* Used by the confirmation box *}
	<div class="ui-dialog-content" id="confirmation" title="Are you sure?" >
		<span class="popup_text"></span>
	</div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Name</th>
				<th>Call log permission</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$staff item=person}
				<tr class="person" id="person_{$person->wpid}">
					<td class="name">{$person->person()->formatname("f l")}</td>
					<td class="permission">{$person->permission}</td>
					<td><button data-wpid="{$person->wpid}" class="promote btn btn-success">Promote</button></td>
					<td><button data-wpid="{$person->wpid}" class="demote btn btn-danger">Demote</button></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/box}
