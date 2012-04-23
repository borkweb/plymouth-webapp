{box id="checklist_admin" class="grid_16 alpha omega" title=$checklist_manager->type|capitalize|replace:'-':' ' }
	<input id="checklist" type="hidden" value="{$checklist.0.type}" />
	<h3 id="pending">Pending:</h3>
	<ul class="checklists">
		{include file='checklist-admin-display.tpl checklists=$checklist_manager->checklists.pending }
	</ul>
	<h3 id="closed">Closed: 
	{if $pages && ( $pages.rows_per_page eq $pages.display_num || $pages.previous_page > 0 ) }
		{if $pages.previous_page > 0}
			<a href="{$PHP.BASE_URL}/checklist/{$checklist_manager->type}/{$pages.previous_page}/">Prev</a>
		{/if}
		{if $pages.last_page != $pages.current_page}
			<a href="{$PHP.BASE_URL}/checklist/{$checklist_manager->type}/{$pages.next_page}/">Next</a>
		{/if}
	{/if}
	</h3>
	<ul class="checklists">
		{include file='checklist-admin-display.tpl checklists=$checklist_manager->checklists.closed }
	</ul>
<div class="clear"></div>
{/box	}
