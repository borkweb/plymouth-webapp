<?php
/**
 * PSUTheme.class.php
 *
 * Theming API
 *
 * @version		1.0.0
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 * @package 		Themes
 */ 
class PSUTheme
{
	var $base_dir;
	var $theme;

	/**
	 * add
	 *
	 * adds a theme's CSS to the theme compilation
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $theme theme id
	 * @param     string $css file for theme css
	 * @param     boolean $clear_previous purge previous theme css
	 */
	function add($theme, $css = 'style.css', $clear_previous = false)
	{
		if(!$this->isOptedOut($_SESSION['wp_id'], $theme))
		{
			$theme_css = $this->base_dir.'/themes/'.$theme.'/'.$css;
			$theme_js = $this->base_dir.'/themes/'.$theme.'/behavior.js';

			if($clear_previous)
			{
				$this->theme = array();
				$this->js = array();
				$this->theme[$theme] = @file_get_contents($theme_css);
				$this->js[$theme] = @file_get_contents($theme_js);
			}//end if
			else
			{
				$this->theme[$theme] = @file_get_contents($theme_css);
				$this->js[$theme] = @file_get_contents($theme_js);
			}//end else
		}//end if
	}//end add

	/**
	 * addExternal
	 *
	 * adds an external CSS to the theme compilation
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $css_url URL to the CSS file
	 * @param     boolean $clear_previous purge previous theme css
	 */
	function addExternal($css_url, $clear_previous = false)
	{
		// create a new cURL resource
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $css_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// grab URL and pass it to the browser
		$css = curl_exec($ch);

		$curl_info = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		// close cURL resource, and free up system resources
		if($curl_info != 'text/css')
		{		
			$css ='';
		}//end else
		
		curl_close($ch);
		
		if($clear_previous)
		{
			$this->theme = array();
			$this->theme[$theme] = $css;
		}//end if
		else
		{
			$this->theme[$theme] = $css;
		}//end else
	}//end addExternal

	/**
	 * getPopularThemes
	 *
	 * retrieves themes by order of popularity 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $level Theme threat level
	 * @return    array
	 */
	function getPopularThemes()
	{
		$data = array();
		$sql = "SELECT t.code,
		               t.name,
									 t.mercury_status,
		               count(t.code) as count 
		          FROM user_theme u,
		               theme t 
		         WHERE u.theme_id = t.id 
		           AND t.code <> 'default' 
						   AND t.mercury_status <> 'disabled'
		         GROUP BY t.code 
		         ORDER BY count(t.code) DESC,
		               t.name";
		if($results = PSU::db('myplymouth')->CacheExecute($sql))
		{
			foreach( $results as $row ) {
				$data[$row['code']] = $row;
			}//end foreach
		}//end if
		
		return $data;
	}//end getPopularThemes

	/**
	 * getThemeCount
	 *
	 * retrieves themes by order of popularity 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $level Theme threat level
	 * @return    array
	 */
	function getThemeCount($theme='all')
	{
		if($theme != 'all')
		{
			$theme_data = $this->getThemeData($theme);
			$where = " AND ut.theme_id = '{$theme_data['id']}'";
		}//end if
		
		$sql = "SELECT count(*)
							FROM user_theme ut
						       JOIN theme t
							       ON t.id = ut.theme_id
						        AND t.mercury_status <> 'disabled'
		         WHERE ut.theme_id <> 1 $where";
		return PSU::db('myplymouth')->CacheGetOne($sql);
	}//end getThemeCount

	/**
	 * getThemeData
	 *
	 * retrieves theme data
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $id Theme identifier
	 * @return    array
	 */
	function getThemeData($id)
	{
		if(is_numeric($id))
		{
			$where = "id = '{$id}'";
		}//end if
		else
		{
			$where = "code = '{$id}'";
		}//end else
		
		return PSU::db('myplymouth')->CacheGetRow("SELECT * FROM theme WHERE 1=1 AND mercury_status <> 'disabled' AND {$where}");
	}//end getThemeData

	/**
	 * getThemes
	 *
	 * retrieves all themes 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $level Theme threat level
	 * @return    array
	 */
	function getThemes($level)
	{
		if(is_array($level))
		{
			$level = "'".implode("','", $level)."'";
		}//end if
	
		$data = array();
		$sql = "
			SELECT *, 
			       DATEDIFF( NOW(), create_date ) days_old 
				FROM theme 
			 WHERE level in({$level}) 
				 AND mercury_status <> 'disabled'
			       {$where} 
			 ORDER BY name
		";

		if($results = PSU::db('myplymouth')->CacheExecute($sql)) {
			foreach($results as $row ) {
				$data[$row['code']] = $row;
			}//end foreach
		}//end if

		return $data;
	}//end getThemes

	/**
	 * getUsersByTheme
	 *
	 * retrieves all themes 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $theme theme
	 * @return    array
	 */
	function getUsersByTheme($theme)
	{
		if($theme != 'all')
		{
			$theme_data = $this->getThemeData($theme);
			$where = " AND theme_id = '{$theme_data['id']}'";
		}//end if
	
		$data = array();
		$sql = "SELECT p.name_last,
		               p.name_first,
		               p.email username,
									 u.set_by,
									 p.psu_id
		          FROM user_theme u,
		               phonebook.phonebook p,
		               theme t 
		         WHERE u.theme_id = t.id 
		           AND u.wp_id = p.wpid $where 
		         ORDER BY p.name_last,
		               p.name_first,
		               t.name";
		if($results = PSU::db('myplymouth')->CacheExecute($sql))
		{
			foreach($results as $row) {
				$row['name_last'] = ucfirst($row['name_last']);
				$row['name_first'] = ucfirst($row['name_first']);
				$data[] = $row;
			}//end foreach
		}//end if
		
		return $data;
	}//end getUsersByTheme

	/**
	 * getUserTheme
	 *
	 * retrieves a user's currently selected theme 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $wp_id Person identifier
	 * @return    string
	 */
	function getUserTheme($wp_id, $return = 'theme_id', $type = 'GetOne')
	{
		return PSU::db('myplymouth')->$type("SELECT {$return} FROM user_theme, theme t WHERE theme_id = t.id AND wp_id=?", array($wp_id));
	}//end getUserTheme

	/**
	 * returns whether or not a theme has been opted out of by the user
	 */
	function isOptedOut($wp_id, $theme)
	{
		$sql = "SELECT 1 
			        FROM theme t, 
							     user_theme_optout o 
						 WHERE t.id = o.theme_id 
						   AND t.level in ('event','hidden-event','holiday','dev') 
							 AND o.wp_id = ? 
               AND ( t.code = ? OR t.code = ?)";
		return PSU::db('myplymouth')->GetOne($sql, array($wp_id, $theme, substr( $theme, 0, -1 )));
	}//end isOptedOut

	/**
	 * loadUserTheme
	 *
	 * loads and applies a user's currently selected theme 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $wp_id Person identifier
	 */
	function loadUserTheme($wp_id)
	{
		$stylesheet = ($_GET['style']) ? preg_replace('/[^a-zA-Z0-9\-\_]/','',$_GET['style']).'.css' : 'style.css';
		$user_theme = isset($_GET['theme']) ? $_GET['theme'] : $this->getUserTheme($wp_id);
	
		if($wp_id)
		{
			if($theme = $this->getThemeData($user_theme))
			{
				$split_theme = explode(':',$theme['code']);
				if(count($split_theme)>1)
				{
					foreach($split_theme as $t)
					{
						$this->add($t, $stylesheet);
					}//end foreach
				}//end if
				else
				{
					$this->add($theme['code'], $stylesheet);
				}//end else
			}//end if
		}//end if
	}//end loadUserTheme

	public static function new_themes( $wp_id ) {
		$sql = "SELECT value FROM user_meta WHERE wp_id = ? AND name = 'visited_theme_page'";
		$last_visit = PSU::db('go')->GetOne( $sql, array( $wp_id ) );

		if(!$last_visit) {
			$last_visit = '2010-01-01';
		}//end if

		$sql = "SELECT count(1) FROM theme WHERE create_date > ? AND level = 'basic'";
		$new_themes = PSU::db('myplymouth')->GetOne( $sql, array( $last_visit ) );

		return $new_themes;
	}//end new_themes

	/**
	 * out
	 *
	 * outputs the user's themes as css 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 */
	function out()
	{
		header('Content-Type: text/css');
		echo $this->text();
	}//end out

	/**
	 * outputs the user's theme js
	 */
	function out_js()
	{
		header('Content-Type: text/javascript');
		echo $this->js();
	}//end out

	/**
	 * setUserTheme
	 *
	 * sets a user's theme
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $wp_id Person identifier
	 * @param     string $theme Theme id(s)
	 */
	function setUserTheme($wp_id,$theme, $set_by = 'user')
	{
		$theme = str_replace(array("'",'"'),'',strip_tags($theme));
		$theme = $this->getThemeData($theme);
		PSU::db('myplymouth')->Execute("REPLACE INTO user_theme (wp_id,theme_id,set_by) VALUES (?, ?, ?)", array( $wp_id, $theme['id'], $set_by ));
	}//end setUserTheme

	/**
	 * text
	 *
	 * compiles a user's themes as a single css string
	 *
	 * @since		version 1.0.0
	 * @access		public
	 */
	function text()
	{
		/****************************************
		 * If HTTPS, alter css urls to use https
		 ****************************************/
		$theme = '';
		if(is_array($this->theme))
		{
			foreach($this->theme as $theme_id => $t)
			{
				$theme .= '/***********[Theme: '.$theme_id.']*************/'."\n\n".$t."\n";
			}//end foreach
		}
		
		if($_SERVER['HTTPS'] == "on")
		{
			$theme = str_replace('http:','https:',$theme);
		}
		
		return $theme;
	}//end text

	/**
	 * compiles a user's themes as a single css string
	 */
	function js()
	{
		/****************************************
		 * If HTTPS, alter css urls to use https
		 ****************************************/
		$js = '';
		if(is_array($this->js))
		{
			foreach($this->js as $theme_id => $t)
			{
				$js .= '/***********[Theme: '.$theme_id.']*************/'."\n\n".$t."\n";
			}//end foreach
		}
		
		return $js;
	}//end text

	/**
	 * update the theme opt-out settings for a user
	 *
	 * @param $wp_id \b person identifier
	 * @param $themes \b all themes are all opted out
	 */
	function updateUserOptout($wp_id, $themes)
	{
		foreach((array) $themes as $theme)
		{
			$sql = "REPLACE INTO user_theme_optout (wp_id, theme_id) VALUES (?, ?)";
			PSU::db('myplymouth')->Execute($sql, array($wp_id, $theme));
		}//end foreach

		$sql = "DELETE FROM user_theme_optout
						 WHERE wp_id = ?";
    if($themes) $sql .= "AND theme_id NOT IN(".implode(',', $themes).")";
		return PSU::db('myplymouth')->Execute($sql, array($wp_id));
	}//end updateUserOutput

	/**
	 * __construct
	 *
	 * PSUTheme constructor
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		adodb &$db Database connection
	 * @param     string $base_dir base directory of themes
	 * @param     string $theme Theme id(s)
	 * @param     string $css Theme CSS file
	 */
	function __construct(&$db='', $base_dir='', $theme ='', $css = 'style.css')
	{
		$this->base_dir = $base_dir;
		$this->db = $db;
		
		if($theme)
		{
			$this->add($theme, $css);
		}//end if
	}//end constructor
}//end class PSUTheme
