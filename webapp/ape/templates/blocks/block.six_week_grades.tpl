		{foreach from=$person->student->levels item=level name=level_loop}
			{assign var=student value=`$person->student->$level`}
			{foreach from=$student->midterm_grades item=item name=midterm_grades_loop}
				{if $smarty.foreach.midterm_grades_loop.first && !$midterm_output}
<div id="ape_midterm_grades" class="ape-section {if $myuser->go_states.midterm_grades === '0'}ape-section-hidden{/if}">
	<h3>Six Week Grades</h3>	
	<table class="grid">
		<thead>
			<tr>
				<th>CRN</th>
				<th>Course Number</th>
				<th>Title</th>
				<th>Grade</th>
				<th>Term Code</th>
			</tr>
		</thead>
		<tbody>
					{assign var=midterm_output value=true}
				{/if}
				<tr>
					<td>{$item.crn}</td>
					<td>{$item.subject_code}{$item.course_num}</td>
					<td>{$item.course}</td>
					<td>{$item.grade}</td>
					<td>{$item.term_code}</td>
				</tr>
			{/foreach}
		{/foreach}
{if $midterm_output}
		</tbody>
	</table>
</div>	
{/if}
