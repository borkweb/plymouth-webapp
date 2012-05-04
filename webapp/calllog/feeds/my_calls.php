<?php
session_start();
header("Content-type: text/xml");
?>
<rss version="2.0">
  <channel> 
    <title>ITS Helpdesk - Call Log Web Feeds</title>
    <description>Feeds that you care about</description>
	<link>https://www.plymouth.edu/webapp/calllog</link>
<?php
$my_calls = returnOpenCalls($_GET['action'], $_GET['group_id']);
for($i=0;$i<count($my_calls);$i++){
	if($my_calls[$i]['call_id'] != ""){
		$my_calls[$i]['comments'] = substr(str_replace("’","&#39;", $my_calls[$i]['comments']), 50)."...";
		$my_calls[$i]['comments'] = substr(str_replace("&","&amp;", $my_calls[$i]['comments']), 50)."...";
?>
		<item>
		<title><?php echo $my_calls[$i]['caller_first_name'] ?> <?php echo $my_calls[$i]['caller_last_name'] ?> (<?php echo $my_calls[$i]['caller_username'] ?>)</title>
		<link>https://www.plymouth.edu/webapp/calllog/new_call.html?caller_user_name=<?php echo $my_calls[$i]['caller_username'] ?>&amp;call_id=<?php echo $my_calls[$i]['call_id'] ?></link>
		<description><?php echo $my_calls[$i]['comments'] ?></description>
		<pubDate><?php echo date("r", strtotime($my_calls[$i]['call_date'].' '.$my_calls[$i]['call_time'])); ?></pubDate>
		<category>Web</category>
		<guid isPermaLink="false">https://www.plymouth.edu/webapp/calllog/new_call.html?caller_user_name=<?php echo $my_calls[$i]['caller_username'] ?>&amp;call_id=<?php echo $my_calls[$i]['call_id'] ?></guid>
		</item>
<?php 
	} 
}
?>
	<item>
	<title>ITS Helpdesk Blog</title>
	<link>http://helpdesk.blogs.plymouth.edu/tag/its-helpdesk-news/</link>
	<description>Your lastest on Helpdesk News</description>
	<guid isPermaLink="false">http://helpdesk.blogs.plymouth.edu/tag/its-helpdesk-news/</guid>
	</item>
</channel>
</rss>