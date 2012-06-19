<?php
class media_FileBeanPopulateFilter implements website_BeanPopulateFilter
{
	/**
	 * @var String
	 */
	private $propName;

	/**
	 * @param string $propName the bean property name to fill with media/file
	 */
	function __construct($propName)
	{
		$this->propName = $propName;
	}

	/**
	 * @param f_mvc_Bean $bean
	 * @param website_BlockActionRequest $request
	 */
	function execute($bean, $request)
	{
		// Some validation
		$model = $bean->getBeanModel();
		if (!$model->hasProperty($this->propName))
		{
			throw new Exception("Bean of class ".get_class($bean)." has no property named ".$this->propName);
		}
		$propInfo = $model->getBeanPropertyInfo($this->propName);
		if ($propInfo->getType() != BeanPropertyType::DOCUMENT)
		{
			throw new Exception("Invalid property type ".$propInfo->getType()." for ".get_class($bean).".".$this->propName);
		}
		$propModel = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($propInfo->getDocumentType());
		if (!$propModel->isModelCompatible("modules_media/file"))
		{
			throw new Exception("Invalid property document type ".$propInfo->getDocumentType()." for ".get_class($bean).".".$this->propName);
		}

		// potential deletion handling
		$toDelete = $request->getParameter($this->propName."_delete");

		if ($toDelete !== null)
		{
			if (is_array($toDelete))
			{
				$values = BeanUtils::getProperty($bean, $this->propName);
				if (is_array($values))
				{
					foreach ($toDelete as $toDeleteIndex => $value)
					{
						if ($value == "on")
						{
							unset($values[$toDeleteIndex]);
						}
					}
					BeanUtils::setProperty($bean, $this->propName, $values);
				}
			}
			else
			{
				BeanUtils::setProperty($bean, $this->propName, null);
			}
		}

		// potential new file handling
		if ($request->hasFile($this->propName."_new"))
		{
			$file = $request->getFile($this->propName."_new");
			$file->save();
				
			if ($propInfo->getCardinality() != 1)
			{
				$values = BeanUtils::getProperty($bean, $this->propName);
				if ($values === null)
				{
					$values = array();
				}
				$values[] = $file;
				BeanUtils::setProperty($bean, $this->propName, $values);
			}
			else
			{
				BeanUtils::setProperty($bean, $this->propName, $file);
			}
		}
	}
}