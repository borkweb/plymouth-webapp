{box size=16 title="Search"}
	<form class="label-left" action="{$PHP.BASE_URL}/admin/equipment/item/model/{$items.0.model}/list/daterange" >
		<ul>
			<li><label>From: </label><input type="text" id="fromdate" name="from_date"></li>

			<li><label>To: </label><input type="text" id="todate" name="to_date"></li>
			<li><input type="Submit" name="Search" value="Search"></li>
			<span class="note">Note: This will generate a gantt chart within this timeframe. More than two weeks can cause the graph to be unreadable.</span>

		</ul>
	</form>
{/box}
{box size=16 title=$title}
	<a href="{$PHP.BASE_URL}/admin/equipment/item/model/{$items.0.model}/list/lastweek">Last Week</a>|
	<a href="{$PHP.BASE_URL}/admin/equipment/item/model/{$items.0.model}/list/thisweek">This Week</a>|
	<a href="{$PHP.BASE_URL}/admin/equipment/item/model/{$items.0.model}/list/nextweek">Next Week</a>

{/box}
{box size=16 title="Equipment Availability"}
	{$gantt_chart}
{/box}
{foreach from=$items item="item"}
	{include file="item.tpl" item=`$item`}
{/foreach}
