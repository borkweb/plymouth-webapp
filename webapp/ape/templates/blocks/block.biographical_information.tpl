<div id="ape_adv_bio" class="ape-section {if $myuser->go_states.ape_adv_bio === '0'}ape-section-hidden{/if}">
	<h3>Biographical Information</h3>
	<ul class="apedata">
		{if $person->confidential}<li><label>Confidential:</label> Confidential</li>{/if}
		
		{if $person->sex}
			<li><label>Gender:</label>
			{if $person->sex=='M'}Male{elseif $person->sex=='F'}Female{else}Unknown{/if}</li>
		{/if}

		{if ($AUTHZ.banner.developmentofficer || $AUTHZ.role.advancement_officer) && $advancement}
			{if $person->alumni->college && $person->alumni->coll_code_pref != 'PL'}
				<li><label>Preferred College:</label>
				{$person->alumni->college}</li>
			{/if}

			{if $person->alumni->coll_code_pref == 'PL'}<li><label>College:</label> Plymouth State University</li>{/if}
			{if $person->alumni->pref_clas}<li><label>Preferred Class:</label>{$person->alumni->pref_clas}</li>{/if}
			{if $person->alumni->category}<li><label>Category:</label> {$person->alumni->category}</li>{/if}
			{if $person->alumni->spouse}
				<li><label>Status:</label>
				{if $person->alumni->primary_spouse=='P'}Primary Spouse{else}Secondary Spouse{/if}</li>
			{/if}
			{if $person->legacy_desc}<li><label>Legacy:</label> {$person->legacy_desc}</li>{/if}
			{if $person->alumni->occupation}<li><label>Occupation:</label> {$person->alumni->occupation}</li>{/if}
			{if $person->alumni->income}<li><label>Income:</label> {$person->alumni->income}</li>{/if}
			{if $person->religion}<li><label>Religion:</label> {$person->religion}</li>{/if}
			{if $person->ethnicity}<li><label>Ethnicity:</label> {$person->ethnicity}</li>{/if}
			{if $person->race}<li><label>Race:</label> {$person->race}</li>{/if}
			{if $person->confirmed_race}<li><label>Confirmed:</label> {$person->confirmed_race}</li>{/if}
		{/if}
		{if ($AUTHZ.banner.developmentofficer || $AUTHZ.role.advancement_officer) && $advancement && $person->alumni->psu_names}
		<li>
			<div id="ape_adv_salutations" class="ape-section {if $myuser->go_states.ape_adv_salutations === '0'}ape-section-hidden{/if}">
				<h4>Presidential Salutations</h4>
				<ul class="apedata">
					{foreach from=$person->alumni->psu_names item=item}
						{if $item.pres_name_line_1} <li><label>President's name line 1:</label> {$item.pres_name_line_1}</li> {/if}
						{if $item.pres_name_line_2} <li><label>President's name line 2:</label> {$item.pres_name_line_2}</li> {/if}
						{if $item.pres_salutation} <li><label>President's salutation:</label> {$item.pres_salutation}</li> {/if}
						<li><hr/></li>
					{/foreach}
				</ul>
			</div>
		</li>
		{/if}
		{if ($AUTHZ.banner.developmentofficer || $AUTHZ.role.advancement_officer) && $advancement && $person->alumni->degrees}
		<li>	
			<div id="ape_adv_degrees" class="ape-section {if $myuser->go_states.ape_adv_degrees === '0'}ape-section-hidden{/if}">
				<h4>Degrees</h4>
				<ul class="apedata">
					{foreach from=$person->alumni->degrees item=item}
						{if $item.institution}<li><label>Institution:</label> {$item.institution}</li>{/if}
						{if $item.degree}<li><label>Degree:</label> {$item.degree}</li>{/if}
						{if $item.honors}<li><label>Honors:</label> {$item.honors}</li>{/if}
						{if $item.majors}<li><label>Majors:</label> {$item.majors}</li>{/if}
						{if $item.year}<li><label>Year:</label> {$item.year}</li>{/if}
						{if $item.campus}<li><label>Campus:</label> {$item.campus}</li>{/if}
						<li><hr/></li>
					{/foreach}
				</ul>
			</div>
		</li>
		{/if}
		{if ($AUTHZ.banner.developmentofficer || $AUTHZ.role.advancement_officer) && $advancement && $person->alumni->employment_history}
		<li>
			<div id="ape_adv_employment" class="ape-section {if $myuser->go_states.ape_adv_employment === '0'}ape-section-hidden{/if}">
				<h4>Employment History</h4>
				<ul class="apedata">
					{foreach from=$person->alumni->employment_history item=item}
						{if $item.employer}<li><label>Employer's Name:</label> {$item.employer}</li>{/if}
						{if $item.position}<li><label>Position:</label> {$item.position}</li>{/if}
						{if $item.emp_status}<li><label>Status:</label> {$item.emp_status}</li>{/if}
						{if $item.from_date}
							<li><label>Duration:</label>
							<small>From {$item.from_date|date_format:"%m/%d/%Y"} To {if $item.to_date==''}the Present {else} {$item.to_date|date_format:"%m/%d/%Y"} {/if}</small></li>
						{/if}
						<li><hr/></li>
					{/foreach}
				</ul>
			</div>
		</li>
		{/if}
	</ul>
</div>
