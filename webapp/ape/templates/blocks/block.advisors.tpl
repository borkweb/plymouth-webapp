{if $person->student->ug->advisors || $person->student->gr->advisors}
<div id="ape_advisors" class="ape-section {if $myuser->go_states.ape_advisors === '0'}ape-section-hidden{/if}">
	<h3>Academic Advisor(s)</h3>	
		<ul class="apedata">
			{foreach from=$person->student->levels item=level}
				{assign var=student value=`$person->student->$level`}
				{foreach from=$student->advisors item=item}
					<li>
						<label title="As of {$item->advisor_term_code_eff}">{if $item->advisor_primary_ind == 'Y'}Primary {/if}Advisor:</label>
						{$item->first_name} {$item->mi} {$item->last_name} (<a href="{$PHP.BASE_URL}/user/{$item->id}">{$item->id}</a>)
					</li>
				{/foreach}
			{/foreach}
		</li>
	</ul>		
</div>
{/if}
