<?php

namespace Nmc9\Uploader\Factory;

use Nmc9\Uploader\UploaderRecord;

class UploaderRecordFactory
{

	public static function create($json){
		return new UploaderRecord($json);
	}

}
