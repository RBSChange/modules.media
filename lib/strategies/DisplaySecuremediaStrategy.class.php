<?php
/**
 * @package modules.media
 */
abstract class media_DisplaySecuremediaStrategy
{
	const OK = 1;
	const KO = 2;
	const NOT_CONCERNED = 3;
	
	/**
	 * @param media_persistentdocument_securemedia $media
	 * @return Integer {OK | KO | NOT_CONCERNED}
	 */
	public abstract function canDisplayMedia($media);
}