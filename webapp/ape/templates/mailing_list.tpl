<div class="block">
	<h1>Campus Mailing Lists</h1>
	<p>View the members of staff mailing lists on campus.</p>
	<form action="{$PHP.BASE_URL}/actions/view-list.php" method="get">
	<select name="list">
	{strip}
		{foreach from=$lists item=list}
			<option value="{$list.attribute}"
				{if $mailing_list.attribute eq $list.attribute}
					selected="selected"
				{/if}
			>{$list.name}</option>
		{/foreach}
	{/strip}
	</select>
	<input type="submit" value="View">
	</form>
</div>
{if $mailing_list}
<div class="block">
	<h1>{$mailing_list.name}</h1>
	<p>Manually-assigned list members are displayed below.</p>
	{if $list_members}
		<table class="listserv">
			<thead>
				<tr>
					<th class="name">Name</th>
					<th class="origin">Origin</th>
					<th class="start">Start</th>
					<th class="end">End</th>
					<th class="actions">Actions</th>
				</tr>
			</thead>
			{foreach from=$list_members item=member}
			<tr>
				{assign var=rowspan value=$member|@count}
				<td rowspan="{$rowspan}" class="name">
					<a href="{$PHP.BASE_URL}/user/{$member[0].pid}">{$member[0].last_name}, {$member[0].first_name} {$member[0].middle_name}</a>
				</td>

				{foreach from=$member item=attribute}
					{if $smarty.foreach.index > 0}
					<tr>
					{/if}
					<td class="origin">{$attribute.origin_attribute} ({$attribute.origin_id})</td>
					<td class="start">{$attribute.origin_start|date_format:'%B %d, %Y'}</td>
					<td class="end">{$attribute.origin_end}</td>
					<td class="actions"><a href="{$PHP.BASE_URL}/actions/idm.php?pidm={$attribute.pid}&amp;action=remove&amp;id={$attribute.id}">remove</a></td>
					</tr>
				{/foreach}
			{/foreach}
		</table>
	{else}
		<p>There are no manually-assigned users in this mailing list. They may be added through the user detail page via APE.</p>
	{/if}
</div>
{/if}
