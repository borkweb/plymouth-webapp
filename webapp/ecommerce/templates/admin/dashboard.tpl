<div class="block">
	
	<h2>{$page_title}</h2>
	
	{if $metrics}
		<table class="grid">
			<tr>
				<th>Department</th>
				<th>Count</th>
				<th>Value</th>
			</tr>
			{foreach from=$metrics item=metric key=token}
				<tr>
					<td>{$token}</td>
					<td>{$metric.metric}</td>
					<td>{if $metric.value}${$metric.value}{else}N/A{/if}</td>
				</tr>
			{/foreach}
		</table>

	{else}		
		<div class="styled_notice">
			<h2>There is no data to be displayed in the dashboard at this time.</h2>
		</div>
	{/if}

</div>