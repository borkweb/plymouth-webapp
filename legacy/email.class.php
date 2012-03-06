<?php

require_once('PSUDatabase.class.php');    //database functions

/**
 * email.class.php
 *
 * Code for PSUEmail
 *
 * @version		2.0
 * @module		email.class.php
 * @copyright 2008, Plymouth State University, ITS
 */ 

class PSUEmail
{
	private $_db;
	
	/**
	 * __construct
	 *
	 * constructor for mail, connects to mail database
	 *
	 * @access	public
	 */
	public function __construct()
	{
		$this->_connect();
	}

	/**
	 * _connect
	 *
	 * connect to the mail database
	 *
	 * @access	private
	 */
	private function _connect()
	{
		$this->_db = PSUDatabase::connect('mysql/email');
	}

	/**
	 * queueMessage
	 *
	 * queue a message into the message table
	 *
	 * @access	public
	 * @return	boolean success fo query true or false
	 */
	public function queueMessage($message)
	{
		$ok = $this->_db->Execute("INSERT INTO message (`to`, `subject`, `from`, `body`, `enter_date`) VALUES ('{$message['to']}', '{$message['subject']}', '{$message['from']}', '{$message['body']}', NOW())");
		return $ok;
	}

	/**
	 * mail
	 *
	 * mail the message
	 *
	 * @access	public
	 * @param	string $email_address
	 * @param	string $subject
	 * @param	string $msg
	 * @param	string $headers
	 * @param	string $attach_filepath
	 * @return	boolean success of send true or false
	 */
	public function mail($email_address,$subject,$msg,$headers,$attach_filepath) 
	{
		$b = 0;
		$mail_attached = "";
		$boundary = md5(uniqid(time(),1))."_xmail";
		if (count($attach_filepath)>0) 
		{
			for ($a=0;$a<count($attach_filepath);$a++)
			{
				if ($fp = fopen($attach_filepath[$a],"rb"))
				{
					$file_name = basename($attach_filepath[$a]);
					$content[$b] = fread($fp,filesize($attach_filepath[$a]));
					$mail_attached .= "--".$boundary."\r\n"
						."Content-Type: image/jpeg; name=\"$file_name\"\r\n"
						. "Content-Transfer-Encoding: base64\r\n"
						. "Content-Disposition: inline; filename=\"$file_name\"\r\n\r\n"
						.chunk_split(base64_encode($content[$b]))."\r\n";
					$b++;
					fclose($fp);
				} 
				else
				{
					echo $a."Error!";
				}
			}
			$mail_attached .= "--".$boundary." \r\n";
			$add_header ="MIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$boundary\"";
			$mail_content = "--".$boundary."\r\n"
					   . "Content-Type: text/plain; charset=iso-8859-1; format=flowed\r\n"
					   . "Content-Transfer-Encoding: 8bit\r\n\r\n"
					   . $msg."\r\n\r\n".$mail_attached;
			return mail($email_address,$subject,$mail_content,$headers."\r\n".$add_header);
		} 
		else
		{
			return mail($email_address,$subject,$msg,"From: ".$email_from);
		}
	}
}

?>