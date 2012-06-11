<?php

require_once 'PSUModels/Model.class.php';

/**
 * A form for representing tabs in the admin interface.
 */
class TabForm extends Model
{
	public function __construct($f = array(), $privileged = false)
		{
			$this->id = new FormText(array('value' => $f['id']));
			$this->name = new FormText(array('maxlength' => 30, 'size' => 50 ));
			$this->slug = new FormText(array('maxlength' => 30, 'size' => 50 ));
			$this->lock_state = new FormCheckbox(array(
				'options' => array(
					array(1,'Move Lock'), 
					array(2, 'Remove Lock'),
					array(4, 'Name Lock'), 
				),
				'selected' => MyValues::parse_my_bits($f['lock_state']),
				'hasBlank' => false,
			));
			$this->targets = new FormSelect(array('options'  => MyValues::targets(), 'size' => 10, 'multiple' => true, 'selected' => MyTab::targets( $f['id'] ), 'hasBlank' => false));
			unset($f['lock_state']);
			parent::__construct($f, $privileged);
		}
} 
