<div style="display:none;">
{foreach from=$finaid->fund_messages item=message}
	<div id="award-message_{$message->fund_code}">
		<h4>{$message->fund}</h4>
		<p>{$message->text|nl2br|link_urls}</p>
	</div>
{/foreach}
</div>

{capture name=title}
	Financial Aid Award for {$target->formatName('f l')}
	{if $params.admin}
		<a href="http://go.plymouth.edu/ape/{$target->id}" class="ape-link" target="_blank"><img src="{"/images/icons/16x16/emotes/face-monkey.png"|cdn}"/></a>
	{/if}
{/capture}

{box size="16" title=$smarty.capture.title}
	{if $finaid->fafsa_received()}
		<p>Your {$aid_year->year_range()} FAFSA was received on {$finaid->fafsa_receive_date|date_format}.</p>
	{/if}
	{if $finaid->awards->has_awards()}
		<p>Contact the <a href="/office/financial-aid/contact-information/">Financial Aid Team</a>
		in writing if you wish to decline an award that is in accepted status. All financial aid awards are subject to
		<a href="/office/financial-aid/terms-of-award/">Terms of Award</a>.</p>

		<table class="grid">
			<thead>
				<tr>
					<th>Fund</th>
					<th>Details</th>
					<th>Status</th>
					{foreach from=$finaid->awards->termcodes() key=term item=desc}
						<th class="monetary">{$desc}</th>
					{/foreach}
					<th class="monetary">Total Award</th>
				</tr>
			</thead>
			<tbody>
				{include file="index-awards.tpl" subtotal=$finaid->awards->offered()->not_empty() field="accepted" awards=$finaid->awards->exclude_offered()}
				{include file="index-awards.tpl" awards=$finaid->awards->offered() field="offered" subtotal=false}
				<tr class="total-row total-overall">
					<td colspan="3" class="label">Overall Total:</td>
					{foreach from=$finaid->awards->termcodes() key=term item=desc}
						<td class="monetary award-sum" data-selector="award-term-{$term}"></td>
					{/foreach}
					<td class="monetary award-sum" data-selector="award-term"></td>
				</tr>
			</tbody>
		</table>

		<p>For information about additional options to assist in paying your PSU
		bill, please visit our <a href="http://go.plymouth.edu/payingforschool">Paying for School</a> guide.</p>
		<p>Your PSU loan history, including estimated payments upon graduation can be found <a href="http://go.plymouth.edu/exitloans">here</a>.</p>
	{else}
		{if $smarty.now < $aid_year->end_date_ts()}
			<p>Your financial aid award has not yet been completed for this aid year. Note, you may have requirements that need to be submitted before a financial aid award can be determined. Please see "Requirements" below.</p>
		{else}
			<p>There are no financial aid awards for this aid year.</p>
		{/if}
	{/if}
{/box}

<div class="clear"></div>

{box size=16 title="Requirements"}
	{if $user->wp_id === $target->wp_id }
		{assign var=unsatisfied value=$finaid->requirements->unsatisfied()}
		{assign var=satisfied value=$finaid->requirements->satisfied()}
	{else}
		{assign var=unsatisfied value=$finaid->requirements->unsatisfied_non_academic()}
		{assign var=satisfied value=$finaid->requirements->satisfied_non_academic()}
	{/if}

	{if $unsatisfied->not_empty() || $satisfied->not_empty()}
		<p>This Requirements section will identify items which must be completed in order for PSU to help you obtain financial aid. <strong>Documents may be faxed to our office at 603-535-2627 or mailed to the PSU Financial Aid Team, Plymouth State University, 17 High Street, MSC #18, Plymouth, NH 03264</strong>. In addition, <strong>you may <a href="/office/financial-aid/contact-information/request-form/">electronically submit documents</a> using <a href="/office/financial-aid/contact-information/request-form/">our contact form</a></strong>.</p>
		<p>Once all requirements are received (and you are admitted), your file will be pulled for Director review. Files are reviewed "once a week". At that time, your award will be determined or additional information may be required for further clarification. You will receive an e-mail once your award has been completed or if additional information is required based upon our review.</p>

		{include file='index-requirements.tpl' title="Action Required" requirements=$unsatisfied show_instructions=true as_of_label="Date Posted"}
		{include file='index-requirements.tpl' title="Requirements Received and/or Completed" requirements=$satisfied show_instructions=false as_of_label="Date Completed"}
	{else}
		<p>There are no requirements to show for this aid year.</p>
	{/if}
{/box}

<div class="clear"></div>

{box size=16 title="Cost of Attendance"}
	{if $student_aid_year->attendancecost->not_empty()}
		<div class="grid_7 alpha">
		<table class="grid myfinaid-coa">
			<thead>
				<tr>
					<th>Description</th>
					<th class="monetary">Cost</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$student_aid_year->attendancecost item=component}
					<tr>
						<td>{$component->description}</td>
						<td class="monetary">{$component->amount_formatted}</td>
					</tr>
				{/foreach}
				<tr class="total-row">
					<td class="label">Total:</td>
					<td class="monetary">{$student_aid_year->attendancecost->total_formatted()}</td>
				</tr>
			</tbody>
		</table>
		</div>

		{if $finaid->status}
			<div class="grid_8 omega">
			{foreach from=$finaid->status item=status}
				<p class="status-{$status->code|lower}">{$status->budget_message_clean()}</p>
			{/foreach}
			</div>
		{/if}
		<div class="clear"></div>
	{else}
		<p>There are no attendance cost line items to show for this aid year. As soon as your financial aid award is completed your cost of attendance will be detailed in this section.</p>
	{/if}
{/box}

<div class="clear"></div>
