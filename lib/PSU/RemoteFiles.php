<?php

namespace PSU;

/**
 * 
 */
class RemoteFiles {
	/**
	 * Update files in ~/.ssh using the contents of $source.
	 */
	public function ssh_config_update( $source ) {
		$home = $this->ssh_home();

		if( ! is_dir( $home ) ) {
			mkdir( $home );
			chmod( $home, 0700 );
		}

		$dh = opendir( $source );
		while( false !== ( $file = readdir( $dh ) ) ) {
			$src_path = $source . '/' . $file;

			if( ! is_file( $src_path ) ) {
				continue;
			}

			$dest_path = $home . '/' . $file;
			copy( $src_path, $dest_path );
			chmod( $dest_path, 0700 );
		}
	}

	/**
	 * Path to the ~/.ssh directory.
	 */
	public function ssh_home() {
		$details = posix_getpwuid( posix_geteuid() );
		return $details['dir'] . '/.ssh';
	}
}//end class RemoteFiles
