<?php

namespace Nmc9\Uploader\Contract;

use Illuminate\Database\Eloquent\Model;

interface UploadableContract
{

	public function getUploaderIdFields();

	public function getModel() : Model;

}
