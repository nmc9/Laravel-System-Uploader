<?php

namespace Nmc9\Uploader\Factory;

use Nmc9\Uploader\Contract\AbstractUploadableModel;
use Nmc9\Uploader\Contract\UploaderModelContract;
use Nmc9\Uploader\UploaderPackage;

class UploaderPackageFactory
{

	public static function create(AbstractUploadableModel $model, array $records){
		return new UploaderPackage($model, $records);
	}

}
