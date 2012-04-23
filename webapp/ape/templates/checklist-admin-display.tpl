{foreach from=$checklists item=checklist }
	<li class="checklist">
		<div class="head">
			{$checklist.person_name} <a href="{$PHP.BASE_URL}/user/{$checklist.pidm}" class="ape-link" target="_blank"><img src="/images/icons/16x16/emotes/face-monkey.png"/></a>
			<div class="options">
				<span class="sub">
				{if $checklist.meta.closed}
					<label>Closed:</label> {$checklist.activity_date|date_format}
				{else}
					<label>Initiated:</label> {$checklist.activity_date|date_format}
				{/if}
				</span>
				<label>Termination:</label> {$checklist.meta.end_date.meta_value|date_format}
				[<a href="{$PHP.BASE_URL}/user/{$checklist.pidm}/checklist/{$checklist.type}">View/Edit</a>]
				[<a href="{$PHP.BASE_URL}/checklist-admin.html?hide={$checklist.id}">Hide</a>]
			</div>
			<input type="hidden" class="checklist-subject" value="{$checklist.pidm}" /> 
		</div>
		<table class="checklist_items" style="display:none;">
			{include file='checklist-admin-item.tpl' categories=$checklist.category}
		</table>
	</li>
{/foreach	}
