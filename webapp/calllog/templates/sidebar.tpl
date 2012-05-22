{col size=4}
	{box title="Open Calls"}
		<div id="main-open-calls">
		{include file="open_calls.tpl"}
		</div>
	{/box}
	{box title="Help Desk News" id="main-helpdesk-news"}
		<div class="helpdesk_news muted">
			{foreach from=$blog item=article}
				<div><a href="{$article.link}" target="_blank">{$article.title}</a></div>
				<div>{$article.date}
				<br/>by {$article.creator}<br/>
				Category: {$article.category}<br/><br/>
				</div>
			{/foreach}
			<div class="helpdesk_news_footer">
				<a href="http://helpdesk.blogs.plymouth.edu/category/its-helpdesk-news/" target="_blank">More Help Desk News</a>
			</div>
		</div>
	{/box}
{/col}
