<?php

namespace Nmc9\Uploader\Factory;

use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\UploaderPackage;

class UploaderPackageFactory
{

	public static function create(UploadableContract $uploadable, array $records){
		return new UploaderPackage($uploadable, $records);
	}

}
