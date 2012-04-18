{foreach from=$myuser->myrelationships->get() item=relationship key=key}
	{if $relationship->initiator->wpid == $myuser->wp_id}
		{assign var=rel value=$relationship->target}
	{else}
		{assign var=rel value=$relationship->initiator}
	{/if}
	{if $rel->grants($permission)}
		{capture name=rel_options}
			{$smarty.capture.rel_options}
			{if $identifier == 'pidm'}
			<option value="{$rel->person->pidm}" {if $selected->pidm == $rel->person->pidm}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
			{elseif $identifier == 'id'}
			<option value="{$rel->person->id}" {if $selected->id == $rel->person->id}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
			{elseif $identifier == 'username' || $identifier == 'login_name'}
			<option value="{$rel->person->login_name}" {if $selected->login_name == $rel->person->login_name}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
			{else}
			<option value="{$rel->person->wp_id}" {if $selected->wp_id == $rel->person->wp_id}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
			{/if}
		{/capture}
	{/if}
{/foreach}

{if $smarty.capture.rel_options}
	<select name="{if $name}{$name}{else}{$identifier}{/if}">
		{if $identifier == 'pidm'}
		<option value="{$myuser->pidm}" {if $selected->pidm == $myuser->pidm}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
		{elseif $identifier == 'id'}
		<option value="{$myuser->id}" {if $selected->id == $myuser->id}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
		{elseif $identifier == 'username' || $identifier == 'login_name'}
		<option value="{$myuser->login_name}" {if $selected->login_name == $myuser->login_name}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
		{else}
		<option value="{$myuser->wp_id}" {if $selected->wp_id == $myuser->wp_id}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
		{/if}
		{$smarty.capture.rel_options}
	</select>
{/if}
