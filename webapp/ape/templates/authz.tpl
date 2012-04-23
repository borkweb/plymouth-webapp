{box title="Access Management"}
<script type="text/javascript">
	$(apejs.distauthz_init);
</script>
	<p>You may administer the following authorizations:</p>

	<div class="datacolumns" id="distauthz">
		<div id="col1" class="datacolumn">
			<ul>
				{foreach from=$dictionary item=role}
					{if $role.admin || $role.child_admin}
						<li class="heading {if $role.admin}clickable{/if}" rel="{$role.type_name}--{$role.attribute}"><h2>{$role.name}</h2></li>
						{foreach from=$role.children item=attribute}
							{if $attribute.admin}
							<li class="child clickable" rel="{$attribute.type_name}--{$attribute.attribute}">{$attribute.name} ({$attribute.attribute})</li>
							{/if}
						{/foreach}
					{/if}
				{/foreach}
			</ul>
		</div>
		<div id="col2" class="datacolumn">
		</div>
		<div id="col3" class="datacolumn datacolumnnolist datacolumnlast">
		</div>
		<div class="clear">&nbsp;</div>
	</div>
{/box}
<div class="add_user_template" style="display:none;">
	{* this template is cloned and added to the top of #col2 for user adding *}
	<ul>
		<li class="adduser">
			Add User: 
			<input type="text" size="15" name="adduser" id="adduser"> 
			<button>Add</button>
			<img src="/images/icons/16x16/animations/throbber.gif" height="16" width="16" style="display: none;" id="adduser-throbber"/>
			<div style="font-size: 0.8em;margin-top: 0.5em;">
			Start: {html_select_date prefix="start_" month_format="%b" day_value_format="%d" end_year='+10'}
			<br/>
			End: {html_select_date prefix="end_" month_format="%b" day_value_format="%d" year_empty='' month_empty='' day_empty='' end_year='+10' time='--'}
			</div>
			<div style="text-align:center;margin-top:0.5em;">
				<a href="#" class="reason_link">Enter Reason</a>
				<textarea name="reason" class="reason" style="text-align:left;width:250px;display:none;"></textarea>
			</div>
		</li>
	</ul>
</div>
{if $AUTHZ.permission.ape_attribute_admin}
{box title="Access Management Management"}
	{if $authz_message}
	<h3>{$authz_message}</h3>
	{/if}
	<form action="{$PHP.BASE_URL}/authz.html?action=grant" method="post" style="margin-top: 1em;">
		Give <input type="text" name="username" tabindex="5"/> the ability to administer <input type="text" name="attribute" tabindex="6"/>.  <button type="submit" tabindex="7">Do it</button>
	</form>
	
	<form action="{$PHP.BASE_URL}/authz.html?action=revoke" method="post" style="margin-top: 1em;">
		Remove <input type="text" name="username" tabindex="8"/>'s ability to administer <input type="text" name="attribute" tabindex="9"/>.  <button type="submit" tabindex="10">Do it</button>
	</form>
	
	<form action="{$PHP.BASE_URL}/authz.html" method="get" style="margin-top: 1em;">
		Display all users that can administer <input type="text" name="administer" tabindex="11"/>.  <button type="submit" tabindex="12">Do it</button>
	</form>

	<form action="{$PHP.BASE_URL}/authz.html" method="get" style="margin-top: 1em;">
		Display roles/permissions that <input type="text" name="administration" tabindex="13"/> can administer.  <button type="submit" tabindex="14">Do it</button>
	</form>
	{if $smarty.get.administer}
		<strong>The following people can administer:</strong> {$smarty.get.administer}
		<ul>
		{foreach from=$administrators item=admin}
			<li>{$admin}: (<a href="{$PHP.BASE_URL}/authz.html?action=revoke&username={$admin}&attribute={$smarty.get.administer}" class="revoke">revoke</a>)</li>
		{/foreach}
		</ul>
	{/if}
	{if $smarty.get.administration}
		{$smarty.get.administration} <strong>can administer the following roles and permissions:</strong>
		<ul>
		{foreach from=$admins item=admin}
			<li>{$admin}: (<a href="{$PHP.BASE_URL}/authz.html?action=revoke&username={$smarty.get.administration}&attribute={$admin}&return=user" class="revoke">revoke</a>)</li>
		{/foreach}
		</ul>
	{/if}	
{/box}
{/if}
