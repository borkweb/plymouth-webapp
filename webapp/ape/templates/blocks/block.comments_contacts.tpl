<div id="ape_adv_comments_contacts" class="ape-section {if $myuser->go_states.ape_adv_comments_contacts === '0'}ape-section-hidden{/if}">
	<h3>Comments and Contacts</h3>
	<ul class="apedata"><li>
		<div id="ape_adv_constituent_comments" class="ape-section {if $myuser->go_states.ape_adv_constituent_comments === '0'}ape-section-hidden{/if}">
			<h4>Constituent Comments</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->constituent_comments item=item}
					<li>
						<h4>{if $item.confid_ind =='Y'}!!Confidential!!{/if} [{$item.entry_date|date_format:"%b %e, %Y %I:%m %p"}] {$item.comment_type}</h4>
						<p>
						{$item.comments|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}
						<br/>
						<small>Comment by: {$item.entered_by}</small>
						</p>
					</li>
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_prospect_comments" class="ape-section {if $myuser->go_states.ape_adv_prospect_comments === '0'}ape-section-hidden{/if}">
			<h4>Prospect Comments</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->prospect_comments item=item}
					<li>
						<h5>{if $item.confidential =='Y'}!!Confidential!!{/if} [{$item.entry_date|date_format:"%b %e, %Y %I:%m %p"}] {$item.subject_indexes}</h5>
						<p>
						{$item.comments|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}
						<br/>
						<small>Comment by: {$item.originator}</small>
						</p>
					</li>
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_prospect_contacts" class="ape-section {if $myuser->go_states.ape_adv_prospect_contacts === '0'}ape-section-hidden{/if}">
			<h4>Prospect Contacts</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->prospect_contacts item=item}
					{if $item.contact}
						<li><h5>{$item.contact}</h5></li>
					{/if}
					{if $item.proposal}
						<li><label>Proposal:</label>
						{$item.proposal}</li>
					{/if}
					{if $item.moves}
						<li><label>Moves:</label>
						{$item.moves}</li>
					{/if}

					{if $item.action}
						<li><label>Action:</label>
						{$item.action}</li>
					{/if}
					{if $item.action_date}
						<li><label>Action Date:</label>
						{$item.action_date|date_format:"%b %e, %Y %I:%m %p"}</li>
					{/if}
					{if $item.project}
						<li><label>Project:</label>
						{$item.project}</li>
					{/if}
					{if $item.descripton}
						<li>{$item.descripton|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}</li>
					{/if}
					{if $item.call_report}
						<li><label>Call Report:</label>
						{$item.call_report|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}</li>
					{/if}
					{if $item.expenses}
						<li><label>Expenses:</label>
						{$item.expenses}</li>
					{/if}
					{if $item.exp_date}
						<li><label>Expense Date:</label>
						{$item.exp_date}</li>
					{/if}
					{if $item.amount}
						<li><label>Amount:</label>
						{$item.amount}</li>
					{/if}
					{if $item.originator}
						<li><small>Contact by: {$item.originator}
						{if $item.contact_date} on {$item.contact_date|date_format:"%b %e, %Y %I:%m %p"}{/if}
						</small></li>
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li>
	<li>
		<div id="ape_adv_const_contacts" class="ape-section {if $myuser->go_states.ape_adv_const_contacts === '0'}ape-section-hidden{/if}">
			<h4>Constituent Contacts</h4>
			<ul class="apedata">
				{foreach from=$person->alumni->contacts item=item}
					{if $item.primary_fullname}
						<li><label>Primary Fullname:</label>
						{$item.primary_fullname}</li>
					{/if}
					{if $item.primary_contact_title}
						<li><label>Primary Contact Title:</label>
						{$item.primary_contact_title}</li>
					{/if}
					{if $item.primary_jobc_desc}
						<li><label>Primary Job:</label>
						{$item.primary_jobc_desc}</li>
					{/if}
					{if $item.primary_mailing_type}
						<li><label>Primary Mailing Type:</label>
						{$item.primary_mailing_type}</li>
					{/if}
					{if $item.primary_street_line1}
						<li>{$item.primary_house_number} {$item.primary_street_line1}</li>
						<li>{$item.primary_street_line2}</li>
						<li>{$item.primary_street_line3}</li>
						<li>{$item.primary_street_line4}</li>
						<li>{$item.primary_city} {$item.primary_stat_code} {$item.primary_zip}</li>
						<li>{$item.primary_cnty_code}</li>
						<li>{$item.primary_natn_code}</li>
					{/if}
					{if $item.secondary_fullname}
						<li><hr/></li>
						<li><label>Secondary Fullname:</label> {$item.secondary_fullname}</li>
					{/if}
					{if $item.secondary_title}
						<li><label>Secondary Title:</label> {$item.secondary_title}</li>
					{/if}
					{if $item.secondary_jobc_desc}
						<li><label>Secondary Job:</label> {$item.secondary_jobc_desc}</li>
					{/if}
					{if $item.secondary_mailing_type}
						<li><label>Secondary Mailing Type:</label> {$item.secondary_mailing_type}</li>
					{/if}
					{if $item.secondary_street_line1}
						<li>{$item.secondary_house_number} {$item.secondary_street_line1}</li>
						<li>{$item.secondary_street_line2}</li>
						<li>{$item.secondary_street_line3}</li>
						<li>{$item.secondary_street_line4}</li>
						<li>{$item.secondary_city} {$item.secondary_stat_code} {$item.secondary_zip}</li>
						<li>{$item.secondary_cnty_code}</li>
						<li>{$item.secondary_natn_code}</li>
					{/if}
					{if $item.secondary_addr_line12}
						<li><hr/></li>
						<li>{$item.secondary_house_number2} {$item.secondary_addr_line12}</li>
						<li>{$item.secondary_addr_line22}</li>
						<li>{$item.secondary_addr_line32}</li>
						<li>{$item.secondary_addr_line42}</li>
						<li>{$item.secondary_city2} {$item.secondary_stat_code2} {$item.secondary_zip2}</li>
						<li>{$item.secondary_cnty_code2}</li>
						<li>{$item.secondary_natn_code2}</li>
					{/if}
					{if $item.phone_number}
						<li><label>Phone:</label> {$item.phone_number}</li>
					{/if}
					<li><hr/></li>
				{foreachelse}
					<li class="apenoresults">No Information Available</li>
				{/foreach}
			</ul>
		</div>
	</li></ul>
</div>
