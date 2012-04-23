	<div id="ape_identifiers" class="ape-section {if $myuser->go_states.ape_identifiers === '0'}ape-section-hidden{/if}">
	<h3>Identifiers</h3>
	<ul class="apedata">
		{if $person->username == 'tnsuarez'}
			<!--<li><img src="{$PHP.BASE_URL}/templates/default/images/porter.jpg" class="id_card" style="width:200px;height:267px;"/></li>-->
			<iframe width="200" height="143" src="http://www.youtube.com/embed/4uLfCp5O04M" frameborder="0" class="id_card" allowfullscreen></iframe>
		{elseif $AUTHZ.permission.ape_idcard_view}
			<li><img src="{$person->idcard()}" class="id_card"/></li>
		{/if}
		<li>
			<label>Username:</label>
			{capture name=username_history}
				{if $person->username_history} history - 
					<table>
						<tr><th>Username</th><th>As Of</th></tr>
					{foreach from=$person->username_history item=history}
						<tr><td>{$history.username}</td><td align=center>{$history.activity_date|date_format:'%B %e, %Y'}</td></tr>
					{/foreach}
					</table>
				{/if}
			{/capture}
			<span class="gobtpac_external_user" title="gobtpac_external_user {$smarty.capture.username_history|escape}">
			{if $person->username == $person->login_name}
				{$person->username|default:"No Username"}
				{if $person->username_history}<img src="{$PHP.ICONS}/16x16/actions/appointment-new.png" height="16" width="16" style="vertical-align:middle;">{/if}
			{else}
				{$person->login_name}
				{if $person->username_history}<img src="{$PHP.ICONS}/16x16/actions/appointment-new.png" height="16" width="16" style="vertical-align:middle;">{/if}</span>
				<span title="gobtpac_ldap_user" class="fade">({$person->username|default:'<em>no username</em>'})
			{/if}
			</span>
		</li>
		<li>
			<label>PSU ID:</label>
			{$person->id|default:"No PSU ID"}
		</li>
		{if $AUTHZ.permission.ape_limited_identifiers}
			<li>
				<label>Pidm:</label>
				{$person->pidm|default:"No Pidm"}
			</li>
			{if $person->wpid}
			<li>
				<label>WPID:</label>
				{$person->wpid}&nbsp;
			</li>
			{/if}
			<li>
				<label>Sourced ID:</label>
				{$person->sourced_id|default:"No Sourced ID"}
			</li>
		{/if}{* $AUTHZ.permission.ape_limited_identifiers *}
		{if ($AUTHZ.permission.ape_limited_identifiers || $AUTHZ.permission.ape_hr || $AUTHZ.permission.ape_usnh_id) && $person->usnh_id}
		<li>
			<label>USNH ID:</label>
			{$person->usnh_id}
		</li>
		{/if}
		{if $AUTHZ.permission.ape_limited_identifiers}
			{if $AUTHZ.permission.ape_ssn}
				{if $person->ssn_exists}
				<li class="secure">
					<label>SSN:</label>
					<a href="{$PHP.BASE_URL}/user.html?username={$person->login_name}&action=view_ssn" class="retrieve" id="view_ssn">View SSN</a> <span style="background-color: black;" id="view_ssn_out"></span>
				</li>
				{/if}
				{if $person->certification_number}
					<li class="secure">
						<label>*Certification #:</label>
						<a href="{$PHP.BASE_URL}/user.html?username={$person->login_name}&action=view_cert" class="retrieve" id="view_cert">View Cert #</a> <span style="background-color: black;" id="view_cert_out"></span>
					</li>
				{/if}
				{if $person->foreign_ssn}
					<li class="secure">
						<label>*Foreign SSN:</label>
						<a href="{$PHP.BASE_URL}/user.html?username={$person->login_name}&action=view_foreign_ssn" class="retrieve" id="view_foreign_ssn">View Foreign SSN</a> <span style="background-color: black;" id="view_foreign_ssn_out"></span>
					</li>
				{/if}{* $foreign *}
				{if $person->certification_number || $person->foreign_ssn}
					<li>
						<small><strong>* Note:</strong> both the Cert # and the Foreign SSN fields are modified from their original versions in Banner.  When testing a default login, simply use the first initial, last initial, and first six of either the displayed Cert # or Foreign SSN.</small>
					</li>
				{/if}
			{/if}{* $AUTHZ.permission.ape_ssn *}
			{if $AUTHZ.permission.ape_birthdate}
			<li>
				<label>Birth Date:</label>
				<span class="sensitive">{$person->birth_date|default:''|date_format:'%B %e, %Y'}</span>
			</li>
			{/if}
		{/if}{* $AUTHZ.permission.ape_limited_identifiers *}
		{if $person->former_last_names}
			{foreach from=$person->former_last_names item=name}
			<li><label>Former Last Name:</label> {$name}</li>	
			{/foreach}
		{/if}
		</ul>
		<div class="clear"></div>
</div>
