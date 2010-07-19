<?php
class validation_MimetypeValidator extends validation_ValidatorImpl
{
	/**
	 * Validate $data and append error message in $errors.
	 *
	 * @param validation_Property $Field
	 * @param validation_Errors $errors
	 *
	 * @return void
	 */
	protected function doValidate(validation_Property $field, validation_Errors $errors)
	{
		$values = $field->getValue();
		if ($values === null || (is_array($values) && f_util_ArrayUtils::isEmpty($values)))
		{
			return true;
		}

		if (!is_array($values))
		{
			if (is_string($values))
			{
				$values = explode(",", $values);
			}
			elseif (is_object($values))
			{
				$values = array($values);
			}
			else
			{
				throw new Exception(__METHOD__."Bad value ". var_export($values, true));
			}
		}

		$allMatch = true;
		foreach ($values as $value)
		{
			if (is_numeric($value))
			{
				$value = DocumentHelper::getDocumentInstance($value);
			}
			if (!$value instanceof media_persistentdocument_file)
			{
				throw new IllegalArgumentException($field->getName()." is not a media/file but ".var_export($value, true));
			}
			$valueOk = false;
			$allowedMimes = $this->getParameter();
			$mimeType = $value->getMimetype();
			foreach ($allowedMimes as $allowedMime)
			{
				if (strpos($allowedMime, "*") !== false)
				{
					$pattern = '/^'.str_replace(array('/', '*'), array('\/', '.*'), $allowedMime).'$/';
					$match = preg_match($pattern, $mimeType);
				}
				else
				{
					$match = ($allowedMime == $mimeType);
				}
				if ($match)
				{
					$valueOk = true;
				}
			}
			$allMatch = ($allMatch && $valueOk);
		}

		if ($allMatch)
		{
			return true;
		}

		$this->reject($field->getName(), $errors, array("allowedMimes" => join(", ", $allowedMimes)));
	}

	/**
	 * @return Boolean
	 */
	protected function canBeReversed()
	{
		return false;
	}

	/**
	 * Sets the value of the unique validator's parameter.
	 *
	 * @param mixed $value
	 */
	public function setParameter($value)
	{
		$mimes = explode(",", $value);
		parent::setParameter($mimes);
	}
	
	protected function getMessageCode()
	{
		// substr(get_class($this), 11, -9) to remove 'validation_' prefix and 'Validator' suffix
		$key = 'modules.media.validation.validator.'.substr(get_class($this), 11, -9).'.Message';
		return '&'.$key.';';
	}
}
