<?php

require_once 'PSUModels/Model.class.php';

/**
 * A form for representing channels.
 */
class ChannelForm extends Model
{
	public function __construct($f = array(), $privileged = false)
		{
			$this->id = new FormText(array('value' => $f['id']));
			$this->name = new FormText(array('maxlength' => 100, 'size' => 50 ));
			$this->slug = new FormText(array('maxlength' => 100, 'size' => 50 ));
			$this->content_text = new FormTextarea(array('rows' => 5, 'cols' => 43));
			$this->description = new FormTextarea(array('rows' => 5, 'cols' => 43));
			$this->content_url = new FormText(array('maxlength' => 150, 'size' => 50 ));
			$this->targets = new FormSelect(array('options'  => MyValues::targets(), 'size' => 10, 'multiple' => true, 'selected' => MyChannel::targets( $f['id'] ), 'hasBlank' => false));
			parent::__construct($f, $privileged);
		}
} 
