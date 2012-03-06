<?php

namespace PSU;

class TeacherCert {

	/**
	 * generates error messages for incomplete required fields in the given model. This method should be
	 * called after "$model->complete()" has been executed.
	 *
	 * @param $model \b Model that holds form elements
	 * @return array
	 */
	protected static function collect_incomplete_field_messages( \PSU\Model $model ) {
		$messages = array();

		foreach( $model->incomplete_fields() as $field ) {
			$field = trim( $field->label, ':' );
			$messages['errors'][] = "The {$field} field is required.";
		}//end foreach

		return $messages;
	}//end collect_incomplete_field_messages

	/**
	 * attempts to save the given model, catches errors
	 *
	 * @param $identifier \b friendly identifier for error messages
	 * @param $data \b form data array
	 * @param $model \b Model that holds form elements to be verified
	 * @param $object \b TeacherCert object OR string with the full TeacherCert object class name
	 * @return stdClass
	 */
	public static function save_model( $identifier, $data, \PSU\Model $model, $object ) {
		$results = new \stdClass;
		$results->success = FALSE;
		$results->messages = array();

		try{
			$model->form( $data );

			if( ! $model->complete() ) {
				throw new \PSU\Model\IncompleteException;
			}//end if

			if( is_object( $object ) ) {
				$post = $model->form();

				foreach( $post as $field => $value ) {
					$object->$field = $value;
				}//end foreach
			} else {
				$object = new $object( $model->form() );
			}//end else

			if( ! $object->save() ) {
				throw new \Exception("The {$object->name} {$identifier}  failed to update.");
			}//end else

			$results->success = TRUE;
			$results->messages['successes'][] = "The {$object->name} {$identifier} has been updated successfully!";
		} catch( \PSU\Model\ValidationException $e ) {
			$results->messages['errors'][] = $e->getMessage();
		} catch( \PSU\Model\IncompleteException $e ) {
			// TODO: toss out the __invoke portion when we update PHP
			$results->messages = array_merge( (array) $results->messages, (array) self::collect_incomplete_field_messages( $model ) );
		} catch( \Exception $e ) {
			$results->messages['errors'][] = $e->getMessage();
		}//end catch

		return $results;
	}//end save_model
}//end PSU\TeacherCert
