<?php defined('SYSPATH') or die('No direct script access.');

class Jelly_Model extends Jelly_Model_Core {

	// Fetch form or subform, respectively
	public function __get($variable)
	{
		if ($variable == 'form')
			return $this->generate_form();
			
		if ($variable == 'subform')
			return $this->get('subform');
		
		// Run Jelly's __get()
		return parent::__get($variable);
	}
	
	// Match values together for validation purposes
	public function __set($variable, $value)
	{
		if (isset($this->form) AND $this->form->find($variable))
		{
			// Set the form's value too
			$this->form->find($variable)->val($value);
		}
		
		// Always run Jelly's __set() too
		parent::__set($variable, $value);
	}
	
	// Return the model's full form, create it if necessary
	protected function generate_form()
	{
		if (isset($this->form))
			return $this->get('form');
			
		return $this->form = Formo::form()->orm_driver()->load($this);
	}
	
	// Pull a subform out of the full form
	public function subform(array $fields)
	{
		if (isset($this->subform))
			return $this->get('subform');
			
		$this->form->create_sub('subform', 'form', $fields);
		
		return $this->subform = $this->form->subform;
	}
	
	// Run the form's load function
	public function load(array $input = NULL)
	{
		$this->form->load($input);
		return $this;
	}
	
	// Check to see if the form was sent
	public function sent()
	{
		return $this->form->get('sent');
	}
	
	// Set the form's error
	public function error($field, $error)
	{
		$this->form->find($field)->error = $error;
	}
	
	// Return form errors
	public function errors()
	{
		return $this->form->errors();
	}
	
	public function validate($data = NULL)
	{
		// If the formo object to validate against doesn't exist, make it
		$this->generate_form();
		
		if ( ! $this->form->validate(TRUE))
			throw new Validator_Exception($this->form->errors(), 'Failed to validate form');
			
		return $this->_changed;
	}
	
	public function render($type, $view_prefix = FALSE)
	{
		// Run Formo's render on the full form object
		return $this->form->render($type, $view_prefix);
	}
	
	// Unique rule
	public function unique($field)
	{
		// If field hasn't been changed, it passes this test
		if (array_key_exists($field, $this->_changed) === FALSE)
			return TRUE;
		
		// Grab a new user object
		$rec = Jelly::select($this->meta()->model())->where($field, '=', $this->$field)->load();

		return $rec->loaded() === FALSE;
	}
	
}