<div id="ape_adv_solicitor_information" class="ape-section {if $myuser->go_states.ape_adv_solicitor_information === '0'}ape-section-hidden{/if}">
	<h3>Solicitor Information</h3>
	<ul class="apedata">
		{foreach from=$person->alumni->solicitor_information item=item}
			{if $item.campaign}
				<li><label>Campaign:</label> {$item.campaign}</li>
			{/if}
			{if $item.organization}
				<li><label>Type & Organization: </label> {$item.type} {$item.organization} </li>
			{/if}
			{if $item.solicitor}
				<li><label>Solicitor:</label> {$item.year} {$item.solicitor}</li>
			{/if}
			{if $item.target_ask_amount}
				<li><label>Target Ask Amount:</label> {$item.target_ask_amount}</li>
			{/if}
			{if $item.rating}
				<li><label>Rating:</label> {$item.rating}</li>
			{/if}
			{if $item.rater}
				<li><label>Rater</label> {$item.rater}</li>
			{/if}
			<li><hr/></li>
		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
</div>
