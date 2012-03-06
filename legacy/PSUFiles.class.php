<?php

/**
 * PSUFiles.class.php
 *
 * === Modification History ===
 * 0.8   13-jul-2005  [zbt]  original
 * 1.0   23-jan-2008  [zbt]  class based
 *
 * @package 		Tools
 */

/**
 * PSUFiles.class.php
 *
 * PSU Files is a series of functions for additional simplification of file handling
 *
 * @version		1.0
 * @module		PSUFiles.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @copyright 2008, Plymouth State University, ITS
 */ 

class PSUFiles
{
/**
  *getFileArray
  *
  *puts all files in a directory into an array
  *
  *@access private
  *@param string $dir
  *@param string $extension
  *@return arrau
  */
	// getImageArray and this function should be combined...
	// currently this function is only being called by /luminis/pastpresent.php
	function getFileArray($dir, $extension)
	{
		$files = array ();
		$handle = opendir($dir); 
		while ($file = readdir($handle))
		{
			if ($file!='.' && $file!='..')
			{
				$file_name = $dir.'/'.$file;

				if(!is_dir($file_name) && substr($file,0,1)!='.')
				{
					if($extension==-1)
					{
						$files[] = $file_name;
					}//end if
					else
					{
						$ext = PSUFiles::getExtension($file_name);
						
						foreach($extension AS $valid_ext)
						{
							if($ext==$valid_ext)
							{
								$files[] = $file_name;
							}//end if
						}//end foreach
					}//end else
				}//end if
			}//end if
		}//end while

		closedir($handle); 

		return $files;
	}//end getFileArray
/**
  *getImageArray
  *
  *collects all of the images from the passed in directory and puts it into an array
  *
  *@access public
  *@param string $dir
  *@param integer $depth
  *@param integer $max_depth
  *@param string array $extension
  *@return string array
  */
	// The following are functions specific to this file
	// This function recursively crawls a given directory to find images in 'rotate' directories
	function getImageArray($dir, $depth=0, $max_depth=-1, $extension=array('jpg','gif','png'))
	{
		if($max_depth>=0 && $depth>$max_depth || $depth==10)
		{
			return array();
		}//end if

		$images = array ();
		$handle = @opendir($dir); 
		while ($file = @readdir($handle))
		{
			if ($file!='.' && $file!='..')
			{
				$file_name = $dir.$file;

				if(is_dir($file_name))
				{
					if($file!='_vti_cnf' && $file!='.AppleDouble')
					{
						$new_depth = $depth+1;
						$additional_images = PSUFiles::getImageArray($file_name.'/', $new_depth, $max_depth);
						$images = array_merge($images, $additional_images);
					}//end if
				}//end if
				elseif(substr($file,0,1)!='.')
				{
					$path_info = pathinfo( $dir.'/'.$file_name );
					if( in_array( $path_info['extension'], $extension ) ) {
						$images[] = $file_name;
					}//end if
				}//end elseif
			}//end if
		}//end while

		@closedir($handle); 

		return $images;
	}//end getImageArray

/**
  *getImageCaptions
  *
  *sets the caption of a picture to the passed in string the default is a global
  *
  *@access public
  *@param string $name
  *@return string
  */
	// This function gets the captions for the image
	function getImageCaptions($name)
	{
		// this function needs to be restored from backup pre 7/7/06
		if($name)
		{
			$alt_caption = $name;
		}//end if
		else
		{
			$alt_caption = $GLOBALS['ALT_TAG'];
		}//end else

		return '';
	}//end getImageCaptions
/**
  *getExtension
  *
  *takes of the extension of the passed in file
  *
  *@access public
  *@param string $file
  *@return string
  */

	// This function gets the extention from a file
	function getExtension($file)
	{
		if(is_file($file))
		{
			$fileInfo = pathinfo($file);
			$extension=$fileInfo['extension'];
		}//end if
		else 
		{
			$extension='';
		}//end else
		return strtolower($extension);
	}//end getExtension
/**
  *chooseRandomElement
  *
  *returns a randomelement from the passed in array
  *
  *@access public
  *@param string array $array
  *@return string
  */

	// This function selects a random element in an array
	// chooseRandomElement will not choose the same one twice on the same page now.
	function chooseRandomElement($array)
	{
		$rand_element = $array[rand(0,count($array)-1)];
		if(is_array($GLOBALS['CHOSEN_ITEMS']) && in_array($rand_element,$GLOBALS['CHOSEN_ITEMS']) && count($GLOBALS['CHOSEN_ITEMS'])<count($array))
		{
			return PSUFiles::chooseRandomElement($array);
		}//end if
		else
		{
			$GLOBALS['CHOSEN_ITEMS'][] = $rand_element;
			return $rand_element;
		}//end else
	}//end chooseRandomElement

/**
  *getFilename
  *
  *returns the file name of the passed in string array
  *
  *@access public
  *@param array $file_with_path
  *@return string
  */
	// this function gets the filename off the end of the path
	function getFilename($file_with_path)
	{
		$sections = explode('/', $file_with_path);
		$filename = $sections[(count($sections)-1)];
		return $filename;
	}//end getFilename
}
?>
