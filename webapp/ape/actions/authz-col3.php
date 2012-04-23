<?php

list($type_id, $type) = $GLOBALS['BannerIDM']->any2type($_GET['type']);
$pidm = (int)$_GET['pidm'];
$attribute = $_GET['attribute'];

$tpl = new APESmarty;
$user = new PSUPerson($pidm);

$where = "type_id = $type_id AND attribute = " . $GLOBALS['BannerIDM']->db->qstr($attribute);

$logs = $GLOBALS['BannerIDM']->getLogs($pidm, $where);
$logs = $logs[$type][$attribute];

$tpl->assign('type', $type);
$tpl->assign('user', $user);
$tpl->assign('logs', $logs);
$tpl->display('authz-person.tpl', false);
