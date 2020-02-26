<?php

namespace Nmc9\Uploader\Method;

use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Contract\UploadMethodContract;
use Nmc9\Uploader\Contract\UploadableContract;

class UploadMethodUpdateOrCreate implements UploadMethodContract
{

	public function handle(UploadableContract $uploadable,$data){
		foreach ($data as $index => $uploaderData) {
			//This insures proper formatting
			$record = $uploaderData->get();
			$query = CompositeId::make($uploadable->getUploaderIdFields())->get($record,$index);

			$uploadable->getModel()->updateOrCreate(
				$query,$record
			);
		}
		return !!$data;
	}

}
