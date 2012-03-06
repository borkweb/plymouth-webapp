<?php
function smarty_modifier_money_format($string, $places= 2)
{
	$env = localeconv();
	if($env['int_curr_symbol'] != 'USD')
		setlocale(LC_MONETARY, 'en_US');
	return money_format("%.{$places}n", $string);
}
?>
