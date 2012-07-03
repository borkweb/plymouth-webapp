{assign var="apple_link" value="http://itunes.apple.com/us/app/plymouth-state-university/id453240011"}
{assign var="android_link" value="https://play.google.com/store/apps/details?id=edu.plymouth.psumobile"}

{* Begin jQuery Mobile Page *}
{jqm_page id="upgrade" class="m-app"}
	{jqm_header title="Upgrade" back_button="true"}{/jqm_header}

	{jqm_content}
		<h2>Upgrade</h2>
		<p>
			A feature you're trying to access is only available on the newer version of the app.
			Please update your app in the
			<span class="app-store-name">
				<a href="{$apple_link}" id="app-store-ios" target="_blank">Apple App Store</a>
				<a href="{$android_link}" id="app-store-android" target="_blank">Google Play Store</a>
			</span>
			to access the new features and any performance improvements and/or bugfixes we may have made.
		</p>
	{/jqm_content}

{/jqm_page}
{* End jQuery Mobile Page *}
