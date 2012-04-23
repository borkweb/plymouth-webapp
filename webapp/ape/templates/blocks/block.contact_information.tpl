<div id="ape_adv_contact" class="ape-section {if $myuser->go_states.ape_adv_contact === '0'}ape-section-hidden{/if}">
	<h3>Contact Information</h3>	
	<ul class="apedata"><li>
		{foreach from=$person->address item=item key=atyp}
			{foreach from=$item item=address}
				<h4>{$address->description}</h4>
				<ul class="apedata">
					<li>{$address->street_line1}</li>
					<li>{$address->street_line2}</li>
					<li>{$address->street_line3}</li>
					<li>{$address->street_line4}</li>
					<li>{$address->city}, {$address->stat_code}  {$address->zip} {$address->nation_desc}</li>
					<li><small>From {$address->from_date|date_format:"%m/%d/%Y"} To {if $address->to_date|date_format:"%m/%d/%Y" ==''}the Present {/if}</small></li>
				</ul>
			{/foreach}
		{/foreach}
		
	</li>
	<li>
		<h4>Phone Information</h4>
		<ul class="apedata">
			{foreach from=$person->phone item=item key=tele}
				{foreach from=$item item=phone}
					{if $phone->tele_code!='CA' && $phone->status_ind==''}
						<li><label>{$phone->description}:</label> ({$phone->phone_area}) {$phone->phone_number|phone_format} {if $phone->unlist_ind=='Y'}(Unlisted){/if} {if $phone->comment} &mdash; {$phone->comment}{/if}</li>
					{/if}
				{/foreach}
			{foreachelse}
				<li class="apenoresults">No Information Available</li>
			{/foreach}
		</ul>
	</li>
	<li>
		<h4>Email Addresses</h4>
		<ul class="apedata">
			<li><label>WP Email:</label>
			<a href="mailto:{$person->wp_email}">{$person->wp_email}</a></li>
			{foreach from=$person->email item=item key=emal}
				{foreach from=$item item=email}
				  {if $email->status_ind == 'A'}
						<li><label>{$email->description()}:</label>
						<a href="mailto:{$email->email_address}">{$email->email_address}</a> {if $email->preferred_ind == 'Y'}(Preferred){/if} {if $email->comment} &mdash; {$email->comment}{/if}</li>
					{/if}
				{/foreach}
			{foreachelse}
				<li class="apenoresults">No Information Available</li>
			{/foreach}
		</ul>
	</li>
	<li>
		<h4>Exclusions</h4>
		<ul class="apedata">
			{foreach name=exclusions from=$person->alumni->exclusions item=item}
				{if $smarty.foreach.exclusions.first}
				<li>
					<table class="grid sortable">
						<thead>
						<tr>
							<th>Date Effective</th>
							<th>Exclusion</th>
							<th>Reason</th>
							<th>Date Ending</th>
						</tr>
						</thead>
						<tbody>
				{/if}
				<tr>
					<td>{$item.date_effective|date_format:"%b %e, %Y %I:%m %p"}</td>
					<td>{$item.exclusion}</td>
					<td>{$item.reason|link_urls|nl2br}</td>
					<td>{$item.date_ending|date_format:"%b %e, %Y %I:%m %p"}</td>
				</tr>
				{if $smarty.foreach.exclusions.last}</tbody></table></li>{/if}
				
			{foreachelse}
				<li class="apenoresults">No Information Available</li>
			{/foreach}
		</ul>
	</li></ul>
</div>	
