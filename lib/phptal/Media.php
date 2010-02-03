<?php
// change:media
//
//   <img change:image="[attributeName ][front/]image_name[ width][ height][ format]" />

class PHPTAL_Php_Attribute_CHANGE_media extends PHPTAL_Php_Attribute
{
    public function start()
    {
        // split attributes to translate
        $expressions = $this->tag->generator->splitExpression($this->expression);

        $media  = 'null';
        $width  = 'null';
        $height = 'null';
        $format = 'null';
        $lang   = 'null';
        $alt    = 'null';
        $contentType = 'null';
        $attributes = array();

        // foreach attribute
        foreach ($expressions as $exp)
        {
            list($attribute, $value) = $this->parseSetExpression($exp);
            switch (trim($attribute))
            {
            	case 'document':
            		$media = $this->evaluate($value);
            		break;
            	case 'width':
            		$width = $this->evaluate($value);
            		break;
            	case 'height':
            		$height = $this->evaluate($value);
            		break;
            	case 'format':
            		$format = $this->evaluate($value);
            		break;
            	case 'lang':
            		$lang = $this->evaluate($value);
            		break;
            	case 'alt':
            		$alt = $this->evaluate($value);
            		break;
            	case 'contentType':
            		$contentType = $this->evaluate($value);
            		break;
            	default:
            		if ($attribute) $attributes[] = "'".$attribute."' => ".$this->evaluate($value);
            		break;
            }
        }

		$this->doEcho("MediaHelper::getContent($media, $lang, $width, $height, $alt, $format, $contentType, array(".join(",", $attributes)."))");
    }

    public function evaluate($exp)
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
    		return $this->tag->generator->evaluateExpression($exp);
    	}
    }
    
    public function end()
    {
    }
}