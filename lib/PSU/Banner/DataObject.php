<?php

abstract class PSU_Banner_DataObject extends PSU_DataObject implements \PSU\ActiveRecord {
	abstract protected function _prep_args();

	/**
	 * Stopgap so all the existing PSU_Banner_DataObject children
	 * don't break when we implement \PSU\ActiveRecord.
	 */
	public static function get( $key ) {
		throw new \Exception( 'Must be redefined in your child class!' );
	}//end get

	/**
	 * Stopgap so all the existing PSU_Banner_DataObject children
	 * don't break when we implement \PSU\ActiveRecord.
	 */
	public static function row( $key ) {
		throw new \Exception( 'Must be redefined in your child class!' );
	}//end get

	/**
	 * validates fields for the given table.  This validation is basic
	 * NULL & type checking.
	 *
	 * @param array $args The data to validate. Defaults to $this.
	 */
	public function validate( $table_name, $args = null ) {
		$table = new PSU_Oracle_Table($table_name);

		if( ! isset($args) ) {
			$args = $this;
		}

		$table->validate( $args, $table_name.'_' );
	}//end validate

	/**
	 * Construct the insert SQL
	 *
	 * @param $table \b Table the insert is being done against
	 * @param $fields \b Associative array of fields (return value of self::_prep_fields)
	 */
	protected function _insert_sql( $table, $fields, $table_prepend = true ) {
		$sql_parts = $this->_prep_sql_parts( $table, $fields, $table_prepend );

		$sql = "
			INSERT INTO {$table} target (
				{$sql_parts['in']}
			) (
				SELECT {$sql_parts['from']} FROM dual
			)
		";

		return $sql;
	}//end _insert_sql

	/**
	 * Construct the merge SQL
	 *
	 * @param $table \b Table the merge is being done against
	 * @param $fields \b Associative array of fields (return value of self::_prep_fields)
	 * @param $on \b The join logic for the MERGE ON statement
	 */
	protected function _merge_sql( $table, $fields, $on, $table_prepend = true ) {
		if( !$on ) {
			throw new InvalidArgumentException('You MUST provide join logic when doing a MERGE');
		}//end if

		$sql_parts = $this->_prep_sql_parts( $table, $fields, $on, $table_prepend );

		$sql = "
			MERGE INTO {$table} target USING (SELECT {$sql_parts['from']} FROM dual) source
			ON ({$sql_parts['on']})
			WHEN MATCHED THEN UPDATE SET {$sql_parts['update']}
			WHEN NOT MATCHED THEN INSERT ( {$sql_parts['in']}) VALUES ( {$sql_parts['insert']} )
		";

		return $sql;
	}//end _merge_sql

	/**
	 * preps the various combinations of target/source fields for
	 * MERGE and INSERT statements
	 *
	 * @param $table
	 * @param $args \b arguments being used in update/inserts
	 * @param $activity_date
	 * @param $table_prepend
	 *
	 * @return array \b The keys are:
	 *                     in - target table for insert
	 *                     from - source for insert (basically the select of bind variables from dual)
	 *                     insert - the insert fields in MERGE
	 *                     update - the target/source assignments for a SET in an UPDATE MERGE
	 */
	protected function _prep_fields( $table, $args, $activity_date = true, $table_prepend = true, $retain = array() ) {
		$fields = array();

		if( $table_prepend ) {
			$table_prepend = $table.'_';
		} else {
			$table_prepend = '';
		}//end else

		foreach( $args as $field => $value ) {
			$fixed_field = $this->_translate_field( $field, $retain );

			$fields['from'][$field] = ":{$field} {$field}";
			$fields['in'][$field] = "target.{$table_prepend}{$fixed_field}";
			$fields['update'][$field] = "target.{$table_prepend}{$fixed_field} = source.{$field}";
			$fields['insert'][$field] = "source.".$field;
		}//end foreach

		if( $activity_date ) {
			// force activity dates across the board
			$fields['from']['activity_date'] = "sysdate activity_date";
			$fields['in']['activity_date'] = "target.{$table_prepend}activity_date";
			$fields['update']['activity_date'] = "target.{$table_prepend}activity_date = source.activity_date";
			$fields['insert']['activity_date'] = "source.activity_date";
		}//end else

		return $fields;
	}//end _prep_fields

	protected function _prep_sql_parts( $table, $fields, $on = null, $table_prepend = true ) {
		if( !$fields['in'] || !$fields['from'] || !$fields['update'] || !$fields['insert'] ) {
			throw new InvalidArgumentException('The fields parameter must have appropriate arguments split into field SQL via self::_prep_fields');
		}//end if

		$sql = array();

		if( $table_prepend ) {
			$table_prepend = $table.'_';
		} else {
			$table_prepend = '';
		}//end else

		if( $on ) {
			$sql['on'] = "";
			foreach( (array) $on as $field ) {
				if( $sql['on'] ) {
					$sql['on'] .= " AND ";
				}//end if

				$fixed_field = $this->_translate_field( $field );

				$sql['on'] .= "target.{$table_prepend}{$fixed_field} = source.{$field} ";

				// we can't have any of the fields used in the ON statement in the update fields
				unset( $fields['update'][$field] );
			}//end foreach
		}//end if

		$sql['from'] = implode( $fields['from'], ', ');
		$sql['in'] = implode( $fields['in'], ', ');
		$sql['update'] = implode( $fields['update'], ', ');
		$sql['insert'] = implode( $fields['insert'], ', ');

		return $sql;
	}//end _prep_sql_parts

	protected function _translate_field( $field, $retain = array() ) {
		if( ! in_array( $field, $retain ) ) {
			if( $field == 'the_user' ) {
				return 'user';
			} elseif( $field == 'description' ) {
				return 'desc';
			} elseif( $field == 'the_id' ) {
				return 'id';
			}//end else
		}//end if

		return $field;
	}//end _translate_field
}//end PSU_Banner_DataObject
