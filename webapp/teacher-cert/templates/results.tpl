{box title="`$gate_system->name` Gate System &raquo; Search Results"}
	<table class="grid">
		<thead>
			<tr>
				<th>Student</th>
				<th>Gate</th>
				<th>Teaching</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$population item=student}
			{assign var=student_gate_system value=$student->gate_systems($student->gate_system_id)}
			<tr>
				<td>
					<a href="{$PHP.BASE_URL}/gate-system/{$gate_system->slug}/{$student->student_gate_system_id}">{$student->person()->formatName('l, f m')}</a>
					<div class="id">ID: {$student->person()->id}</div>
				</td>
				<td class="center">{$student->gate_name}</td>
				<td class="center">{$student->teaching_term}</td>
				<td>
					{if $student_gate_system->active()}
						<strong>Active</strong>
					{else}
						{if $student_gate_system->exit_date}
							<strong>Exit Date:</strong> {$student_gate_system->exit_date}
						{elseif $student_gate_system->complete_date}
							<strong>Complete Date:</strong> {$student_gate_system->complete_date}
						{/if}
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/box}

