<?php
// change:icon
//
//   <button change:icon="[attributeName ]icon_name[/size][ shadow]" />

/**
 * @package phptal.php.attribute
 * @author INTbonjF
 * 2007-04-19
 */
class PHPTAL_Php_Attribute_CHANGE_icon extends PHPTAL_Php_Attribute
{
	private $pageId;
	private $lang;

    public function start()
    {
        $exp = trim($this->expression);

        $expArray = explode(' ', $exp);

    	switch (count($expArray))
    	{
    	    case 1:
    	        switch (strtolower($this->tag->name))
            	{
            	    case 'img':
            	    case 'input':
            	    case 'image':
            	        $attribute = 'src';
            	        break;

            	    default:
            	        $attribute = 'image';
            	        break;
            	}
        	list($icon, $size) = explode('/', $expArray[0]);
        	$layout = MediaHelper::LAYOUT_PLAIN;
    	        break;

    	    case 2:
    	        if ($expArray[1] == 'shadow')
    	        {
    	            	switch (strtolower($this->tag->name))
                	{
                	    case 'img':
                	    case 'input':
                	    case 'image':
                	        $attribute = 'src';
                	        break;

                	    default:
                	        $attribute = 'image';
                	        break;
                	}
        	        list($icon, $size) = explode('/', $expArray[0]);
        	        $layout = MediaHelper::LAYOUT_SHADOW;
    	        }
    	        else
    	        {
    	            $attribute = $expArray[0];
        	    list($icon, $size) = explode('/', $expArray[1]);
    	            $layout = MediaHelper::LAYOUT_PLAIN;
    	        }
    	        break;

    	    default:
    	        $attribute = $expArray[0];
		if (isset($expArray[1])) list($icon, $size) = explode('/', $expArray[1]);
	        $layout = MediaHelper::LAYOUT_SHADOW;
    	        break;
    	}

        if (empty($size))
        {
        	$size = MediaHelper::NORMAL;
	}

	if (empty($icon))
	{
		$icon = 'unknown';
	}
	
	$this->tag->attributes[$attribute] = MediaHelper::getIcon($icon, $size, null, $layout);
    }

    public function end()
    {
    }
}
