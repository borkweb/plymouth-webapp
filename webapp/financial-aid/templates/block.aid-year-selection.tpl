<form method="post" action="{$PHP.BASE_URL}/set-params">
	View Financial Aid for 
	<select name="aid_year">
		{foreach from=$aid_years item=aid_years_year}
		<option value="{$aid_years_year->aidy_code}" {if (!$aid_year && $aid_years_year->is_current == 'Y') || ($aid_year->aidy_code == $aid_years_year->aidy_code)}selected="selected"{/if}>{$aid_years_year->aidy_desc}</option>
		{/foreach}
	</select>
	{if $params.admin}
	for <input type="text" name="id" size="9" value="{$target->id}"/>
	{elseif $user->myrelationship_grants.finaid}
	for {myrel_access permission='finaid' user=$user selected=$target identifier='id'}
	{/if}
	<button type="submit">Go</button>
</form>
