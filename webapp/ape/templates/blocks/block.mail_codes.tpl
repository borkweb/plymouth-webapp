<div id="ape_adv_mail_codes" class="ape-section {if $myuser->go_states.ape_adv_mail_codes === '0'}ape-section-hidden{/if}">
	<h3>Mail Codes</h3>
	<ul class="apedata">
		{foreach name=mail_codes from=$person->alumni->mail_codes item=item}
			{if $smarty.foreach.mail_codes.first}
			<li>
				<table class="grid sortable">
					<thead>
					<tr>
						<th>Date</th>
						<th>Mailing</th>
					</tr>
					</thead>
					<tbody>
			{/if}
			<tr>
				<td>{$item.starting_date|date_format:"%b %e, %Y %I:%m %p"}</td>
				<td>{$item.mailing}</td>
			</tr>
			{if $smarty.foreach.mail_codes.last}</tbody></table></li>{/if}
			
		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>			
</div>
<div id="ape_adv_endowmt_scholarshp_contacts" class="ape-section {if $myuser->go_states.ape_endowmt_scholarshp_contacts === '0'}ape-section-hidden{/if}">
	<h3>Endowment/Scholarshp Contacts</h3>
	<ul class="apedata">
		{foreach name=endowmt_scholarshp_contacts from=$person->alumni->endowmt_scholarshp_contacts item=item}
			{if $smarty.foreach.endowmt_scholarshp_contacts.first}
			<li>
				<table class="grid sortable">
					<thead>
					<tr>
						<th>status</th>
						<th>contact_type</th>
						<th>endowment_scholarship</th>
					</tr>
					</thead>
					<tbody>
			{/if}
			<tr>
				<td>{$item.status}</td>
				<td>{$item.contact_type}</td>
				<td>{$item.endowment_scholarship}</td>
			</tr>
			{if $smarty.foreach.endowmt_scholarshp_contacts.last}</tbody></table></li>{/if}
			
		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>			
</div>
