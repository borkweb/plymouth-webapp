{if !$smarty.get.ajax}
{box title=""}
<div class="grid_8 prefix_4 suffix_4 alpha omega">
	<p>
		In the event of <a href="http://www.plymouth.edu/commencement/undergraduate/weather.html">inclement weather</a>, 
		commencement ceremonies will be held in <a href="http://www.plymouth.edu/commencement/undergraduate/weather.html">three indoor locations</a> on campus.
		To find the commencement location for a graduating senior, search for that senior by name:
	</p>
	<form method="get" style="text-align:center;margin-bottom: 1em;">
		<input name="search"/> <button type="submit">Search</button>
	</form>
	<p>
		Check the <a href="http://www.plymouth.edu/commencement/undergraduate/">Commencement Web site</a> for additional information.
	</p>
</div>
<div class="clear"></div>
{/box}
{/if}
{if $smarty.get.search}
	{if $smarty.get.ajax}
		{include file="search_list.tpl"}
	{else}
		<style>
			#raintix-results li{
				border-bottom: 1px solid #ccc;
				list-style-type: none;
				margin-bottom: 1em;
				padding-bottom: 1em;
			}
			#raintix-results label{
				display: inline;
				font-weight: bold;
			}
			#raintix-results .head{
			}
			#raintix-results .major{
				float: right;
			}
		</style>
		{box title="Search Results"}
			{include file="search_list.tpl}
			<div class="clear"></div>
		{/box}
	{/if}
{/if}
