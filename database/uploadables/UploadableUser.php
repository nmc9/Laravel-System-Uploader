<?php

namespace Nmc9\Uploader\Database\Uploadables;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\User;

class UploadableUser implements UploadableContract
{

    public function getUploaderIdFields(){
        return ["user_id"];
    }

    public function getModel() : Model{
        return new User();
    }
}
