{foreach from=$person->student->levels item=level name=level_loop}
	{assign var=student value=`$person->student->$level`}
	{assign var="termcode" value=$student->term_code}
	{foreach from=$person->student->courses[$termcode] item=item name=course_loop}
		{if $smarty.foreach.course_loop.first && !$schedule_output}
		<div id="ape_schedule" class="ape-section {if $myuser->go_states.ape_schedule === '0'}ape-section-hidden{/if}">
			<h3>Current Schedule ({$termcode})</h3>	
			<ul class="apedata">
			{assign	var=schedule_output value=true}
		{/if}
		<li>
			<a href="#" id="course-{$item->crn}" class="course-title">{$item->subject_code} {$item->course_number}.{$item->section_num} <strong>{$item->title}</strong></a>
			<ul id="details-{$item->crn}" class="course-details" style="display:none;padding-left: 1em;">
				<li><label>Credits:</label> {$item->credits}</li>
				{foreach from=$item->schedule item=meet}
				<li>
					<label>Meets:</label>
					{$meet.begin_time} - {$meet.end_time} in {$meet.building} Rm {$meet.room_number} on {$meet.days} {*({$meet.start_date|date_format:"%b %e"} to {$meet.end_date|date_format:"%b %e"})*}
				</li>
				{/foreach}
			</ul>
		</li>
		{if $smarty.foreach.course_loop.last && !$schedule_output}
			</ul>
		{/if}
	{/foreach}
	{if $level == 'UG'}
		{if $termcode|substr:4:2 =='50'}
			{assign var="termcode" value=$student->term_code+70}
		{else}
			{assign var='termcode' value='none'}
		{/if}
	{else}
		{if $termcode|substr:4:2 =='95'}
			{assign var="termcode" value=$student->term_code+97}
		{elseif $termcode|substr:4:2 =='81'}
			{assign var="termcode" value=$student->term_code+11}
		{else}
			{assign var='termcode' value='none'}
		{/if}
	{/if}

	{if $person->student->courses[$termcode]}
	<h3>Student Future Schedule ({$termcode})</h3>	
	<ul class="apedata">
	{foreach from=$person->student->courses[$termcode] item=item name=course_loop}
		{if $smarty.foreach.course_loop.first}
			{if !$schedule_output}
				<div id="ape_schedule" class="ape-section {if $myuser->go_states.ape_schedule === '0'}ape-section-hidden{/if}">
				{assign	var=schedule_output value=true}
			{/if}
			<h3>Current Schedule ({$termcode})</h3>	
			<ul class="apedata">
		{/if}
		<li>
			{$item->subject_code} {$item->course_number}.{$item->section_num} <strong>{$item->title}</strong> for {$item->credits} credits
		</li>
		{if $smarty.foreach.course_loop.last && !$schedule_output}
			</ul>
		{/if}
	{/foreach}
	{/if}
{/foreach}
{if $schedule_output}
</div>
{/if}
