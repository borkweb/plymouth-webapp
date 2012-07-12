{box size=16}

{if $departments}
	<style>
		ul#alphabet_browse{ list-style-type: none; margin: 0; padding: 0}
		ul#alphabet_browse li{ display:inline; margin-left: 10px; }
	</style>

	<ul id="alphabet_browse">
		<li><a href="index.html?cmd=browseletter&letter=a">a</a></li>
		<li><a href="index.html?cmd=browseletter&letter=b">b</a></li>
		<li><a href="index.html?cmd=browseletter&letter=c">c</a></li>
		<li><a href="index.html?cmd=browseletter&letter=d">d</a></li>
		<li><a href="index.html?cmd=browseletter&letter=e">e</a></li>
		<li><a href="index.html?cmd=browseletter&letter=f">f</a></li>
		<li><a href="index.html?cmd=browseletter&letter=g">g</a></li>
		<li><a href="index.html?cmd=browseletter&letter=h">h</a></li>
		<li><a href="index.html?cmd=browseletter&letter=i">i</a></li>
		<li><a href="index.html?cmd=browseletter&letter=j">j</a></li>
		<li><a href="index.html?cmd=browseletter&letter=k">k</a></li>
		<li><a href="index.html?cmd=browseletter&letter=l">l</a></li>
		<li><a href="index.html?cmd=browseletter&letter=m">m</a></li>
		<li><a href="index.html?cmd=browseletter&letter=n">n</a></li>
		<li><a href="index.html?cmd=browseletter&letter=o">o</a></li>
		<li><a href="index.html?cmd=browseletter&letter=p">p</a></li>
		<li><a href="index.html?cmd=browseletter&letter=q">q</a></li>
		<li><a href="index.html?cmd=browseletter&letter=r">r</a></li>
		<li><a href="index.html?cmd=browseletter&letter=s">s</a></li>
		<li><a href="index.html?cmd=browseletter&letter=t">t</a></li>
		<li><a href="index.html?cmd=browseletter&letter=u">u</a></li>
		<li><a href="index.html?cmd=browseletter&letter=v">v</a></li>
		<li><a href="index.html?cmd=browseletter&letter=w">w</a></li>
		<li><a href="index.html?cmd=browseletter&letter=x">x</a></li>
		<li><a href="index.html?cmd=browseletter&letter=y">y</a></li>
		<li><a href="index.html?cmd=browseletter&letter=z">z</a></li>
	</ul>

	<strong>Click on a department name to browse faculty members or <a href="index.html?cmd=add_edit">Add New Faculty</a></strong><br />
	<ul>
		<li><a href="index.html?cmd=browsedepartment&dept_code=%&list=print">View All Faculty</a></li>
		{foreach from=$departments item=department}
		<li><a href="index.html?cmd=browsedepartment&dept_code={$department.code}">{$department.name}</a></li>
		{/foreach}
	</ul>
{/if}

{if $list == 'list'}
	Browsing faculty {$title}<br />
	<table class="table table-condensed table-bordered table-striped">
		{foreach from=$people item=person}
		<tr>
			<td><a href="index.html?cmd=add_edit&uid={$person.uid}">{$person.firstname} {$person.middleinitial} {$person.lastname}&nbsp;{$person.suffix}</a></td>
			<td>{$person.department}</td>
			<td>[<a href="index.html?cmd=remove&uid={$person.uid}">remove</a>]</td>
		</tr>
		{/foreach}
	</table>
{/if}

{if $list == 'print_list'}
	View all faculty<br />
		<ul style="padding:0;margin:0;text-align: left;width: 350px;">
		{foreach from=$people item=person}
		<li style="list-style: none; margin: 15px 0;width: 300px;">
			<div class="name" style="font-weight: bold;">{$person.lastname}, {$person.firstname} {$person.middleinitial}. ({$person.yearhired})</div>
			<div class="title" style="font-style: italic;">{$person.title}</div>
			<div class="education">{$person.education}</div>
		</li>
		{/foreach}
		</ul>
{/if}

{if $faculty_form}
	<form method="post">
	<div align="center">
	  <center>
	  <table border="0" width="40%" cellspacing="0" cellpadding="0">
		<tr>
		  <td width="100%"><font face="Tahoma" size="2"><b>Edit faculty member: </b>{$person.2} {$person.4}</font></td>
		</tr>
		<tr>
		  <td width="100%">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="43%">username:</td>
				<td width="57%">
				   <p><input type="text" name="person[uid]" size="26" value="{$person.uid}"></p>
				</td>
			  </tr>
			  <tr>
				<td width="43%">First Name:</td>
				<td width="57%"><input type="text" name="person[firstname]" size="26" value="{$person.firstname}"></td>
			  </tr>
			  <tr>
				<td width="43%">Middle Name:</td>
				<td width="57%"><input type="text" name="person[middleinitial]" size="2" value="{$person.middleinitial}"></td>
			  </tr>
			  <tr>
				<td width="43%">Last Name:</td>
				<td width="57%"><input type="text" name="person[lastname]" size="26" value="{$person.lastname}"></td>
			  </tr>
			  <tr>
				<td width="43%">Suffix:</td>
				<td width="57%"><input type="text" name="person[suffix]" size="26" value="{$person.suffix}"></td>
			  </tr>
			  <tr>
				<td width="43%">Year Hired:</td>
				<td width="57%"><input type="text" name="person[yearhired]" size="26" value="{$person.yearhired}"></td>
			  </tr>
			  <tr>
				<td width="43%">Department:</td>
				<td width="57%"><input type="text" name="person[department]" size="26" value="{$person.department}"></td>
			  </tr>
			  <tr>
				<td width="43%">Title:</td>
				<td width="57%"><input type="text" name="person[title]" size="26" value="{$person.title}"></td>
			  </tr>
			  <tr>
				<td width="43%">Education:</td>
				<td width="57%"><input type="text" name="person[education]" size="60" value="{$person.education}"></td>
			  </tr>
			  <tr>
				<td width="43%">Faculty Number:</td>
				<td width="57%"><input type="text" name="person[facultynumber]" size="6" value="{$person.facultynumber}"></td>
			  </tr>
			  <tr>
				<td width="43%">Homepage:</td>
				<td width="57%"><input type="text" name="person[homepageurl]" size="26" value="{$person.homepageurl}"></td>
			  </tr>
			  <tr>
				<td width="43%">Active Status:</td>
				<td width="57%"><input type="radio" name="person[active_status]" value="1" {$active_status_selected_1}>Active <input type="radio" name="person[active_status]" value="0" {$active_status_selected_0}>Inactive</td>
			  </tr>  
			  <tr>
				<td width="43%"></td>
				<td width="57%"><input type="submit" value="Submit" name="B1"></td>
			  </tr>
			</table>
		  </td>
		</tr>
	  </table>
	  </center>
	</div>
	</form>
{/if}

{/box}
