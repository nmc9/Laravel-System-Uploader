<?php

namespace Nmc9\Uploader\Contract;

use Nmc9\Uploader\Contract\UploadableContract;

interface UploadMethodContract{

	public function handle(UploadableContract $uploadable,$data);
}
