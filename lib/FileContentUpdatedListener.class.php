<?php
class media_FileContentUpdated
{
	public function onMediaFileContentUpdated($sender, $params)
	{
		/**
		 * @var media_persistentdocument_file
		 */
		$media = $params['media'];
		
		/**
		 * @var string
		 */
		$filePath = $params['filePath'];
		
		if (defined('NODE_NAME') && ($media instanceof media_persistentdocument_media))
		{
			Framework::info(__METHOD__ .  ' ' . $media->__toString() . ' Media ' . $filePath . ' updated on node ' . NODE_NAME);	
		}
	}
}