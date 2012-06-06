<div id="user_info">
	{capture name="title"}{$person->formatName('f m l')} ({$person->id}){/capture}
	{capture name="print}<a href="?print">{icon id="ape-print" boxed=true} Print</a>{/capture}
	{box title="<span class='section-title'>Advancement:</span> `$smarty.capture.title`" secondary_title=$smarty.capture.print title_size=12 secondary_title_size=4 size=16}
	<img id="print-confidential" src="/webapp/style/templates/images/confidential_960.png"/>
	{if $AUTHZ.permission.ape_ssn}
		<div class="note">Note: all requests for SSNs, Pins, Cert Numbers, etc are logged.</div>
	{/if}
	
	{include file="advancement.alerts.tpl"}

		<div class="grid_8 grid-internal">
			{include file="blocks/block.identifiers.tpl"}
			{include file="blocks/block.biographical_information.tpl"}
			{include file="blocks/block.comments_contacts.tpl"}
			{include file="blocks/block.activities.tpl"}
			{include file="blocks/block.mail_codes.tpl"}
			{include file="blocks/block.letter_history.tpl"}
		</div>
		
		<div class="grid_8 grid-internal">
			{include file="blocks/block.relationships.tpl"}
			{include file="blocks/block.contact_information.tpl"}			
			{include file="blocks/block.prospect_information.tpl"}	
		</div>		
		
		<div class="clear"></div>
		
		{include file="blocks/block.giving.tpl"}

		<div class="clear"></div>

	{/box}
</div>
