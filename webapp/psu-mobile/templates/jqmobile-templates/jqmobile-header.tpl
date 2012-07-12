{if !$jqm_header.position}
	{assign var='position' value='fixed'}
{else}
	{assign var='position' value=$jqm_header.position}
{/if}

{if !$params.back_button_url}
	{assign var='back_url' value=`$PHP.BASE_URL`/}
{else}
	{assign var='back_url' value=$params.back_button_url}
	{assign var='force_href' value='true'}
{/if}

{if !$jqm_header.title}
	{assign var='title' value='PSU Mobile'}
{else}
	{assign var='title' value=$jqm_header.title}
{/if}

<header data-role="header" data-position="{$position}">
	{if $jqm_header.back_button}
		<a href="{$back_url}" {if $force_href}data-force-href="true"{/if} data-icon="arrow-l" class="ui-btn-left" data-rel="back" data-theme="c">back</a>
	{/if}

	<h1 id="header-logo"><span>{$title}</span></h1>

	{$jqm_header.content}
</header>
