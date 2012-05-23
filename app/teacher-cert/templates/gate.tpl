{capture assign=title}
	<a href="{$PHP.BASE_URL}/gate-system/{$gate_system->slug}/">{$gate_system->name}</a> &raquo; Students in {$gate->name}
{/capture}
{box title=$title size="10"}
	{if count($gate->students()) > 0}
	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>Student</th>
				<th>Gate</th>
				<th>Teaching</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$gate->students() item=student}
			<tr class="psu-lazyload" data-object="person" data-id="{$student->pidm|escape}">
				<td>
				<a href="{$PHP.BASE_URL}/gate-system/{$gate_system->slug}/{$student->student_gate_system_id}"/>
					<span class="lazy-field" data-type="last_name"></span>, <span class="lazy-field" data-type="first_name"></span></a>
					<div class="id">ID: <span class="lazy-field" data-type="id"></span></div>
				</td>
				<td class="center middle">{$gate->name}</td>
				<td class="center middle">{$student->teaching_term}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	{else}
		<p>There are no students in this gate.</p>
	{/if}
{/box}

{box title="Add Student" size="6"}
	{include file="form.student-add.tpl" submit_url="`$PHP.BASE_URL`/gate-system/`$gate_system->slug`/gate/`$gate->slug`"}
{/box}
