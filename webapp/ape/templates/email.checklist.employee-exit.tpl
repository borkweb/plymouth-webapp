<p>Greetings,</p>
<p>
<strong>{$employee_name}</strong>, is leaving employment {if $position}as <strong>{$position.classification}</strong> in the <strong>{$position.organization_title}</strong> Department{/if} at Plymouth State University on <strong>{$end_date|date_format}</strong>.  As part of the automated employment exit process, 
you have been identified as the person responsible for "clearing" the exiting employee for your area.  This means reviewing and ensuring that the exiting employee has settled 
all currently open transactions with the University and returned all materials/equipment as it pertains to your area.
</p>
<p>
Please visit the <a href="{$link}">Employee Clearance Form</a> to complete the necessary section for your designated area.
</p>
<p>
Note:  Principal Administrators, Deans and Department Chairs may forward this automated e-mail to the assigned Administrative Assistant for completion.
</p>
<ul>
	<li>Username: <a href="http://go.plymouth.edu/ape/{$subject->username}">{$subject->username}</a></li>
	<li>PSU ID: <a href="http://go.plymouth.edu/ape/{$subject->id}">{$subject->id}</a></li>
	{if $subject->wp_id}<li>WP ID: <a href="http://go.plymouth.edu/ape/{$subject->wp_id}">{$subject->wp_id}</a></li>{/if}
	{if $no_outstanding_charges}
		<li>There are no outstanding charges for this account.</li>
	{/if}
	{if $no_ape_attributes}
		<li>There are no manually assigned APE attributes for this account.</li>
	{/if}
	{if $no_academic_admin}
		<li>There are no academic administration privileges for this account.</li>
	{/if}
</ul>
