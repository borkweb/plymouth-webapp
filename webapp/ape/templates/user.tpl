<div id="user_info">
	{capture name="title"}{$person->formatName('f m l')} ({$person->id}){/capture}
	{box title="<span class='section-title'>Identity/Access:</span> `$smarty.capture.title`"}
	<img id="print-confidential" src="/webapp/style/templates/images/confidential_960.png"/>
	<div class="note ticket">
		<a href="https://www.plymouth.edu/webapp/calllog/{if $person->login_name or $person->pidm}user/{if $person->login_name}{$person->login_name}{else}{$person->pidm}{/if}/{else}new_call.html?caller=generic{/if}">create ticket</a>{*
		*}{if $person->tickets_open}, <a href="https://www.plymouth.edu/webapp/calllog/index.html?action=view_open_calls&amp;option=caller&amp;group={$person->pidm}">open tickets ({$person->tickets_open})</a>{/if}{*
		*}{if $person->pidm}, <a href="{$PHP.BASE_URL}/audit/{$person->username|default:$person->pidm}">check logs</a>{/if}{*
		*}{if $AUTHZ.permission.ape_hardware}, <a href="{$PHP.BASE_URL}/hardware/u/{$person->username}">hardware</a>{/if}
		{if $has_employee_exit_checklist}
			{*{if $person->banner_roles.employee && $AUTHZ.role.ape_checklist_employee_exit}*}, <a href="{$PHP.BASE_URL}/user/{$person->pidm}/checklist/employee-exit">employee clearance</a>{*{/if}*}
		{else}
			{*{if $person->banner_roles.employee && $AUTHZ.permission.ape_checklist_employee_exit_hr}*}, <a href="{$PHP.BASE_URL}/user/{$person->pidm}/checklist/employee-exit">employee clearance</a>{*{/if}*}
		{/if}
	</div>
	{if $AUTHZ.permission.ape_ssn}
		<div class="note">Note: all requests for SSNs, Pins, Cert Numbers, etc are logged.</div>
	{/if}

	{if $person->hasIssue() || $person->lame || $person->confidential || $person->deceased}
	<div class="alerts">
		{if $person->confidential}<strong style="display:block;">This account is marked as CONFIDENTIAL.</strong>{/if}
		{* if $person->hasIssue() || $person->lame}<strong style="display:block;">There may be problems with this account:</strong>{/if*}
		<ol>
			{if $person->confidential}
				<li>Confidential accounts must be kept private.  No information may be given to third parties regarding this user OR even the existence of this user's records in our system.</li>
			{/if}
			{if $person->deceased}
				<li>This person is deceased.</li>
			{/if}			
			{if $person->lame}
				<li>This user is exceedingly lame.</li>
				<li>so i herd u liek mudkips?<img src="{$PHP.BASE_URL}/templates/default/images/mudkip.gif" style="vertical-align:middle;"/></li>
			{/if}
			{if $person->issues.bad_oracle_account_status}
				<li>This user's Oracle Account is {$person->oracle_account_status}.</li>
			{/if}
			{if $person->issues.no_system_username}
				<li>This user's username is not in Systems (no active/alumni record in USER_DB).</li>
			{/if}
			{if $person->issues.pending_creation}
				<li>This user is in the Systems account creation queue. Access to email, clusters, and
				other Active Directory-driven services will not be granted until account creation has
				completed. Please open a Call Log ticket for Systems if you believe this user's account
				should have already been created or expedited creation is needed.</li>
			{/if}
			{if $person->issues.pending_ldi_sync}
				<li>{icon id="ape-sync" size="medium" flat=true} <strong>This user is undergoing synchronization with Moodle.</strong>  When this
				message goes away, synchronization is complete. Please note: you have to refresh the page to check sync status.
			{/if}
			{if $person->issues.pin_disabled}
				<li>This user's SSB pin is disabled possibly due to repeated attempts to authenticate with an incorrect PIN #. The pin can be re-enabled in GOATPAD or GOATPAC via Banner INB.  <strong>NOTE:</strong> An enabled pin is needed for both direct authentication and for SSO (Single-Sign On) into SSB.</li>
			{/if}
			{if $person->issues.userdb_username_mismatch}
				<li>This user does not have the same username in Banner and USER_DB.</li>
			{/if}
			{if $person->issues.support_locked}
				<li>This user's account has been locked by the Help Desk. <span id="ping-support-locked"></span></li>
			{/if}
			{if $person->duplicate_ssn}
				<li>This person has the same <abbr title="spbpers_ssn">SSN</span> as another person in Banner.</li>
			{/if}			
			{if $person->issues.ssn_mismatch}
				<li>This user has different SSNs in Banner and the Systems table.</li>
			{/if}
			{if $person->issues.username_sync}
				<li>This user's account has been created, but their username has not properly synchronized into myPlymouth.  Please run a sync now.</li>
			{/if}
			{if $person->issues.no_ssn}
				<li>This user has no <strong><abbr title="spbpers_ssn">SSN</abbr></strong>, <strong><abbr title="gobintl_foreign_ssn">Foreign SSN</abbr></strong>, or <strong><abbr title="gobintl_cert_number">Foreign Certification Number</abbr></strong>.  When all three are missing, this user's account will not be provisioned.</li>
			{/if}
			{if $person->issues.no_pin}
				<li>This user does not have a <strong>PIN</strong> and must have one to gain access to SSB (Single Sign On or otherwise).</li>
			{/if}
			{if $person->issues.bad_username}
				<li>This user has an invalid character in their username.</li>
			{/if}
			{if $person->issues.swipe_issue_mismatch}
				<li>This user has a mismatched issue number between the card swipe system and Banner.  Please notify Donnie Perrin, the new issue number is: {$person->idcard_issue_num}</li>
			{/if}			
			{if $person->issues.no_app_zack}
				<li>Admissions has not flagged this applicant to receive a myPlymouth invitation. He/she may still receive an account through finaid eligibility. (Does WPID exist? Is there a "Date of Portal Invite?")</li>
			{/if}
			{if $person->issues.no_wpid}
				<li>
					This user does not have a WordPress account in connect.plymouth.edu:
					{if $person->is_applicant}
						{if $person->issues.app_provision_error}
							see applicant invite error below.
						{elseif $person->applicant_pending_invite}
							applicant is currently waiting for scheduled provisioning.
						{else}
							this applicant is not currently in the invite pool. <em><strong>Do not tell the applicant he is not in the invite pool.</strong></em>
						{/if}
					{else}
						this account will be provisioned automatically during the user's next myPlymouth login. (No
						intervention from support is necessary.)
					{/if}
				</li>
			{/if}
			{if $person->issues.app_provision_error}
				<li>The myPlymouth invitation for this applicant could not be sent:
					{if $person->issues.app_provision_error eq 'dupe-email'}
						the email address on this user's application is tied to an existing myPlymouth user. Applicant should provide a new personal email address, <em>not</em> a shared address.
					{elseif $person->issues.app_provision_error eq 'dupe-pidm'}
						the user already has a connect.plymouth.edu account. (Duplicate PIDM.)
					{elseif $person->issues.app_provision_error eq 'missing-email'}
						he/she did not provide an email address to Common App. User should provide email address to Admissions.
					{elseif $person->issues.app_provision_error eq 'unrecognized-match'}
						provisioning claimed to find a user with this email address, but couldn't identify which field matched. Contact MIS.
					{elseif $person->issues.app_provision_error eq 'disallowed-email'}
						the provided email address is a known-bad value, which the provisioning process is coded to reject. If possible, user should provide a real email address to Admissions.
					{elseif $person->issues.app_provision_error eq 'link-required'}
						user provided a new email address during the application process, and must complete <a href="http://www.plymouth.edu/webapp/devwiki/wiki/Undergraduate_applicants#Email_requesting_user_verify_identity_.28.22link_accounts.22.29">the challenge/response</a> we initiated via email.
					{elseif $person->issues.app_provision_error eq 'email-claimed'}
						the email address supplied by the user is already used for another person's WordPress account.
					{elseif $person->issues.app_provision_error eq 'no-unique-ident'}
						needed to send a linkacct request, but the user has no SSN, FSSN, or CN in Banner.
					{else}
						unknown error! Please ask MIS to explain what &ldquo;{$person->issues.app_provision_error|escape}&rdquo; means.
					{/if}
				</li>
			{/if}
			{if $person->issues.app_no_sabiden_sabnstu}
				<li>This applicant appears to be missng a SABIDEN and/or SABNSTU record which is required to generate an applicant username.  As such, this user will be unable to access myPlymouth until this is resolved by the Admissions Office.</li>
			{/if}
		</ol>
	</div>
	{/if}
	<div class="grid_8 alpha">
		{include file="blocks/block.identifiers.tpl"}
		{include file="blocks/block.accounts.tpl"}
		<div id="ape_id_password" class="ape-section {if $myuser->go_states.ape_id_password === '0'}ape-section-hidden{/if}">
			<h3>Password Information</h3>
			<ul class="apedata">
			<li>
				<label>Password Changed:</label>
				{$person->password_change_date|date_format:"%B %d, %Y %l:%M:%S %p"|default:'N/A'}
			</li>
			<li>
				<label title="Whether or not the user has been flagged in the expiration table.">Password Expired?</label>
				{ape_bool value=$is_password_expired}
			</li>
			{if $can_reset_password}
			<li>
				<label>Password Tools:</label>
				{if $person->account_creation_date && (($person->birth_date && $person->ssn && $infodesk) || $AUTHZ.permission.ape_pw) }
					<a href="{$PHP.BASE_URL}/actions/resetpassword.php?username={$person->login_name}" id="resetpassword">Reset</a>;
				{elseif $infodesk && (!$person->birth_date || !$person->ssn)}
					(Reset not available due to lack of data. Please see supervisor.)
				{/if}
				<a class="{if not $person->support_locked}do-support-locked{else}do-support-unlocked{/if}" href="{$PHP.BASE_URL}/actions/account-lock.php?pidm={$person->pidm}&lock={if $person->support_locked}0{else}1{/if}">{if $person->support_locked}Unlock{else}Lock{/if}</a>;
				<a href="{$PHP.BASE_URL}/actions/account-impersonate.php?identifier={if $person->wp_id}{$person->wp_id}{else}{$person->pidm}{/if}">Impersonate</a>;
				{if $person->wpid}
					<a href="https://connect.plymouth.edu/wp-login.php?action=lostpassword&user_login={$person->login_name}&from=ape">Self-reset</a>;
				{/if}
				{if $person->account_creation_date}
					<a href="{$PHP.BASE_URL}/password-test.html?username={$person->login_name}">Test</a>
				{/if}
				{if ($person->account_creation_date && $person->birth_date && $person->ssn_exists && $infodesk) || $can_reset_password }
				<div>
				<form method="get" action="{$PHP.BASE_URL}/actions/resetpassword.php?username={$person->login_name}" id="resetpassword-form">
					<input type="hidden" name="username" value="{$person->login_name|escape}">
					<ul>
						{if $AUTHZ.permission.ape_pw}
							<li><label>Why are you resetting the password?</label> <input type="reason" name="reason" size="50"/></li>
						{else}
							{if $person->birth_date && $person->ssn}
							<li><label>Last 4 of SSN:</label> <input type="password" name="ssn" size="4" maxlength="4"/></li>
							<li><label>Date of Birth:</label> {html_select_date reverse_years=true start_year=1920 day_value_format='%02d'}</li>
							{else}
							<li><label>Verification Method:</label> <input type="text" name="reason"/>
							{/if}
						{/if}
						<li><label></label> <button type="submit">Reset Password</button></li>
					</ul>
				</form>
				</div>
				{/if}
			</li>
			{if $person->wpid}
			<li>
				<label>Reset email:</label>
				{if $person->wpuser->user_email}<span class="sensitive">{$person->wpuser->user_email}</span>{else}<em>none</em>{/if}
				{if $AUTHZ.permission.mis || $AUTHZ.permission.ape_wp_email_reset}
				[<a href="{$PHP.BASE_URL}/actions/reset_email.php?identifier={$person->login_name|default:$person->wp_id}&amp;type=primary" class="reset-email">Set</a>]
				[<a href="{$PHP.BASE_URL}/actions/connect-linkacct.php?wp_id={$person->wp_id}&amp;type=user_email" class="connect-linkacct">Linkacct</a>]
				{/if}
			</li>
			<li>
				<label>Reset email (alternate):</label>
				{if $person->wpuser->email_alt}<span class="sensitive">{$person->wpuser->email_alt}</span>{else}<em>none</em>{/if}
				{if $AUTHZ.permission.mis || $AUTHZ.permission.ape_wp_email_reset}
				[<a href="{$PHP.BASE_URL}/actions/reset_email.php?identifier={$person->login_name}&amp;type=alternate" class="reset-email">Set</a>]
				[<a href="{$PHP.BASE_URL}/actions/connect-linkacct.php?wp_id={$person->wp_id}&amp;type=email_alt" class="connect-linkacct">Linkacct</a>]
				{/if}
			</li>
			{/if}
			{if $person->wpuser->phone}
			<li>
				<label>Reset phone:</label>
				<span class="sensitive">{$person->wpuser->phone}</span>
			</li>
			{/if}
			</ul>
			{/if}
		</div>
		
		<div id="ape_id_misc" class="ape-section {if $myuser->go_states.ape_id_misc === '0'}ape-section-hidden{/if}">
			<h3>Misc. Information</h3>
			<ul class="apedata">
			<li>
				<label>Demographics:</label>
				In 
				{if $person->in_employee_demog}
					{if $person->in_student_demog}
						Employee and Student
					{else}
						Employee
					{/if}
				{elseif $person->in_student_demog}
					Student
				{else}
					Neither
				{/if}			
				Demographics
			</li>
			<li>
				<label>Eligible to Register?</label>
				{ape_bool value=$person->isEligibleToRegister()}
			</li>
			<li>
				<label>Active Student Record?</label>
				{ape_bool value=$person->isActiveStudent()}
			</li>
			{if $person->in_student_demog}
			<li>
				<label>Max. Term Code:</label>
				{$person->max_term} ({$person->max_term_status})
			</li>
			{/if}
			<li>
				<label>Off Campus Email:</label>
				{$person->personal_email|default:'N/A'}&nbsp;
			</li>
			<li>
				<label>Emergency # Status:</label>
				{$person->emergency_phone_status}&nbsp;
			</li>
			<li>
				<label>Status in Rave:</label>
				{$person->rave_status}&nbsp;
				{if $person->emergency_phone && $person->rave_role != 'SITE_ADMIN' && ($AUTHZ.permission.mis || $infodesk ) }
					[<a href="{$PHP.BASE_URL}/actions/emergency-text-reset.php?wp_id={$person->wp_id}">Reset</a>]
				{/if}
			</li>
			{if $AUTHZ.permission.ape_workflow}
			<li>
				<label>Has Workflow Hiring Roles?</label>
				{ape_bool value=$person->can_workflow_hire}
				{if !$person->can_workflow_hire}
					<a href="{$PHP.BASE_URL}/user.html?username={$person->username}&action=add_workflow_hiring_roles" class="retrieve" id="add_workflow_hiring_roles">Add hiring roles</a> <span id="add_workflow_hiring_roles_out"></span>
				{/if}
			</li>
			{/if}
			{if $person->voicemail}
			<li>
				<label>Voicemail:</label>
					(603) 535-{$person->voicemail}
			</li>
			{/if}
			{if $person->is_applicant && $AUTHZ.permission.ape_applicant}
			<li>
				<label>Date of Portal Invite:</label>
				{$person->applicant_invite_timestamp|date_format:"%F %r"|default:'<em>unsent</em>'}
			</li>
			<li>
				<label>Application Decision:</label>
				{if $person->apdc_code}
					{$person->apdc_desc} ({$person->apdc_code})
				{else}
					<em>none entered</em>
				{/if}
			</li>
			{if $person->apdc_date}
			<li>
				<label>Decision Date:</label>
				{$person->apdc_date|date_format:"%F %r"}
			</li>
			{/if}
			<li>
				<label>Admissions Type:</label>
				{$person->admt_desc} ({$person->admt_code})
			</li>
			<li>
				<label title="Used during Connect account provisioning, to detect an existing account or create a new account.">Applicant Email:</label>
				{$person->applicant_email}
			</li>
			{/if}
			{if ($person->ug || $person->gr) && $person->curriculum}
				{if $person->curriculum.major}
					<li>
						<label>Major:</label>
						<ul>
						{foreach from=$person->curriculum.major key=code item=data}
								<li>{$data.0.description}</li>
						{/foreach}
						</ul>
					</li>
				{/if}
				{if $person->curriculum.minor}
					<li>
						<label>Minor:</label>
						<ul>
						{foreach from=$person->curriculum.minor key=code item=data}
								<li>{$data.0.description}</li>
						{/foreach}
						</ul>
					</li>
				{/if}
				{if $person->curriculum.concentration}
					<li>
						<label>Concentration:</label>
						<ul>
						{foreach from=$person->curriculum.concentration key=code item=data}
								<li>{$data.0.description}</li>
						{/foreach}
						</ul>
					</li>
				{/if}
			{/if}
			</ul>
		</div>
		
		<div id="ape_id_idcard" class="ape-section {if $myuser->go_states.ape_id_idcard === '0'}ape-section-hidden{/if}">
			<h3>ID Card</h3>
			<ul class="apedata">
				<li class="secure">
					<label>Has ID Card?:</label>
					{ape_bool value=$person->hasIDCard()}
				</li>
				<li>
					<label>Issue #:</label>
					{$person->idcard_issue_num}
				</li>
				<li>
					<label>Card Swipe Access:</label>
					<ul>
						{foreach from=$person->door_access item=door}
						<li>{$door}</li>
						{foreachelse}
						<li>No card swipe access</li>
						{/foreach}
					</ul>
				</li>
			</ul>
		</div>	

		
		<div id="ape_id_quota" class="ape-section {if $myuser->go_states.ape_id_quota === '0'}ape-section-hidden{/if}">
			<h3>Quota Information</h3>
			<ul>
			<li style="margin-bottom: 5px;">
				<label>Print Balance:</label>
				${$print_balance}
			</li>
			<li>
				<label>MyDrive Quota Usage:</label>
				{if $person->system_account_exists}
				<script type="text/javascript">
					var the_username = '{$person->login_name}';
				{literal}
					$(function(){
					if( the_username != '' ) {
						$('#drive_quota').load(BASE_URL + '/user.html?action=drive_quota&username=' + the_username);
					}//end if
					});
				{/literal}
				</script>
				{/if}
				<div id="drive_quota"></div>
			</li>
			<li>
				<label>MyMail Quota Usage:</label>
				<em>(not yet implemented)</em>
				{$mymail_quota_graph}
			</li>	
			</ul>
		</div>
		<div id="ape_id_ods" class="ape-section {if $myuser->go_states.ape_id_ods === '0'}ape-section-hidden{/if}">
			<h3>SSR (ODS) Information [TEST for Spring 2009]</h3>
			<ul>
			<li style="margin-bottom: 5px;">
				<label>Student Profile:</label>
				<a href="http://vega.plymouth.edu:7777/apex/f?p=101:101072:::NO::P101072_STUDENT_UID,P101072_ACADEMIC_PERIOD,P101072_ACADEMIC_PERIOD_DESC:{$person->pidm}%2C200930%2CUG%20Spring%202009" target="_blank">Student Profile</a>
			</li>
			<li style="margin-bottom: 5px;">
				<label>Financial Aid Profile:</label>
				<a href="http://vega.plymouth.edu:7777/apex/f?p=101:501012:::NO::P501012_STUDENT_UID,P501012_ACADEMIC_PERIOD,P501012_ACADEMIC_PERIOD_DESC:{$person->pidm}%2C200930%2CUG%20Spring%202009" target="_blank">Financial Aid Profile</a>
			</li>
			<li style="margin-bottom: 5px;">
				<label>Advancement Profile:</label>
				<a href="http://vega.plymouth.edu:7777/apex/f?p=101:401012:::NO::P401012_CONSTITUENT_UID:{$person->pidm}" target="_blank">Advancement Profile</a>
			</li>		
			</ul>
		</div>
		{if $AUTHZ.permission.ape_wp_meta && $person->wpid}
		<div id="ape_id_connect" class="ape-section {if $myuser->go_states.ape_id_connect === '0'}ape-section-hidden{/if}">
			<h3>Connect.plymouth.edu</h3>
			<ul class="apedata">
				{if $AUTHZ.permission.mis || $AUTHZ.permission.ape_wordpress_admin}
				<li><label>Toggle Mercury Opt-In:</label><a href="{$PHP.BASE_URL}/actions/portalv5.php?identifier={$person->id}">Do-It</a></li>
				{/if}
				{foreach from=$person->wpuser_array key=wpkey item=wpvalue}
					<li><label>{$wpkey}</label>
					<span class="limited">
					{if is_array($wpvalue) || is_object($wpvalue)}
						{$wpvalue|@serialize}
					{else}
						{$wpvalue|default:'&nbsp;'}
					{/if}
					</span>
					</li>
				{/foreach}
			</ul>
		</div>
		{/if}
	</div>
	<div class="grid_8 omega">
		{include file="blocks/block.roles.tpl"}
		{if $AUTHZ.permission.ape_attribute || $AUTHZ.permission.ape_attribute_admin}
		<div id="ape_id_attributes" class="ape-section {if $myuser->go_states.ape_id_attributes === '0'}ape-section-hidden{/if}">
			<h3>Attributes and Authorizations</h3>
			{if $admin_title}
			<script type="text/javascript">
	$(function(){
		$('.prompt_action').click(function(){
			var value = $(this).siblings('span').html();
			var the_class = $(this).siblings('span').attr('class');
			var response = prompt("Please enter a " + $(this).attr('title') + ".", value);
			if(response)
			{
				$('.' + the_class).load($(this).attr('href')+'&method=js&value=' + encodeURIComponent(response));
			}//end if
			return false;
		});
	});
			</script>
			{/if}
			<ul>
			{foreach from=$person->attributes.display_title key=attribute item=attribute_data}
			<li>
				<label>Display Title:</label>
				{if $admin_title}<a class="prompt_action" href="{$PHP.BASE_URL}/actions/hr.php?pidm={$person->pidm}&amp;action=display_title" title="Display Title">[edit]</a>{/if}
				<span class="display_title">{$attribute}</span>
			</li>
			{/foreach}
			{foreach from=$person->attributes.job_title key=attribute item=attribute_data}
			<li>
				<label>Job Title:</label>
				{if $admin_title}<a class="prompt_action" href="{$PHP.BASE_URL}/actions/hr.php?pidm={$person->pidm}&amp;action=job_title" title="Job Title">[edit]</a>{/if}
				<span class="job_title">{$attribute}</span>
			</li>
			{/foreach}
			{foreach from=$person->attributes.department key=attribute item=attribute_data}
			<li>
				<label>Department:</label>
				{if $admin_title}<a class="prompt_action" href="{$PHP.BASE_URL}/actions/hr.php?pidm={$person->pidm}&amp;action=department" title="Department">[edit]</a>{/if}
				<span class="department">{$attribute}</span>
			</li>
			{/foreach}
			</ul>
			<div class="clear"></div>
			<table class="roles grid">
			<thead>
				<tr>
					<th>Role</th>
					<th>Permission</th>
				</tr>
			</thead>
			{foreach from=$ape->userRoles($person->pidm) item=role}
				{if $role.source != 'hr' || $AUTHZ.permission.ape_hr}
				<tr id="role-{$role.id}">
					<td class="idmrole {if $role.origin_id}origin-{$role.origin_id}{/if}">
						{strip}<span class="idmrole" title="{$role.attribute} (via {$role.source})">{$role.name}</span>
						{if $ape->canAdminRole($role) && $AUTHZ.permission.ape_attribute_admin}
							&nbsp;[<a href="{$PHP.BASE_URL}/actions/idm.php?pidm={$person->pidm}&amp;action=remove&amp;id={$role.id}">x</a>]
						{/if}{/strip}
						<div class="hidden" id="tooltip-role{$role.id}">{strip}
							<ul>
							<li>ID: {$role.id}</li>
							<li>Granted by: {$role.granted_by} {if $role.grantor_pidm}({$role.grantor_pidm}){/if}</li>
							<li>Source: {$role.source}</li>
							{if $role.origin_id}
								<li>Origin: {$role.origin.attribute} (<span class="origin">{$role.origin_id}</span>)</li>
							{/if}
							<li>Start: {$role.start_date|date_format:'%m/%d/%Y'}</li>
							{if $role.end_date}
								<li>End: {$role.end_date|date_format:'%m/%d/%Y'}</li>
							{/if}
							<li>Reason: {$role.reason|nl2br}</li>
							<li>Attribute: {$role.attribute}</li>
							</ul>
						</div>{/strip}
					</td>
					<td>
						{strip}<ul>
						{assign var=children value=$ape->roleChildren($role.id)}
						{foreach from=$children item=child}
							<li>
							{if $child.name}
								<span title="{$child.attribute|escape}">{$child.name}</span>
							{else}
								<code>{$child.attribute}</code>
							{/if}
							{if $ape->canAdminRole($role) && $AUTHZ.permission.ape_attribute_admin}
								&nbsp;[<a href="{$PHP.BASE_URL}/actions/idm.php?pidm={$person->pidm}&amp;action=remove&amp;id={$child.id}">x</a>]
							{/if}
							</li>
						{/foreach}
						</ul>{/strip}
						{if $ape->canAdminRole($role) && $AUTHZ.permission.ape_attribute_admin}
							{assign var=childrenToAdd value=$ape->childrenToAdd($role, $children)}
							{if $childrenToAdd}
								<form action="{$PHP.BASE_URL}/actions/idm.php" method="post">
								<input type="hidden" name="action" value="add">
								<input type="hidden" name="pidm" value="{$person->pidm}">
								<input type="hidden" name="parent_id" value="{$role.id}">
								<input type="hidden" name="type" value="permission">
								<select name="attribute[]">
								{html_options options=$childrenToAdd}
								</select>
								<input type="submit" value="Add">
								<img src="{$PHP.ICONS}/16x16/actions/go-down.png" height="16" width="16" alt="Show More" title="Show More" class="more">
								</form>
							{/if}
						{/if}
					</td>
				</tr>
				{/if}
			{/foreach}
			</table>
			{if $ape->canAdminRole() && $AUTHZ.permission.ape_attribute_admin}
				{include file=idm_add_role.tpl}
			{/if}
		</div>
		{/if}{* $AUTHZ.permission.ape_attribute || $AUTHZ.permission.ape_attribute_admin *}
    {if $AUTHZ.permission.ape_address}
      {include file="blocks/block.contact_information.tpl"}
    {/if}
	</div>
	<div style="clear:both;">&nbsp;</div>
</div>
<br clear="all"/>
{/box}
</div>

<script type="text/javascript">
$(document).ready(function(){
	$('span.idmrole').tooltip({
		bodyHandler: function(){
			var id = $(this).parents('tr:eq(0)').attr('id').substr(5);
			return $('#tooltip-role' + id).html();
		},
		delay: 0
	});

	$('td.idmrole').mouseover(function(){
		var id = $(this).parents('tr:eq(0)').attr('id').substr(5);
		var origin = $(this).find('span.origin').text();

		if(origin)
		{
			id = origin;
		}

		$('td.origin-' + id).addClass('highlight-light');
		$('tr#role-' + id + ' td.idmrole').addClass('highlight');
	});

	$('td.idmrole').mouseout(function(){
		var id = $(this).parents('tr:eq(0)').attr('id').substr(5);
		var origin = $(this).find('span.origin').text();

		if(origin)
		{
			id = origin;
		}

		$('td.origin-' + id).removeClass('highlight-light');
		$('tr#role-' + id + ' td.idmrole').removeClass('highlight');
	});

	$('#resetpassword-form').submit(function(){
		$('button[type=submit]', this).attr('disabled', 'true');
	});

	$('#resetpassword').colorbox({
		inline: true,
		href: '#resetpassword-form',
		height: '250px',
		width: '500px'
	});
});
</script>
