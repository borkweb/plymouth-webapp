{box class="grid_16"}
<div style="margin: 0px auto;text-align:left; width:500px;">
		<div style="text-align:center;">
			<h5>Plymouth State Directory Information is for <a href="#disclaimer">personal use only.</a></h5>
			This directory tool provides access to <a href="#public_info">public information</a>. 
		</div>


	<form id="person_search">
		<input type="search" x-webkit-speech name="what" value="{$what}" />
		<input name="submit" type="submit" value="Search >>" />
		<br/>Display: <input type="radio" name="empstu" value="0" {$empstu_0}/> All
			<input type="radio" name="empstu" value="1" {$empstu_1}/> Faculty/Staff
			<input type="radio" name="empstu" value="2" {$empstu_2}/> Students
		</select>
	</form>

	{if !$username}
		<br /><small>Note: A maximum of 10 results will be returned. <a href="{$login_uri}">Login for more</a>.</small>
	{else}
		<p>Printable directories: <a href="/webapp/banner/psc1/printed_directory/loadFacultyPDF/Printed_Directory_FacStaff.pdf">Faculty/Staff <img src="/images/pdficonsm.gif" alt="PDF Icon" /></a>  or <a href="/webapp/banner/psc1/printed_directory/loadStudentPDF/Printed_Directory_Students.pdf">Students <img src="/images/pdficonsm.gif" alt="PDF Icon" /></a></p>
		<p>Supplemental Information: <a href="/webapp/banner/psc1/printed_directory/deptandservices.pdf">Departments, Services, Etc.<img src="/images/pdficonsm.gif" alt="PDF Icon" /></a> (updated 9/2012)</p>
	{/if}
	</div>
<div class="results">
	{if $people}
		<h4>Results for '{$what}'</h4>
		{foreach from=$people item=person}
			<div class="block person">
				<table width="100%">
					<tr>
						<td valign="top">
							{if $can_see_images}<img src="/webapp/idcard/u/{$person.email}" width="98" height="131" style="border:1px solid #000; float: left;" alt="{$person.email}" />{/if}
							<table style="float:left;">
								<tr>
									<td align=right width="150"><strong>Name: </strong></td>
									<td>{$person.name_full}</td>
								</tr>
								{if $person.title && $person.title != 'Unknown'}
								<tr>
									<td align=right><strong>Title: </strong></td>
									<td>{$person.title}</td>
								</tr>
								{/if}
								{if $person.phone_of}
								<tr>
									<td align=right><strong>Office Phone: </strong></td>
									<td>{$person.phone_of}</td>
								</tr>
								{/if}
								{if $person.phone_vm}
								<tr>
									<td align=right><strong>Student Voicemail: </strong></td>
									<td>{$person.phone_vm}</td>
								</tr>
								{/if}
								{if $person.msc}
								<tr>
									<td align=right><strong>Mail Stop: </strong></td>
									<td>{$person.msc}</td>
								</tr>
								{/if}
								{if $person.dept}
								<tr>
									<td align=right><strong>Department: </strong></td>
									<td>{$person.dept}</td>
								</tr>
								{/if}
								{if $person.major}
								<tr>
									<td align=right><strong>Major: </strong></td>
									<td>{$person.major}</td>
								</tr>
								{/if}
								{if $person.email}
								<tr>
									<td align=right><strong>Email: </strong></td>
									<td class="em"></td>
								</tr>
								{/if}
							</table>
						</td>
					</tr>
				</table>
				<br clear="all"/>
			</div>
		{/foreach}
  	{/if}
</div>
<script type="text/javascript">
	$(function(){ 
		var a, emails = [{foreach from=$people item=person}'{$person.js_escaped_email}',{/foreach}];
		for(var i = 0; i < emails.length; i++)
		{
			a = $('<a/>').attr('href', 'mailto:' + emails[i]).text(emails[i]);
			$('.em').eq(i).append(a);
		}
	});
</script>



	<div style="margin: 0px auto;text-align:left; width:500px;">

	<h1 id="disclaimer">Personal Use</h1>
	<p>No part of the Plymouth State Directory may be reproduced, stored in a retrieval system, or retransmitted without the prior written permission of an authorized officer of the University.</p>

	<p>Any use of the addresses or other information for any multiple mailing without the express written consent of the appropriate officer is contrary to University policy and is prohibited.</p>

	<p>Contact the <a href="mailto:securityofficer@plymouth.edu">Chief Security Officer</a> at Plymouth State University for more information.</p>

	{if !$username}
	{/if}
	
	<h1 id="public_info">Public Information</h1>
	<p>PSU provides public directory information to facilitate communication among students, faculty, staff and the public.</p>
	
	<p>The Public Student Database contains the following data elements:</p>
	
	<ul>
		<li>First Name, Middle Initial, Last Name</li>
		<li>Phone Number</li>
		<li>Campus Suite Number</li>
		<li>Declared Major</li>
		<li>E-mail Address</li>
	</ul>
	
	<p>Issues of public confidentiality and FERPA concerning students should be directed to the Dean of Student Affairs.</p>

	<p>The Public information for faculty and staff contains the following data elements:</p>

	<ul>
		<li>First, Middle, and Last Name</li>
		<li>Title</li>
		<li>Office Phone</li>
		<li>Location</li>
		<li>Department and Mail Stop</li>
		<li>E-mail Address</li>
	</ul>

	<p>Issues of public confidentiality concerning employees 
	should be directed to the Director of Human Resources.</p>
	</div>

{/box}
