{col size="4" id="go-sidebar"}
{box class="widget go-profile" no_grid=true}
	{if $portal->person->email.CA.0}
		<a href="#gravatar-info" id="gravatar-info" title="Change Your Avatar" class="left"><img class="avatar" width="32" height="32" alt="Avatar" src="https://secure.gravatar.com/avatar/{$portal->person->wp_email|md5}.jpg?s=32&amp;r=g"/></a>
		<div id="gravatar-info-message" style="display:none;" class="no-print">
			<h3>Change Your Avatar</h3>
			<p style="font-size: 1.2em;">
			To customize your Avatar, head on over to <a href="http://www.gravatar.com" target="_blank">Gravatar</a> and
			log in (or <a href="http://www.gravatar.com/site/signup" target="_blank">sign up</a> if you don't have an account
			with them yet)!
			</p>
			<p>
			The email address that is used for your gravatar is: <strong>{$portal->person->wp_email}</strong>
			</p>
			<p>
			Your Avatar (Gravatar) is an image that follows you from site to site appearing beside your name when you do things like 
			comment or post on a blog. Avatars help identify your posts on blogs and web forums, so why not on any site?  Plymouth
			State utilizes the Gravatar service to give users more control over their avatar images used both in and out of educational
			settings.
			</p>
		</div>
		<ul class="identifiers">
			{if $portal->person->formatName('f l')}
			<li class="name">
				{$portal->person->formatName('f l')}
			</li>
			{/if}
			<li class="username"><label>Username:</label>{$portal->person->login_name}</li>
			{if $portal->person->pidm && ! $smarty.session.AUTHZ.role.staff && ! $smarty.session.AUTHZ.role.faculty && ! $smarty.session.AUTHZ.role.lecturer}
			<li class="psu-id"><label>ID:</label>{$portal->person->id}</li>
			{/if}
		</ul>
	{else}
		<strong>{$portal->person->formatName('f l')}</strong><br/>
	{/if}
	<ul class="items">
		{if $portal->person->account_creation_date}
		<li><a href="http://go.plymouth.edu/mymail" target="_blank"><img src="/images/icons/16x16/apps/internet-mail.png" alt="MyMail"/>View myMail</a></li>
		{/if}
		<li>
			<a href="http://go.plymouth.edu/{if $new_themes}new{/if}themes" target="_blank"><img src="/images/icons/16x16/mimetypes/image-x-generic.png"/>Change Theme</a>
			{if $new_themes}
			<span class="new-themes">(<span class="quantity">{$new_themes} New!</span>)</span>
			{/if}
		</li>
		<li><a href="{$PHP.BASE_URL}/channels/name/" class="add-stuff"><img src="/images/icons/16x16/apps/preferences-system-windows.png" alt="Add Stuff"/>Add Stuff to myPlymouth</a></li>
		<li>
			<a href="//www.plymouth.edu/webapp/chat/phplive.php?d=0" target="new"><img src="/images/icons/16x16/apps/internet-group-chat.png" alt="Live Support">Chat with ITS</a>
		</li>
	</ul>
{/box}
<div class="clear"></div>
<div id="go-search" class="widget box border-box alpha omega">
	<div class="header title">
		<div class="box-inner">
			<form method="get" action="{$PHP.BASE_URL}/search/">
				<img src="/images/1x1trns.gif" class="sidebar_icon sb_search" alt="search icon"/>
				<input type="hidden" name="cx" value="005322158811873917109:eb5xtxv98mg" />
				<input type="hidden" name="cof" value="FORID:11" />
				<input type="hidden" name="uP_tparam" value="frm" />
				<input type="hidden" name="frm" value="search" />
				<input type="text" name="q" class="go-box"/>
				<input type="submit" class="go-submit" value="go"/>
			</form>
		</div>
	</div>
</div>
<div id="remote-widget-container">
	<div style="text-align:center;">
	<img src="/images/1x1trns.gif" class="sidebar_throbber" alt="Loading"/><br/>
	<small>Loading Go Sidebar...</small>
	</div>
</div>
{/col}
