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
			<li id="{$rel->person->pidm}"><a href="{$PHP.WEBAPP_URL}/bill/?id={$rel->person->id}">{$rel->person->formatName('f m l')}</a></li>
			{elseif $identifier == 'id'}
			<li id="{$rel->person->id}"><a href="{$PHP.WEBAPP_URL}/bill/?id={$rel->person->id}">{$rel->person->formatName('f m l')}</a></li>
			{elseif $identifier == 'username' || $identifier == 'login_name'}
			<li id="{$rel->person->login_name}">{$rel->person->formatName('f m l')}</li>
			{else}
			<li id="{$rel->person->wp_id}">{$rel->person->formatName('f m l')}</li>
			{/if}
		{/capture}
	{/if}
{/foreach}

{if $smarty.capture.rel_options}
	<ul name="myrel_bills">
		{if $identifier == 'pidm'}
		<li id="{$myuser->pidm}"><a href="{$PHP.WEBAPP_URL}/bill/?id={$myuser->id}">{$myuser->formatName('f m l')}</a></li>
		{elseif $identifier == 'id'}
		<li id="{$myuser->id}"><a href="{$PHP.WEBAPP_URL}/bill/?id={$myuser->id}">{$myuser->formatName('f m l')}</a></li>
		{elseif $identifier == 'username' || $identifier == 'login_name'}
		<li id="{$myuser->login_name}"><a href="{$PHP.WEBAPP_URL}/bill/?id={$myuser->id}">{$myuser->formatName('f m l')}</a></li>
		{else}
		<li id="{$myuser->wp_id}"><a href="{$PHP.WEBAPP_URL}/bill/?id={$myuser->id}">{$myuser->formatName('f m l')}</a></li>
		{/if}
		{$smarty.capture.rel_options}
	</ul>
{/if}
