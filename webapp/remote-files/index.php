<?php

$tpl = new RFSmarty();

$valid_directories = $GLOBALS['RFP']->directoriesForUser($_SESSION['pidm']);
$tpl->assign('valid_directories', $valid_directories);

$tpl->assign('title', 'Directories on <span>' . $GLOBALS['SSH_HOST'] . '</span>');

$tpl->assign('servers', $GLOBALS['RFP']->servers());

$tpl->assign('content', 'index');
$tpl->display('_wrapper.tpl');

// vim:ts=2:sw=2:noet:
