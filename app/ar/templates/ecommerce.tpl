{box size="8" title="Processed E-Commerce"}
	<p>
		The following numbers are based on the data parsed from:
		<ul>
		{foreach from=$ecommerce_files item=file}
			<li>
				File ID: {$file.fileid} <em>(processed on {$file.processed_date|date_format})</em>
			</li>
		{/foreach}
		</ul>
	</p>
	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>Payment Type</th>
				<th style="width: 100px;">Total</th>
			</tr>
		</thead>
		<tbody>
		{assign var=ar_total value=0}
		{assign var=finance_total value=0}
		{assign var=deposit_total value=0}
		{foreach from=$ecommerce_report item=row}
			{if $row.type == 'ar' || $row.type == 'legacy ar'}
				{math equation="x + y" x=$ar_total y=$row.amount assign=ar_total}
			{elseif $row.type == 'deposit'}
				{math equation="x + y" x=$deposit_total y=$row.amount assign=deposit_total}
			{else}
				{math equation="x + y" x=$finance_total y=$row.amount assign=finance_total}
			{/if}
			<tr>
				<th class="alignleft">{$row.ordertype}</th>
				<td class="alignright">{$row.amount|money_format}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>Category</th>
				<th style="width: 100px;">Total</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th class="alignleft">Accounts Receivable</th>
				<td class="alignright">{$ar_total|money_format}</td>
			</tr>
			<tr>
				<th class="alignleft">Finance</th>
				<td class="alignright">{$finance_total|money_format}</td>
			</tr>
			<tr>
				<th class="alignleft">Deposits</th>
				<td class="alignright">{$deposit_total|money_format}</td>
			</tr>
		</tbody>
	</table>
{/box}
{box size="8" title="Pending E-Commerce"}
	<p>
		There are currently {$ecommerce_pending_files|@count} pending EOD file(s).
	</p>

	{if $ecommerce_pending}
	<p>
		There are pending transactions that have not been processed.
		<a href="{$PHP.BASE_URL}/ecommerce/process">Process them</a>.
	</p>
	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>Payment Type</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$ecommerce_pending item=row}
			<tr>
				<th class="alignleft">{$row.name}</th>
				<td class="alignright">{$row.amount|money_format}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	{/if}
{/box}
