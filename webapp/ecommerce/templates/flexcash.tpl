{box}
	<div style="text-align: center;">
	<h3>Add Campus FlexCash Now!</h3>
	<p class="brief">
		<img src="{$PHP.BASE_URL}/templates/images/flexcash_small.gif" class="logo flexcash"/>
		This tool allows the purchase of Campus FlexCash funds with the use of
		Visa or Mastercard for any active faculty, staff member, or
		student having a valid PSU ID Card. Family members, friends, or associates
		purchasing FlexCash for other parties should inform the recipient so they
		are aware that they have new funds on their account.
	</p>
	<form id="person_search" action="#results">
		<div class="subtle">Want to add Campus Flexcash? Search for someone's name below:</div>
		<input type="text" name="s" value="{$person_what}" />
		<input type="submit" value="Search >>" />
		<br/>Display: <input type="radio" name="empstu" value="all" {$empstu_all}/> All
			<input type="radio" name="empstu" value="emp" {$empstu_emp}/> Faculty/Staff
			<input type="radio" name="empstu" value="stu" {$empstu_stu}/> Students
	</form>
	</div>
	<p class="note subtle pad">
		<strong>What is Campus FlexCash?</strong> Campus FlexCash are funds held on account that can
		be accessed at a variety of locations throughout campus with an individual's
		valid PSU Identification Card. All foodservice operations, food and beverage
		vending machines, laundry rooms, the Follett Campus Bookstore, Ice Rink
		skate rental area, and some campus copy machines accept Campus FlexCash. Campus FlexCash 
		funds do not expire and balances are refunded to students when they graduate or leave PSU for any reason.
	</p>
	<p class="note subtle pad">
		<strong>How is this different from Board FlexCash?</strong>
		Board FlexCash is issued with select semester-based meal plans.  Board FlexCash is only valid for the semester in which the 
		particular meal plan is purchased and thus expires at the close of that term.  Locations that use FlexCash automatically 
		draw from the Board FlexCash portion first (since that type has an expiration) and then from the Campus FlexCash portion 
		when those funds are depleted.  Students can monitor their balances through their myPlymouth account.
	</p>
	<p class="note subtle pad">
		<div class="message-container">
			<div class="message message-messages">
				<strong>Please Note:</strong> A purchase made prior to 4pm on a business day will be credited
				to the account the morning of the next business day. A purchase made after
				4pm on a business day will be credited on the account the morning of the
				second business day.
			</div>
		</div>
	</p>
	<p class="note subtle pad">
		Questions or problems regarding this system should be referred to PSU
		Residential Life by emailing <a href="mailto:flex-cash@plymouth.edu">flex-cash@plymouth.edu</a> or 
		by telephone at	603-535-2260.
	</p>
	<div style="clear:both;"></div>
{/box}
{if $smarty.get.s}
{box id="results" class="results" title="Search Results for `$what`"}
	{if $people}
		<ul class="people">
		{foreach from=$people item=person}
			<li class="block person" style="position:relative;zoom:1;">
			{box class="person" style="clear"}
				{if $person.has_idcard && $person.username}
					<a href="{$PHP.BASE_URL}/flexcash/add/{$person.username}" class="add-flexcash">Add Campus FlexCash</a>
				{/if}
				<ul class="attributes">
					<li>
						<label>Name:</label>
						{$person.name_full} {if $person.display_roles}<span class="subtle">({$person.display_roles})</span>{/if}
					</li>
					{if $person.email}
					<li>
						<label>Email:</label>
						<a href="mailto:{$person.email}@plymouth.edu">{$person.email}@plymouth.edu</a>
					</li>
					{/if}
					{if $person.phone}
					<li>
						<label>Phone:</label>
						{$person.phone}
					</li>
					{/if}
					{if $person.mail_stop}
					<li>
						<label>Mail Stop:</label>
						{$person.mail_stop}
					</li>
					{/if}
					{if $person.title}
					<li>
						<label>Title:</label>
						{$person.title}
					</li>
					{/if}
					{if $person.dept}
					<li>
						<label>Department:</label>
						{$person.dept}
					</li>
					{/if}
					{if $person.major}
					<li>
						<label>Major:</label>
						{$person.major}
					</li>
					{/if}
				</ul>
				<div style="clear:both;"></div>
			{/box}
		{/foreach}
  {/if}
	</div>
{/box}
{/if}
