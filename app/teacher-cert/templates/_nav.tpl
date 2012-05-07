<ul class="grid_16">
	<li><a href="{$PHP.BASE_URL}">Home</a></li>
	{foreach from=$gatesystems item=iter_system}
		{if $permissions->has_gatesystem($iter_system->level_code)}
			<li>
				<a href="{$PHP.BASE_URL}/gate-system/{$iter_system->slug}">{$iter_system->name}</a>
				<ul>
				{foreach from=$iter_system->gates() item=iter_gate}
					<li>
						<a href="{$PHP.BASE_URL}/gate-system/{$iter_system->slug}/gate/{$iter_gate->slug}">{$iter_gate->name}</a>
					</li>
				{/foreach}
				</ul>
			</li>
		{/if}
	{/foreach}
	{if $permissions->has('admin')}
		<li>
			<a href="http://go.plymouth.edu/analytics">Reports</a>
			<ul>
				<li>
					<a href="/webapp/analytics/report/tcert-gpa/">GPAs</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-student-contact/">Student Contact Info</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-sa/">SAU Employees By Student</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-vouchers/">Vouchers</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-gates-1-2-3/">UG Gates 1, 2, 3</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-ug-gate2/">UG Gate 2</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-ug-gate3/">UG Gate 3</a>
				</li>
				<li>
					<a href="/webapp/analytics/report/tcert-ug-gate4/">UG Gate 4</a>
				</li>
			</ul>
		</li>
		<li>
			<a href="{$PHP.BASE_URL}/admin">Administration</a>
			{include file="menu.general-data.tpl"}
		</li>
	{/if}
</ul>
