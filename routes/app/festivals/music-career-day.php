<?php

respond( function( $request, $response, $app ){
	PSU::session_start();

	$GLOBALS['BASE_URL'] = '/app/festivals/music-career-day';

	$app->event_year = 2012;

	$GLOBALS['TITLE'] = "{$app->event_year} Music Technology and Education Career Day";
	$GLOBALS['TEMPLATES'] = PSU_BASE_DIR . '/app/festivals/music-career-day/templates';

	if( !isset($_SESSION['mtecd']) ) {
		$_SESSION['mtecd'] = array();
	}

	$app->mail = function($id) {
		$tpl = new PSU\Template;

		$f = PSU::db('myplymouth')->GetRow("SELECT * FROM mte_career_day WHERE id_ = ?", array($id));
		$form = new PSU\Festivals\MusicCareerDay\Model($f, true);
		$form->readonly(true);

		$tpl->assign('form', $form);
		$message = $tpl->fetch( 'email.tpl' );

		$to = array('rikp@plymouth.edu');

		if( PSU::isdev() ) {
			$to[0] = 'ambackstrom@plymouth.edu';
		}

		$subject = sprintf("[MTECD {$app->event_year}] Application for %s %s", $form->first_name->value(), $form->last_name->value());

		PSU::mail( $to, $subject, $message, 'Content-Type: text/html' );
	};

	$app->tpl = new PSU\Template;
	$app->tpl->assign( 'event_year', $app->event_year );
});

respond( '/apply', function( $request, $response, $app ){
	$f = isset($_SESSION['mtecd']['f']) ? $_SESSION['mtecd']['f'] : array();
	$form = new PSU\Festivals\MusicCareerDay\Model($f);

	$app->tpl->assign('form', $form);
	$app->tpl->display( 'apply.tpl' );
});

respond( '/', function( $request, $response, $app ){
	$app->tpl->display( 'index.tpl' );
});

respond( '/thank-you', function( $request, $response, $app ){
	$f = $_SESSION['mtecd']['f'];
	unset($_SESSION['mtecd']['f']);

	if( ! $f ) {
		PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
	}

	$form = new PSU\Festivals\MusicCareerDay\Model($f);
	$form->readonly(true);

	$app->tpl->assign('form', $form);
	$app->tpl->display( 'thank_you.tpl' );
});

respond( '/_submit', function( $request, $response, $app ){
	$f = $_POST;
	$application = new PSU\Festivals\MusicCareerDay\Model($f);

	list( $required, $filled, $percent ) = $application->progress();

	$_SESSION['mtecd']['f'] = $application->form();

	if( $percent < 1 ) {
		$_SESSION['errors'][] = 'Please fill in all required fields.';
		PSU::redirect( $GLOBALS['BASE_URL'] . '/apply' );
	}

	if( $id = $application->save() ) {
		$app->mail( $id );
	} else {
		$_SESSION['errors'][] = 'There was an error saving your submission data.';
		PSU::redirect( $GLOBALS['BASE_URL'] . '/apply' );
	}

	PSU::redirect( $GLOBALS['BASE_URL'] . '/thank-you' );
});
