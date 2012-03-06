<?php

/**
 * Needed for the registry.
 */
require_once('PSUTools.class.php');
require_once 'StatsD.class.php';

/**
 * A class for dealing with Go keywords, and the Go sidebar in the portal.
 */
class go
{
	/**
	 * The currently logged-in user.
	 */
	public $wp_id;

	/**
	 * Whether or not debugging mode is enabled, default false.
	 */
	public $_debug = false;

	/**
   * constucter for the portal connection
   *
   * @param boolean wp_id
   */
	function __construct($wp_id = false)
	{
		if($wp_id)
		{
			$this->setUser($wp_id);
		}// end if
	}//end __construct
	
	/**
   * Set the currently logged-in user.
   *
   * @param boolean $wp_id
   */
	function setUser($wp_id)
	{
		$this->wp_id = $wp_id;
	}//end setUser

	/**
   * function debug
   * 
   * preps the issues for debuging
   * 
   */
	function debug( $state = true )
	{
		PSU::db('go')->debug = $this->debug_ = $state;
	}//end debug

	/**
	 * Translate the incoming hostname to go.ply if we are on the dev box so that
	 * database lookups on the hostname will catch all the normal go keywords.
	 */
	public static function dev_hostname( $host ) {
		if( $host == 'go.dev.plymouth.edu' && PSU::isdev() ) {
			return 'go.plymouth.edu';
		}

		return $host;
	}//end dev_hostname

	/**
   * creates a new row in the sql database with the feedback
   * 
   * @param string $keyword value inserted into SQL db
   * @param string $feedback value inserted int SQL db
   *
   */
	function saveFeedback($keyword, $feedback)
	{
		$keyword = $this->cleanKeyword( $keyword );
		$sql = "INSERT INTO feedback (wp_id, failed_keyword, description) VALUES (?, ?, ?)";
		PSU::db('go')->Execute($sql, array( $this->wp_id, $keyword, $feedback ));
	}//end saveFeedback

	/**
   * Log an entry in the database if the user connects from on-campus.
   */
	function studentSighted()
	{
		if(substr($_SERVER['REMOTE_ADDR'],0,8)=='158.136.')
		{
			$sql = "INSERT DELAYED INTO student_sighted (wp_id, date_stamp) VALUES (?, NOW())";
			PSU::db('go')->Execute($sql, array( $this->wp_id ));
		}//end if
	}//end studentSighted

	/**
   * takes all of the unnessessary characters out of $keyword then returns that string
   *
   * @param string $keyword
   * @return string $keyword
   */
	function cleanKeyword($keyword)
	{
		$keyword = trim(strtolower(urldecode($keyword)));
		$keyword = str_replace(array(' ', '"', "'"), '', $keyword);
		$keyword = str_replace(',', '/', $keyword);

		$keyword = rtrim( $keyword, '/' );

		// remove period (.) characters from the first segment
		if(preg_match('!^([^/]*\.[^/]*)!', $keyword, $matches))
		{
			$replace = str_replace('.', '', $matches[0]);
			$keyword = $replace . substr( $keyword, strlen($matches[0]) );
		}//end if

		return $keyword;
	}//end cleanKeyword

	/**
   * Return the url keyword information.
   *
   * @param $keyword the target keyword
   */
	function getKeywordInfo($keyword, $domain = 'go.plymouth.edu')
	{
		$domain = PSU::apply_filters('go_hostname', $domain);

		if($keyword=='main/404html')
		{
			$keyword = $this->cleanKeyword(str_replace('/go/','',$_SERVER['REQUEST_URI']));
			return $this->getKeywordInfo($keyword, $domain);
		}//end if

		// at minimum, we examine a cleaned keyword
		$keyword = $this->cleanKeyword( $keyword );
		$keyword_orig = $keyword;

		$keyword_long = $keyword; // long version, not exploded into an array
		$key_array = explode('/',$keyword);
		$keyword = $key_array[0];

		$args = array( $domain );
		$keywords = array( $keyword );

		if( $keyword_long != $keyword ) {
			// look for the full keyword itself
			$keywords[] = $keyword_long;
		}

		array_walk( $keywords, function( &$item, $key ) {
			$item = PSU::db('go')->qstr( $item );
		} );
		$keywords = implode( ', ', $keywords );

		$info = PSU::db('go')->GetRow("
			SELECT
				d.url, k.id AS keyword_id,
				d.sso_url,
				d.id AS destination_id,
				k.is_ambiguous,
				k.dynamic_destination_id,
				k.keyword,
				dom.domain AS go_domain,
				d.pidm_required
			FROM
				keyword k LEFT JOIN
				domain dom ON k.domain_id = dom.id LEFT JOIN
				destination d ON k.destination_id=d.id
			WHERE
				k.keyword IN ( $keywords ) AND
				dom.domain = ?
			ORDER BY LENGTH( k.keyword ) DESC
		", $args);

		// if we matched on a keyword that contained forward slash (/)...
		if( $keyword != $keyword_long && $info['keyword'] == $keyword_long ) {
			// ... skip all the query string stuff
			return $info;
		}

		$info['keyword'] = $keyword;

		// append variables to the URLs
		if($info['destination_id'] && count($key_array)>1)
		{
			unset($key_array[0]);

			$url = '';
			if($info['dynamic_destination_id'])
			{
				$url = $db->GetOne("SELECT url FROM destination WHERE id='{$info['dynamic_destination_id']}'");
			}// end if
			else
			{
				$url = $info['url'];
			}// end else

			$info['dyn_url'] = '';
			$info['dyn_url'] = $this->addVarsToURL($url,$key_array);

			if(strpos($info['dyn_url'],'$')!==false)
			{
				$info['dyn_url'] = '';
			}//end if
		}//end if
	
		$info['url'] = ($info['dyn_url'])?$info['dyn_url']:$info['url'];

		return $info;
	}//end getKeywordInfo
	
	/**
   * trackUnknownKeyword takes any keyword passed in that wasn't found anywhere and puts it into a database.
   * 
   * @param string $keyword
   */
	function trackUnknownKeyword($keyword)
	{
		$sql = "INSERT DELAYED INTO keyword (keyword) VALUES ('$keyword')";
		PSU::db('go')->Execute($sql);
		StatsD\StatsD::increment( 'app.go.unknown_keyword' );
	}//end trackUnknownKeyword

	/**
   * updates a keyword count and then adds a new row with the keyword
   *
   * @param mixed $keyword-id
   * @param mixed $destination_id
   */
	function trackKeywordUsage($keyword_id, $destination_id)
	{
		$sql = "UPDATE statistics SET count=count+1 WHERE destination_id = ? AND keyword_id = ? AND date_stamp=NOW() AND wp_id = ?";
		PSU::db('go')->Execute($sql, array( $destination_id, $keyword_id, $this->wp_id ));
		if(PSU::db('go')->Affected_Rows()==0)
		{
			$sql = "INSERT INTO statistics (destination_id, keyword_id, wp_id, date_stamp, count) VALUES(?, ?, ?, NOW(),1)";
			PSU::db('go')->Execute($sql, array( $destination_id, $keyword_id, $this->wp_id));
		}//end if

		StatsD\StatsD::increment( 'app.go.keyword' );
	}//end trackKeywordUsage
	
	/**
   * takes any terms used in a search and inserts them into a sql database
   *
   * @param $search_term
   */
	function trackSearchTerm($search_term)
	{
		$search_term = stripslashes($search_term);
		$sql = "INSERT DELAYED INTO search_statistics (search_term, wp_id, date_stamp) VALUES(?, ?, NOW())";
		PSU::db('go')->Execute($sql, array( $search_term, $this->wp_id));
		$this->trackKeywordUsage(459, 359);
		StatsD\StatsD::increment( 'app.go.search' );
	}//end trackSeachTerm
	
	/**
   * alters the current url with the passed in variables
   *
   * @param string $url
   * @param array $keyarray
   * @return $url
   */
	function addVarsToURL($url,$key_array)
	{
		$num = count($key_array);
		$found = false;
		foreach($key_array as $i=>$value)
		{
			if(strpos($url,'$'.$i)!==false)
			{
				$url = str_replace('$'.$i,$value,$url);
				$found = true;
			}//end if
		}//end foreach

		if(!$found)
		{
			$url .= (strpos($url,'?')===false)?'?':'&';
			$url .= 'go='.implode('||',$key_array);
		}//end if

		return $url;
	}//end addVarsToURL
	
	/**
   * sets up the primary list of links on the portal page
   *
   * @param array $options
   */
	function getSites($options=array())
	{
		// valid options include: return_type, limit, wp_id, order

		if(!$options['order']){$options['order']='ORDER BY count DESC, d.title';}

		if(!$options['return_type']){$options['return_type']='array';} // other valid option is list

		if($options['wp_id']===true)
		{
			$options['wp_id'] = $_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username'];
			if($options['wp_id'])
			{
				$options['wp_id'] = "AND s.wp_id='{$options['wp_id']}'";
				$options['hidden'] = true;
			}//end if
		}//end if
		elseif($options['portal']===true)
		{
			$options['wp_id'] = "AND s.wp_id<>'0' AND k.id NOT IN(SELECT keyword_id FROM keyword_category)";
		}//end ifelse

		// hidden will need to be reworked to respect permissions and IdM
		if(!$options['hidden'])
		{
			$options['hidden'] = 'AND k.is_hidden=0';
		}//end if
		else
		{
			$options['hidden'] = '';
		}//end else
		
		if(isset($options['num_days']))
		{
			$tmp_date = date('Y-m-d', strtotime('-'.$options['num_days'].' days'));
			$options['date_range'] = "AND s.date_stamp>'$tmp_date'";
		}//end if
		else
		{
			$options['date_range'] = '';
		}//end else

		if(!isset($options['limit']))
		{
			$options['limit']='LIMIT 10';
		}//end if
		elseif($options['limit']<=0)
		{
			$options['limit'] = '';
		}// end ifelse
		else
		{
			$options['limit'] = 'LIMIT '.$options['limit'];
		}//end else

		if(isset($options['exclude_pidm_required']))
		{
			$options['exclude_pidm_required'] = 'AND d.pidm_required=0';
		}
		else
		{
			$options['exclude_pidm_required'] = '';
		}

		if($options['return_type']=='list')
		{
			$top = '';
		}//end if
		else
		{
			$top = array();
		}//end else
		
		$sql = "SELECT k.keyword, SUM(s.count) as count, d.title, d.target, d.url 
			FROM statistics s, keyword k, destination d 
			WHERE k.id=s.keyword_id 
				AND d.id=k.destination_id 
				AND k.destination_id>0 
				AND k.is_ambiguous=0 
				AND k.deleted=0 
				AND d.luminis_only <> 1
				{$options['date_range']} 
				{$options['hidden']} 
				{$options['exclude_pidm_required']} 
				{$options['wp_id']} 
			GROUP BY d.id 
			{$options['order']} 
			{$options['limit']}";

		if($res = PSU::db('go')->Execute($sql)) 
		{
			while($row=$res->FetchRow())
			{
				if($options['return_type']=='list')
				{
					$top .= '<li>'.$this->getATag($row).'</li>';
				}//end if
				elseif($options['return_type']=='array')
				{
					$top[] = $row;
				}//end elseif
			}// end while
		}//end if
		return $top;
	}//end getSites

	function cacheGetSites($selected = 'popular-everyone', $return = 'array') {
		if( !file_exists( '/web/temp/popular' ) ) {
			mkdir( '/web/temp/popular' );
		}

		if($_SESSION['pidm']) {
			$exclude_pidm_required = false;
			$pop_everyone_path = '/web/temp/popular/popular-pidm.txt';
		}
		else {
			$exclude_pidm_required = true;
			$pop_everyone_path = '/web/temp/popular/popular.txt';
		}

		if( $this->wp_id ) {
			$pop_me_path = '/web/temp/popular/'.$this->wp_id.'.txt';
		}//end if

		$cache_expiration = time() - 86400; // cache files for 24 hours

		if( !$selected ) {
			$selected = $this->wp_id && $selected ? $selected: 'popular-everyone';
		}//end if

		$path = $selected == 'popular-everyone' ? $pop_everyone_path : $pop_me_path;
		$force_update = $options['force'] ? true : @filemtime($path) < $cache_expiration;
		$popular = @file_get_contents($path);

		if(!$popular || $force_update) {
			if( $selected == 'popular-everyone' ) {
				$options = array(
					'return_type'=>'array',
					'limit'=>10,
					'num_days'=>3,
					'portal'=>true,
					'exclude_pidm_required'=>$exclude_pidm_required
				);
				$popular = $this->getSites($options);
			} else {
				$options = array('return_type'=>'array','limit'=>10,'wp_id'=>true);
				$popular = $this->getSites($options);
			}//end else

			$popular = serialize($popular);
			file_put_contents($path, $popular);
		}//end if

		$popular = $popular ? unserialize($popular) : array();

		if($return == 'list') {
			$top = '';

			foreach($popular as $link) {
					$top .= '<li>'.$this->getATag($link).'</li>';
			}//end foreach

			$popular = $top;
		}//end if

		return $popular;
	}//end cacheGetSites

/**
  *this funtion takes in a url and formats it to a go link
  *
  *@param string $url
  *@return string $go
  */
	function convertUrlToGo($url)
	{
		if(strcmp(substr($url, strpos($url, 'http'), '10'),'http://go.')==0)
		return false;
		
		$url=trim($url);
		
			$url = preg_replace('/\/$/', '', $url);
		
		$url = preg_replace('/^(.*):\/\//', '', $url);

		$sql = 'SELECT k.keyword 
			FROM keyword AS k
			LEFT JOIN destination AS d
			ON k.destination_id=d.id 
			WHERE url 
			REGEXP "[a-z]+://'.$url.'/?"
			AND k.is_hidden=0
			ORDER BY k.destination_id' ;
		$keyword = PSU::db('go')->getone($sql);
               	if($keyword)
		{
			$go = 'http://go.plymouth.edu/'.$keyword;
			return $go;
		}
		
			return false;
		
		
	}	
	/**
   * this function takes in an array and sends that info to a tag to a tag on the page
   *
   * @param array row$
   * @return sting $a_tag
   */
	function getATag($row)
	{
		switch($row['target'])
		{
			case 'top':
				$target='_top';
				$prepend='';
			break;
			case 'blank':
				$target='_blank';
				$prepend='';
			break;
			case 'frame':
				$target='_top';
				$prepend='/render.userLayoutRootNode.uP?uP_tparam=frm&frm=';
			break;
		}//end switch
		
		$onclick='';
		if(substr($row['url'],0,10)=='javascript')
		{
			$target='_top';
			$url = '';
			$onclick = $row['url'];
		}//end if
		else
		{
			$url = 'http://go.plymouth.edu/'.$row['keyword'];
		}//end else

		$a_tag = '<a href="'.$prepend.$url.'" title="'.$row['keyword'].'" target="'.$target.'" onclick="'.$onclick.'">'.(($row['title'])?$row['title']:'[No Title]').'</a>';

		return $a_tag;
	}//end getATag

	/**
   * gets the list of all the items from a folder and the bookmakred ones
   *
   * @param integer $folder_id
   * @return string $content
   */
  function generateBookmarkList($folder_id = 0, $type = 'luminis')
	{
		if(!$this->wp_id)
		{
			return '';
		}//end if 

		$content = '';

		$found = false;

		$folder_res = PSU::db('go')->Execute("SELECT name, id FROM folder WHERE wp_id=? AND parent_id=?", array( $this->wp_id, $folder_id ));
		
		while($folder = $folder_res->FetchRow())
		{
			$found = true;
			$display='';
			$open = PSU::db('go')->GetOne("SELECT open FROM folder WHERE id=".$folder['id']);
			$display = ($open)?"-open":"";

			if($type === 'luminis'){
				$content .= '<li class="bookmark-folder'.$display.' treeItem" id="folder-'.$folder['id'].'"><div class="folder-header"><img src="/psu/images/spacer.gif" class="icon folder" alt="folder"/>'.$folder['name'].'</div>';
				$content .= $this->generateBookmarkList($folder['id'], $type);
				$content .= '</li>';
			}else{
				$content .= '<li rel="folder" class="bookmark-folder'.$display.' treeItem" id="folder-'.$folder['id'].'"><a href="#">'.$folder['name'].'</a>';
				$content .= $this->generateBookmarkList($folder['id'], $type);
				$content .= '</li>';
			}
		}//end while
		
		$res = PSU::db('go')->Execute("SELECT id,url, title, folder_id, go_destination_id FROM bookmark WHERE wp_id=? AND folder_id=?", array($this->wp_id, $folder_id));
		while($bookmark = $res->FetchRow())
		{
			$style = 'treeItem bookmark';

			if($bookmark['go_destination_id'])
			{
				$keyword = PSU::db('go')->GetOne("SELECT keyword FROM keyword WHERE destination_id={$bookmark['go_destination_id']} AND deleted=0 ORDER BY is_secondary, is_hidden");
				if($keyword)
				{
					$bookmark['url'] = 'http://go.plymouth.edu/'.$keyword;
					$style .=  ' go_bookmark';
				}//end if
			}//end if
			$found = true;

			
			if($type === 'luminis'){
				$content .= '<li class="'. $style.' treeItem" id="bookmark-'.$bookmark['id'].'"><a href="'.$bookmark['url'].'" target="_blank">'.$bookmark['title'].'</a></li>';
			}else{
				$content .= '<li rel="file" class="'. $style.' treeItem" id="bookmark-'.$bookmark['id'].'"><a href="'.$bookmark['url'].'" target="_blank">'.$bookmark['title'].'</a></li>';
			}
		}//end while
		
		if($found)
		{
			if($folder_id==0)
			{
				if($type === 'luminis'){
					$content =  '<div id="bookmarks" class="bookmark-folder"><ul id="bookmark_list">'.$content.'</ul></div>';
				}else{
					$content =  '<div id="bookmarks" class="bookmark-folder"><ul id="bookmark_list">'.$content.'</ul></div>';
				}	
			}//end if
			else
			{
				if($type === 'luminis'){
					$content =  "<ul id=\"bookmark-folder_$folder_id\" style=\"$display\">$content</ul>";
				}else{
					$content =  "<ul id=\"bookmark-folder_$folder_id\" style=\"$display\">$content</ul>";
				}	
			}//end else
		}//end if
		
		return $content;
	}//end generateBookmarkList
	
	/**
   * makes a call to the db folder and changes the open value at $folder_id
   *
   * @param integer $folder_id 
   * @param boolean $open
   */
	function saveFolderState($folder_id, $open)
	{
		PSU::db('go')->Execute("UPDATE folder SET open=$open WHERE id=$folder_id AND wp_id=?", array($_SESSION['wp_id']));
	}//end saveFolderState
	
	/**
   * adds values to the db bookmark with the variable $bookmark
   *
   * @param string $bookmark
   */
	function addBookmark($bookmark)
	{
		PSU::db('go')->Execute("INSERT INTO bookmark (wp_id, folder_id, go_destination_id, url, title) VALUES (?, 0, 0, ?, ?)", array( $_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username'], $bookmark['url'], $bookmark['title']));
	}//end addBookmark
	
	/**
   * Adds into the folder database a folder for bookmarks.
   * @param sting $title
   * @param integer $folder
   */
	function addBookmarkFolder($title, $folder=0)
	{
		PSU::db('go')->Execute("INSERT INTO folder (wp_id, name, parent_id) VALUES (?, ?, ?)", array( $_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username'], $title, $folder));
	}//end addBookmarkFolder
	
	/**
   * gives a bookmark folder a specific id so it can be look at as a bookmarkfolder?
   * @param string $bookmark_id
   * @param string $folder_id
   */
	function setBookmarkFolder($bookmark_id, $folder_id)
	{
		PSU::db('go')->Execute("UPDATE bookmark SET folder_id=$folder_id WHERE id=$bookmark_id AND wp_id=", array($_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username']));
	}//end setBookmarkFolder
	
	/**
   * sets the folder that is above the passed in folder
   * @param string $folder_id
   * @param string $parent_id
   */
	function setFolderParent($folder_id, $parent_id)
	{
		if($folder_id!=$parent_id)
			PSU::db('go')->Execute("UPDATE folder SET parent_id=$parent_id WHERE id=$folder_id AND wp_id=?", array($_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username']));
	}//end setFolderParent

	/**
   * delete information from the database folder and bookmark
   * @param string $folder_id
   */
	function deleteFolder($folder_id)
	{
		if($folder_id)
		{
			PSU::db('go')->Execute("DELETE FROM folder WHERE id=$folder_id AND wp_id=?", array($_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username']));
			PSU::db('go')->Execute("DELETE FROM bookmark WHERE folder_id=$folder_id AND wp_id=?", array($_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username']));
		}//end if
	}//end deleteFolder
	
	/**
   * deletes a bookmark from the db bookmark
   * @param string $fbookmark_id
   */
	function deleteBookmark($bookmark_id)
	{
		if($bookmark_id)
		{
			PSU::db('go')->Execute("DELETE FROM bookmark WHERE id=$bookmark_id AND wp_id=?", array($_SESSION['wp_id'] ? $_SESSION['wp_id'] : $_SESSION['username']));
		}
	}//end deleteBookmark
	
	/**
   * takes the state of a certain value from user_meta
   * @param string $name
   * @param boolean $pid
   * @return string UNDEFINED 
   */
	function getState($name,$pid=false)
	{
		if($pid===false)
			$pid = $this->wp_id;
		return PSU::db('go')->GetOne("SELECT value FROM user_meta WHERE wp_id=? AND name LIKE ?", array($pid, $name));
	}//end getState
	
	/**
   * returns the state of all the plymouth user ids
   * @param string $name
   * @param boolean $pid
   * @return array $return_states
   */
	function getStates($name='%',$pid=false)
	{
		if($pid===false)
			$pid = $this->wp_id;

		$states = PSU::db('go')->GetAll("SELECT name, value FROM user_meta WHERE wp_id=? AND name LIKE ?", array($pid, $name));

		$return_states = array();
		if(is_array($states))
		{
			foreach($states as $state)
			{
				$return_states[$state['name']]=$state['value'];
			}//end foreach
		}//end if

		$states = PSU::db('go')->GetAll("SELECT name, value FROM user_meta WHERE wp_id='0' AND name LIKE '".$name."'");
		if(is_array($states))
		{
			foreach($states as $state)
			{
				if(!isset($return_states[$state['name']]))
				{
					$return_states[$state['name']]=$state['value'];
				}//end if
			}//end foreach
		}//end if

		return $return_states;
	}//end getStates

	/**
   * getting the sidebar from the db user_meta
   * @param boolean $wp_id
   * @return mixed
   */
	function getSidebarSort($wp_id)
	{
		return $this->getUserMeta($wp_id,'go_sidebar_order');
	}//end getSidebarSort
	
	/**
   * saves the sorted sidebar information in the db user_meta
   * @param boolean $wp_id
   * @param string $order
   * @return string $content
   */
	function saveSidebarSort($wp_id,$order)
	{
		$this->saveUserMeta($wp_id,'go_sidebar_order',$order);
	}//end saveSidebarSort
	
	/**
   * returns the current user_meta from the db user_meta
   *
   * @param boolean $wp_id
   * @param string $field
   * @return string UNDEFINED
   */
	function getUserMeta($wp_id,$field)
	{
		return PSU::db('go')->GetOne("SELECT value FROM user_meta WHERE wp_id=? AND name=?", array($wp_id, $field));
	}//end getUserMeta

	/**
   * Calling the db user_meta and changes the value of the current user_meta
   *
   * @param boolean $wp_id
   * @param string $field
   * @param integer $value 
   */
	function saveUserMeta($wp_id,$field,$value)
	{
		$args = array($wp_id, $field, $value);
		PSU::db('go')->Execute("REPLACE INTO user_meta (wp_id,name,value,activity_date) VALUES (?, ?, ?, NOW())", $args);
	}//end saveUserMeta
	
	
	/**
	* Get all the keywords that begin with a given letter
	*
	* @param string $letter
	* @return array $value 
	*/	
	function getKeywordsByAlpha($letter)
	{
		return PSU::db('go')->GetAll("SELECT k.keyword, d.title, d.id destination_id, k.id keyword_id FROM keyword k, destination d WHERE d.id=k.destination_id AND k.destination_id>0 AND k.is_ambiguous=0 AND k.deleted=0 AND k.is_secondary=0 AND k.is_hidden=0 AND k.keyword LIKE '{$letter}%' ORDER BY k.keyword");
	}

	/**
	 * Remove www from incoming host names before database lookup.
	 */
	public static function strip_www( $domain ) {
		if( substr($domain, 0, 4) == "www." ) {
			return substr($domain, 4);
		}

		return $domain;
	}//end strip_www

	/**
	 * Take a string, and return two copies of that string, including
	 * and excluding the trailing slash.
	 *
	 * @param $string The string
	 * @return array The string without a trailing slash, and the string with a trailing slash, in that order.
	 */
	public function trailingSlash( $string ) {
		if( '/' === substr( $string, -1 ) ) {
			$withSlash = $string;
			$withoutSlash = substr( $string, 0, -1 );
		} else {
			$withoutSlash = $string;
			$withSlash = $string . '/';
		}

		return array( $withoutSlash, $withSlash );
	}//end trailingSlash
}//end go
