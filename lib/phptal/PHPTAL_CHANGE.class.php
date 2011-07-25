<?php
class media_PHPTAL_CHANGE
{
	/**
	 * @param PHPTAL_Namespace_CHANGE $namespaceCHANGE
	 */
	public static function addAttributes($namespaceCHANGE)
	{
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('download', 30));             
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('media', 32));  	
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('img', 32));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('gauge', 32));
	}
}
