{if $url}
	{if $no_url_params && (($url|strpos:"[value]") == false)}
		{assign var=url value="`$url`?`$identifier`="}
	{elseif ($url|strpos:"[value]") == false}
		{assign var=url value="`$url`&`$identifier`="}
	{/if}
{/if}
{foreach from=$myuser->myrelationships->get() item=relationship key=key}
	{if $relationship->initiator->wpid == $myuser->wp_id}
		{assign var=rel value=$relationship->target}
	{else}
		{assign var=rel value=$relationship->initiator}
	{/if}
	{if $rel->grants($permission)}
		{capture name=rel_options}
			{$smarty.capture.rel_options}
			{if $type == 'select'}
				{if $identifier == 'pidm'}
				<option value="{$rel->person->pidm}" {if $selected->pidm == $rel->person->pidm}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
				{elseif $identifier == 'id'}
				<option value="{$rel->person->id}" {if $selected->id == $rel->person->id}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
				{elseif $identifier == 'username' || $identifier == 'login_name'}
				<option value="{$rel->person->login_name}" {if $selected->login_name == $rel->person->login_name}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
				{else}
				<option value="{$rel->person->wp_id}" {if $selected->wp_id == $rel->person->wp_id}selected="selected"{/if}>{$rel->person->formatName('f m l')}</option>
				{/if}
			{else}
				{if $rel->person->id && $url}
					{if $identifier == 'pidm'}
					<li class="bill_{$rel->person->pidm}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$rel->person->pidm}{/if}">{$rel->person->formatName('f m l')}</a></li>
					{elseif $identifier == 'id'}
					<li class="bill_{$rel->person->id}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$rel->person->id}{/if}">{$rel->person->formatName('f m l')}</a></li>
					{elseif $identifier == 'username' || $identifier == 'login_name'}
					<li class="bill_{$rel->person->login_name}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$rel->person->login_name}{/if}">{$rel->person->formatName('f m l')}</a></li>
					{else}
					<li class="bill_{$rel->person->wp_id}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$rel->person->wp_id}{/if}">{$rel->person->formatName('f m l')}</a></li>
					{/if}
				{else}
					<li>{$rel->person->formatName('f m l')}</li>
				{/if}
			{/if}
		{/capture}
	{/if}
{/foreach}
{if $smarty.capture.rel_options && $type == 'select'}
	<select name="{if $name}{$name}{else}{$identifier}{/if}">
		{if $family_member == false}
			{if $identifier == 'pidm'}
			<option value="{$myuser->pidm}" {if $selected->pidm == $myuser->pidm}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
			{elseif $identifier == 'id'}
			<option value="{$myuser->id}" {if $selected->id == $myuser->id}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
			{elseif $identifier == 'username' || $identifier == 'login_name'}
			<option value="{$myuser->login_name}" {if $selected->login_name == $myuser->login_name}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
			{else}
			<option value="{$myuser->wp_id}" {if $selected->wp_id == $myuser->wp_id}selected="selected"{/if}>{$myuser->formatName('f m l')}</option>
			{/if}
		{/if}
		{$smarty.capture.rel_options}
	</select>
{elseif $smarty.capture.rel_options && $type == 'list'}
	<ul class="myrel_bills">
		{if $family_member == false}
			{if $myuser->id && $url}
				{if $identifier == 'pidm'}
				<li class="bill_{$myuser->pidm}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$myuser->pidm}{/if}">{$myuser->formatName('f m l')}</a></li>
				{elseif $identifier == 'id'}
				<li class="bill_{$myuser->id}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$myuser->id}{/if}">{$myuser->formatName('f m l')}</a></li>
				{elseif $identifier == 'username' || $identifier == 'login_name'}
				<li class="bill_{$myuser->login_name}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$myuser->login_name}{/if}">{$myuser->formatName('f m l')}</a></li>
				{else}
				<li class="bill_{$myuser->wp_id}"><a href="{if $url|strpos:"[value]"}{$url|replace:'[value]':$rel->person->id}{else}{$url}{$myuser->wp_id}{/if}">{$myuser->formatName('f m l')}</a></li>
				{/if}
			{else}
				<li>{$myuser->formatName('f m l')}</li>
			{/if}
		{/if}
		{$smarty.capture.rel_options}
	</ul>
{/if}
