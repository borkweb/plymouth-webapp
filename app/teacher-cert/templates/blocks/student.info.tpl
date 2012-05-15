{capture assign=student_title}
{$student->person()->first_name} {$student->person()->last_name} ({$student->person()->id})
{/capture}
{capture assign=secondary_title}
<a class="ape-link" href="http://go.plymouth.edu/ape/{$student->person()->login_name}" target="_blank"><img src="/images/icons/22x22/emotes/face-monkey.png"/></a>
{/capture}
{box title=$student_title secondary_title=$secondary_title class="student" title_size=7}

	{capture assign=level}{$gate_system->level_code|strtolower}{/capture}
	{capture assign=level_inactive}{$level}_inactive{/capture}

	{if $student->person()->student->$level}
		{assign var="student_data" value=$student->person()->student->$level}	
	{else}
		{assign var="inactive" value=TRUE}
		{assign var="student_data" value=$student->person()->student->$level_inactive}	
	{/if}

	{if $inactive}
		{message type="message"}
			This student does not have an active student record for the {$level|strtoupper} level.
			{if $student_data}
			Displaying the inactive student record instead.
			{/if}
		{/message}
	{/if}
	<ul class="unstyled">
		<form method="post" action="">
			<input type="hidden" name="action" value="teaching-term">
			{include file='blocks/student.curriculum-item.tpl' which='major' student_data=$student_data}
			{include file='blocks/student.curriculum-item.tpl' which='concentration' student_data=$student_data}
			{include file='blocks/student.curriculum-item.tpl' which='minor' student_data=$student_data}
			<li><strong>GPA:</strong><ul><li>{$student_data->gpa}</li></ul></li>
			<li>
				<!-- match existing block styles -->
				<strong>Student Teaching Term:</strong>
				<ul>
					<li>
						{if $student_gate_model}
							{$student_gate_model->teaching_term_code}
							<input class="stu-term-edit" type="submit" value="Update">
						{else}
							{$student_gate_system->teaching_term_code}
						{/if}
					</li>
				</ul>
			</li>
		</form>
	</ul>
{/box}
