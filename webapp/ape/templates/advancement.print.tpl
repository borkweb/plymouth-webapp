<div id="user_info">

	{capture name="title"}{$person->formatName('f m l')} ({$person->id}){/capture}
	{box title=$smarty.capture.title}
	<img id="print-confidential" src="/webapp/style/templates/images/confidential_960.png"/>
	
	{include file="advancement.alerts.tpl"}

		{include file="blocks/block.identifiers.tpl"}
		{include file="blocks/block.contact_information.tpl"}
		{include file="blocks/block.biographical_information.tpl"}
		{include file="blocks/block.relationships.tpl"}
		{include file="blocks/block.prospect_information.tpl"}	
		{include file="blocks/block.comments_contacts.tpl"}
		{include file="blocks/block.activities.tpl"}
		{include file="blocks/block.mail_codes.tpl"}
		{include file="blocks/block.letter_history.tpl"}
		{include file="blocks/block.giving.tpl"}

	{/box}
</div>

<script type="text/javascript">
	$(function(){
		window.print();
	});
</script>
