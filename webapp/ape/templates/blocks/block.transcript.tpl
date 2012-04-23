{foreach from=$person->student->all_levels item=level}
	{assign var=student value=`$person->student->$level`}
	{foreach name=transcript from=$student->transcript item=item name=transcript_loop}
		{if $smarty.foreach.transcript_loop.first}
			{if !$transcript_output}
				<div id="ape_{$level}_transcript" class="ape-section {if $myuser->go_states.ape_transcript === '0'}ape-section-hidden{/if}">
				{assign var=transcript_output value=true}
			{/if}
			<h3>{if $level == 'ug' || $level == 'ug_inactive'}Undergraduate{elseif $level == 'gr' || $level == 'gr_inactive'}Graduate{/if} Transcript</h3>	
			<table class="grid">
				<thead>
				<tr>
					<th>Term Code</th>
					<th>Subject</th>
					<th>Number</th>
					<th>Title</th>
					<th>Credits</th>
					<th>Grade</th>
					<th>Grade Points</th>
					<th>Sem. Points</th>
					<th>Total Points</th>
					<th>Semester GPA</th>
					<th>CUM GPA</th>
				</tr>
				</thead>
				<tbody class="sensitive">
			{/if}
			{if $item.term}
				<tr>
					<td>{$item.term}</td>
					<td>{$item.subject}</td>
					<td>{$item.crsenumb}</td>
					<td>{$item.title}</td>
					<td>{$item.credits}</td>
					<td>{$item.finalgrade}</td>
					<td>{$item.gradepoints}</td>
					<td>{$item.sempoints}</td>
					<td>{$item.totalpoints}</td>
					<td>{$item.semgpa}</td>
					<td>{$item.cumgpa}</td>
				</tr>
			{/if}
			{if $smarty.foreach.transcript_loop.last}
				</tbody>
			</table>
			{/if}
		{/foreach}
	{/foreach}
{if $transcript_output}
	</div>	
{/if}
