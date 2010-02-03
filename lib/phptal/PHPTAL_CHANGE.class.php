<?php
class media_PHPTAL_CHANGE
{
	/**
	 * @param PHPTAL_Namespace_CHANGE $namespaceCHANGE
	 */
	public static function addAttributes($namespaceCHANGE)
	{
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('download', 30));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('icon', 31));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('image', 32));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeReplace('media', 32));
        $namespaceCHANGE->addAttribute(new PHPTAL_NamespaceAttributeSurround('webappimage', 32));	
	}
}
