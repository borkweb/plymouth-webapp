<?php
// gets RSS feed from the helpdesk blog to get lastest entries then loops through and parses them in the template
function displayNewsFeed($template_file, $level=""){

	$tpl = new XTemplate($template_file);

	$rss = getNewsFeed();

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

		$tpl->assign('BlogNewsLink', $item['link']);
		$tpl->assign('BlogNewsTitle', $item['title']);
		$tpl->assign('BlogNewsPubDate', $item['date']);
		$tpl->assign('BlogNewsCreator', $item['creator']);
		$tpl->assign('BlogNewsCategory', $item['category']);

		$tpl->parse('main'.$level.'.BlogNews');
	} // end foreach

	return $tpl->text('main'.$level.'.BlogNews');
} // end function displayNewsFeed

// gets RSS feed from the helpdesk blog to get lastest entries then loops through and parses them in the template
function getNewsFeed(){

	$news = array();

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
		$article = array();
		if($count>$limit-1) {
			break;
		}
		$count++;

		$article['link'] = $item->link;
		
		$emdash = chr(226).chr(128).chr(148);
		$apos = chr(226).chr(128).chr(153);
		$replace = array($emdash, $apos);
		$with = array('&mdash;',"'");
		$title = str_replace($replace,$with,$item->title());

		$article['title'] = $title;

		$pubdate = date('M jS, Y \a\t g:ia',strtotime($item->pubDate));
		$article['date'] = $pubdate;

		$article['creator'] = $item->{'dc:creator'};

		// iterate over all categories
		$all_categories = array();
		foreach($item->category as $category) {
			$all_categories[] = $category;
		}// end foreach

		// if there is only one category, Zend will not iterate over it, so we need to grab it individually
		$all_categories = (count($all_categories)>0)?$all_categories:array($item->category);
		$article['category'] = implode( ', ', $all_categories );

		$news[] = $article;
	} // end foreach

	return $news;
} // end function displayNewsFeed

