		<div id="ape_id_roles" class="ape-section {if $myuser->go_states.ape_id_roles === '0'}ape-section-hidden{/if}">
			<h3>Roles</h3>
			<table class="grid roles" width="100%">
				<tr>
					<th>Banner
							(<a href="{$PHP.BASE_URL}/actions/synchronize.php?pidm={$person->pidm}&synchronize_ldi=1" title="This link is merely a mimic of the ICGORODM tool in INB.  If a user's Banner roles or Moodle data gets out of sync, simply click this link to synchronize the account!">sync</a>)
					</th>
					<th>Active Directory
					(<a href="{$PHP.BASE_URL}/actions/synchronize.php?pidm={$person->pidm}&synchronize_ad=1" title="If a user's AD and Banner roles get out of sync, simply click this link to synchronize the account!">sync</a>)</th>
				</tr>
				<tr>
					<td valign="top">
						<ul class="banner_myp">
							{* add role descriptions to mysql myplymouth.gtvsqru_desc. html is allowed. *}
							{foreach from=$person->combined_roles key=role item=contents}
								<li {if $contents.description}title="{$role|escape} - {$contents.description|escape|nl2br}"{/if}>
								<img src="{$PHP.BASE_URL}/images/blank.gif" class="badge badge-ban{if $contents.banner} badge-active{/if}">
								{$role}
								</li>
							{foreachelse}
								<li><em>none</em></li>
							{/foreach}
						</ul>
					</td>
					<td valign="top" class="ad">
						<ul>
							{foreach from=$person->ad_roles  key=role item=description}
								<li {if $description}title="{$role|escape} - {$description|escape|nl2br}"{/if}>{$role}</li>
							{foreachelse}
								<li><em>none</em></li>
							{/foreach}
						</ul>
					</td>
				</tr>
			</table>
			<a href="#" class="copy-roles">Copy Roles</a>
			<div class="copy-roles-dialog">
				<p>Well, you have to copy it yourself, but here's the text.</p>
				<textarea>
* Banner: {$person->banner_roles|@array_keys|@implode:', '}
* Luminis: {$person->portal_roles|@array_keys|@implode:', '}
* Active Directory: {$person->ad_roles|@array_keys|@implode:', '}</textarea>
			</div>
		</div>
