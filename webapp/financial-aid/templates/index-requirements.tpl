{if $requirements->not_empty()}
	<div style="display:none;">
		{foreach from=$requirements item=requirement}
			<div id="requirements_{$requirement->code|cssslug}">
				<h4>{$requirement->longdesc_clean()}</h4>
				<p>{$requirement->instructions|nl2br}</p>
			</div>
		{/foreach}
	</div>
	<h3>{$title}</h3>
	<table class="grid finaid-requirements">
		<thead>
			<tr>
				<th>Requirement</th>
				{if $show_instructions}
					<th class="finaid-req-inst">Instructions</th>
					<th class="finaid-req-form">Form</th>
					<th class="finaid-req-website">Website</th>
				{/if}
				<th>Status</th>
				<th>{$as_of_label|default:'As of'}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$requirements item=requirement}
				<tr data-reqcode="{$requirement->code|cssslug}">
					<td>{$requirement->longdesc_clean()}</td>
					{if $show_instructions}
						<td class="finaid-req-inst">
							{if $requirement->has_instructions()}
								<a href="#" class="instructions">View</a>
							{/if}
						</td>
						<td class="finaid-req-form">
							{if $requirement->url && $requirement->url_is_pdf()}
								<a target="_blank" href="{$requirement->url|escape}">Download</a>
							{/if}
						</td>
						<td class="finaid-req-website">
							{if $requirement->url && ! $requirement->url_is_pdf()}
								<a target="_blank" href="{$requirement->url|escape}">Visit</a>
							{/if}
						</td>
					{/if}
					<td>{$requirement->status}</td>
					<td>{$requirement->as_of_timestamp()|date_format:'%b %d %Y'}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}
