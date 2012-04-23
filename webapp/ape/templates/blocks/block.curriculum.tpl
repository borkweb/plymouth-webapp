<div id="ape_curriculum" class="ape-section {if $myuser->go_states.ape_curriculum === '0'}ape-section-hidden{/if}">
	<h3>Curriculum</h3>	
		<ul class="apedata">
			{if ($person->ug || $person->gr) && $person->curriculum}
				{foreach from=$person->curriculum.major item=major}
				<li><label>Program:</label> {$major.0.program}</li>
				{if $major.0.levl_code == 'UG'}
				{if $person->student->ug->department}<li><label>Department:</label> {$person->student->ug->department}</li>{/if}
				{else}
				{if $person->student->gr->department}<li><label>Department:</label> {$person->student->gr->department}</li>{/if}
				{/if}
				<li><label>Major:</label> {$major.0.description}</li>
				{/foreach}
				{foreach from=$person->curriculum.minor item=minor}
				<li><label>Minor:</label> {$minor.0.description}</li>
				{/foreach}
				{foreach from=$person->curriculum.concentration item=concentration}
				<li><label>Concentration:</label> {$concentration.0.description}</li>
				{/foreach}
			{/if}
		</ul>
</div>	
