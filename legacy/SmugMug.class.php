<?php

require_once 'autoload.php';

/**
 * SmugMug.class.php
 *
 * SmugMug API
 *
 * @version		1.3
 * @module		SmugMug.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>, Nathan Porter <nrporter@plymouth.edu>
 * @GPL 2007, Plymouth State University, ITS
 */ 
class SmugMug {
	var $m_curId;
	var $m_api_version;
	// This var breaks the trend of m_ but it's for simplicities sake
	var $debug;

	protected $config;
	
	/**
	* __construct
	*
	* constructor sets up api version
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $defaultapi_version defaults to 'https://api.smugmug.com/hack/php/1.2.0/'
	*/
	function __construct( $defaultapi_version = 'https://api.smugmug.com/hack/php/1.2.0/' ) {
		$this->m_api_version = $defaultapi_version;
		$debug = false;

		// code smell! TODO: dependency injection
		$this->config = new PSU\Config;
		$this->config->load();
	}
	
	/**
	* anonLogin
	*
	* anonymous login 
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $apikey the key to log in anonymously
	* @return	boolean	$success
	*/
	function anonLogin( $apikey ) {
		$success = false;
		$response = $this->call( 'smugmug.login.anonymously', array( 'APIKey' => $apikey ) );
		if( $response['stat'] == 'ok' ) {
			$this->m_curId = $response['Login']['Session']['id'];
			$success = true;
		}
		return $success;
	}
	
	/**
	* userLogin
	*
	* login function for user
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $apikey api key to be logging into
	* @param	string $userEmail users email address
	* @param	string $password user password
	* @return	boolean $success
	*/
	function userLogin( $apikey, $userEmail, $password ) {
		$success = false;
		$params = array( 'EmailAddress' => $userEmail, 'Password' => $password,
							'APIKey' => $apikey );
		$response = $this->call( 'smugmug.login.withPassword', $params );
		if( $response['stat'] == 'ok' ) {
			$this->m_curId = $response['Login']['Session']['id'];
			$success = true;
		}
		return $success;
	}

	/**
	* logout
	*
	* log user out
	*
	* @since		version 1.0
	* @acess	public
	* @return	boolean $success
	*/
	function logout() {
		$success = false;
		if( isset( $this->m_curId ) ) {
			$response = $this->call( 'smugmug.logout' );
			if( $response['stat'] == 'ok' ) {
				unset( $this->m_curId );
				$success = true;
			}
		}
		return $success;
	}

	/**
	* isLoggedIn
	*
	* check to see if user is logged in
	*
	* @since		version 1.0
	* @acess	public
	* @return	boolean if the current id is set
	*/
	function isLoggedIn() {
		return isset( $this->m_curId );
	}

	/**
	* echoDebug
	*
	* function for outputting debug information
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $contents information to be written
	* @param	int $level defaults to 2 which will cho to the screen
	*/
	function echoDebug( $contents, $level = 2 ) {
		/*
		* 0 = No Debugging
		* 1 = Debug to log file
		* 2 = Debug to screen
		* 3 = Debug to both
		*/
		$logFile = '/web/temp/picCacheLog.txt';
		switch( $level ) {
			case 0:
				break;
			case 1:
			{
				$file = fopen( $logFile, 'a' );
				fwrite( $file, date( 'd M Y - H:i:s' ) . "\t" . $contents );
				fclose( $file );
				break;
			}
			case 2:
			{
				echo $contents;
				break;
			}
			case 3:
			{
				$file = fopen( $logFile, 'a' );
				fwrite( $file, date( 'd M Y - H:i:s' ) . "\t" . $contents );
				fclose( $file );
				echo $contents;
				break;
			}
			default:
				echo 'Invalid debug level!';
				break;
		}
	}
	
	/**
	* refreshPics
	*
	* returns 6 random images from a random album not in the hidden list
	*
	* @since		version 1.0
	* @acess	public
	* @param	boolean $locked_id defaults to false
	* @return	string
	*/
	function refreshPics($locked_id=false) {
		/**
		* ===============
		* Config Settings
		* ===============
		*/
		global $cache_file;
		global $debug_lvl;

		$username = $this->config->get( 'smugmug', 'email' );
		$password = $this->config->get_encoded( 'smugmug', 'password' );
		$apikey = $this->config->get( 'smugmug', 'api_key' );

		$num_images = 6;
		$cache_time = 3600; // user seconds
		
		// Simply add the title of any gallery you wish not to display
		$hidden_gallery = array( 'site images' );
		/* ========================== */

		// Simply dump out if we have not hit the threshold yet on a random album
		if ( $locked_id === false && (file_exists( $cache_file ) === false || time() - filemtime( $cache_file ) > $cache_time )) {
			if( !$this->userLogin( $apikey, $username, $password ) ) {
				$this->echoDebug( 'Log-in failed', $debug_lvl );
			}
			// We are logged in
			else {
				$response = $this->call( 'smugmug.albums.get' );
				//print_r($response);
				if( $response['stat'] == 'ok' ) {
					// ===========================================
					// Extract a random album ID from the response
					// ===========================================
					$albums = $response['Albums'];
					$num_albums = sizeof( $albums );
					// Find a gallery which is not in our do not show list
					do {
						$i = rand( 0, $num_albums-1 );
						$album_id = $albums[ $i ]['id'];
						$album_title = $albums[ $i ]['Title'];
					}while( in_array( $album_title, $hidden_gallery ) );
				} else
					$this->echoDebug( 'Album retrieval failed.', $debug_lvl );
				// ===========================================
				// Extract a selection of random images from the album
				// ===========================================
				$params = array( 'AlbumID' => $album_id , 'Heavy' => true);
				$response = $this->call( 'smugmug.images.get', $params );
				if( $response['stat'] == 'ok' ) {
					$images = $response['Images'];
					$img_rand = array_rand($images,$num_images);
					//print_r($img_rand);
					$image_urls = array();
					// Should probably ensure we don't have duplicates
					foreach($img_rand as $image_id) {
						$params = array( 'ImageID' => $images[ $image_id ]['id'] );
						//print_r($params);
						$response = $this->call( 'smugmug.images.getURLs', $params );
						if( $response['stat'] == 'ok' ) {
							$image_urls[] = $response['Image']['TinyURL'].'?width='.$images[$image_id]['Width'].'&height='.$images[$image_id]['Height'];
						}
					}
					$data = array( 'AlbumTitle' => $album_title, 'URLs' => $image_urls, 'AlbumID' => $album_id );
					//print_r($data);
					$file_contents = serialize( $data );
					if( !file_put_contents( $cache_file, $file_contents ) )
						$this->echoDebug( 'Pic cache write failed.', $debug_lvl );
				}
				else
					$this->echoDebug( 'Images retrieval failed.', $debug_lvl );
			}
			return "";
		} else if ($locked_id !== false) {
		}
	}
	
	/**
	 * cmpLastUpdated
	 *
	 * function used to compare elements for getImages function
	 *
	 * @access	public
	 * @param	array $a first array element
	 * @param	array $b second array element
	 * @return	integer same conditions as strcmp
	 */
	function cmpLastUpdated($a, $b){
		if($a['LastUpdated']==$b['LastUpdated']){
			return 0;
		}
		return ($a['LastUpdated'] > $b['LastUpdated']) ? +1 : -1;
	}
	
	/**
	* getNewestImage
	*
	* makes a call to the getImage function, returning the one newet album in the collection
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $size defaults to Tiny
	* @return	string page of images
	*/	
	function getNewestImage($size='Tiny')
	{
		return SmugMug::getImage('newest',false,$size,'1');
	}

	/**
	* getImage
	*
	* function that can return a random, or newest set of images based on input
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $type defaults to random
	* @param	int $album_num defaults to false
	* @param	string $image_size defaults to Tiny
	* @param	string $num_images defaults to 1
	* @return	string page of images
	*/	
	function getImage($type='random', $album_num=false, $image_size='Tiny', $num_images='1'){
		/**
		* ===============
		* Config Settings
		* ===============
		*/
		global $debug_lvl;
		
		$username = $this->config->get( 'smugmug', 'email' );
		$password = $this->config->get_encoded( 'smugmug', 'password' );
		$apikey = $this->config->get( 'smugmug', 'api_key' );

		$numimages = $num_images;
		$num_images2 = 1;
		$cache_time = 3600; // user seconds

		// Simply add the title of any gallery you wish not to display
		$hidden_gallery = array( 'site images' );
		/* ========================== */
			if( !$this->userLogin( $apikey, $username, $password ) ) {
				$this->echoDebug( 'Log-in failed', $debug_lvl );
			}
			else { // We are logged in
				if(!$album_num) { // if no album id, select the newest album
					$param = array( 'Heavy' => true);
					$response = $this->call( 'smugmug.albums.get', $param );
					if( $response['stat'] == 'ok' ) {
						$albums=$response['Albums'];
						$updated_albums=array();
						$num_albums = sizeof( $albums );
						/*
						for($k=0; $k<=$num_albums-1; $k++){
							//make sure to only select public albums...addressing call log ticket 141181
							if($albums[$k]['Public']==1){
								$updated_albums[$k]=$albums[$k]['LastUpdated'];
							}
						}
						*/
						foreach($albums as $alb){
							if($alb['Public']==1){
								$updated_albums[]=$alb['LastUpdated'];
							}
						}
						sort($updated_albums);
						
						$newest_album=end($updated_albums);
						/*
						for($d=0; $d<=$num_albums-1; $d++){
							if($albums[$d]['LastUpdated']==end($updated_albums)){
								$album_id=$albums[$d]['id'];
								$album_title=$albums[$d]['Title'];
							}
							else{
							}
						}
						*/
						foreach($albums as $alb){
							if($alb['LastUpdated']==end($updated_albums)){
								$album_id=$alb['id'];
								$album_title=$alb['Title'];
							}
						}
					} 
					else
						$this->echoDebug( 'Album retrieval failed.', $debug_lvl );
				}
				else
				{
					// get the albumn...
					$response = $this->call( 'smugmug.albums.get' );
					if( $response['stat'] == 'ok' ) {
						$albums=$response['Albums'];
						$album_id = $album_num;
						$num_albums = sizeof( $albums );
						for($i=0; $i<=$num_albums-1; $i++){
							if($albums[$i][ 'id' ]==$album_id){
								$album_title = $albums[ $i ]['Title'];
							}
							else{
							}
						}
					} else
						$this->echoDebug( 'Album retrieval failed.', $debug_lvl );
				}
				if($type=='random'){
					// ===========================================
					// Extract a selection of random images from the album
					// ===========================================
					$params = array( 'AlbumID' => $album_id );
					$response = $this->call( 'smugmug.images.get', $params );
					if( $response['stat'] == 'ok' ) {
						$images = $response['Images'];
						$max_num = sizeof( $images );
						if($numimages > $max_num){
							$numimages = $max_num;
						}
						$img_rand = array_rand($images,$numimages);
						//print_r($img_rand);
						$image_urls = array();
						if($num_images != '1'){
							foreach($img_rand as $image_id) {
								$params = array( 'ImageID' => $images[ $image_id ]['id'] );
								$response = $this->call( 'smugmug.images.getURLs', $params );
								if( $response['stat'] == 'ok' ) {
									$image_urls[] = $response['Image'];
								}
							}
							$data = array( 'AlbumTitle' => $album_title, 'URLs' => $image_urls, 'AlbumID' => $album_id );
						}
						else{
							$image_id=$img_rand;
							$params = array( 'ImageID' => $images[ $image_id ]['id'] );
							$response = $this->call( 'smugmug.images.getURLs', $params );
							if( $response['stat'] == 'ok' ) {
								$image_urls = $response['Image'];
							}
							$data = array( 'AlbumTitle' => $album_title, 'URLs' => $image_urls, 'AlbumID' => $album_id );
						}
					}
					else
						$this->echoDebug( 'Images retrieval failed.', $debug_lvl );
				}
				elseif($type=='newest'){
					// ===========================================
					// Extract a selection of newest image from the album
					// ===========================================
					$params = array( 'AlbumID' => $album_id ,'Heavy' => true);
					$response = $this->call( 'smugmug.images.get', $params );
					if( $response['stat'] == 'ok' ) {
						$images = $response['Images'];
						//================================
						usort($images, array($this, "cmpLastUpdated"));
						
						$images=array_reverse($images);
						
						$newest_images=array();
						foreach($images as $img){
							if($img['Hidden']==false){
								$newest_images[]=$img;
							}
						}
						
						if($num_images > sizeof($newest_images)){
							$num_images=sizeof($newest_images);
							$numimages=$num_images;
						}
						$newest=array_reverse($newest_images);
						$newest=array_slice($newest, sizeof($newest_images)-$num_images);
						
						//print_r($newest);
						$image_urls = array();

						foreach($newest as $img){
							$params = array( 'ImageID' => $img['id'] );
							$response = $this->call( 'smugmug.images.getURLs', $params );
							if( $response['stat'] == 'ok' ) {
								if($num_images==1){
									$image_urls = $response['Image'];
								}
								else
									$image_urls[] = $response['Image'];
							}
						}
						//=================================
						/*
						$num_pics=sizeof( $images );
						$last_update=array();
						for($h=0; $h<=$num_pics-1; $h++){
							if($images[$h]['Hidden']==false){
								$last_update[$h]=$images[$h]['LastUpdated'];
							}
							elseif($images[$h]['Hidden']==true){
								$last_update[$h]=0;
							}
						}
						//print_r($images);
						sort($last_update);
						if($num_images != 1){
							$newest = array();
							if($numimages > sizeof($last_update)){
								$numimages = sizeof($last_update);
							}
							//print_r($last_update);
							$b = sizeof($last_update)-1;
							for($x =0; $x <= $numimages-1; $x++){
								$newest[$x] = $last_update[$b];
								$b--;
							}
							//print_r($newest);
							$newest_id = array();
							for($q=0; $q<=sizeof($newest)-1; $q++){
								for($p=0; $p<=$num_pics-1; $p++){
									if($newest[$q]==$images[$p]['LastUpdated']){
										$newest_id[$q] = $p;
									}
								}
							}
							//print_r($newest_id);
							$image_urls = array();
							foreach($newest_id as $image_id){
								$params = array( 'ImageID' => $images[ $image_id ]['id'] );
								$response = $this->call( 'smugmug.images.getURLs', $params );
								if( $response['stat'] == 'ok' ) {
									$image_urls[] = $response['Image'];
								}							
							}
						}
						else{
							$newest=$last_update[$num_pics-1];
							for($n=0; $n<=$num_pics-1; $n++){
								if($images[$n]['LastUpdated']==$last_update[$num_pics-1]){
									$image_id=$n;
								}
								else{
								}
							}
							$image_urls = array();
							$params = array( 'ImageID' => $images[ $image_id ]['id'] );
							$response = $this->call( 'smugmug.images.getURLs', $params );
							if( $response['stat'] == 'ok' ) {
								$image_urls = $response['Image'];
							}
						}
						*/
						$data = array( 'AlbumTitle' => $album_title, 'URLs' => $image_urls, 'AlbumID' => $album_id );
					}
					else
						$this->echoDebug( 'Images retrieval failed.', $debug_lvl );
				}
				if($num_images!='1'/* && $type!='newest'*/){
					$html = "";
					for($i = 0; $i<=$numimages-1; $i++){
						$html.=	'<a href="'.$data['URLs'][$i]['LargeURL'].'" class ="thickbox"><img src="'.$data['URLs'][$i][$image_size.'URL'].'" /></a>';
					}
					return $html;
				}
				else{
					return '<a href="'.$data['URLs']['LargeURL'].'" class ="thickbox"><img src="'.$data['URLs'][$image_size.'URL'].'" /></a>';
				}
			}
	}
	
	/**
	* call
	*
	* makes a call to SmugMug
	*
	* @since		version 1.0
	* @acess	public
	* @param	string $method method to be stored in params
	* @param	array $params array to hold params for call
	* @param	string $api_version version of the api that we are calling to
	* @return	string contents of file
	*/	
	function call( $method, $params = array() , $api_version = '' ) {
		if( empty( $api_version ) )
			$api_version = $this->m_api_version;
		$params['method'] = $method;
		
		$filename = '/web/temp/smugmug/'.md5($method.serialize($params).$api_version);

		if(!file_exists($filename) || time() - filemtime( $filename ) > 3600) {
			// If we are logged in, append our session id
			if( isset( $this->m_curId ) )
				$params['SessionID'] = $this->m_curId;
			// Prepare our params for transfer
			foreach ($params as $key => $value)
				$query_string .= "$key=" . urlencode($value) . '&';
			// Remove the last &
			if( $query_string{strlen($query_string)} == '&' )
				$query_string = substr_replace( $query_string, "", -1 );
			$url = "$api_version?$query_string";
			$response = unserialize( @file_get_contents( $url ) );

			if( $this->debug === true ) {
				echo '<pre>';
				echo "\n==================================================\n";
				echo 'SmugMug Remote Method Call: ' . $method;
				echo "\n==================================================\n";
				echo 'Full URL: ' . $url . "\n\n";
				echo 'Query String: ' . $query_string . "\n";
				echo "\nResponse:\n";
				print_r( $response );
				echo "\n==================================================\n";
				echo 'END Debug for method call: ' . $method;
				echo "\n==================================================\n";
				echo '</pre>';
			}
			
			file_put_contents($filename,serialize($response));
		}
		else{
			$response = unserialize(file_get_contents($filename));
		}

		return $response;
	}
}
