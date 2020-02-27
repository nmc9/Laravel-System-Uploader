<?php

namespace Nmc9\Uploader\Database\Uploadables;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\JustIndex;

class UploadableJustIndex implements UploadableContract
{

    public function getUploaderIdFields(){
        return ['data'];
    }

    public function getModel() : Model{
        return new JustIndex();
    }
}
