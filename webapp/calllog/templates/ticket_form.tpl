{if !$hide_checklist}
<div id="checklists">
	<label>What type of ticket is this?</label>
	<select name="checklist">
		<option value=""></option>
		<option value="account">Account Creation and Privileges</option>
		<option value="banner">Banner (Student, INB, HR, Finance)</option>
		<option value="cts">Classroom Application/Operating System (Installation, Problem, etc)</option>
		<option value="desktop">Desktop Application/Operating System (Installation, Problem, etc)</option>
		<option value="repair">Device Repair</option>
		{if $PHP.is_employee}<option value="purchase">Hardware Purchase</option>{/if}
		<option value="ltoe">Learning Management System (Moodle/Mahara)</option>
		<option value="network">Network (Bradford, Connectivity, etc)</option>
		<option value="printing">Printing</option>
		<option value="surplus">Surplus Pickup Request</option>
		<option value="telephone">Telephone (Add, Move, Remove)</option>
		<option value="voice">Voice (Voicemail, Calling, etc)</option>
		<option value="webapp">Web Application (myPlymouth, myMail, Self-Service, etc)</option>
		<option value="misc">Misc.</option>
	</select>

	<ul class="sub" rel="telephone">
		<li>
			<label rel="Type:">Type:</label>
			<select name="checklist_telephone_type">
				<option value=""></option>
				<option value="Add">Add</option>
				<option value="Move">Move</option>
				<option value="Remove">Remove</option>
			</select>
			<ul class="sub" rel="Move">
				<li>
					<label rel="Current Phone Number:">Current Phone Number:</label>
					<input type="text" name="checklist_telephone_currentphonenuber" />
				</li>
				<li>
					<label rel="New Jack ID:">New Jack ID:</label>
					<input type="text" name="checklist_telephone_newjackid"/>
				</li>
				<li>
					<label rel="New Location:">New Location:</label>
					<input type="text" name="checklist_telephone_newlocation"/>
				</li>
			</ul>
			<ul class="sub" rel="Remove">
				<li>
					<label rel="Current Phone Number:">Current Phone Number:</label>
					<input type="text" name="checklist_telephone_currentphonenuber" />
				</li>
			</ul>
		</li>
		<li>
			<label rel="Jack ID:">Jack ID:</label>
			<input type="text" name="checklist_telephone_jackid"/>
		</li>
		<li>
			<label rel="Location:">Location:</label>
			<input type="text" name="checklist_telephone_location"/>
		</li>
		<li>
			<label rel="Requested Change Date:">Requested Change Date:</label>
			<input type="text" name="checklist_telephone_changedate"/>
		</li>
		<li>
			<label rel="FOAPAL:">FOAPAL:</label>
			<input type="text" name="checklist_telephone_foapal"/>
		</li>
	</ul>

	<ul class="sub" rel="surplus">
		<li>
			<label rel="Location (Building and Room #):">Location (Building and Room #):</label>
			<input type="text" name="checklist_surplus_location"/>
		</li>
		<li>
			<label rel="Is the item clearly marked as surplus?">Is the item clearly marked as surplus?</label>
			<input type="text" name="checklist_surplus_marked"/>
		</li>
		<li>
			<label rel="What is being picked up?">What is being picked up?</label>
			<input type="text" name="checklist_surplus_marked"/>
		</li>
	</ul>

	<ul class="sub" rel="purchase">
		<li>
			<label rel="Dell eQuote:">Dell eQuote:</label>
			<input type="text" name="checklist_purchase_dellequote"/>
		</li>
		<li>
			<label rel="Mac eQuote:">Mac eQuote:</label>
			<input type="text" name="checklist_purchase_macequote"/>
		</li>
		<li>
			<label rel="GovConnection eQuote:">GovConnection eQuote:</label>
			<input type="text" name="checklist_purchase_govconnectionequote"/>
		</li>
		<li>
			<label rel="Other:">Other:</label>
			<input type="text" name="checklist_purchase_other"/>
		</li>
		<li>
			<label rel="Items Purchased:">Items Purchased:</label>
			<textarea name="checklist_purchase_items"></textarea>
		</li>
	</ul><!-- purchase -->
	<ul class="sub" rel="account">
		<li>
			<label rel="Account Topic:">What account topic is this about?</label>
			<select name="checklist_account_topic">
				<option value=""></option>
				<option value="Account Creation">Account Creation</option>
				<option value="Applicant Access">Applicant Access</option>
				<option value="More Privileges">Additional Privileges Required</option>
				<option value="Fewer Privileges">Fewer Privileges Required</option>
				<option value="Privileges Missing">Privileges Are Missing</option>
			</select>
			<ul class="sub" rel="Applicant Access">
				<li>
					<label rel="Password Reset Email:">What is the personal email address that should be used for password resets?</label>
					<input type="text" name="checklist_applicant_password"/>
				</li>
			</ul>
			<p class="sub" rel="Account Creation">
				Please explain the account creation details in the Problem Description below.
			</p>
			<p class="sub" rel="More Privileges|Fewer Privileges">
				Please explain the privilege adjustment in the Problem Description below.
			</p>
			<ul class="sub" rel="Privileges Missing">
				<li>
					<label>Did the user have these privileges before?</label>
					<select name="checklist_account_service">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="Unsure">Unsure</option>
					</select>
					<ul class="sub" rel="Yes">
						<li>
							<label>Approximately when did these privileges go away?</label>
							<input type="text" name="checklist_account_access_when"/>
						</li>
					</ul>
				</li>
			</ul>
			<p class="sub" rel="Privileges Missing">
				Please detail the missing privileges in the Problem Description below.
			</p>
		</li>
	</ul><!-- end rel=account -->
	<ul class="sub" rel="banner|cts|desktop|ltoe|repair|network|voice|webapp|misc">
		<li>
			<label rel="Device Used:">What type of device is being used?</label>
			<select name="checklist_generic_device">
				<option value=""></option>
				<optgroup label="Computing Device">
					<option value="Desktop">Desktop</option>
					<option value="Laptop">Laptop</option>
					<option value="Mobile Device">Mobile Device</option>
				</optgroup>
				<optgroup label="Peripheral">
					<option value="Printer">Printer</option>
				</optgroup>
				<optgroup label="Voice/Fax">
					<option value="Cell Phone">Cell Phone</option>
					<option value="Fax Machine">Fax Machine</option>
					<option value="Landline Phone">Landline Phone (Campus Phone)</option>
				</optgroup>
				<option value="Other">Other</option>
			</select>
			<ul class="sub" rel="Other">
				<li>
					<label rel="Device (Other) Details:">Other? Oh really? Do tell:</label>
					<input type="text" name="checklist_generic_device_other"/>
				</li>
			</ul>
			<ul class="sub" rel="Landline Phone">
				<li>
					<label>Extension:</label>
					<input type="text" name="checklist_extension"/>
				</li>
			</ul>
			<ul class="sub" rel="Printer">
				<li>
					<label rel="Printer Name/Queue:">What is the printer's name (a.k.a. printer queue)?</label>
					<input type="text" name="checklist_generic_printer"/>
				</li>
			</ul>
			<ul class="sub" rel="Desktop|Laptop|Mobile Device|Printer|Cell Phone|Fax Machine|Landline Phone">
				<li>
					<label rel="Device Details:">List some more details (make, model, etc) about the device, please:</label>
					<input type="text" name="checklist_generic_device_details"/>
				</li>
			</ul>
			<ul class="sub" rel="Desktop|Laptop|Mobile Device">
				<li>
					<label>Does the problem still occur after the device has been rebooted?</label>
					<select name="checklist_generic_reboot">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="N/A">A reboot was not done</option>
					</select>
					<p class="sub" rel="N/A">
						It never hurts to attempt a reboot regardless of operating system.  Do this at your discretion depending on the device and situation.
					</p>
				</li>
			</ul>
			<ul class="sub" rel="Desktop|Laptop|Mobile Device|Cell Phone|Fax Machine|Landline Phone">
				<li>
					<label rel="Connecting Via:">How is the device connecting to (or attempting to connect to) the internet?</label>
					<select name="checklist_generic_connecting">
						<option value=""></option>
						<option value="On Campus, Wired">On Campus (Wired)</option>
						<option value="On Campus, Wireless">On Campus (Wireless)</option>
						<option value="Off Campus">Off Campus</option>
					</select>
				</li>
			</ul><!-- end rel=banner|cts|desktop|network|printing|webapp|misc -->
			<ul class="sub" rel="Desktop|Laptop|Mobile Device|Printer|Cell Phone|Fax Machine|Landline Phone">
				<li>
					<label rel="Device Location:">Where is the device located (Building & Room, Office, Cluster, etc)?</label>
					<input type="text" name="checklist_generic_room"/>
				</li>
			</ul><!-- end rel=banner|cts|desktop|network|printing|repair|voice|webapp|misc -->
		</li>
	</ul>
	<ul class="sub" rel="printing">
		<li>
			<label rel="Printer Name/Queue:">What is the printer's name (a.k.a. printer queue)?</label>
			<input type="text" name="checklist_printer"/>
		</li>
		<li>
			<label rel="Printer Details:">List some more details (make, model, etc) about the printer, please:</label>
			<input type="text" name="checklist_printer_details"/>
		</li>
		<li>
			<label rel="Printer Location:">Where is the printer located (Building & Room, Office, Cluster, etc)?</label>
			<input type="text" name="checklist_printer_room"/>
		</li>
		<li>
			<label rel="Printing From:">What type of device are you printing from?</label>
			<select name="checklist_generic_device">
				<option value=""></option>
				<optgroup label="Computing Device">
					<option value="Desktop">Desktop</option>
					<option value="Laptop">Laptop</option>
					<option value="Mobile Device">Mobile Device</option>
				</optgroup>
				<optgroup label="Voice/Fax">
					<option value="Cell Phone">Cell Phone</option>
				</optgroup>
				<option value="Other">Other</option>
			</select>
			<ul class="sub" rel="Other">
				<li>
					<label rel="Device (Other) Details:">Other? Oh really? Do tell:</label>
					<input type="text" name="checklist_generic_device_other"/>
				</li>
			</ul>
			<ul class="sub" rel="Desktop|Laptop|Mobile Device|Printer|Cell Phone|Fax Machine|Landline Phone">
				<li>
					<label rel="Device Details:">List some more details (make, model, etc) about the device, please:</label>
					<input type="text" name="checklist_generic_device_details"/>
				</li>
			</ul>
			<ul class="sub" rel="Desktop|Laptop|Mobile Device">
				<li>
					<label>Does the problem still occur after the device has been rebooted?</label>
					<select name="checklist_generic_reboot">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="N/A">A reboot was not done</option>
					</select>
					<p class="sub" rel="N/A">
						It never hurts to attempt a reboot regardless of operating system.  Do this at your discretion depending on the device and situation.
					</p>
				</li>
			</ul>
		</li>
	</ul>
	<ul class="sub" rel="banner|cts|desktop|ltoe|network|printing|webapp|misc">
		<li>
			<label>Is the VPN being used?</label>
			<select name="checklist_generic_vpn">
				<option value=""></option>
				<option value="Yes">Yes</option>
				<option value="No">No</option>
				<option value="N/A">N/A</option>
			</select>
		</li>
	</ul><!-- end rel=banner|cts|desktop|network|printing|webapp|misc -->
	<ul class="sub" rel="banner">
		<li>
			<label rel="Banner Issue:">What type of Banner issue is this?</label>
			<select name="checklist_banner_issue">
				<option value=""></option>
				<option value="Student">Student</option>
				<option value="INB">INB</option>
				<option value="Banner Finance">Banner Finance</option>
				<option value="HR">HR</option>
				<option value="Training">Training</option>
			</select>
			<p class="sub" rel="Banner Finance">
				Please describe the issue in the Problem Description below.  Note: many Banner Finance issues involving: Webi, permissions, etc are all handled by USNH.
			</p><!-- end rel=Banner Finance-->
			<ul class="sub" rel="INB">
				<li>
					<label rel="Operating System:">What Operating System is being used?</label>
					<select name="checklist_desktop_os">
						<option value=""></option>
						<option value="Mac OS">Mac OS</option>
						<option value="Windows">Windows</option>
						<option value="Other">Other</option>
					</select>
					<ul class="sub" rel="Other">
						<li>
							<label>Other?  Oh really?  Do tell:</label>
							<input type="text" name="checklist_desktop_os_other"/>
						</li>
					</ul><!-- end rel=other -->
					<ul class="sub" rel="Mac OS|Windows|Other">
						<li>
							<label rel="Operating System Version:">What version is the Operating System (Windows: XP/Vista/7; Mac OS X.6; etc)?</label>
							<input type="text" name="checklist_desktop_os_version"/>
						</li>
					</ul><!-- end rel=Mac OS|Windows|Other -->
				</li>
			</ul>
			<ul class="sub" rel="Reporting">
				<li>
					<label rel="What reporting tool?">How are you running your report (PSU Analytics, MS Access, INB, etc)?</label>
					<select name="checklist_banner_report_method">
						<option value=""></option>
						<option value="INB">INB</option>
						<option value="MS Access">MS Access</option>
						<option value="PSU Analytics">PSU Analytics</option>
						<option value="Self-Service">Self-Service Banner</option>
						<option value="Other">Other</option>
						<option value="I don't know">I don't know</option>
					</select>
					<ul class="sub" rel="Other">
						<li><label rel="Other! What reporting tool?">What reporting tool?</label> <input type="text" name="checklist_banner_reporting_tool"/></li>
					</ul>
					<ul class="sub" rel="INB|PSU Analytics|Self-Service|Other">
						<li><label>Browser:</label> <input type="text" name="checklist_webapp_browser"/></li>
						<li><label>Browser Version:</label> <input type="text" name="checklist_webapp_browser_version"/></li>
					</ul>
				</li>
			</ul>
			<ul class="sub" rel="INB">
				<li><label>Browser:</label> <input type="text" name="checklist_webapp_browser"/></li>
				<li><label>Browser Version:</label> <input type="text" name="checklist_webapp_browser_version"/></li>
				<li>
					<label rel="Java Version:">What version of Java is running?</label>
					<input type="text" name="checklist_java"/>
				</li>
			</ul>
		</li>
	</ul><!-- end rel=banner -->
	<ul class="sub" rel="cts|desktop|network">
		<li>
			<label>Device Name/IP:</label>
			<input type="text" name="checklist_device_name"/>
		</li>
	</ul><!-- end rel=cts|desktop|network -->
	<ul class="sub" rel="network">
		<li>
			<label>MAC Address:</label>
			<input type="text" name="checklist_network_macaddr"/>
		</li>
	</ul>
	<ul class="sub" rel="network|voice">
		<li>
			<label>Jack ID:</label>
			<input type="text" name="checklist_jack"/>
		</li>
	</ul><!-- end rel=network|voice-->
	<ul class="sub" rel="network">
		<li>
			<label>Is the system up to date?:</label>
			<input type="radio" name="checklist_network_uptodate" value="Yes" style="width: auto;"/> Yes
			<input type="radio" name="checklist_network_uptodate" value="No" style="width: auto;margin-left:1.5em;"/> No
			<input type="radio" name="checklist_network_uptodate" value="I don't know" style="width: auto;margin-left:1.5em;"/> I don't know
		</li>
		<li>
			<label>Does the system have a virus?:</label>
			<input type="radio" name="checklist_network_virus" value="Yes" style="width: auto;"/> Yes
			<input type="radio" name="checklist_network_virus" value="No" style="width: auto;margin-left:1.5em;"/> No
			<input type="radio" name="checklist_network_virus" value="I don't know" style="width: auto;margin-left:1.5em;"/> I don't know
		</li>
		<li>
			<label>Are there multiple Anti-Virus products running?:</label>
			<select name="checklist_network_av">
				<option value=""></option>
				<option value="Yes">Yes</option>
				<option value="Yes, Multiple">Yes, Multiple</option>
				<option value="No">No</option>
				<option value="I don't know">I don't know</option>
			</select>
			<ul class="sub" rel="Yes|Yes, Multiple">
				<li>
					<label>What antivirus program is installed?</label>
					<input type="text" name="checklist_network_bradford_av_product">
				</li>
				<li>
					<label>What antivirus definitions are installed?</label>
					<input type="text" name="checklist_network_bradford_av_def">
				</li>
			</ul>
		</li>
		<li>
			<label>Is the system in Bradford Remediation?:</label>
			<input type="radio" name="checklist_network_remediation" value="Yes" style="width: auto;"/> Yes
			<input type="radio" name="checklist_network_remediation" value="No" style="width: auto;margin-left:1.5em;"/> No
			<input type="radio" name="checklist_network_remediation" value="I don't know" style="width: auto;margin-left:1.5em;"/> I don't know
		</li>
		<li>
			<label>Does the system have the Bradford Persistent Agent?:</label>
			<select name="checklist_network_agent">
				<option value=""></option>
				<option value="Yes">Yes</option>
				<option value="No">No</option>
				<option value="I don't know">I don't know</option>
			</select>
			<ul class="sub" rel="Yes">
				<li>
					<label>What version is the Bradford Persistent Agent?</label>
					<input type="text" name="checklist_network_bradford_agent_version">
				</li>
			</ul>
		</li>
	</ul>
	<ul class="sub" rel="desktop|cts">
		<li>
			<label rel="Application/OS Topic:">Does this involve an application or operating system?</label>
			<select name="checklist_desktop_issue">
				<option value=""></option>
				<option value="Application Install">Application Install (Browser, Photoshop, MS Office, etc)</option>
				<option value="Application Problem">Application Problem (Browser, Photoshop, MS Office, etc)</option>
				<option value="OS Install">Operating System Install</option>
				<option value="OS Problem">Operating System Problem</option>
				<option value="Web Application">Neither. It is a Web Application Problem</option>
			</select>
			<p class="sub" rel="Web Application">
				If this is a web application issue, please use the Web Application ticket type.
			</p><!-- end rel=web -->
			<ul class="sub" rel="Application Install|Application Problem">
				<li>
					<label>Application:</label>
					<input type="text" name="checklist_desktop_application"/>
				</li>
				<li>
					<label>Application Version:</label>
					<input type="text" name="checklist_desktop_application_version"/>
				</li>
			</ul><!-- end rel$=Install|Problem -->
			<ul class="sub" rel="OS Install|OS Problem">
				<li>
					<label rel="Operating System:">Operating System:</label>
					<select name="checklist_desktop_os">
						<option value=""></option>
						<option value="Mac OS">Mac OS</option>
						<option value="Windows">Windows</option>
						<option value="Other">Other</option>
					</select>
					<ul class="sub" rel="Other">
						<li>
							<label>Other?  Oh really?  Do tell:</label>
							<input type="text" name="checklist_desktop_os_other"/>
						</li>
					</ul><!-- end rel=other -->
				</li>
				<li>
					<label>Operating System Version:</label>
					<input type="text" name="checklist_desktop_os_version"/>
				</li>
			</ul><!-- end rel=Install|Problem -->
			<ul class="sub" rel="Application Problem|OS Problem">
				<li>
					<label>Is there an error message?</label>
					<select name="checklist_desktop_error_exists">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="N/A">N/A</option>
					</select>
					<ul class="sub" rel="Yes">	
						<li>
							<label rel="Error Message:">What is the error message?</label>
							<textarea name="checklist_desktop_error"></textarea>
						</li>
					</ul><!-- end rel=Yes -->
				</li>
			</ul><!-- end rel$=Problem -->
		</li>
	</ul><!-- end rel=desktop|cts -->
	<ul class="sub" rel="ltoe">
		<li>
			<label rel="Which LMS:">What type of Learning Management System (LMS) is being used?</label>
			<select name="checklist_ltoe_lms">
				<option value=""></option>
				<option value="moodle1">Moodle 1</option>
				<option value="moodle2">Moodle 2</option>
			</select>
		</li>
		<li><label>Browser:</label> <input type="text" name="checklist_ltoe_browser"/></li>
		<li><label>Browser Version:</label> <input type="text" name="checklist_ltoe_browser_version"/></li>
		<li>
			<label>Does this occur in multiple browsers?:</label>
			<select name="checklist_ltoe_multi_browser">
				<option value=""></option>
				<option value="Yes">Yes</option>
				<option value="No">No</option>
				<option value="N/A">N/A</option>
			</select>
		</li>
		<li><label>What is the name of the course?:</label> <input type="text" name="checklist_ltoe_course"/></li>
		<li><label>What is the course ID? (eg. BU-2500-01):</label> <input type="text" name="checklist_ltoe_course_id"/></li>
		<li><label>Who is the course instructor?:</label> <input type="text" name="checklist_ltoe_course_instructor"/></li>
	</ul><!-- end rel=ltoe -->
	<ul class="sub" rel="webapp">
		<li>
			<label rel="Web Application:">What is the Web Application (or website) in question?</label>
			<input type="text" name="checklist_webapp"/>
		</li>
		<li>
			<label rel="URL:">What is the URL that is being accessed?</label>
			<input type="text" name="checklist_webapp_url"/>
		</li>
		<li>
			<label>What sort of Web Application issue is this?</label>
			<select name="checklist_webapp_issue">
				<option value=""></option>
				<option value="access">Access to the application is blocked/missing</option>
				<option value="error">Something isn't working as expected (error, blank page, etc)</option>
			</select>
			<ul class="sub" rel="access">
				<li>
					<label>Is this a service they have had before?</label>
					<select name="checklist_webapp_service">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="Unsure">Unsure</option>
					</select>
					<ul class="sub" rel="Yes">
						<li>
							<label>Approximately when did access to this service change?</label>
							<input type="text" name="checklist_webapp_access_when"/>
						</li>
					</ul><!-- end rel=Yes -->
					<p class="sub" rel="No|Unsure">
						Please provide additional details in the Problem Description below.
					</p><!-- end re=No|Unsure -->
				</li>
			</ul><!-- end rel=access -->
			<ul class="sub" rel="error">
				<li>
					<label>Is there an error message?</label>
					<select name="checklist_webapp_error_exists">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="N/A">N/A</option>
					</select>
					<ul class="sub" rel="Yes">	
						<li>
							<label rel="Error Message:">What is the error message?</label>
							<textarea name="checklist_webapp_error"></textarea>
						</li>
					</ul>
				</li>
				<li><label>Browser:</label> <input type="text" name="checklist_webapp_browser"/></li>
				<li><label>Browser Version:</label> <input type="text" name="checklist_webapp_browser_version"/></li>
				<li>
					<label>Does this occur in multiple browsers?:</label>
					<select name="checklist_webapp_multi_browser">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="N/A">N/A</option>
					</select>
				</li>
				<li>
					<label rel="Click Path:">What was the click path to get to this issue?</label>
					<textarea name="checklist_webapp_clickpath"></textarea>
				</li>
				<li>
					<label>Can this problem be reproduced?</label>
					<select name="checklist_webapp_reproducable">
						<option value=""></option>
						<option value="Yes">Yes</option>
						<option value="No">No</option>
						<option value="N/A">N/A</option>
					</select>
				</li>
			</ul><!-- end rel=error -->
		</li>
	</ul><!-- end rel=webapp -->
	<ul class="sub">
		<li>
			<label rel="Method of Contact:">Method of contact (phone number, email, etc) if followups are needed?</label>
			<input type="text" name="checklist_generic_contact"/>
		</li>
	</ul>
</div>
{/if}

<label>Ticket Title:</label>
<input type="text" name="title" id="title" maxlength="100" size="50" placeholder="Short problem description (title)" value="{$title|escape}">
<label>{$details_title|default:'Problem Description'}:</label>
<textarea name="problem_details" id="problem_details">{$details}</textarea>
