{* CSV report template. *}
name_last,name_first_middle,{if ! $PHP.IS_SUMMER}event,{/if}certificate,addr1,addr2,city,state,zip
{foreach from=$students item=student}
{strip}
	"{$student.name_last}",
	"{$student.first_middle}",
	{if ! $PHP.IS_SUMMER}
		"{if $student.confirmed == -1}
			Did Not Answer
		{elseif $student.confirmed == 0}
			No
		{else}
			Yes
		{/if}",
	{/if}
	"{if $student.confirmed_cert == -1}
		Did Not Answer
	{elseif $student.confirmed_cert == 0}
		No
	{else}
		Yes
	{/if}",
	"{$student.addr1}",
	"{$student.addr2}",
	"{$student.city}",
	"{$student.state}",
	"{$student.zip}"{/strip}
{/foreach}
