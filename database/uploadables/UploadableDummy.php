<?php

namespace Nmc9\Uploader\Database\Uploadables;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\Dummy;

class UploadableDummy implements UploadableContract
{

    public function getUploaderIdFields(){
        return [];
    }

    public function getModel() : Model{
        return new Dummy();
    }
}
