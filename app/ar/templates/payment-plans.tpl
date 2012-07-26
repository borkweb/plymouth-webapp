{capture assign=contract_run}
	{if ! $contracts_processing}
		<a href="{$PHP.BASE_URL}/payment-plans/process/contract">Run Process</a>
	{/if}
{/capture}

{capture assign=disbursement_run}
	{if ! $disbursements_processing}
		<a href="{$PHP.BASE_URL}/payment-plans/process/disbursement">Run Process</a>
	{/if}
{/capture}

{box size="16" title="Disbursements" title_size=8 secondary_title=$disbursement_run}
	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>File</th>
				<th>Level</th>
				<th>Loaded</th>
				<th>Parsed</th>
				<th>Processed</th>
				<th>Invalid IDs</th>
				<th>Amount Expected</th>
				<th>Amount Applied</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$disbursements item=feed}
			{assign var=total value=$feed->total()}
			{assign var=processed_total value=$feed->processed_total()}
			{assign var=invalid_id_count value=$feed->invalid_id_count()}
			{assign var=difference value=$feed->processed_difference()}

			{capture name=invalid_ids}{strip}
				{if $invalid_id_count > 0}
					<ul>
					{foreach from=$feed->invalid_id() item=invalid}
						<li>{$invalid->id} ({$invalid->amount()|money_format})</li>
					{/foreach}
					</ul>
				{/if}
			{/strip}{/capture}

			<tr class="{if $total != $processed_total}unequal-amounts{/if} {if $invalid_id_count > 0}has-invalid-ids{/if}">
				<td rel="tooltip" title="{$feed->file_name}" class="centered">{$feed->id}</td>
				<td class="centered">{if false != strpos( $feed->file_name, 'Grad' )}GR{else}UG{/if}</td>
				<td class="centered">{$feed->date_loaded_timestamp()|date_format:$date_format}</td>
				<td class="centered">{$feed->date_parsed_timestamp()|date_format:$date_format}</td>
				<td class="centered">{$feed->date_processed_timestamp()|date_format:$date_format}</td>
				<td class="invalid-ids centered" {if $invalid_id_count}title="Invalid IDs" data-content="{$smarty.capture.invalid_ids}"{/if}>{$invalid_id_count}</td>
				<td {if $difference}rel="tooltip" title="Off by {$difference|money_format}"{/if} class="expected alignright">{$total|money_format}</td>
				<td {if $difference}rel="tooltip" title="Off by {$difference|money_format}"{/if} class="processed alignright">{$processed_total|money_format}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/box}

{box size="16" title="Contracts" title_size=8 secondary_title=$contract_run}
	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>File</th>
				<th>Level</th>
				<th>Loaded</th>
				<th>Parsed</th>
				<th>Processed</th>
				<th>Invalid IDs</th>
				<th>Amount Expected</th>
				<th>Amount Memoed</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$contracts item=feed}
			{assign var=contract_date value=$feed->date_loaded_timestamp()}

			{if $feed->date_loaded_timestamp() > $contract_max_date}
				{assign var=contract_max_date value=$feed->date_loaded_timestamp()}
			{/if}

			{assign var=total value=$feed->total()}
			{assign var=processed_total value=$feed->processed_total()}
			{assign var=invalid_id_count value=$feed->invalid_id_count()}
			{assign var=difference value=$feed->processed_difference()}

			{capture name=invalid_ids}{strip}
				{if $invalid_id_count > 0}
					<ul>
					{foreach from=$feed->invalid_id() item=invalid}
						<li>{$invalid->id} ({$invalid->amount()|money_format})</li>
					{/foreach}
					</ul>
				{/if}
			{/strip}{/capture}

			<tr class="{if date('Y-m-d', $contract_date ) < date( 'Y-m-d', $contract_max_date )}old{/if} {if $total != $processed_total}unequal-amounts{/if} {if $invalid_id_count > 0}has-invalid-ids{/if}">
				<td rel="tooltip" title="{$feed->file_name}" class="centered">{$feed->id}</td>
				<td class="centered">{if false != strpos( $feed->file_name, 'Grad' )}GR{else}UG{/if}</td>
				<td class="centered">{$feed->date_loaded_timestamp()|date_format:$date_format}</td>
				<td class="centered">{$feed->date_parsed_timestamp()|date_format:$date_format}</td>
				<td class="centered">{$feed->date_processed_timestamp()|date_format:$date_format}</td>
				<td class="invalid-ids centered" {if $invalid_id_count}title="Invalid IDs" data-content="{$smarty.capture.invalid_ids}"{/if}>{$invalid_id_count}</td>
				<td {if $difference}rel="tooltip" title="Off by {$difference|money_format}"{/if} class="expected alignright">{$total|money_format}</td>
				<td {if $difference}rel="tooltip" title="Off by {$difference|money_format}"{/if} class="processed alignright">{$processed_total|money_format}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/box}
