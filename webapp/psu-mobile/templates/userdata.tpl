{* Begin jQuery Mobile Page *}
{jqm_page id="userdata" class="m-app"}
	{jqm_header title="User Data" back_button="true"}{/jqm_header}

	{jqm_content}
		<ul id="events" data-role="listview" data-theme="d">
		{foreach from="$userdata" item="item"}
			<li>{$item}</li>
		{/foreach}
			<li>{$smarty.session.username}</li>
		</ul>
	{/jqm_content}

{/jqm_page}
{* End jQuery Mobile Page *}
