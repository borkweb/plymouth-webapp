<?php 
ob_end_clean();
// version 1.0
// last updated: 1/24/06
// stream a binary file if logged in through portal CAS, requires rewrite rule for each filetype.  Rule currently applies to any subfolder named secure with SWF
// Zach Tirrell

require_once 'autoload.php';
PSU::session_start();

$WEB_ROOT = '/web/pscpages';

if(!file_exists($WEB_ROOT.$_SERVER['REDIRECT_URL'])) {
	header('HTTP/1.1 404 Not Found');
	exit('File not found error ('.$WEB_ROOT.$_SERVER['REDIRECT_URL'].')');
}

$user = IDMObject::authN();

$temp = preg_split('/secure\//i',$_SERVER['REDIRECT_URL']);
$temp[count($temp)-1] = '.htrole';
$htrole = $WEB_ROOT.implode('secure/',$temp);

if(file_exists($htrole)) {
	$roles = file($htrole);

	$pid = PSU::get('idmobject')->getIdentifier($user,'username','pid');
	$user_roles = PSU::get('idmobject')->getAllBannerRoles($pid);
	$ok = false;

	foreach($roles as $role) {
		$ok |= in_array(trim($role),$user_roles);
	}

	if(!$ok) {
		header('HTTP/1.1 403 Forbidden');
		exit('You do not have access to the requested file.');
	}
}

$file = $WEB_ROOT.$_SERVER['REDIRECT_URL'];
$type = 'octet-stream'; // default
$pos = strrpos( $file, '.' );

// pull out the file suffix for Content-type
if( $pos !== false ) {
	$suffix = substr( $file, $pos + 1 );

	// if suffix is just letters/numbers...
	if( ctype_alnum($suffix) ) {
		$type = $suffix;
	}
}

$len = filesize($file);

header('Pragma: ');
header('Cache-Control: ');

header('Content-type: application/'.$type);
header("Content-Length: $len");
//header('Content-Disposition: inline; filename=file1.'.$type);
header("Content-Transfer-Encoding: binary\n");
readfile_chunked($file);

// zbtirrell@psu - problem with large file downloads resolved for 5.0.4, revisit after upgrade to 5.1+
function readfile_chunked($filename,$retbytes=true) {
   $chunksize = 1*(1024*1024); // how many bytes per chunk
   $buffer = '';
   $cnt = 0;
   $handle = fopen($filename, 'rb');

   if ($handle === false) {
       return false;
   }

   while (!feof($handle)) {
       $buffer = fread($handle, $chunksize);
       echo $buffer;
       if ($retbytes) {
           $cnt += strlen($buffer);
       }
   }

   $status = fclose($handle);
   if ($retbytes && $status) {
       return $cnt; // return num. bytes delivered like readfile() does.
   }
   return $status;

}


/* Required Rewrite:
RewriteEngine On
RewriteBase /
RewriteCond   %{REQUEST_URI}  ^/(.*)secure/(.*).swf [NC]
RewriteRule   ^(.*)    /webapp/binary.php  [L,E:TYPE=swf]
*/
