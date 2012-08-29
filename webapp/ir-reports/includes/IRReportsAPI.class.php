<?php

class IR {

	public function get_all_file_info($sort){

		$handle = opendir('files')

		// Loop through all files in the files directory and only look at pdf files
		while (false !== ($entry = readdir($handle))) {
			if (preg_match("/[0-9a-zA-Z].pdf/i", $entry)){
				if ($files){
					$temp_array['file_name'] = $entry;
					$temp_array['display_name'] = $entry;
					$files[$entry] = $temp_array;
				}
				else{
					$files[$entry]['file_name'] = $entry;
					$files[$entry]['display_name'] = $entry;
				}
			}
		}
		closedir($handle);

		$display_names = PSU::db('myplymouth')->GetAll('SELECT * from ir_reports where 1');
		foreach ($display_names as $display_name){
			$name = $display_name['file'];
			if ($files[$name]){
				// if the file exists give it its stored display name.
				$files[$name]['display_name'] = $display_name['link_text'];
				$files[$name]['id'] = $display_name['ID'];
			}
			else{
				// If the file doesn't exist remove its 'nickname'
				PSU::db('myplymouth')->Execute('DELETE FROM ir_reports WHERE ID = ?', array($display_name['ID']));
			}
		}
		
		// Sorting files by display name
		if ($files){
			if ($sort == 'display_name')
				usort($files, "IR::cmp_display_name");
			else
				usort($files, "IR::cmp_file_name");
		}
		return $files;
	} // end get_all_file_info()

	// Sorting function by display name
	function cmp_display_name($a, $b){
		if ($a['display_name'] == $b['display_name']) {
			return 0;
		}
		return ($a['display_name'] < $b['display_name']) ? -1 : 1;
	}

	// Sorting function by file name
	function cmp_file_name($a, $b){
		if ($a['file_name'] == $b['file_name']) {
			return 0;
		}
		return ($a['file_name'] < $b['file_name']) ? -1 : 1;
	}
}
