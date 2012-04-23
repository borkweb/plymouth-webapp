<?php

class APEAuthZManagement {
	/*
	 * delete_child deletes a child from a parent
	 *
	 * @param $par string the parent attribute slug
	 * @param $child string the child attribute slug that needs deleting
	 */

	public static function delete_child( $par, $child ) {
		$sql = '
			DELETE FROM psu_identity.attribute_group 
			WHERE 
				parent_attribute=:par 
				AND child_attribute=:chld
			';	 

		$binds = array( 'par'=>$par, 'chld'=>$child ) );
	
		PSU::db( 'banner' )->Execute( $sql, $binds );
	}

	/*
	 * get_attribute returns a single attribute along with its info.
	 *
	 * @param $attr string The attribute to retrieve the database for.
	 */

	public static function get_attribute( $attr ) {
		$sql = '
			SELECT 
				type_id,
				description, 
				name, 
				attribute 
			FROM psu_identity.attribute_description 
			WHERE attribute_description.attribute=:attr
			ORDER BY name
		';
		$binds = array( 'attr' => $attr ) );

		return PSU::db('banner')->GetAll( $sql, $binds );

	}

	/*
	 * get_attribute_desc is called for the first column in the display. This function also returns a list of attributes from a search.
	 *
	 * @param $attr string The attribute to search the PSU database for.
	 */
	public static function get_attribute_desc( $attr = null ) {
		if( $attr !== null ) {
			$attr = '%'.$attr.'%';
			$binds = array( 'attr' => $attr );
			$sql = 'SELECT * FROM psu_identity.attribute_description WHERE attribute_description.attribute LIKE ( :attr ) OR attribute_description.name LIKE ( :attr ) ORDER BY name';

			$attriubute_info = PSU::db('banner')->GetAll( $sql, $binds );
		}
		else {
			$sql = 'SELECT * FROM psu_identity.attribute_description ORDER BY name';

			$attriubute_info = PSU::db('banner')->GetAll( $sql );
		}
		return $attribute_info;
	}

	/*
	 * get_attribute_meta returns the meta data for a specific attribute
	 *
	 * @param $attr string The attribute to retrieve the meta data on.
	 */

	public static function get_attribute_meta( $attr ) {
		$sql = '
			SELECT 
				attribute, 
				meta  
			FROM psu_identity.attribute_meta 
			WHERE attribute_meta.attribute = :attr
			ORDER BY meta';
		$binds = array( 'attr' => $attr ) );

		return PSU::db( 'banner' )->getall( $sql, $binds );
	}
	
	/*
	 * get_attribute_meta returns the meta data for a specific attribute
	 *
	 * @param $attr string The attribute to retrieve the meta data on.
	 */

	public static function get_role_children( $attr ) {

		$sql = '
			SELECT
				g.parent_attribute,
				g.child_type_id,
				g.child_attribute,
				g.is_default,
				d.name
			FROM
				psu_identity.attribute_group g,
				psu_identity.attribute_description d
			WHERE
				d.attribute=g.child_attribute
				AND parent_attribute = :attr
			ORDER BY d.name';
		$binds = array( 'attr' => $attr ) );

		return PSU::db( 'banner' )->getall( $sql, $binds );
	}

	/*
	 * is_attribute returns a boolean if the given slug of an attribute exists
	 *
	 * @param $attr string the parent attribute slug
	 * @return boolean
	 */
	public static function is_attribute( $attr ) {
		if(PSU::db(	'banner'	)->getone( 'SELECT 1 FROM psu_identity.attribute_description WHERE attribute=:attr', array( 'attr' => $attr ) ) ) {
			return json_encode(array('is_attribute'=>true));
		}
	}

	/*
	 * possible_adds retrieves all of the possible children of the currently selected attribute. In other words all roles and premissions that are not currently a child or the attribute itself.
	 *
	 * @param $attr string The attribute to retrive the possible children to add.
	 */

	public static function possible_adds( $attr ) {
		$sql = '
			SELECT * 
			FROM psu_identity.attribute_description 
			WHERE attribute 
			NOT IN ( 
				SELECT g.child_attribute
				FROM 
					psu_identity.attribute_group g,
					psu_identity.attribute_description d
				WHERE 
					d.attribute=g.child_attribute
					AND parent_attribute = :attr 
			)
			ORDER BY name';
	
			$binds = array( 'attr' => $attr ) );

			return PSU::db( 'banner' )->getall( $sql, $binds );

	}

	/*
	 * update_attribute updates all the information of the attribute.
	 *
	 * @param $params array containing the necessary information for the update.
	 * @return boolean
	 */

	public static function update_attribute( $params ) {

		$sql['update_condition'] = '
			SELECT meta 
			FROM psu_identity.attribute_meta 
			WHERE attribute = :attr 
			AND type_id = :type_id 
			AND meta = :old_meta
		';
		$sql['update'] = '
			UPDATE psu_identity.attribute_meta 
			SET meta=:meta 
			WHERE type_id = :type_id 
			AND attribute = :attr 
			AND meta = :old_meta
		';
		$sql['insert'] = '
			INSERT INTO psu_identity.attribute_meta 
			VALUES ( 
				:type_id, 
				:attr, 
				:meta 
			)
		';
		$param = array( 'type_id' => $params['type'], 'attr'=>$params['slug'] );
		foreach( $params['meta'] as $meta ) {
			$param[ 'old_meta' ]=substr($meta, 0, strpos($meta, '--') );
			if( PSU::db('test')->getone( $sql[ 'update_condition' ], $param )  ) {
				$param[ 'meta' ]=substr($meta, strpos($meta, '--')+2, strlen($meta));
				PSU::db('test')->execute( $sql[ 'update' ], $param );
			}//end if 
			else {
				unset($param[ 'old_meta' ]);
				$param[ 'meta' ]=substr($meta, strpos($meta, '--')+2, strlen($meta));
				PSU::db('test')->execute( $sql[ 'insert' ], $param );
			}//end else
			unset($param['meta']);
		}//end foreach
	}

	/*
	 * update_child updates the information for a child in the database
	 *
	 * @param $parent string The attribute parent of the child to change.
	 * @param $child string The attribute to update.
	 * @param $default enum 'y' or 'n' Is this a default permission of its parent role?.
	 */

	public static function update_child( $parent, $child, $default ) {
		$sql = '
			UPDATE psu_identity.attribute_group 
			SET is_default=:dflt 
			WHERE parent_attribute=:prnt 
				AND child_attribute=:chld';

		$binds = array( 
			'prnt' => $parent, 
			'chld'=>$child, 
			'dflt'=>$default 
		);

		PSU::db( 'banner' )->Execute( $sql, $binds );
	}

	/*
	 * update_children updates the parents children
	 *
	 * @param $parent string The attribute parent of the child to change.
	 * @param $child string The attribute to update.
	 * @param $child_type integer 1 is 'permission' or 2 is 'role' 6 is 'admin' What type is the child being added?.
	 */
	public static function update_children( $parent, $child, $child_type ) {
		$sql = '
			INSERT INTO psu_identity.attribute_group 
			VALUES (2, :prnt, :child_type, :chld, :def)';

		$binds = array( 
			'prnt' =>$parent, 
			'chld'=>$child, 
			'child_type'=>$child_type,
			'def'=>'n' 
		)

		PSU::db( 'banner' )->Execute( $sql, $binds );
	}
}
