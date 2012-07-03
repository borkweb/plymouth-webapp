<?php

class CDNAPI {
	public static function fspath( $root, $path = null ) {
		if( !isset($path) ) {
			$path = $root;
			$root = '/web/pscpages';
		}

		$path = $root . $path;
		$path = escapeshellarg($path);

		$extensions = array('jpg', 'gif', 'js', 'css', 'png');

		$args = array();
		foreach( $extensions as $extension ) {
			$args[] = "-name '*.{$extension}'";
		}
		$args = implode(" -o ", $args);

		$rootlen = strlen($root) + 1;
		exec("/usr/bin/find $path -type f -maxdepth 3 \! -path '*/.svn/*' \( {$args} \) | cut -b $rootlen-", $files);

		return $files;
	}

	public static function dbpath( $path ) {
		$path .= '%';

		$result = PSU::db('myplymouth')->Execute("SELECT * FROM http_resources WHERE path LIKE ?", array($path));

		$files = array();
		foreach( $result as $row ) {
			$f = new stdClass;
			$f->id = $row['id'];
			$f->version = $row['version'];

			$files[ $row['path'] ] = $f;
		}

		return $files;
	}

	/**
	 * Return all files under a given path.
	 */
	public static function files( $path ) {
		$fsfiles = self::fspath( $path );
		$dbfiles = self::dbpath( $path );

		// tag dbfiles
		foreach($dbfiles as $path => $file) {
			$dbfiles[$path]->tags = array('cdn-db');
		}

		// merge fsfiles into dbfiles and tag
		foreach( $fsfiles as $file ) {
			if( isset($dbfiles[$file]) ) {
				$dbfiles[$file]->tags[] = 'cdn-fs';
				continue;
			}

			$f = new stdClass;
			$dbfiles[$file] = $f;
			$dbfiles[$file]->tags = array('cdn-fs');
			unset($f);
		}

		// act on everything
		foreach( $dbfiles as $path => &$file ) {
			$file->formkey = $file->id ? $file->id : $path;
		}

		ksort( $dbfiles );

		return $dbfiles;
	}//end files

	/**
	 *
	 */
	public static function update( $files, $wpid = null ) {
		// LAST_INSERT_ID(id) is magic to make last insert id available whether we inserted OR updated. http://dev.mysql.com/doc/refman/5.0/en/insert-on-duplicate.html
		static $sql_path = "INSERT INTO http_resources (path, version) VALUES (?, 1) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), version = version + 1";
		static $sql_id = "UPDATE http_resources SET version = version + 1 WHERE id = ?";
		static $sql_revs = "INSERT INTO http_resources_revs (res_id, wpid, version) VALUES (?, ?, ?)";
		static $sql_version = "SELECT path, version FROM http_resources WHERE id = ?";

		foreach( (array)$files as $file ) {
			if( is_numeric($file) || is_int($file) ) {
				// updating
				PSU::db('myplymouth')->Execute( $sql_id, array($file) );
				$id = (int)$file;
			} else {
				// inserting, with a possible update
				PSU::db('myplymouth')->Execute( $sql_path, array($file) );
				$id = PSU::db('myplymouth')->Insert_ID();
			}

			// pull most recent version number
			$resource = PSU::db('myplymouth')->GetRow( $sql_version, array($id));

			PSU::cdn_expire( $resource['path'] );

			PSU::db('myplymouth')->Execute( $sql_revs, array($id, $wpid, $resource['version']) );
		}
	}//end update
}//end CDNAPI
