{col size="4"}
	{box title="Jump To"}
		<ul class="clean">
			<li>
				<a href="http://go.plymouth.edu/ape">APE</a>
			</li>
			<li>
				<a href="http://go.plymouth.edu/billestimator">Bill Estimator</a>
			</li>
			<li>
				<a href="http://go.plymouth.edu/miscbilling">Misc Billing</a>
			</li>
			<li>
				<a href="http://go.plymouth.edu/bursarmaintenance">Misc Billing Maintenance</a>
			</li>
			<li>
				<a href="http://go.plymouth.edu/bill">Online Bill</a>
			</li>
		</ul>
	{/box}
{/col}

{col size="12"}
	{box title="AR Dashboard"}
		<h4>ECommerce</h4>
		<p>
			<ul>
				<li>
					There {if $pending_ecommerce == 1}is{else}are{/if} {$pending_ecommerce} ecommerce file{if $pending_ecommerce != 1}s{/if} awaiting processing.
				</li>
			</ul>
		</p>

		<h4>Payment Plan Disbursements</h4>
		<p>
			<ul>
				<li>
					There {if $disbursements.unprocessed == 1}is{else}are{/if} {$disbursements.unprocessed} disbursement file{if $disbursements.unprocessed != 1}s{/if} awaiting processing.
				</li>
				{if $disbursements.invalid_id}
				<li class="danger">
					There {if $disbursements.invalid_id == 1}is{else}are{/if} {$disbursements.invalid_id} invalid ID{if $disbursements.invalid_id != 1}s{/if} in the most recently processed file.
				</li>
				{/if}
				{if $disbursements.difference}
				<li class="danger">
					The is a difference between the expected disbursement totals and what was actually processed into TBRACCD.
				</li>
				{/if}
			</ul>
		</p>

		<h4>Payment Plan Contracts</h4>
		<p>
			<ul>
				<li>
					There {if $contracts.unprocessed == 1}is{else}are{/if} {$contracts.unprocessed} contract file{if $contracts.unprocessed != 1}s{/if} awaiting processing.
				</li>
				{if $contracts.invalid_id}
				<li class="danger">
					There {if $contracts.invalid_id == 1}is{else}are{/if} {$contracts.invalid_id} invalid ID{if $contracts.invalid_id != 1}s{/if} in the most recently processed files.
				</li>
				{/if}
				{if $contracts.difference}
				<li class="danger">
					The is a difference between the expected contract totals and what was actually processed into TBRMEMO.
				</li>
				{/if}
			</ul>
		</p>
	{/box}
{/col}
