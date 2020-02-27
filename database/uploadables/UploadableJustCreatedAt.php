<?php

namespace Nmc9\Uploader\Database\Uploadables;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\JustCreatedAt;

class UploadableJustCreatedAt implements UploadableContract
{

    public function getUploaderIdFields(){
        return ["company_id","customer_id"];
    }

    public function getModel() : Model{
        return new JustCreatedAt();
    }
}
