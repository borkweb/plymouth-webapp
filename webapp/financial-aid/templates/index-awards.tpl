{* $awards is an Iterable of PSU_Student_Finaid_Award objects *}
{if $awards->not_empty()}
	{foreach from=$awards item=award name=awards}
		<tr class="award award-field-{$field} award-fund-{$award->fund_code} {if $award->has_message()}message-available{/if}" id="award_{$award->fund_code}">
			<td class="title">{$award->fund}</td>
			<td class="details"><span class="view"><a href="#" rel="award-message">View</a></span></td>
			<td class="status status-{$award->status|lower}">{$award->status}</td>
			{foreach from=$finaid->awards->terms() key=term item=desc}
				{if $award.$term}
					<td class="award-term award-term-{$term} award-term-{$term}-{$field} monetary">
						{if $award.$term->has_declined()}
							<span class="details"></span>
							<ul class="tooltip">
								<li><strong>Declined:</strong> {$award.$term->declined_formatted()}</li>
								<li><strong>Accepted:</strong> {$award.$term->accepted_formatted()}</li>
							</ul>
						{/if}
						<span class="award-value" data-value="{$award.$term->field($field)*100}">{$award.$term->field_formatted($field)}</span>
					</td>
				{else}
					<td></td>
				{/if}
			{/foreach}
			<td class="monetary award-sum" data-selector="award-fund-{$award->fund_code}"></td>
		</tr>
	{/foreach}
	{if $subtotal}
		<tr class="total-row">
			<td colspan="3" class="label">Subtotal:</td>
			{foreach from=$finaid->awards->termcodes() key=term item=desc}
				<td class="monetary award-sum" data-selector="award-term-{$term}-{$field}"></td>
			{/foreach}
			<td class="monetary award-sum" data-selector="award-field-{$field}"></td>
		</tr>
	{/if}
{/if}
