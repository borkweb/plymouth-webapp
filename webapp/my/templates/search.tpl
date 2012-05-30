{include file='sidebar.tpl'}
{capture name="icons"}
<ul class="options">
	<li class="ui-state-default ui-corner-all icon-expand" title="Expand/Contract Channel">
		<span class="ui-icon ui-icon-triangle-2-e-w"></span>
	</li>
</ul>
{/capture}
{col size="6" class="search-column"}
	{box title="PSU Help Search" title_size="3" secondary_title=`$smarty.capture.icons` id="psu-help-search" class="channel search-channel" no_grid=true}
		 <iframe id="search-help" height="100%" width="100%"></iframe>
	{/box}
{/col}
{col size="6" class="search-column"}
	{box title="PSU Web Search" title_size="3" secondary_title=`$smarty.capture.icons` id="psu-web-search" class="channel search-channel" no_grid=true}
		 <div id="results_005322158811873917109:eb5xtxv98mg" class="results"></div>
		 <script type="text/javascript" src="https://www.google.com/afsonline/show_afs_search.js"></script>
	{/box}
{/col}
