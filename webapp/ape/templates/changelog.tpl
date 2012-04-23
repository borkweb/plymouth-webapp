{box title="APE Changelog"}
<h2>14 December 2011</h2>
<ul>
	<li>Blur sensitive text (visible on mouse over)</li>
</ul>
<h2>24 August 2011</h2>
<ul>
	<li>Removing reliance on deprecated Luminis role: llcinfodesk.</li>
</ul>
<h2>15 July 2011</h2>
<ul>
	<li>Splitting the Account Lock tool into two tools: Account Lock (for locking accounts only) and Account Impersonation (for acting as a user)</li>
</ul>
<h2>20 March 2011</h2>
<ul>
	<li>There is a rudimentary "Copy Roles" feature, allowing the viewer to copy another user's roles for logging purposes.</li>
</ul>
<h2>10 March 2011</h2>
<ul>
	<li>CallLog ticket creation from APE has been modified to pass the login_name rather than username, which should fix some lingering issues creating tickets for applicants.</li>
</ul>
<h2>21 February 2011</h2>
<ul>
	<li>The log viewer has been updated to cast a wider net when searching for people, checking for all current usernames in addition to pidm (as approprite).</li>
	<li>The log viewer should now function better when the target user does not have a username (i.e. applicants, family portal people).</li>
</ul>
<h2>15 February 2011</h2>
<ul>
	<li>"Issues" (user errors) for applicants have been reworded in an effort to make them more helpful.</li>
</ul>
<h2>7 December 2010</h2>
<ul>
	<li>Added auto ticket creation for password resets.</li>
	<li>Added auto ticket creation/closing for account locks/unlocks</li>
</ul>
<h2>29 November 2010</h2>
<ul>
	<li>Add some missing error messages relating to applicant provisioning.</li>
</ul>
<h2>4 November 2010</h2>
<ul>
	<li>Show the "Applicant Email" field.</li>
	<li>Hide the password "Reset" button for applicants, as it only applies to Active Directory users.</li>
	<li>Hide the password "Self Reset" button if the target lacks a Connect account.</li>
</ul>
<h2>21 September 2010</h2>
<ul>
	<li>Added APE Family page</li>
</ul>
<h2>20 September 2010</h2>
<ul>
	<li>Added password reset utility with data verification fields</li>
</ul>
<h2>17 September 2010</h2>
<ul>
	<li>Added APE Student page</li>
	<li>Added Employee Clearance Checklist functionality</li>
</ul>
<h2>13 September 2010</h2>
<ul>
	<li>Show time zone in combined audit log.</li>
</ul>
<h2>17 August 2010</h2>
<ul>
	<li>On the user page, sort roles and permissions alphabetically.</li>
</ul>
<h2>12 July 2010</h2>
<ul>
	<li>Allow searching by Sourced ID.</li>
</ul>
<h2>9 July 2010</h2>
<ul>
	<li>Launched a new color scheme as a preview of what the PSU apps will be looking like soon.</li>
	<li>Added Advancement APE page for Development Officers and MIS</li>
	<li>Added the collapsable navigation based on available top level links.  The average user will see only Identity/Access.</li>
</ul>
<h2>30 June 2010</h2>
<ul class="bullets">
	<li>Added the ability to save the collapse state of sections within APE</li>
</ul>
<h2>14 June 2010</h2>
<ul class="bullets">
	<li>Fixed a bug where LDI Sync would erroneously report "person is already in the queue" in some cases.</li>
</ul>
<h2>11 June 2010</h2>
<ul class="bullets">
	<li>Always show Reset Email and Reset Email (Alt), and allow setting of both. (MIS feature.)</li>
</ul>
<h2>10 June 2010</h2>
<ul class="bullets">
	<li>Comments can now be saved into hardware records.</li>
</ul>
<h2>10 May 2010</h2>
<ul class="bullets">
	<li>Chunked logs into a two column layout with separate page loads for each of the logs.  The combined log is set as the default.</li>
	<li>Added the Luminis login log to the list of available choices and added it to the combined log.</li>
	<li>Tweaked the text to ensure that WordPress and Luminis portal logins are obviously different items.</li>
</ul>
<h2>7 May 2010</h2>
<ul class="bullets">
	<li>Initial version of exact email search (as opposed to wildcard search). Consults WordPress, and Banner's GOREMAL (active only).</li>
</ul>
<h2>29 April 2010</h2>
<ul class="bullets">
	<li>Converted underlying templating to PSUTemplate.</li>
	<li>Implemented dynamic navigation output.</li>
	<li>Added comment functionality to the Hardware UI.</li>
</ul>
<h2>19 March 2010</h2>
<ul class="bullets">
	<li>Added the ability to synchronize login_name with WordPress psuname and change the WP reset email</li>
</ul>
<h2>26 January 2010</h2>
<ul class="bullets">
	<li>Connect.ply modified to redirect self-resets back to APE using pidm, if possible.</li>
</ul>
<h2>20 January 2010</h2>
<ul class="bullets">
	<li>Display Zimbra account status in "Systems Account Information" block.</li>
</ul>
<h2>15 January 2010</h2>
<ul class="bullets">
	<li>Removed references to pluto and OpenLDAP.</li>
</ul>
<h2>12 January 2010</h2>
<ul class="bullets">
	<li>Show a Call Log "open tickets" count on the user page, and link to ticket list.</li>
</ul>
<h2>22 December 2009</h2>
<ul class="bullets">
	<li>Added curriculum info to active students</li>
</ul>
<h2>17 December 2009</h2>
<ul class="bullets">
	<li>APE users with the &ldquo;APE Applicant&rdquo; permission can now view an applicant's application decision code and type.</li>
</ul>
<h2>30 November 2009</h2>
<ul class="bullets">
	<li>Updated APIs to better facilitate username searches for applicants. (Fixes "username not found" after initiating applicant self-reset.)</li>
	<li>Hardware updated to accept no name field. DHCP will be persistent if a name is provided, dynamic otherwise.</li>
</ul>
<h2>24 November 2009</h2>
<ul class="bullets">
	<li>Fixed a bug where editable cells on the hardware page would walk across the page in WebKit.</li>
</ul>
<h2>23 November 2009</h2>
<ul class="bullets">
	<li>Added a mouseover timestamp for the Banner Security page when reviewing Banner Security Classes</li>
	<li>
		Revamping the Distributed Authorization management (Access Management) UI
		<ul>
			<li>Organized the role/permission column to be easier to navigate.</li>
			<li>Adding the ability to assign start/end dates for roles and permission</li>
			<li>Added the ability to fill in a Reason for adding an attribute</li>
		</ul>
	</li>
</ul>
<h2>20 November 2009</h2>
<ul class="bullets">
	<li>Removing the APE Admin role from existence.  The role is being replaced with more granular permissions that will be more easily audited.</li>
</ul>
<h2>16 November 2009</h2>
<ul class="bullets">
	<li>New user warnings when an applicant hasn't been flagged for account creation, and when applicants failed to provide an email address.</li>
	<li>Warn when user lacks a WPID.</li>
	<li>Don't show SSN warning for applicants.</li>
</ul>
<h2>12 November 2009</h2>
<ul class="bullets">
	<li>Show connect.plymouth.edu user meta fields to ape_admin users.</li>
</ul>
<h2>6 November 2009</h2>
<ul class="bullets">
	<li>Modified account locking to identify people by pidm rather than username, and update fields from Banner when necessary to prevent historic usernames and full names from leaking back into Luminis.</li>
</ul>
<h2>4 November 2009</h2>
<ul class="bullets">
	<li>Fixed a bug where the "create ticket" button created generic tickets for users with a pidm, but no username.</li>
</ul>
<h2>3 November 2009</h2>
<ul class="bullets">
	<li>Lock and unlock accounts in connect.plymouth.edu via pidm, not username.</li>
</ul>
<h2>2 November 2009</h2>
<ul class="bullets">
	<li>Remove password Reset and Test buttons for applicants, who control their own passwords through connect.plymouth.edu and self-resets.</li>
</ul>
<h2>19 October 2009</h2>
<ul class="bullets">
	<li>Removing Pin viewing.  Pins are now encrypted in Banner 8, so this feature is useless.  Replaced with an indicator specifying whether or not a Pin exists</li>
	<li>Removing Applicant Pin emailing.  Applicants should use the myPlymouth Forgot Password feature from this point forward.</li>
</ul>
<h2>14 October 2009</h2>
<ul class="bullets">
	<li>An initial pass has been made to make account locking work with applicants provisioned through Common App. Some lingering display issues will be fixed in a future release.</li>
</ul>
<h2>5 October 2009</h2>
<ul class="bullets">
	<li>The account lock "reason" dialog now differntiates betweeen a "Cancel" (which will abort the lock) and clicking "OK" with no message provided (which will continue with the lock).</li>
	<li>Account locks will now log the pidm of the user locking the account for display on the Locks page. Previously, this information was only logged to the audit tables.</li>
</ul>
<h2>1 October 2009</h2>
<ul class="bullets">
	<li>The Banner and myPlymouth role lists have been consolidated into a single list.</li>
	<li>Added Username History as a tooltip on the username</li>
</ul>
<h2>30 September 2009</h2>
<ul class="bullets">
	<li>A new permission, ape_profilereset, has been added to target the APE Profile Reset function to specific users, rather than just APE admins.</li>
</ul>
<h2>28 September 2009</h2>
<ul class="bullets">
	<li>Users may enter a lock reason when locking an account. This field is optional.</li>
</ul>
<h2>25 September 2009</h2>
<ul class="bullets">
	<li>The "recent portal logins" portion of the audit log will now search by pidm to provide real-time login stats.</li>
</ul>
<h2>23 September 2009</h2>
<ul class="bullets">
	<li>A &ldquo;<a href="{$PHP.BASE_URL}/deletion.html">Pending Deletion</a>&rdquo; page has been added, with links in the APE header.</li>
</ul>
<h2>22 September 2009</h2>
<ul class="bullets">
<li>Duplicate MAC addresses and comptuer names will now be highlighted on the hardware page.</li>
<li>Hardware records may now be deleted.</li>
</ul>
{/box}
