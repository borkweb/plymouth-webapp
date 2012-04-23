<div id="ape_registration_status" class="ape-section {if $myuser->go_states.ape_registration_status === '0'}ape-section-hidden{/if}">
	<h3>Student Data</h3>
	<ul class="apedata">
		{foreach from=$person->student->levels item=level}
			{assign var=student value=`$person->student->$level`}

			{if $student->level}<li><label>Level:</label>{$student->level}</li>{/if}
			{if $student->rate}<li><label>Rate:</label>{$student->rate}</li>{/if}
			{if $student->admit_term}<li title="{$student->admit_type}"><label>Admit Term:</label>{$student->admit_term}</li>{/if}
			{if $student->catalog_term}<li><label>Catalog Term:</label>{$student->catalog_term}</li>{/if}
			{* if $student->residency}<li><label>Residency:</label>{$student->residency}</li>{/if *}
			{if $student->type}<li><label>Student Type:</label>{$student->type}</li>{/if}
			{if $student->class}<li><label>Class Year:</label>{$student->class}</li>{/if}
			{if $student->gpa}<li><label>Current GPA:</label><span class="sensitive">{$student->gpa|number_format:2}</span></li>{/if}
			{if $student->hours_earned}<li><label>Total Credits Earned:</label>{$student->hours_earned|number_format:2}</li>{/if}
			{if $student->last_term_attended}<li><label>Last Term Attended:</label>{$student->last_term_attended}</li>{/if}
		{/foreach}
	</ul>
</div>
