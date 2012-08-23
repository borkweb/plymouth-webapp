{PSU_JS src="/webapp/training-tracker/js/index.js"}
{PSU_CSS href="css/index.css"}
{* TODO: OMG SPACES? Yea Bro. Spaces are the new thing. *}

{box size="16" title="Person selection"}
	{foreach from=$staff  item=staffer }
		<div class="staff light ui-corner-all smoothness" >
			<a href="staff/statistics/{$staffer->wpid}">View/edit {$staffer->person()->formatName("f l")}</a>{foreach from=$staffer->demerit item=demerit}<span title = "{$demerit.notes}" class='merit'>{icon id='ape-no' class='red'}</span> {/foreach} {foreach from=$staffer->merit item=merit}<span title = "{$merit.notes}" class='merit'>{icon id='ape-yes' class='green'}</span> {/foreach}
			<div id="{$staffer->wpid}" class="progressbar" data-progress="{$staffer->stats('progress')}"></div>		
		</div>
	{/foreach}
{/box}
