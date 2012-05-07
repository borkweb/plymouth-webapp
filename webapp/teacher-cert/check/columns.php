<?php

require 'autoload.php';
require 'includes/TempDataCheck.php';
require 'includes/TempDataChecks.php';

$checks = new \PSU\TeacherCert\TempDataChecks;

?>
<style>
table {
	border-collapse: collapse;
}

td, th {
	border: 1px solid #ccc;
	padding: 0.3em;
}

.number {
	text-align: right;
}

.good td {
	color: #ccc;
}

.bad td.diff {
	color: red;
	font-weight: bold;
}

</style>
<?php
echo '<a href="columns.generate.php">Generate</a><br/>';
echo '
<table>
	<thead>
		<tr>
			<th>New Table</th>
			<th>New Column</th>
			<th>New Count</th>
			<th>New Difference</th>
			<th>Old Count</th>
			<th>Old Column</th>
			<th>Old Table</th>
		</tr>
	</thead>
	<tbody>
';
foreach( $checks as $check ) {
echo '
	<tr class="'.( ( $check->new_count - $check->old_count ) == 0 ? 'good' : 'bad' ).'">
		<td>'.$check->new_table.'</td>
		<td>'.$check->new_column.'</td>
		<td class="number">'.$check->new_count.'</td>
		<td class="number diff">'.( $check->new_count - $check->old_count ).'</td>
		<td class="number">'.$check->old_count.'</td>
		<td>'.$check->old_column.'</td>
		<td>'.$check->old_table.'</td>
	</tr>
';
}//end foreach

echo '
	</tbody>
</table>
';
