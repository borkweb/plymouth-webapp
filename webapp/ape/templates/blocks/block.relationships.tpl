<div id="ape_adv_relationships" class="ape-section {if $myuser->go_states.ape_adv_relationships === '0'}ape-section-hidden{/if}">		
			<h3>Relationships</h3>
			<ul class="apedata"><li>
				<div id="ape_adv_spouse_information" class="ape-section {if $myuser->go_states.ape_adv_spouse_information === '0'}ape-section-hidden{/if}">
					<h4>Spouse Information</h4>
					<ul class="apedata">
						{if $person->alumni->spouse}
							{if $person->alumni->spouse.fullname}
								<li><label>Spouse Name:</label>
								{$person->alumni->spouse.fullname}
								{if $person->alumni->spouse.id}
								 (<a href="{$PHP.BASE_URL}/user/advancement/{$person->alumni->spouse.id}">{$person->alumni->spouse.id}</a>)
								{/if}
								</li>
							{/if}
							{if $person->alumni->spouse->pref_class > '0000' && $person->alumni->spouse.pref_class < '9999'}
								<li><label>Spouse Preferred Class:</label>
								{$person->alumni->spouse.pref_clas}
								</li>
							{/if}
							{if $person->alumni->spouse.confidential =='Y'}
								<li>Confidential</li>
							{/if}
							{if $person->alumni->spouse.deceased == 'Y'}
								<li><strong>Deceased</strong></li>
							{/if}
							{if $person->alumni->spouse.coll_code_pref == 'PL'}
								<li><label>Spouse College:</label>
									Plymouth State University 
								</li>
							{/if}
							<li>
								<label>Status:</label>
								{if !$person->alumni->primary_spouse}Primary Spouse{else}Secondary Spouse{/if}
							</li>
							{if $person->alumni->spouse_category}
							<li><label>Spouse Category:</label>
								{$person->alumni->spouse_category}
							</li>
							{/if}
						{/if}
					</ul>
				</div>
			</li>
			<li>
				<div id="ape_adv_employment" class="ape-section {if $myuser->go_states.ape_adv_employment === '0'}ape-section-hidden{/if}">
					<h4>Spouse Employment History</h4>
					<ul class="apedata">
						{foreach from=$person->alumni->spouse_employment_history item=item}
							{if $item.employer}
								<li><label>Employer's Name:</label>
								{$item.employer}</li>
							{/if}
							{if $item.position}
								<li><label>Position:</label>
								{$item.position}</li>
							{/if}
							{if $item.from_date}
								<li><label>Duration:</label>
								<small>From {$item.from_date|date_format:"%m/%d/%Y"} To {if $item.to_date==''}the Present {else} {$item.to_date|date_format:"%m/%d/%Y"} {/if}</small></li>
							{/if}
							<li><hr/></li>
						{foreachelse}
							<li class="apenoresults">No Information Available</li>
						{/foreach}
					</ul>
				</div>
			</li>
			<li>
				<div id="ape_adv_children" class="ape-section {if $myuser->go_states.ape_adv_children === '0'}ape-section-hidden{/if}">				
					<h4>Children</h4>
					<ul class="apedata">
						{foreach from=$person->alumni->children item=item}
							{if $item.fullname}
								<li>{$item.fullname} {if $item.id}(<a href="{$PHP.BASE_URL}/user/advancement/{$item.id}">{$item.id}</a>){/if}
								{if $item.sex}
									({if $item.sex=='M'}Male{elseif $item.sex=='F'}Female{else}Unknown Gender{/if})
								{/if}
								</li>
							{/if}
							{if $item.birth_date}
								<li><label>Birthday:</label> {$item.birth_date|date_format:"%B %e, %Y"}</li>
							{/if}
							{if $item.age}
								<li><label>Age:</label> {$item.age}</li>
							{/if}
							{if $item.deceased_ind == 'Y'}
								<li><strong>Deceased</strong></li>
								{if $item.deceased_date}
									<li><label>Date Deceased:</label> {$item.deceased_date|date_format:"%B %e, %Y"}</li>
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
				<div id="ape_adv_cross_references" class="ape-section {if $myuser->go_states.ape_adv_cross_references === '0'}ape-section-hidden{/if}">
					<h4>Cross References</h4>
					<ul class="apedata">
						{foreach from=$person->alumni->cross_references item=item}
							{if $item.fullname}
								<li><label>{$item.desc}:</label> {$item.fullname} {if $item.id}(<a href="{$PHP.BASE_URL}/user/advancement/{$item.id}">{$item.id}</a>){/if}</li>
							{/if}	
							{if $item.dead_ind=='Y'}
								<li><strong>Deceased</strong></li>
							{/if}
							<li><hr/></li>
						{foreachelse}
							<li class="apenoresults">No Information Available</li>
						{/foreach}
					</ul>				
				</div>				
			</li></ul>
		</div>
