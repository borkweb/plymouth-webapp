<h2>Your Receipt</h2>
<div class="block">
	<ul class="receipt">
		<li class="head">
			<strong>Your payment was successful!</strong>  Here is your receipt for your {$trans->ordertype} payment:
		</li>
		<li>
			<label>Confirmation Number:</label> <div>{$trans->transactionid}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Payment Date:</label> <div>{$trans->formatted_date}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Account Name:</label> <div>{$trans->ordername}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Activity Description:</label> <div>{$trans->ordertype}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Amount Purchased:</label> <div>${$trans->formatted_orderamount}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Cardholder's Name:</label> <div>{$trans->accountholdername}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Payment Method:</label> <div>{$trans->accounttype}</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Billing Address:</label> 
			<div class="address">
				{$trans->streetone}<br/>
				{if $trans->streettwo}{$trans->streettwo}<br/>{/if}
				{$trans->city}, {$trans->state} {$trans->zip}
			</div>
			<div class="clear"></div>
		</li>
		<li>
			<label>Contact Info:</label>
			<div class="contact">
				{$trans->daytimephone}<br/>
				{$trans->email}
			</div>
			<div class="clear"></div>
		</li>
		<li class="clear"></li>
	</ul>
	<div class="under-receipt">
		Have questions?  Contact the Plymouth State University Bursar's Office using their contact information found <a href="http://www.plymouth.edu/bursar/about.html">here</a>.
	</div>
</div>