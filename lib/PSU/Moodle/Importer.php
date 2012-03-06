<?php

/**
 *
 */
class PSU_Moodle_Importer {

	public $population;
	public $course;
	public $args;
	public $import_xml;
	public $importer_url;

	public function courses_xml() {

		if( is_object($this->population) || is_array($this->population) ) {
			die('Only one user can go into multiple courses...');
		}//end if
		
		$date_str = date("Y-m-d\TH:i:s");
		$import_xml = "<enterprise>
						<properties>
							<datasource>Plymouth State University SCT Banner</datasource>
							<datetime>".$date_str."</datetime>
						</properties>\n";

		$membership = "
			<membership>
				<sourcedid>
					<source>Plymouth State University SCT Banner</source>
					<id>%s</id>
				</sourcedid>
				<member>
					<sourcedid>
						<source>Plymouth State University SCT Banner</source>;
						<id>".$this->population."</id>
					</sourcedid>
					<idtype>1</idtype>
					<role roletype = \"03\">
						<status>1</status>
					</role>
				</member>
			</membership>\n";

		foreach( $this->course as $course_id_number ) {
			$import_xml .= sprintf( $membership, $course_id_number );
		}//end foreach

		$import_xml .= "</enterprise>\n</enterprise>\n";

		$this->import_xml = $import_xml;
		return $import_xml;

	}//end courses_xml

	public function unenrollXML() {

		$date_str = date("Y-m-d\TH:i:s");
		$import_xml = "<enterprise>
						<properties>
							<datasource>Plymouth State University SCT Banner</datasource>
							<datetime>".$date_str."</datetime>
						<properties>\n";

		$pre_id = "<membership>
						<sourcedid>
							<source>Plymouth State University SCT Banner</source>
							<id>".$this->course."</id>
						</sourcedid>
						<member>
							<sourcedid>
								<source>Plymouth State University SCT Banner</source>\n";

		$post_id = "		</sourcedid>
							<idtype>1</idtype>
							<role recstatus = \"3\" roletype = \"01\">
								<status>0</status>
							</role>
						</member>
						</membership>\n";

		$current_enrol_query = "SELECT distinct personsourcedid
								FROM mdl_lmb_enrolments 
								WHERE coursesourcedid = ? 
								AND role=?";

		$currently_enrolled = PSU::db('moodle')->GetCol( $current_enrol_query, array($this->course, 1) );
		$to_be_enrolled = array();

		//If no one is currently enrolled, then don't worry about deletes
		if( !$currently_enrolled ){
			return '';
		}//end if

		$this->population->query( $this->args );

		foreach( $this->population as $id ) {
			$to_be_enrolled[] = $id->scalar;
		}//end foreach

		$to_unenroll = array_diff( $currently_enrolled, $to_be_enrolled );

		foreach( $to_unenroll as $id ) {
			$import_xml .= $pre_id."<id>".$id."</id>\n".$post_id;
		}//end foreach

		$import_xml .= "</enterprise>\n</enterprise>\n";

		$this->import_xml = $import_xml;
		return $import_xml;
	}

	public function prepare_file() {

		if( is_array($this->course) ) {
			$this->courses_xml();
			$this->course = 'Multiple';
		}//end if
		
		if( !isset( $this->import_xml ) ) {
			$this->toXML();
		}

		$in_place = file_put_contents( '/web/temp/moodle/auto/'.$this->course.'_moodle_imp.xml', $this->import_xml );
		echo "All is in place, and ready to go...\n";

	}//end prepare_file

	public function process_auto() {
		$url = PSU::isdev()?'http://uranus.dev.plymouth.edu':'http://capricorn.plymouth.edu';

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url."/webapp/courses/enrol/lmb/psu_importnow.php" );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array("Host: www.".(PSU::isdev()?"dev.":"")."plymouth.edu\r\n") );
		$head = curl_exec( $ch );
		curl_close( $ch );
		return $head;

	}//end process_auto

	public function toXML() {

		$date_str = date("Y-m-d\TH:i:s");
		$import_xml = "<enterprise>
						<properties>
							<datasource>Plymouth State University SCT Banner</datasource>
							<datetime>".$date_str."</datetime>
						<properties>\n";

		$pre_id = "<membership>
						<sourcedid>
							<source>Plymouth State University SCT Banner</source>
							<id>".$this->course."</id>
						</sourcedid>
						<member>
							<sourcedid>
								<source>Plymouth State University SCT Banner</source>\n";

		$post_id = "		</sourcedid>
							<idtype>1</idtype>
							<role roletype = \"01\">
								<status>1</status>
							</role>
						</member>
					</membership>\n";

		$this->population->query( $this->args );
		foreach( $this->population as $id ) {
			$import_xml .= $pre_id."<id>".$id->scalar."</id>\n".$post_id;
		}//end foreach

		$import_xml .= "</enterprise>\n</enterprise>\n";

		$this->import_xml = $import_xml;
		return $import_xml;

	}//end toXML

	public function __construct( $course, $population, $args = '' ) {
		$this->course = $course;
		$this->population = $population;
		$this->args = $args;
	}//end __construct

}//end PSU_Moodle_Importer
