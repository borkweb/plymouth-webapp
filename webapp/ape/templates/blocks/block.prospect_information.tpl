<div id="ape_adv_prospecct_info" class="ape-section {if $myuser->go_states.ape_adv_prospecct_info === '0'}ape-section-hidden{/if}">
	<h3>Prospect Information</h3>
	<ul class="apedata"><li>
		<div id="ape_adv_staff_assignments" class="ape-section {if $myuser->go_states.ape_adv_staff_assignments === '0'}ape-section-hidden{/if}">
			<h4>Staff Assignments</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->staff_assignments item=item}
					{if $item.staff_name}
						<li><label>Name:</label> {$item.staff_name}</li>
					{/if}
					{if $item.staff_type}
						<li><label>Type:</label> {$item.staff_type}</li>
					{/if}
					{if $item.staff_primary}
						<li><label>Primary:</label> {$item.staff_primary}</li>
					{/if}
					{if $item.staff_date}
						<li><label>Date:</label> {$item.staff_date|date_format:"%b %e, %Y %I:%m %p"}</li>
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_prospect_info" class="ape-section {if $myuser->go_states.ape_adv_prospect_info === '0'}ape-section-hidden{/if}">
			<h4>General Information</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->prospect_info item=item}
					{if $item.status}
						<li><label>Status:</label> {$item.status}</li>
					{/if}
					{if $item.reference_type}
						<li><label>Reference Type:</label> {$item.reference_type}</li>
					{/if}
					{if $item.reference}
						<li><label>Reference:</label> {$item.reference}</li>
					{/if}
					{if $item.web_pldg_ind}
						<li><label>Allow Web Pledges:</label> {if $item.web_pldg_ind=='Y'}Yes{else}No{/if}</li>
					{/if}
					{if $item.web_gift_ind}
						<li><label>Allow Web Gifts:</label> {if $item.web_gift_ind=='Y'}Yes{else}No{/if}</li>
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_projects" class="ape-section {if $myuser->go_states.ape_adv_projects === '0'}ape-section-hidden{/if}">
			<h4>Projects or Interests</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->projects item=item}
					{if $item.project_interest}
						<li><label>Project or Interest:</label> {$item.project_interest}</li>
						{if $item.giving_vehicle}
							<li><label>Giving Vehicle:</label> {$item.giving_vehicle}</li>
						{/if}
						{if $item.target_ask_amt}
							<li><label>Targe Ask Amount:</label> {$item.target_ask_amt|money_format}</li>
						{/if}
						{if $item.target_ask_date}
							<li><label>Target Ask Date:</label> {$item.target_ask_date|date_format:"%b %e, %Y %I:%m %p"}</li>
						{/if}
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>	
		</div>
	</li>
	<li>
		<div id="ape_adv_proposal" class="ape-section {if $myuser->go_states.ape_adv_proposal === '0'}ape-section-hidden{/if}">
			<h4>Proposals</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->proposal item=item}
					<li><h4>Proposal</h4></li>
					{if $item.proposal}
						<li><label>Proposal:</label> {$item.proposal}</li>
						
						{if $item.sequence_no}
							<li><label>Sequence:</label> {$item.sequence_no}</li>
						{/if}
						{if $item.status}
							<li><label>Status:</label> {$item.status}</li>
						{/if}
						{if $item.staff}
							<li><label>Staff:</label> {$item.staff}</li>
						{/if}
						{if $item.create_date}
							<li><label>Create Date:</label> {$item.create_date|date_format:"%b %e, %Y %I:%m %p"}</li>
						{/if}
						{if $item.amount}
							<li><label>Amount:</label> {$item.amount|money_format}</li>
						{/if}
						{if $item.due_date}
							<li><label>Due:</label> {$item.due_date|date_format:"%b %e, %Y %I:%m %p"}</li>
						{/if}
					
						<li><h4>Proposal Project Details</h4></li>
						{if $item.project}
							<li><label>Project:</label> {$item.project}</li>
							{if $item.giving_vehicle}
								<li><label>Giving Vehicle:</label> {$item.giving_vehicle}</li>
							{/if}
							{if $item.status}
								<li><label>Status:</label> {$item.status}</li>
							{/if}
							{if $item.finance_proposal}
								<li><label>Finance Proposal:</label> {$item.finance_proposal}</li>
							{/if}
							{if $item.target_ask_amount}
								<li><label>Target Ask Amount:</label> {$item.target_ask_amount|money_format}</li>
							{/if}
						{/if}
						
						<li><hr/></li>
						
						{if $item.comments}
							<li>
								<h4>Proposal Comments</h4>
								<p>
									{$item.comments|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}
								</p>
							</li>
						{/if}
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_research_data" class="ape-section {if $myuser->go_states.ape_adv_research_data === '0'}ape-section-hidden{/if}">
			<h4>Research Data</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->research_data item=item}
					{if $item.research_data}
						{if $item.data_type}
							<li><label>Data Type:</label> {$item.data_type}</li>
						{/if}
						{if $item.effective_date}
							<li><label>Effective Date</label> {$item.effective_date|date_format:"%b %e, %Y %I:%m %p"}</li>
						{/if}
						{if $item.activity_date}
							<li><label>Activity Date:</label> {$item.activity_date|date_format:"%b %e, %Y %I:%m %p"}</li>
						{/if}
						<li><label>Research Data:</label> 
							<p>
							{$item.research_data|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}
							</p>
						</li>
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_external_ratings" class="ape-section {if $myuser->go_states.ape_adv_external_ratings === '0'}ape-section-hidden{/if}">
			<h4>External Ratings</h4>
			<ul class="apedata">
				{foreach name=external_ratings from=$person->alumni->external_ratings item=item}
					{if $smarty.foreach.external_ratings.first}
					<li>
						<table class="grid sortable">
							<thead>
							<tr>
								<th>Date</th>
								<th>Source</th>
								<th>Value</th>								
								<th>Score</th>
								<th>Level</th>
							</tr>
							</thead>
							<tbody>
					{/if}
					<tr>
						<td>{$item.rate_date|date_format:"%b %e, %Y %I:%m %p"}</td>
						<td>{$item.rate_source}</td>
						<td>{$item.rate_value}</td>
						<td>{$item.rate_score}</td>
						<td>{$item.rate_level}</td>
					</tr>
					{if $smarty.foreach.external_ratings.last}</tbody>
					</table></li>{/if}
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>	
		<div id="ape_adv_ratings" class="ape-section {if $myuser->go_states.ape_adv_ratings === '0'}ape-section-hidden{/if}">
			<h4>Internal Ratings</h4>
			<ul class="apedata">
				{foreach name=ratings from=$person->alumni->ratings item=item}
					{if $smarty.foreach.ratings.first}
						<li>
						<table class="grid sortable">
							<thead>
							<tr>
								<th>Date</th>
								<th>Type</th>
								<th>Rating</th>
								<th>Primary</th>
								<th>Rating Amount</th>
								<th>Rater Type</th>
							</tr>
							</thead>
							<tbody>
					{/if}
					<tr>				
						<td>{$item.rate_date|date_format:"%b %e, %Y %I:%m %p"}</td>
						<td>{$item.rate_type}</td>
						<td>{$item.rating}</li>
						<td>{$item.rate_primary}</td>
						<td>{$item.rating_amount}</td>
						<td>{$item.rater_type}</td>
					</tr>
						
					{if $smarty.foreach.ratings.last}</tbody></table></li>{/if}
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_strategy_plans" class="ape-section {if $myuser->go_states.ape_adv_strategy_plans === '0'}ape-section-hidden{/if}">
			<h4>Strategy Plans</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->strategy_plans item=item}
					{if $item.strategy}
						<li><label>Strategy:</label> {$item.strategy}</li>
						<li><label>Project:</label> {$item.project}</li>
						<li><label>Start Date:</label> {$item.start_date|date_format:"%b %e, %Y %I:%m %p"}</li>
						<li><label>Moves Manager:</label> {$item.moves_manager}</li>
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li></ul>
</div>
