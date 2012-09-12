{box title="Clinical Faculty" title_size=6}
	{if count($student_gate_system->clinical_faculty())}
		<ul>
		{foreach from=$student_gate_system->clinical_faculty() item=faculty}
			<li data-stu-clifac-id="{$faculty->id}" data-constituent-id="{$faculty->constituent()->id}">{$faculty->constituent()->last_name}, {$faculty->constituent()->first_name} - (<a class="remove-link" href="{$PHP.BASE_URL}/student-clinical-faculty/{$student_gate_system->id}/remove-clinical_faculty/{$faculty->id}">Remove</a>)</li>
		{/foreach}
		</ul>
	{else}
		<p>There are no faculty associated with this student.</p>
	{/if}

	<div class="stu-faculty-add">
		<h3>Add Clinical Faculty</h3>

		<form id="add-clinical_faculty" action="{$PHP.BASE_URL}/student-clinical-faculty/{$student_gate_system->id}/add-clinical_faculty" method="POST">
			<ul>
				<li>
					<select name="clinical_faculty_id">
						<option></option>
						{foreach from=$teachers item=item}
							<option value="{$item->id}">{$item->last_name}, {$item->first_name} {$item->mi}</option>
						{/foreach}
					</select>
				</li>
				<li class="well">
					<button type="submit">Add</button>	
				</li>
			</ul>
		</form>
	</div>
{/box}
