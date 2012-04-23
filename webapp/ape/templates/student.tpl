<style>
.course-details label{
	width: 4em;
}
</style>
{if $AUTHZ.role.faculty || ($AUTHZ.banner.bannerinb && $AUTHZ.role.staff) || $AUTHZ.role.registrar}
<div id="user_info">
	{capture name="title"}{$person->formatName('f m l')} ({$person->id}){/capture}
	{box title="<span class='section-title'>Student:</span> `$smarty.capture.title`" secondary_title='<a href="?print"><img src="/images/icons/16x16/actions/document-print.png" class="icon"/> Print</a>' title_size=12 secondary_title_size=4}
	<img id="print-confidential" src="/webapp/style/templates/images/confidential_960.png"/>
	{if $AUTHZ.permission.ape_ssn}
		<div class="note">Note: all requests for SSNs, Pins, Cert Numbers, etc are logged.</div>
	{/if}
	
	{include file=student.alerts.tpl"}

		<div class="grid_8 alpha">
			{include file="blocks/block.identifiers.tpl"}
			{include file="blocks/block.biographical_information.tpl"}
			{include file="blocks/block.advisors.tpl"}
			{if $AUTHZ.role.registrar || $AUTHZ.permission.mis}
			{include file="blocks/block.six_week_grades.tpl"}
			{include file="blocks/block.highschool.tpl"}			
			{/if}
			{include file="blocks/block.contact_information.tpl"}			
		</div>
		
		<div class="grid_8 omega">
			{include file="blocks/block.student_data.tpl"}			
			{include file="blocks/block.curriculum.tpl"}
			{include file="blocks/block.schedule.tpl"}			
			{include file="blocks/block.webreg.tpl"}			
		</div>		
		<div class="clear"></div>

		{if $AUTHZ.role.registrar || $AUTHZ.permission.mis}
		<div class="grid_16 alpha omega">
		{include file="blocks/block.transcript.tpl"}
		</div>		
		<div class="clear"></div>
		<div class="grid_16 alpha omega">
		{include file="blocks/block.transfer_credit.tpl"}
		</div>		
		<div class="clear"></div>
		{/if}

	{/box}
</div>
<script>
$(function(){
	$('.course-title').bind('click', function(){
		var id = $(this).attr('id');
		id = id.replace('course', 'details');
		$('#' + id).slideToggle('fast');
		return false;
	});
});
</script>
{/if}
