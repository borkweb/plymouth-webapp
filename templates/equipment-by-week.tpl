{box title="Search"}
	<form class="label-left" action="{$PHP.BASE_URL}/admin/equipment/by-week/daterange" >
		<ul class="form-actions">
			<li><label>From: </label><input type="text" id="fromdate" name="from_date"></li>

			<li><label>To: </label><input type="text" id="todate" name="to_date"></li>
			<li><input type="Submit" name="Search" value="Search"></li>
		</ul>
		<span class="note">Note: Queries are limited to 35 results.</span>
	</form>
{/box}
{box size=16 title=$title}
	<a href="{$PHP.BASE_URL}/admin/equipment/by-week/lastweek">Last Week</a>|
	<a href="{$PHP.BASE_URL}/admin/equipment/by-week/thisweek">This Week</a>|
	<a href="{$PHP.BASE_URL}/admin/equipment/by-week/nextweek">Next Week</a>

{/box}
{if $reservations}
		{foreach from=$reservations item="reservation"}
			{include file="reservation-equipment.tpl" item=$reservation}
		{/foreach}
{else}
{box}
	<span>There are no reservations.</span>
{/box}
{/if}
