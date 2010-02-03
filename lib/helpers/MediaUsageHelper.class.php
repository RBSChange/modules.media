<?php
class media_MediaUsageHelper
{
	const REGEX_MEDIA_URL = '/publicmedia\/(?:original|formatted)\/((?:[0-9]{1,3}\/)+)([a-z]{2})\//i';
	const REGEX_MEDIA_TAGS = '/<(?:a|img)\s+(.*?)>/i';
	const REGEX_MEDIA_BLOCK = '/<(?:change:block|wblock)\s+(.*?)>/i';
		
	/**
	 * @param String $XhtmlString
	 * @param Integer $documentId
	 * @param String $lang
	 * @param array<> $usagesArray
	 * @return array<>
	 */
	public static function getByXhtml($XhtmlString, $documentId, $lang, $usagesArray)
	{
		if (!is_array($usagesArray)) {$usagesArray = array();}
		if (empty($XhtmlString)) {return $usagesArray;}
		
		$matches = array();
		if (preg_match_all(self::REGEX_MEDIA_TAGS, $XhtmlString, $matches))
		{
			foreach ($matches[1] as $matchInfos) 
			{
				$usagesArray = self::parseMediasTags($matchInfos, $documentId, $lang, $usagesArray);		
			}
		}
		$matches = array();
		if (preg_match_all(self::REGEX_MEDIA_BLOCK, $XhtmlString, $matches))
		{
			foreach ($matches[1] as $matchInfos) 
			{
				$usagesArray = self::parseMediasBlock($matchInfos, $documentId, $lang, $usagesArray);		
			}			
		}
		return $usagesArray;
	}
	
	/**
	 * @param array $array
	 * @param Integer $documentId
	 * @param array<> $usagesArray
	 * @return array<>
	 */
	public static function getByArray($array, $documentId, $usagesArray)
	{
		foreach ($array as $file) 
		{
			if ($file instanceof media_persistentdocument_file) 
			{
				$usagesArray[self::buildKey($file->getId(), '', $documentId, '')] = self::buildValue($file->getId(), '', $documentId, '');
			}
		}
		
		return $usagesArray;
	}
	
	/**
	 * @param media_persistentdocument_file $file
	 * @param Integer $documentId
	 * @param array<> $usagesArray
	 * @return array<>
	 */
	public static function getByDocument($file, $documentId, $usagesArray)
	{
		$usagesArray[self::buildKey($file->getId(), '', $documentId, '')] = self::buildValue($file->getId(), '', $documentId, '');
		return $usagesArray;
	}
	
	/**
	 * @param String $matchInfo
	 * @param Integer $documentId
	 * @param String $lang
	 * @param array<> $usagesArray
	 * @return array<>
     */
    private function parseMediasBlock($matchInfo, $documentId, $lang, $usagesArray)
    {       
        $attributes = self::parseAttributes(trim($matchInfo));
        $type = $attributes['type'];
        if (strpos($type, 'modules_media_') !== 0) { return $usagesArray;}
        
        if (isset($attributes['xml:lang']))
        {
        	$medialang = $attributes['xml:lang'];
        } 
        else if (isset($attributes['lang']))
        {
        	$medialang = $attributes['lang'];
        }
        else
        {
        	$medialang = $lang;
        }
        
        $mediaId = 0;
        if (isset($attributes['cmpref']))
        {
            $mediaId = intval($attributes['cmpref']);
        }  
        
        if ($mediaId > 0)
        {
        	try 
        	{
        		$media = DocumentHelper::getDocumentInstance($mediaId);
        		if ($media instanceof media_persistentdocument_file) 
        		{
        			$usagesArray[self::buildKey($mediaId, $medialang, $documentId, $lang)] = self::buildValue($mediaId, $medialang, $documentId, $lang);
        		}
        	}
        	catch (Exception $e)
        	{
        		if (Framework::isInfoEnabled())
        		{
        			Framework::info('Invalid document id : ' . $mediaId);
        		}
        	}
        }
        
        return $usagesArray;
    }
	
    /**
	 * @param String $matchInfo
	 * @param Integer $documentId
	 * @param String $lang
	 * @param array<> $usagesArray
	 * @return array<>
     */
    private function parseMediasTags($matchInfo, $documentId, $lang, $usagesArray)
    {       
        $attributes = self::parseAttributes(trim($matchInfo));
        
        if (isset($attributes['xml:lang']))
        {
        	$medialang = $attributes['xml:lang'];
        } 
        else if (isset($attributes['lang']))
        {
        	$medialang = $attributes['lang'];
        }
        else
        {
        	$medialang = $lang;
        }
        
        $mediaId = 0;
        if (isset($attributes['cmpref']))
        {
            $mediaId = intval($attributes['cmpref']);
        }
        else if (preg_match(self::REGEX_MEDIA_URL, $matchInfo, $urlMathesInfo))
        {
            $mediaId = intval(str_replace(array('/', '\\'), '', $urlMathesInfo[1]));
        }
        
        if ($mediaId > 0)
        {
        	try 
        	{
        	    $media = DocumentHelper::getDocumentInstance($mediaId);
        		if ($media instanceof media_persistentdocument_file) 
        		{
        			$usagesArray[self::buildKey($mediaId, $medialang, $documentId, $lang)] = self::buildValue($mediaId, $medialang, $documentId, $lang);
        		}
        	}
        	catch (Exception $e)
        	{
        		if (Framework::isInfoEnabled())
        		{
        			Framework::info('Invalid document id : ' . $mediaId);
        		}
        	}
        }
        
        return $usagesArray;
    }
    
    /**
     * @param String $string
     * @return Array
     */
    private static function parseAttributes ($string)
    {
        $attributeArray = array();
        
        if (!empty($string))
        {
        	$matches = array();
            preg_match_all("/\s*([\w:]*)\s*=\s*\"(.*?)\"/i", $string, $matches, PREG_SET_ORDER);
            foreach ($matches as $match)
            {
                $attributeArray[strtolower($match[1])] = isset($match[3]) ? $match[3] : $match[2];
            }
        }
        return $attributeArray;
    }

    /**
	 * @param Integer $mediaId
	 * @param String $mediaLang
	 * @param Integer $documentId
	 * @param String $documentLang
	 * @return String
     */   
    public static function buildKey($mediaId, $mediaLang, $documentId, $documentLang)
    {
    	return $mediaId . '/'. $mediaLang . ':'. $documentId . '/'. $documentLang;
    }
    
    /**
	 * @param Integer $mediaId
	 * @param String $mediaLang
	 * @param Integer $documentId
	 * @param String $documentLang
	 * @return array<>
     */   
    public static function buildValue($mediaId, $mediaLang, $documentId, $documentLang)
    {
    	return array($mediaId, $mediaLang, $documentId, $documentLang);
    }
    
    /**
	 * @param Integer $mediaId
	 * @param array<> $usagesArray
	 * @return array<>
     */      
    public static function getUsagesByMediaId($mediaId, $usagesArray)
    {
    	$result = array();
    	if (f_util_ArrayUtils::isEmpty($usagesArray)) {return $result;}
    	
    	foreach ($usagesArray as $key => $value) 
    	{
    		if ($value[0] == $mediaId)
    		{
    			$result[$key] = $value;
    		}
    	}
    	
    	return $result;
    }

    /**
	 * @param Integer $documentId
	 * @param array<> $usagesArray
	 * @return array<>
     */ 
    public static function getUsagesByDocumentId($documentId, $usagesArray)
    {
    	$result = array();
    	if (f_util_ArrayUtils::isEmpty($usagesArray)) {return $result;}
    	
    	foreach ($usagesArray as $key => $value) 
    	{
    		if ($value[2] == $documentId)
    		{
    			$result[$key] = $value;
    		}
    	}
    	
    	return $result;
    }   
}