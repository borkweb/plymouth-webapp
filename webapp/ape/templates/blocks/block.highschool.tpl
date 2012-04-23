{if $person->student->highschool_gpa || $person->student->highschool_test_scores}
<div id="ape_test_scores" class="ape-section {if $myuser->go_states.ape_test_scores === '0'}ape-section-hidden{/if}">
	<h3>High School Information</h3>	
	<ul class="apedata">
		{foreach from=$person->student->highschool_gpa item=gpa key=school}
		<li><label>{$school} GPA:</label> {$gpa}</li>
		{/foreach}
		<li>
			<h4>Test Scores</h4>
			<table class="grid">
				<thead>
					<tr>
						<th>Test</th>
						<th>Score</th>
						<th>High School</th>
						<th>Test Date</th>
					</tr>
				</thead>
				<tbody>
					{foreach name=test_scores from=$person->student->highschool_test_scores item=item}
					<tr>
						<td>{$item.test_description}</td>
						<td class="alignright">{$item.test_score}</td>
						<td>{$item.highschool}</td>
						<td>{$item.test_date|date_format:"%m/%d/%Y"}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</li>
	</ul>
</div>	
{/if}
