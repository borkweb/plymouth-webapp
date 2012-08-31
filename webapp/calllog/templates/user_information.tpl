<input type="hidden" id="caller_pidm" name="caller_pidm" value="{$caller.pidm}" />
<input type="hidden" id="caller_wp_id" name="caller_wp_id" value="{$caller.wp_id}" />
<input type="hidden" id="call_log_username" name="call_log_username" value="{$call_log_username}" />
<input type="hidden" id="caller_user_name" name="caller_user_name" value="{$caller.username}" />
<input type="hidden" id="caller_first_name" name="caller_first_name" value="{$caller.name_first}" />
<input type="hidden" id="caller_last_name" name="caller_last_name" value="{$caller.name_last}" />
<input type="hidden" id="caller_role" name="caller_role" value="{$caller.role}" />
<input type="hidden" id="caller_phone_number" name="caller_phone_number" value="{$caller.phone_number}" />

{if $edit_call_id}
	<input type="hidden" name="call_id" value="{$call_id}"/>
	<div class="ticket_num">
		<span>Ticket:</span> {$call_id}<br/>
		<a href="http://go.plymouth.edu/log/{$call_id}">http://go.plymouth.edu/log/{$call_id}</a>
	</div>
{/if}
<ul class="user-info">
	<li class="user">
		<a href="mailto:{$caller.email}">{$caller.name_full}</a> 
		{if $ape}
			(<a href="https://www.plymouth.edu/webapp/ape/user/{$caller.identifier}" target="_blank"><img src="https://www.plymouth.edu/images/icons/16x16/emotes/face-monkey.png" style="vertical-align: middle;"/> {$caller.username}</a>)
		{else}
			({$caller.username})
		{/if}
	</li>
	{if $phone}
		<li>
			<label>Phone:</label>
			{$caller.phone_number}
		</li>
	{/if}
	{if $location}
		<li>
			<label>Location:</label>
			{$caller.location}
		</li>
	{/if}
	{if $caller_title}
		<li>
			<label>Title:</label>
			{$caller.title}
		</li>
	{/if}
	{if $dept}
		<li>
			<label>Department:</label>
			{$caller.dept}
		</li>
	{/if}
	{if $role}
		<li>
			<label>Roles:</label>
			{$caller.role}
		</li>
	{/if}
	<li style="clear:both;"></li>
</ul>
<input type="hidden" name="caller_phone_number" value="{$caller.phone_number}"/>
<input type="hidden" name="caller_location" value="{$caller.location}"/>
<input type="hidden" name="caller_title" value="{$caller.title}"/>
<input type="hidden" name="caller_department" value="{$caller.dept}"/>
<input type="hidden" name="caller_role" value="{$caller.role}"/>
