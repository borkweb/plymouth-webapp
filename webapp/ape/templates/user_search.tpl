<form id="search" method="get" action="{$PHP.BASE_URL}/search.html">
	Search by <select id="search_type" name="type" tabindex="1">
		<option value="name" {$search_name}>Name</option>
		<option value="pidm" {$search_pidm}>Pidm</option>
		<option value="psu_id" {$search_psu_id}>PSU ID</option>
		<option value="login_name" {$search_login_name}>Username</option>
		<option value="sourced_id" {$search_sourced_id}>Sourced ID</option>
		<option value="email" {$search_email}>Email</option>
		<option value="device" {$search_device}>Device</option>
	</select>: <input type="search" name="identifier" value="{$search_term|escape}" size="25" tabindex="2" placeholder="Enter Search Term" autofocus results="5"> <input type="submit" value="Go" tabindex="3"> 
</form>
