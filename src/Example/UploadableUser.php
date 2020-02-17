<?php

namespace Nmc9\Uploader\Example;

use Nmc9\Uploader\Example\User;
use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Contract\UploadableContract;

class UploadableUser implements UploadableContract
{

    public function getUploaderIdFields(){
        return ["company_id","customer_id"];
    }

    public function getModel() : Model{
        return new User();
    }
}
