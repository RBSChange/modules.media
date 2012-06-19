<?php
if (count($_POST['argv']) === 2)
{
	$chunckSize = intval($_POST['argv'][0]);
	$startId = intval($_POST['argv'][1]);	
}
else
{
	$chunckSize = 100;
	$startId = 0;	
}

$start = date_Calendar::getInstance()->sub(date_Calendar::HOUR, 2)->toString();
$mtfs = media_TmpfileService::getInstance();

$tmpFiles = $mtfs->createStrictQuery()->add(Restrictions::gt('id', $startId))
	->add(Restrictions::lt('creationdate', $start))
	->addOrder(Order::asc('id'))
	->setMaxResults($chunckSize)
	->find();
	
foreach ($tmpFiles as $tmpFile)
{
	try
	{
		$lastId = $tmpFile->getId();
		$mtfs->checkTmpFile($tmpFile);
	}
	catch (Exception $e)
	{
		Framework::exception($e);
	}
}

if (count($tmpFiles) === $chunckSize)
{
	echo $lastId;
}
else
{
	echo '-1';
}