<?php
/*****************************************
 *  
 * Options:
 *  url    : feed url (required)
 *  id     : container id (required)
 *  title  : 0 = hide title (default)
 *           1 = output title
 *  desc   : 0 = hide desc (default)
 *           1 = output desc
 *	num    : number of stories (default: 5)
 *  summary: 0 = full text (default)
 *           1 = summary text
 *  expand : 0 = disabled
 *           1 = enabled (default)
 *           2 = always expanded
 *
 *****************************************/
/**
 * Register the application specific GLOBALS
 */
$GLOBALS['BASE_URL'] = '/webapp/portal/channel/rss';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);
$GLOBALS['TEMPLATES'] = __DIR__ . '/templates';

/**
 * Require necessary scripts starting with autoload.
 * Bring in Zend since it is external.
 * Also bring in our own included API
 */
require_once 'autoload.php';
require_once 'Zend/Feed.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/RSSAPI.class.php';

/**
 * Start our PSU Session handler
 */
PSU::session_start();

/**
 * Start the translation to handle this as an object...??
 */
ob_start( 'RSSAPI::translate' );

use PSU\Feed;

$id = $_GET['channel_id'] ?: 'rsschannel';

$tpl = new PSUTemplate;
$tpl->channel(array(
	'channel_js_callback' => "$.my.rss.init('".$id."')",
));

/**
 * Go through, and assess if variables different from the defaults have been passed in, 
 * and set them accordingly.
 */
$num = $_GET['num'] ?: 5;   // records to show
$open = $_GET['open'] ?: 0; // initially expanded items
$expand = isset($_GET['expand']) ? $_GET['expand'] : Feed::EXPAND_DISABLED; // 0 is a valid value; use isset
$summary = isset($_GET['summary']) ? $_GET['summary'] : Feed::BODY_FULL;
$title = $_GET['title'] ? Feed::FIELD_SHOW : Feed::FIELD_HIDE;
$read_more = $_GET['read_more'] ?: 'Read more';
$desc = isset($_GET['desc']) ? $_GET['desc'] : Feed::FIELD_HIDE;
$length = $_GET['len'] ?: 500; // passing in as "len" because JS objects don't like "length"

/**
 * Look at what we are getting for a URL, and handle if it needs special attention/
 * Problem Children
 * ----------------
 * - mycomics
 * - MERLOT
 * - NY Times
 */
$mycomics = false;

if( $_GET['url'] == 'mycomics' ) {
	$mycomics = true;
	$my_subscribed_comics = PSU::db('go')->GetOne("SELECT value FROM user_meta WHERE pidm = ? AND name = 'mycomics'", array( $_SESSION['pidm'] ) );
	
	if( ! $my_subscribed_comics ) {
		$safe = array('blondie', 'dilbert', 'archie', 'calvin', 'beetlebailey', 'dennisthemenace', 'garfield', 'heathcliff', 'mothergooseandgrimm', 'sallyforth', 'ziggy', 'wizardofid', 'hagar');
		$my_subscribed_comics = $safe[array_rand($safe)];
	}

	$comics_rss_url = 'http://darkgate.net/comic/rss2.php?';
	$_GET['url'] = $comics_rss_url.$my_subscribed_comics;
}

$bad_rss = strpos($_GET['url'], 'merlot.org') !== false;
$nytimes = strpos($_GET['url'], 'nytimes.com') !== false;

/**
 * Have Zend go out and pull in the feed that we are looking at.
 */
try {
    $rss = Feed::import( $_GET['url'] );
} catch (Zend_Feed_Exception $e) {
    echo "Exception caught importing feed: {$e->getMessage()}\n";
    exit;
}

/**
 * Set the $rss object parameters to match the values passed into the channel.
 */
$rss->max = $num;
$rss->open = $open;
$rss->expand = $expand;
$rss->summary = $summary;

switch( $expand ) {
	case EXPAND_FORCED: $li_class = 'expanded'; break;
	case EXPAND_ENABLED: $li_class = 'contracted'; break;
	default: $li_class = '';
}

$link_text = $summary ? 'Read the full story' : 'Go to story';

$entries = array();
foreach( $rss as $item ) {
	$contentencoded = 'content:encoded';
	$ftext = $item->description();

	/**
	 * Run the links in an item through the deatomize function.
	 * This acts to handle in case the links are in an atom format instead
	 * of RSS. If they are normal, they will simply be returned...
	 */
	$link = RSSAPI::deatomize( $item->link() );
	$item->deatomized_link = $link;
	if( $item->image() ) {
		$image_link = RSSAPI::deatomize($item->image->link());
		$item->image->deatomized_link = urlencode($image_link);
	}
	
	if($length) {	
		// Look for img tags
		$textCharThresh = $length;
		$goodCharCount = 0;
		$counting = true;
		$ftext = RSSAPI::deatomize($ftext);

		$ftext = htmlentities( $ftext, ENT_NOQUOTES, 'UTF-8' );

		for ($i=0;$i<strlen($ftext);$i++) {	
			if ($ftext[$i] == '<') {
				$counting = false;
			}

			if ($counting) {
				$goodCharCount++;
			}

			if ($ftext[$i] == '>') {
				$counting = true;
			}

			if ($goodCharCount >= $textCharThresh) {
				break;
			}
		}

		
		$snippet = substr($ftext,0,$i);

		$snippet = PSU::closeOpenTags($snippet);

		list($snippet) = preg_split('/\s\w+$/',$snippet);
		$xtra = preg_replace('/( \w+)$/','',trim($snippet));
		
		$xtra = preg_replace('/\[...\]/','',$xtra);

		$text = $xtra;
		$text .= ' <a href="'.$item->handled_link.'" target="_blank">...</a>'; 
	} else {
		$text = $ftext;
	}//end else

	if($bad_rss) {
		$text = html_entity_decode($text);
	}

	$test = preg_match('/<img(.*)src( ){0,1}=( ){0,1}(.*?).flickr.com\/(.*?)\/>/',$text,$matches);
	if (stripos($matches[0], 'flickr') !== false) {
		$orgstr = $matches[0];
		$matches[0] = preg_replace('/width="(.*?)"/','',$matches[0]);
		$matches[0] = preg_replace('/height="(.*?)"/','',$matches[0]);
		if (preg_match('/_o\.jpg/',$matches[0])) {
			$matches[0] = str_replace('_o.jpg','_s.jpg',$matches[0]);
		}
		elseif (preg_match('/_m\.jpg/',$matches[0])) {
			///nothing;
		}
		elseif ((!preg_match('/_sq\.jpg/',$matches[0])) && (!preg_match('/_s\.jpg/',$matches[0])) && (!preg_match('/_t\.jpg/',$matches[0]))) {
			$matches[0] = str_replace('.jpg','_m.jpg',$matches[0]);
		}
	}
	$text = str_replace($orgstr,$matches[0],$text);
	$text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', '', $text);

	$item->out_text = $text;

	$entries[] = $item;

	//There's no need to process more items than have been requested, so break
	//when that ammount has been reached.
	if( count( $entries ) >= $num ) {
		break;
	}//end if
}//end foreach

/**
 * Assign all of our relevant variables to the template.
 * We can do this using compact in the assign, and we will have
 * associated variables that we can call in the template.
 *
 * Ex. if we have an array $entries here, we will have an array variable called 
 * entries in the template...
 */
$tpl->assign( compact(
	'title', 'rss', 'desc',
	'id', 'mycomics', 'summary',
	'link_text', 'entries'
) );

//Display the template
$tpl->display('index.tpl');
