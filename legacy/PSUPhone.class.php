<?php

class PSUPhone extends BannerObject
{
	public $data = array();
	static $tele;
	static $spraddr_columns = null;

	/**
	 * Return a new PSUPhone, fetched by rowid.
	 * @param $rowid \b string the row id
	 */
	public static function get_by_rowid( $rowid ) {
		$sql = "BEGIN :c_cursor := gb_telephone.f_query_by_rowid(:rowid); END;";

		$cursor = PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', compact('rowid'));
		foreach($cursor as $row) {
			unset($row['rowid']);
			return new self($row);
		}
	}//end get_by_rowid
	
	/**
	 * Parse incoming SPRTELE data and populate $this.
	 * @param $data \b array the data array, with or without sprtele_ prefix on the fields
	 */
	public function parse($data)
	{
		foreach($data as $key => $value)
		{
			$key = str_replace('sprtele_', '', strtolower($key));
			
			$this->$key = $value;
		}//end foreach

		if( strlen($this->phone_number) == 7 ) {
			$this->phone_number_formatted = substr($this->phone_number, 0, 3) . '-' .
				substr($this->phone_number, 3);
		}

		$this->tele_desc = $this->description = self::$tele[$this->tele_code];
	}//end parse
	
	/**
	 * Test if a phone exists in SPRTELE.
	 * @param $pidm \b int
	 * @param $tele_code \b string
	 * @param $seqno \b int
	 * @return boolean
	 */
	public static function phoneExists($pidm, $tele_code, $seqno = null)
	{
		$sql = "DECLARE v_exists VARCHAR2(1); BEGIN :v_exists := gb_telephone.f_exists(:p_pidm, :p_tele_code, :p_seqno); END;";
		PSU::db('banner')->debug = true;

		$stmt = PSU::db('banner')->PrepareSP($sql);
		PSU::db('banner')->OutParameter($stmt, $exists, 'v_exists');
		PSU::db('banner')->InParameter($stmt, $pidm, 'p_pidm');
		PSU::db('banner')->InParameter($stmt, $tele_code, 'p_tele_code');
		PSU::db('banner')->InParameter($stmt, $seqno, 'p_seqno');
		PSU::db('banner')->Execute($stmt);

		return $exists == 'Y';
	}//end phoneExists

	/**
	 * Save phone data to a new record.
	 * @return \b PSUPhone the new phone record
	 */
	public function save() {
		if( !isset(self::$tele[$this->tele_code]) ) {
			throw new PSUPhoneException( PSUPhoneException::UNKNOWN_TELE_CODE );
		}

		// if an existing (active) record exists for this user/type, we must deactivate it
		if( self::phoneExists($this->pidm, $this->tele_code) ) {
			if( $this->seqno === null ) {
				$args = array('pid' => $this->pidm, 'type' => $this->tele_code);
				$this->seqno = PSU::db('banner')->GetOne("SELECT MAX(sprtele_seqno) as seqno FROM sprtele WHERE sprtele_pidm = :pid AND sprtele_tele_code = :type", $args);
			}//end if

			$query = "DECLARE v_row gb_common.internal_record_id_type; BEGIN gb_telephone.p_lock(p_pidm => :p_pidm, p_tele_code => :p_tele_code, p_seqno => :p_seqno, p_rowid_inout => :v_row); END;";
			$stmt = PSU::db('banner')->PrepareSP($query);
			PSU::db('banner')->OutParameter($stmt, $row_id, 'v_row');
			PSU::db('banner')->InParameter($stmt, $this->pidm, 'p_pidm');
			PSU::db('banner')->InParameter($stmt, $this->tele_code, 'p_tele_code');
			PSU::db('banner')->InParameter($stmt, $this->seqno, 'p_seqno');
			PSU::db('banner')->Execute($stmt);

			// BANINST1 -> Packages -> GB_TELEPHONE
			$query = "BEGIN gb_telephone.p_update( p_pidm => :p_pidm, p_tele_code => :p_tele_code, p_seqno => :p_seqno, p_status_ind => 'I', p_rowid => :p_rowid); END;";
			$stmt = PSU::db('banner')->PrepareSP($query);
			PSU::db('banner')->InParameter($stmt, $this->pidm, 'p_pidm');
			PSU::db('banner')->InParameter($stmt, $this->tele_code, 'p_tele_code');
			PSU::db('banner')->InParameter($stmt, $this->seqno, 'p_seqno');
			PSU::db('banner')->InParameter($stmt, $row_id, 'p_rowid');
			PSU::db('banner')->Execute($stmt);
		}//end if

		//
		// first build the sql statement, with bind variable placeholders for
		// any table columns (cached in sprtele_columns) that have a value in $this
		//

		$sql = "
			DECLARE
				insert_seqno sprtele.sprtele_seqno%TYPE;
				insert_rowid gb_common.internal_record_id_type;
			BEGIN gb_telephone.p_create(
				p_seqno_out => :p_seqno_out,
				p_rowid_out => :p_rowid_out,
				";

		$args = array();

		$sql_tmp = array();
		foreach( $this->sprtele_columns as $col ) {
			if( $col == 'seqno' ) {
				continue;
			}

			if( isset($this->$col) ) {
				$args[] = $col; // cache
				$sql_tmp[] = "p_$col => :p_$col";
			}
		}
		$sql .= implode(",\n\t\t\t\t", $sql_tmp) . ");\n\t\t\tEND;";
		unset($sql_tmp);

		//
		// bind "out" params manually, then bind everything that's marked as an "in" parameter
		//

		$stmt = PSU::db('banner')->PrepareSP($sql);
		PSU::db('banner')->OutParameter($stmt, $insert_seqno, 'p_seqno_out');
		PSU::db('banner')->OutParameter($stmt, $insert_rowid, 'p_rowid_out');

		foreach($args as $col) {
			PSU::db('banner')->InParameter($stmt, $this->$col, "p_" . $col );
		}

		$result = PSU::db('banner')->Execute($stmt);

		return self::get_by_rowid($insert_rowid);
	}//end save
	/**
	 * PSUPhone constructor
	 */
	public function __construct($data)
	{
		parent::__construct();

		if( empty(self::$tele) )
		{
			$types = PSU::db('banner')->CacheGetAll("SELECT stvtele_code, stvtele_desc FROM stvtele");
			foreach($types as $type)
			{
				self::$tele[$type['stvtele_code']] = $type['stvtele_desc'];
			}
		}

		if( $this->sprtele_columns === null )
		{
			$rset = PSU::db('banner')->CacheExecute("SELECT * FROM sprtele WHERE 1 = 2");

			$this->sprtele_columns = array();

			$i = 0;
			while( $field = $rset->FetchField($i++) )
			{
				$this->sprtele_columns[] = substr( $field->name, 8 );
			}
		}
		
		$this->parse($data);
	}//end __construct
}//end class PSUPhone

/**
 * Exception class for PSUPhone.
 */
class PSUPhoneException extends PSUException {
	const UNKNOWN_TELE_CODE = 1;

	private static $_msgs = array(
		self::UNKNOWN_TELE_CODE => 'Unknown telephone type code provided'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null) {
		parent::__construct($code, $append, self::$_msgs);
	}
}
