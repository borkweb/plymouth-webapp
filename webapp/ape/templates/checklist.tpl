<div id="user_info">
{capture name=person_name}{$person->formatName('f m l')}{/capture}
{box title="Employee Clearance Checklist for <a href='`$PHP.BASE_URL`/user/`$person->pidm`'>`$smarty.capture.person_name`</a>"}
	{if $AUTHZ.permission.ape_checklist_employee_exit_infotech}
		<div class="grid_8 alpha">
			{include file='blocks/block.identifiers.tpl'}
			{include file='blocks/block.accounts.tpl'}
			<div class="clear"></div>
		</div>
		<div class="grid_8 omega">
			{include file="blocks/block.roles.tpl"}
			<div class="clear"></div>
		</div>
	{else}
		{include file='blocks/block.identifiers.tpl'}
	{/if}
		<div class="clear"></div>
{/box}
{if !$checklist_items}
	{if $AUTHZ.permission.ape_checklist_employee_exit_hr}
	{box}
	<div class="checklist_not_started">
		<p> The employee clearance process has not been started for this individual.</p>
		<p> Would you like to start it? </p>
		<form method="post">
			<ul>
				<li>
					<input type="text" class="hide" value="{$checklist}" name="list_type" />
					<input type="text" class="hide" value="{$person->pidm}" name="pidm" />
				</li>
				<li>
					<label class="checklist_position">Position to trigger process on:</label>
					{if sizeof($person->employee_positions) > 0}
					<select name="position">
					{foreach from=$person->employee_positions key=position_code item=position}
						<option value="{$position_code}">{$position.organization_title}: {$position.classification}</option>
					{/foreach}
					</select>
					{else}
						{$person->employee_positions|@current}
					{/if}
				</li>
				<li>
					<label class="checklist_end_date">Employment end date:</label>
					<input type="text"  name="end_date" class="has_datepicker" />
				</li>
				<li><button class="checklist_submit" type="submit" name="submission">Initiate Process</button></li>
			</ul>
		</form>
	</div>
	{/box}
	{/if}
{else}
{box class="center"}
	<a href="{$PHP.BASE_URL}/hr_checklist_pdf.php?identifier={$person->pidm}&complete={$complete}&checklist={$checklist}"><img src="{$PHP.BASE_URL}/images/pdf.png" width="22" height="22" alt="" style="vertical-align: middle;"/> Save as PDF</a>
	{if !$closed && ($AUTHZ.permission.mis || $AUTHZ.permission.ape_checklist_employee_exit_hr)}
		<div>
			<a href="{$PHP.BASE_URL}/user/{$person->pidm}/checklist/employee-exit/?toggle=1&checklist={$checklist}">Close Checklist</a>
		</div>
	{elseif $closed && ($AUTHZ.permission.mis || $AUTHZ.permission.ape_checklist_employee_exit_hr)}
		<div>
			<a href="{$PHP.BASE_URL}/user/{$person->pidm}/checklist/employee-exit/?toggle=0&checklist={$checklist}">Re-Open Checklist</a>
		</div>
	{/if}
{/box}
{if sizeof($person->employee_positions) > 1}
<div class="message-container">
	<div class="message message-warnings">
		Warning:  This user has multiple positions at PSU.
		<ul>
			{foreach from=$person->employee_positions key=position_code item=position}
				<li>{$position.organization_title}: {$position.classification}</li>
			{/foreach}
		</ul>
	</div>
</div>
{/if}
{if !$closed}<form class="checklist" method="post">{else}<div class="checklist">{/if}
	{if $AUTHZ.permission.ape_checklist_employee_exit_hr}
		{if $complete && !$closed}
		<div id="complete" class="message-container">
			<div class="message message-messages">
				It appears as if all of the items have been reviewed and completed.<br>
				Mark this checklist as complete?<br/>

				<img src="{$PHP.BASE_URL}/templates/images/positive.png" height="22px" width="22px;" alt="Completed" />
				<input {if $closed.meta_value == 'true'}checked="checked"{/if} type="checkbox" name="checklist_closed" value="true" /> Yes!<br><br>
				<button type="submit">Save</button>
			</div>
		</div>
		{elseif $closed}
		<div id="complete" class="message-container">
			<div class="message message-messages">
				This Employee Clearance Form has been closed by {$closed_by->formatName('f m l')}.
			</div>
		</div>
		{/if}
	{/if}
	{if $closed && !$AUTHZ.permission.ape_checklist_employee_exit_hr}
		<div id="complete" class="message-container">
			<div class="message message-messages">
				This Employee Clearance Form has been closed by HR.
			</div>
		</div>
{else}
	{if !$AUTHZ.permission.ape_checklist_employee_exit_hr}
		<div id="complete" class="message-container">
			<div class="message message-messages">
				Please contribute any information you may have regarding the following item(s).
			</div>
		</div>
	{/if}
	<ul>
	{foreach from=$checklist_items item=checklist_item key=key}
		{if $key != 'id' && $key != 'type' && $key != 'pidm'}
			{if ($key == 'Campus Police' && $AUTHZ.permission.ape_checklist_employee_exit_police) || 
				($key == 'Travel Office/Accounts Payable' && $AUTHZ.permission.ape_checklist_employee_exit_payable) ||
				($key == 'Residential Life' && $AUTHZ.permission.ape_checklist_employee_exit_reslife) || 
				($key == 'Library' && $AUTHZ.permission.ape_checklist_employee_exit_library) || 
				($key == "Student Account Services Office" && $AUTHZ.permission.ape_checklist_employee_exit_bursar) || 
				($key == 'Physical Plant' && $AUTHZ.permission.ape_checklist_employee_exit_physicalplant) || 
				($AUTHZ.permission.ape_checklist_employee_exit_hr) || 
				($key == 'Information Technology' && $AUTHZ.permission.ape_checklist_employee_exit_infotech) ||
				($key == 'Department' && $myuser->department == $person->department )
			}
			<li id="category_{$slugs.$key}">
				{capture name=category_title}{$key}{if $key == 'Department'}: {$person->department}{/if}{/capture}
				{box title=$smarty.capture.category_title}
				<ul class="checklist">
					{foreach from=$checklist_item item=entry}
						<li id="item_{$entry.slug}"class="checklist_item">
							<h4>{$entry.name}: <span>{$entry.description}</span></h4>

							<div class="notes">
								<label style="float:none;">Do you have more details or anything else to add?</label>
								<textarea name="notes_{$entry.id}" cols="80" rows="4"></textarea>
							</div>
							<ul class="checklist-options">
								<li class="check">
								<label><img src="{$PHP.BASE_URL}/templates/images/positive.png" style="height:16px;width:16px;" alt="Completed" for="response_{$entry.id}_complete"/></label>
								<input {if $entry.records.0.response == 'complete'}checked="checked"{/if} type="checkbox" id="response_{$entry.id}_complete" name="response_{$entry.id}" value="complete" />
								Yes!
								</li>
								<li class="check">
									<label><img src="{$PHP.BASE_URL}/templates/images/na.png" style="height:16px;width:16px;" alt="N/A" for="response_{$entry.id}_na"/></label>
									<input {if $entry.records.0.response == 'n/a'}checked="checked"{/if} type="checkbox"  id="response_{$entry.id}_na" name="response_{$entry.id}" value="n/a" />
									This does not apply to this person.
								</li>
								<li class="check">
									<label><img src="{$PHP.BASE_URL}/templates/images/negative.png" style="height:16px;width:16px;" alt="No" for="response_{$entry.id}_incomplete"/></label>
									<input {if $entry.records.0.response == 'incomplete'}checked="checked"{/if} type="checkbox"  id="response_{$entry.id}_incomplete" name="response_{$entry.id}" value="incomplete" />
									No.  This has not yet happened.
								</li>
							</ul>
							<div class="clear"></div>
							{if $entry.records}
							<h5>Checklist History</h5>
							<ul class="checklist-history">
								{foreach from=$entry.records item=record name=history_loop}
								<li class="checklist_record {if $smarty.foreach.history_loop.first}first-record{/if}" title="{$record.notes}">
										Marked as 
										{if $record.response == 'incomplete'}
											<img src="{$PHP.BASE_URL}/templates/images/negative.png" alt="Incomplete" style="height:16px;width:16px;" />
										{elseif $record.response == 'complete'}
											<img src="{$PHP.BASE_URL}/templates/images/positive.png" alt="Completed" style="height:16px;width:16px;" />
										{elseif $record.response == 'n/a'}
											<img src="{$PHP.BASE_URL}/templates/images/na.png" alt="N/A" style="height:16px;width:16px;" />
										{/if} by <a href="{$PHP.BASE_URL}/user/{$record.pidm}">{$record.updated_by}</a> at {$record.item_date|date_format:"%r on %b %e, %Y"} {if $record.notes}: {$record.notes}{/if}
								</li>
								{/foreach}
							</ul>
							{/if}
						</li>
					{/foreach}
				</ul>
				{/box}
			</li>
		{/if}
		{/if}
	{/foreach}
	{if !$closed}
	<li class="checklist_complete">
		{if $update_permissions}
			<input class='save' type="submit" name="submission" value="Save Changes" />
		{/if}
	</li>	
	{else}
	<script>
		$(function(){
			$('.checklist-options input').attr('disabled', 'disabled');
			$('.notes').hide();
		});
	</script>
	{/if}
	</ul>
{/if}
{if !$closed}</form>{else}</div>{/if}
{/if}
<div class="clear"></div>
</div>
