{PSU_JS src="/webapp/training-tracker/js/index.js"}
{PSU_CSS href="/webapp/training-tracker/css/index.css"}

{box size="16" title="My team"}
	{if $is_mentor}
		{foreach from=$teams  item=staffer name=count}
			{if isset($staffer->wpid)}
					<div class="smoothness">
						<a href="/webapp/training-tracker/staff/statistics/{$staffer->wpid}">View/edit {$staffer->person()->formatName('f l')} - progress: {$staffer->stats('progress')}% </a>
						<div id="{$staffer->wpid}" data-progress="{$staffer->stats('progress')}" class="progressbar"></div>
					</div>
				{/if}
		{/foreach}
	{/if}
{/box}
