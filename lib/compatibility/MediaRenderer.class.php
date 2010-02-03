<?php
/**
 * @package modules.media
 */
class media_lib_MediaRenderer extends f_component_DocumentComponent
{
	public static function renderBlock($pageHandler, $type, $display = array(), $refs = array(), $requestParameters = array(), $lang = null, $indexing = false)
	{
	    $content = '';
        foreach ($refs as $ref)
	    {
    	    $media = new Media($ref);

            /*
            EXAMPLE :
    	    $format = array(
    	       'width' => "100px"
    	    );

    	    $format = "thumb";

    	    $media->applyFormat($format);

    	    $style = array(
    	       'border' => "1px solid red"
    	    );

    	    $style = "border";

    	    $media->applyStyle($style);
            */

            if (isset($display['format']))
            {
                $media->applyFormat($display['format']);
            }
            else
            {
                $format = array();
                if (isset($display['width']))
                {
                    $format['width'] = $display['width'];
                }
                if (isset($display['height']))
                {
                    $format['height'] = $display['height'];
                }
                if ($format)
                {
                    $media->applyFormat($format);
                }
            }

    		switch ($type)
    		{
    		    case "modules_media_image":
    		        $content .= $media->renderImage(K::HTML);
            		break;

    		    case "modules_media_flash":
    		        $templateComponent = Loader::getInstance('template')
    		            ->setpackagename('modules_media')
    		            ->setMimeContentType(K::HTML)
    		            ->load('Media-compatibility-flash');

            		$flashInfo = $media->getFlashInfo();
            		$templateComponent->setAttribute("id", $type . '_' . $ref);
            		$templateComponent->setAttribute("data", $flashInfo['url']);
            		if (isset($flashInfo['width']))
            		{
            		    $templateComponent->setAttribute('width', $flashInfo['width']);
            		}
            		if (isset($flashInfo['height']))
            		{
            		    $templateComponent->setAttribute('height', $flashInfo['height']);
            		}
            		$document = ComponentFactory::getComponentInstance($ref);
            		$versions = $document->getVersions();
        		    $document->switchVersion($versions[0]['lang'], $versions[0]['status'], $versions[0]['revision']);
            		$templateComponent->setAttribute("alt", $document->getDescriptionValue());
            		foreach ($display as $attribute => $value) {
    					$templateComponent->setAttribute($attribute, $value);
    				}
            		$content .= $templateComponent->execute();
    		        break;
    		}
	    }
	    return $content;
	}
}