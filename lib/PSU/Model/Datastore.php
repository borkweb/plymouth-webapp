<?php

namespace PSU\Model;

abstract class Datastore {
	/**
	 * Given a model, a form, and an id, save the model to the database.
	 */
	abstract public function save( \PSU\Model $model, $form = null, $id = null );

	/**
	 * Return the form representing a model, identified by ID.
	 */
	abstract public function load( \PSU\Model $model, $id = null );
}
