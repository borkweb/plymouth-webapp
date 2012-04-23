<div id="ape_adv_membership_information" class="ape-section {if $myuser->go_states.ape_adv_membership_information === '0'}ape-section-hidden{/if}">
	<h3>Membership Information</h3>
	<ul class="apedata">
		{foreach from=$person->alumni->membership_information item=item}
			{if $item.program}
				<li><label>Program:</label> {$item.program}</li>
			{/if}
			{if $item.status}
				<li><label>Status:</label> {$item.status} </li>
			{/if}
			{if $item.joined}
				<li><label>Joined:</label> {$item.joined}</li>
			{/if}
			{if $item.renewed}
				<li><label>Renewed:</label> {$item.renewed}</li>
			{/if}
			{if $item.expiration}
				<li><label>Expiration:</label> {$item.expiration}</li>
			{/if}
			<li><hr/></li>
		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
</div>
