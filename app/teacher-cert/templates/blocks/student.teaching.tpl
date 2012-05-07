{box title="Student Teaching"}
	{foreach from=$student_gate_system->schools() item=school}
		<section class="stu-school-wrapper">
			<h1>{$school->parent()->name}</h1>
			<ul>
				<li>
					<strong>Grade:</strong>
					{$school->grade}
				</li>
				<li>
					<strong>Had interview?</strong>
					{if $school->interview_ind == 'Y'}Yes{else}No{/if}
				</li>
				<li>
					<strong>Placement:</strong>
					{$school->placement}
				</li>
			</ul>
			{if $school->notes}
				<h2>Notes</h2>
				<blockquote>
					{$school->notes|escape:'html'|nl2br}
				</blockquote>
			{/if}
			{if count($school->cooperating_teachers())}
			<table class="grid" style="width:100%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Vouchers</th>
					</tr>
				</thead>
				{foreach from=$school->cooperating_teachers() item=teacher}
					<tr>
						<td>
							{$teacher->constituent()->last_name}, {$teacher->constituent()->first_name}
							{if $teacher->association_attribute}
								<br>{$teacher->association_attribute}
							{/if}
						</td>
						<td>
							{$teacher->voucher|default:0} {if $teacher->voucher}({$teacher->voucher_date|date_format}){/if}
						</td>
					</tr>
				{/foreach}
			</table>
			{else}
				<p>No schools have been attached to this student's gate system.</p>
			{/if}
			<a href="{$PHP.BASE_URL}/student-school/{$student_gate_system->id}/edit/{$school->id}" class="btn stu-school-edit">Edit School</a>
		</section>
	{foreachelse}
		<p>There are no schools associated with this student.</p>
	{/foreach}

	<a href="{$PHP.BASE_URL}/student-school/{$student_gate_system->id}/add" class="btn stu-school-add">Add School</a>
{/box}
