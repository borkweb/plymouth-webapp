<?php

PSU::get()->banner = PSU::db('test');
PSU::db('banner')->debug = true;

$args = array(
	'pidm' => $_REQUEST['pidm'],
	'term_code' => $_REQUEST['term_code'],
	'comment_text' => $_REQUEST['note'],
);

$query = "INSERT INTO sgrscmt(
					sgrscmt_pidm,
					sgrscmt_seq_no,
					sgrscmt_term_code,
					sgrscmt_comment_text,
					sgrscmt_activity_date
				)
				VALUES
				(
					:pidm,
					(SELECT MAX(NVL(sgrscmt_seq_no,0))+1 FROM sgrscmt WHERE sgrscmt_pidm=:pidm),
					:term_code,
					:comment_text,
					SYSDATE
				)";
$action = PSU::db('banner')->Execute($query,$args); 

PSUHTML::redirect($GLOBALS['BASE_URL'] . '/student/' . $person->pidm);
