{if count($gate_system->gates())}
	{box title="`$gate_system->name` Gate System" size="10"}
		<h3>Gates</h3>

		<table class="grid">
			<thead>
				<tr>
					<th>Gate</th>
					<th>Students</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$gate_system->gates() item=gate}
				<tr>
					<td><a href="{$PHP.BASE_URL}/gate-system/{$gate_system->slug}/gate/{$gate->slug}/">{$gate->name}</a></td>
					<td class="alignright">{$gate->student_count()|number_format}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	{/box}

	{box title="Add Student" size="6"}
		{include file="form.student-add.tpl" submit_url="`$PHP.BASE_URL`/gate-system/`$gate_system->slug`"}
	{/box}
{else}
	{box title="`$gate_system->name` Gate System" size=16}
		<p>This gate system does not contain any gates.</p>
	{/box}
{/if}
