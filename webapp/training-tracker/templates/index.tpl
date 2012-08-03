{PSU_JS src="/webapp/training-tracker/js/index.js"}
{PSU_CSS href="css/index.css"}
{* TODO: OMG SPACES? Yea Bro. Spaces are the new thing. *}

{box size="16" title="Person selection"}
	{foreach from=$staff  item=staffer }
		<div class="staff light ui-corner-all smoothness" >
			<a href="staff/statistics/{$staffer->wpid}">View/edit {$staffer->person()->formatName("f l")}</a>{foreach from=$staffer->demerit item=demerit}<img title = "{$demerit.notes}" class='merit' src="https://s0.plymouth.edu/images/icons/22x22/status/dialog-warning.png"> {/foreach} {foreach from=$staffer->merit item=merit}<img title = "{$merit.notes}" class='merit' src="images/star.png"> {/foreach}
			<div id="{$staffer->wpid}" class="progressbar" data-progress="{$staffer->stats('progress')}"></div>		
		</div>
	{/foreach}
{/box}
