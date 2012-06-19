<?php
// change:media
//
//   <img change:image="[attributeName ][front/]image_name[ width][ height][ format]" />

class PHPTAL_Php_Attribute_CHANGE_Media extends PHPTAL_Php_Attribute
{
	
	/**
	 * Called before element printing.
	 */
	public function before(PHPTAL_Php_CodeWriter $codewriter)
	{
		$expressions = $codewriter->splitExpression($this->expression);

		$media  = 'null';
		$width  = 'null';
		$height = 'null';
		$format = 'null';
		$lang   = 'null';
		$alt	= 'null';
		$contentType = 'null';
		$attributes = array();

		// foreach attribute
		foreach ($expressions as $exp)
		{
			list($attribute, $value) = $this->parseSetExpression($exp);
			switch (trim($attribute))
			{
				case 'document':
					$media = $this->evaluate($value, $codewriter);
					break;
				case 'width':
					$width = $this->evaluate($value, $codewriter);
					break;
				case 'height':
					$height = $this->evaluate($value, $codewriter);
					break;
				case 'format':
					$format = $this->evaluate($value, $codewriter);
					break;
				case 'lang':
					$lang = $this->evaluate($value, $codewriter);
					break;
				case 'alt':
					$alt = $this->evaluate($value, $codewriter);
					break;
				case 'contentType':
					$contentType = $this->evaluate($value, $codewriter);
					break;
				default:
					if ($attribute) $attributes[] = "'".$attribute."' => ".$this->evaluate($value, $codewriter);
					break;
			}
		}
		$codewriter->doEchoRaw("MediaHelper::getContent($media, $lang, $width, $height, $alt, $format, $contentType, array(".join(",", $attributes)."))");
	}

	public function evaluate($exp, PHPTAL_Php_CodeWriter $codewriter)
	{
		$exp = trim(strval($exp));
		$end = strlen($exp) - 1;
		if (is_numeric($exp) || ($exp[0] == "'" && $exp[$end] == "'") ||
		 	($exp[0] == '"' && $exp[$end] == '"'))
		{
			// Static value
			return $exp;
		}
		else
		{
			return $codewriter->evaluateExpression($exp);
		}
	}
	
	/**
	 * Called after element printing.
	 */
	public function after(PHPTAL_Php_CodeWriter $codewriter)
	{
	  
	}
}