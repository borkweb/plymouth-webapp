<?php
// gets RSS feed from the helpdesk blog to get lastest entries then loops through and parses them in the template
function displayNewsFeed($template_file, $level=""){

	$tpl = new XTemplate($template_file);

	require_once 'Zend/Feed.php';

	$feed_url = 'http://helpdesk.blogs.plymouth.edu/category/its-helpdesk-news/feed';
	try {
			$rss = Zend_Feed::import($feed_url);
	} // end try
	catch (Zend_Feed_Exception $e) {
			return "Exception caught importing feed: {$e->getMessage()}\n";
	} // end catch

	if(!$rss) {
		return 'There are no articles in this news feed';
	} // end if

	$limit = 2;
	$count = 0;
	foreach($rss as $item) {	
		if($count>$limit-1) {
			break;
		}
		$count++;

		$tpl->assign('BlogNewsLink', $item->link);

		$emdash = chr(226).chr(128).chr(148);
		$apos = chr(226).chr(128).chr(153);
		$replace = array($emdash, $apos);
		$with = array('&mdash;',"'");
		$title = str_replace($replace,$with,$item->title());
		$tpl->assign('BlogNewsTitle', $title);

		$pubdate = date('M jS, Y \a\t g:ia',strtotime($item->pubDate));
		$tpl->assign('BlogNewsPubDate', $pubdate);

		$tpl->assign('BlogNewsCreator', $item->{'dc:creator'});

		// iterate over all categories
		$all_categories = array();
		foreach($item->category as $category) {
			$all_categories[] = $category;
		}// end foreach

		// if there is only one category, Zend will not iterate over it, so we need to grab it individually
		$all_categories = (count($all_categories)>0)?$all_categories:array($item->category);
		$tpl->assign('BlogNewsCategory', implode(', ', $all_categories));

		$tpl->parse('main'.$level.'.BlogNews');
	} // end foreach

	return $tpl->text('main'.$level.'.BlogNews');
} // end function displayNewsFeed

