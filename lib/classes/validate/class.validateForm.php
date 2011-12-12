<?php

class validateForm
{
	/**
	 * Validate a form build by formfields
	 *
	 * This function will validate the user-input of a form made by the formgenerator
	 * It always will return an array with 3 values:
	 * -> "safe": The data was checked by the validation rules given in the form - array
	 * -> "failed": The checks given by the form - array failed validating these values (!UNTRUSTED!)
	 * -> "unsafe": These values are from $data which didn't have a corresponding formfield in the original array (!UNTRUSTED!)
	 * @param array $data The submitted data by the user (i.e. $_POST)
	 * @param array $referenceForm The array used to build the original form
	 * @return array The result with checked safe values, check failed values and not checked values
	 */
	public static function validate($data, $referenceForm)
	{
		$safe = array();
		$failed = array();
		$unsafe = array();
		foreach ($data as $formname => $formvalue)
		{
			foreach ($referenceForm['sections'] as $sectionname => $section)
			{
				if (array_key_exists($formname, $referenceForm['sections'][$sectionname]['fields']))
				{
					$rules = array('required' => false);
					if (isset($referenceForm['sections'][$sectionname]['fields'][$formname]['validation']))
					{
						$rules = $referenceForm['sections'][$sectionname]['fields'][$formname]['validation'];
					}
					$res = self::validateField($formvalue, $rules);
					if ($res === true)
					{
						$safe[$formname] = $formvalue;
					}
					else
					{
						$res['label'] = $referenceForm['sections'][$sectionname]['fields'][$formname]['label'];
						$failed[$formname] = $res;
					}
					unset($data[$formname]);
					break;
				}
				// Maybe it is an unlimited field?
				elseif(substr($formname, -3) == "_ul" && array_key_exists(substr($formname, 0, strlen($formname) - 3), $referenceForm['sections'][$sectionname]['fields']))
				{
					var_dump($data[$formname]);
					if ($data[$formname] != "-1")
					{
						$failed[$formname] = array(
							'label' => $referenceForm['sections'][$sectionname]['fields'][substr($formname, 0, strlen($formname) - 3)]['label'] . " (" . _('Unlimited') . ")",
							'wrongvalue' => _('Unlimited need to be -1 if given')
						);
					}
					else
					{
						$safe[$formname] = "-1";
					}
					unset($data[$formname]);
					break;
				}
			}
		}
		return array('safe' => $safe, 'failed' => $failed, 'unsafe' => $data);
	}

	public static function validateField($value, $rules)
	{
		$safe = 1;
		$result = array();
		if (is_array($rules))
		{
			// If the field isn't required and it isn't given, don't do any of the checks below, they will fail
			// Reason: The field only needs to be checked if a data is given, but if a data is given, it has to folow the rules
			if ($rules['required'] == 0 && strlen($value) == 0)
			{
				return true;
			}
			if ($rules['required'] == 1 && strlen($value) == 0)
			{
				$result[] = _('The field is required but no value was given.');
				$safe = 0;
			}

			// Loop to all given rules
			foreach($rules as $key => $rule)
			{
				switch ($key)
				{
					case 'minlen':
						if (strlen($value) < $rule)
						{
							$result[] = sprintf(_('The minimum required length for this field is %d.'), $rule);
							$safe = 0;
						}
						break;
					case 'maxlen':
						if (strlen($value) > $rule)
						{
							$result[] = sprintf(_('The maximum allowed length for this field is %d.'), $rule);
							$safe = 0;
						}
						break;
					case 'minvalue':
						if (!is_numeric($value) || $value > $rule)
						{
							$result[] = sprintf(_('The value has to be greater than %d.'), $rule);
							$safe = 0;
						}
						break;
					case 'maxvalue':
						if (!is_numeric($value) || $value < $rule)
						{
							$result[] = sprintf(_('The value has to be smaller than %d.'), $rule);
							$safe = 0;
						}
						break;
					case 'format':
						if ($rule == 'email')
						{
							if (!filter_var($value, FILTER_VALIDATE_EMAIL))
							{
								$result[] = _('The value has to be an e-mail.');
								$safe = 0;
							}
						}
						elseif($rule == 'phone')
						{
							if (!preg_match('/^[0-9\- \+\(\)\/]*$/', $value))
							{
								$result[] = _('The value contains invalid characters.');
								$safe = 0;
							}
						}
						elseif($rule == 'zipcode')
						{
							if (!preg_match('/^[0-9 \-A-Z]+$/', $value))
							{
								$result[] = _('The value contains invalid characters for a zipcode. Only numbers, letters, spaces and dashes are allowed.');
								$safe = 0;
							}
						}
						elseif($rule == 'customernumber')
						{
							if (!preg_match('/^[A-Za-z0-9 \-]*$/Di', $value))
							{
								$result[] = _('The value contains invalid characters for a customer number. Only numbers, letters, spaces and dashes are allowed.');
								$safe = 0;
							}
						}
						elseif($rule == 'number')
						{
							if (!preg_match('/^\d+$/', $value))
							{
								$result[] = _('The value is not a valid number');
								$safe = 0;
							}
						}
						elseif($rule == 'decimal')
						{
							if (!preg_match('/^\d+(?:[,\.]\d+)?$/', $value))
							{
								$result[] = _('The value is not a valid decimal number');
								$safe = 0;
							}
						}
						elseif($rule == 'boolean')
						{
							if (!preg_match('/^\d+$/', $value))
							{
								$result[] = _('The value is not a valid boolean. Only 0 or 1 allowed');
								$safe = 0;
							}
						}
						elseif($rule == 'string')
						{
							if (preg_match('/[\r\n\t\f\0]/', $value))
							{
								$result[] = _('The value contains invalid characters.');
								$safe = 0;
							}
						}
						elseif($rule == 'password')
						{
							if (preg_match('/[\r\n\t\f\0]/', $value))
							{
								$result[] = _('The value contains invalid characters.');
								$safe = 0;
							}
							if (getSetting('panel', 'password_min_length') > 0)
							{
								if (strlen($value) < getSetting('panel', 'password_min_length'))
								{
									$result[] = sprintf(_('The minimum required length for this field is %d.'), getSetting('panel', 'password_min_length'));
									$safe = 0;
								}
							}
							if (getSetting('panel', 'password_regex') != '')
							{
								if (!preg_match(getSetting('panel', 'password_regex'), $value))
								{
									$result[] = _('The password is not complex enough.');
									$safe = 0;
								}
							}
						}
						break;
					case 'regex':
						if (!preg_match('/^' . $rule . '$/', $value))
						{
							$result[] = sprintf(_('The value does not match the required format: %s.'), $rule);
							$safe = 0;
						}
						break;
				}
			}
		}
		if ($safe)
		{
			return true;
		}
		return $result;
	}
}
