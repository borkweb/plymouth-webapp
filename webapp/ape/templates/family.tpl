<div id="user_info">
	{capture name="title"}{$person->formatName('f m l')} ({$person->id}){/capture}
	{box title="<span class='section-title'>Family:</span> `$smarty.capture.title`" size=16}
	<img id="print-confidential" src="/webapp/style/templates/images/confidential_960.png"/>
	{if $AUTHZ.permission.ape_ssn}
		<div class="note">Note: all requests for SSNs, Pins, Cert Numbers, etc are logged.</div>
	{/if}
	
	{include file="advancement.alerts.tpl"}

		<div class="grid_8 grid-internal">
			{include file="blocks/block.identifiers.tpl"}
      {include file="blocks/block.contact_information.tpl"}
		</div>
		
		<div class="grid_8 grid-internal">
			{include file="blocks/block.family-relationships.tpl"}
		</div>

		<div class="clear"></div>

	{/box}
</div>
