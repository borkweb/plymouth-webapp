<div id="ape_adv_giving" class="ape-section {if $myuser->go_states.ape_adv_giving === '0'}ape-section-hidden{/if}">
	<h3>Giving History</h3>
	<ul class="apedata"><li>
		<div id="ape_adv_pledges" class="ape-section {if $myuser->go_states.ape_adv_pledges === '0'}ape-section-hidden{/if}">
			<h4>Pledge Totals</h4>
			<ul class="apedata">
				{foreach name=pledges from=$person->alumni->pledges item=item}
					{if $smarty.foreach.pledges.first}
					<li>
						<table class="grid sortable">
							<thead>
							<tr>
								<th>Date</th>
								<th>Credit</th>
								<th>Amount</th>
								<th>Balance</th>
								<th>Status</th>
								<th>Campaign</th>
								<th>Designation</th>
								<th>Pledge Class</th>
								<th>Pledge Class2</th>
								<th>Pledge Class3</th>
							</tr>
							</thead>
							<tbody>
					{/if}
					
					<tr>
						<td>{$item.pledge_date|date_format:"%m/%d/%Y"}</td>
						<td>{$item.credit}</td>
						<td>{$item.amount|money_format}</td>
						<td>{$item.balance|money_format}</td>
						<td>{$item.status}</td>
						<td> <a href="{$PHP.HTTP_HOST}/webapp/analytics-dev/report/adv-view-camp-population?drill&campaign_code={$item.campaign_type}&campaign={$item.campaign_code}" target="{$PHP.HTTP_HOST}/webapp/analytics-dev/report/adv-view-camp-population?drill&campaign_code={$item.campaign_type}&campaign={$item.campaign_code}">{$item.campaign}</a></td>
						<td>{$item.designation}</td>
						<td>{if $item.pledge_class == 'MEML'}Memorial
							{elseif $item.pledge_class == 'ANON'}Anonymous
							{elseif $item.pledge_class ==  'HONR'}In Honor of
							{/if}</td>
						<td>{if $item.pledge_class2 == 'MEML'}Memorial
							{elseif $item.pledge_class2 == 'ANON'}Anonymous
							{elseif $item.pledge_class2 == 'HONR'}In Honor of
							{/if}</td>
						<td>{if $item.pledge_class3 == 'MEML'}Memorial
							{elseif $item.pledge_class3 == 'ANON'}Anonymous
							{elseif $item.pledge_class3 == 'HONR'}In Honor of
							{/if}</td>
					</tr>

					{assign var=total_amount value=$total_amount+$item.amount}
					{assign var=number_pledges value=$number_pledges+1}
					{assign var=total_balance value=$total_balance+$item.balance}

					{if $smarty.foreach.pledges.last}
					</tbody>
					<tfoot>
						<th>Total #: {$number_pledges}</th>
						<th></th>
						<th>{$total_amount|money_format}</th>
						<th>{$total_balance|money_format}</th>
						<th colspan="6">&nbsp;</th>
					</tfoot>
					</table></li>
					{/if}
				{foreachelse}
					<li class="apenoresults">No Information Available</li>

				{/foreach}

			</ul>
		</div>
	</li>
	<ul class="apedata"><li>
		<div id="ape_adv_gift_history" class="ape-section {if $myuser->go_states.ape_adv_gift_history === '0'}ape-section-hidden{/if}">
			<h4>Gift History</h4>
			<ul class="apedata">
				{if $person->alumni->gift_history}
					<li>
						<table class="grid sortable">
							<thead>
							<tr>
								<th>Type</th>
								<th>Date</th>
								<th>Credit</th>
								<th>Amount</th>
								<th>Campaign</th>
								<th>Designation</th>
								<th>Gift Class</th>
								<th>Gift Class2</th>
								<th>Gift Class3</th>
							</tr>
							</thead>
							<tbody>
					{foreach name=gift_history from=$person->alumni->gift_history item=item}
						
						<tr>
							<td>{$item.type}</td>
							<td>{$item.gift_date|date_format:"%m/%d/%Y"}</td>
							<td>{$item.credit}</td>
							<td>{$item.gift_amount|money_format}</td>
							<td> <a href="{$PHP.HTTP_HOST}/webapp/analytics-dev/report/adv-view-camp-population?drill&campaign_code={$item.campaign_type}&campaign={$item.campaign_code}" target="{$PHP.HTTP_HOST}/webapp/analytics-dev/report/adv-view-camp-population?drill&campaign_code={$item.campaign_type}&campaign={$item.campaign_code}">{$item.campaign}</a></td>
							<td>{$item.designation}</td>
							<td>{if $item.gift_class == 'MEMY'}In Memory of
								{elseif $item.gift_class == 'ANON'}Anonymous
								{elseif $item.gift_class == 'HONR'}In Honor of
								{/if}</td>
							<td>{if $item.gift_class2 == 'MEMY'}In Memory of
								{elseif $item.gift_class2 == 'ANON'}Anonymous
								{elseif $item.gift_class2 == 'HONR'}In Honor of
								{/if}</td>
							<td>{if $item.gift_class3 == 'MEMY'}In Memory of
								{elseif $item.gift_class3 == 'ANON'}Anonymous
								{elseif $item.gift_class3 == 'HONR'}In Honor of
								{/if}</td>
						</tr>
					{/foreach}
						</table></li>
				{else}
					<li class="apenoresults">No Information Available</li>
				{/if}
			</ul>
		</div>
	</li>
	<li>	
		<div id="ape_adv_gift" class="ape-section {if $myuser->go_states.ape_adv_gift === '0'}ape-section-hidden{/if}">
			<h4>Gift Summary</h4>
			<ul class="apedata">
				{if $person->alumni->number_gifts}
					{if $person->alumni->number_gifts}
						<li><label>Number of Gifts:</label> {$person->alumni->number_gifts}</li>
					{/if}					
					{if $person->alumni->outright_gifts !=''}
						<li><label>Outright Gifts:</label> {$person->alumni->outright_gifts|money_format}</li>
					{/if}					
					{if $person->alumni->pledge_payments !=''}
						<li><label>Pledge Payments:</label> {$person->alumni->pledge_payments|money_format}</li>
					{/if}
					{if $person->alumni->hard_credit !=''}
						<li><label>Hard Credit:</label> {$person->alumni->hard_credit|money_format}</li>
					{/if}
					<li><hr/></li>
					
					<li>
						<h5>Most Recent Gift:</h5>
						<ul class="apedata">
							{if $person->alumni->recent_designation}
								<li>{$person->alumni->recent_designation}</li>
							{/if}
							{if $person->alumni->recent_gift_date}
								<li>{$person->alumni->recent_gift_date|date_format:"%b %e, %Y %I:%m %p"} {if $person->alumni->recent_gift_year}<em>(FY {$person->alumni->recent_gift_year})</em>{/if}</li>
							{/if}						
							{if $person->alumni->recent_gift_amount!=''}
								<li>{$person->alumni->recent_gift_amount|money_format}</li>
							{/if}
						</ul>
					</li>		
							
					<li>
						<h5>Highest Gift:</h5>
						<ul class="apedata">
							{if $person->alumni->high_designation}
								<li>{$person->alumni->high_designation}</li>
							{/if}
							{if $person->alumni->high_gift_date}
								<li>{$person->alumni->high_gift_date|date_format:"%b %e, %Y %I:%m %p"}</li>
							{/if}
							{if $person->alumni->high_gift_amount !=''}
								<li>{$person->alumni->high_gift_amount|money_format}</li>
							{/if}
						</ul>
					</li>


					<li><hr/></li>
				{else}
					<li class="apenoresults">No Information Available</li>
				{/if}
			</ul>
		</div>
	</li>
	<li>	
		<div id="ape_adv_matching" class="ape-section {if $myuser->go_states.ape_adv_matching === '0'}ape-section-hidden{/if}">
			<h4>Matching and Soft Gift Summary</h4>
			<ul class="apedata">
					{if $person->alumni->matching_soft.third_party_credit !=''}
						<li><label>Third Party Credit:</label> {$person->alumni->matching_soft.third_party_credit}</li>
					{else}
						<li>No Matching</li>
					{/if}
					{if $person->alumni->matching_soft.waiting_match != 0} 
						<li><label>Waiting Match:</label> {$person->alumni->matching_soft.waiting_match|money_format}</li>
					{/if}
					{if $person->alumni->matching_soft.match_credit != 0} 
						<li><label>Match Credit:</label> {$person->alumni->matching_soft.match_credit|money_format}</li>
					{/if}
					{if $person->alumni->matching_soft.soft_credit != 0} 
						<li><label>Soft Credit:</label> {$person->alumni->matching_soft.soft_credit|money_format}</li>
					{/if}
					{if $person->alumni->matching_soft.total_soft_credit != 0} 
						<li><label>Total Soft Credit:</label> {$person->alumni->matching_soft.total_soft_credit|money_format}</li>
					{/if}
					{if $person->alumni->matching_soft.grand_total != 0} 
						<li><label>Overall Total:</label> {$person->alumni->matching_soft.grand_total|money_format}</li>
					{/if}
				<li><hr/></li>
			</ul>
		</div>
	</li>
	<li>	
		<div id="ape_adv_giftsociety" class="ape-section {if $myuser->go_states.ape_adv_giftsociety === '0'}ape-section-hidden{/if}">
			<h4>Gift Society Information</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->gift_society item=item}
					<li>
						<ul class="flush">
							{if $item.gift_society}
								<li><label>Gift Society:</label> {$item.gift_society}</li>
							{/if}
							{if $item.type}
								<li><label>Type:</label> {$item.type} </li>
							{/if}
							{if $item.priority}
								<li><label>Priority:</label> {$item.priority}</li>
							{/if}
							{if $item.year}
								<li><label>Year:</label> {$item.year}</li>
							{/if}
						</ul>
						<hr/>
					</li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	</ul>
</div>
