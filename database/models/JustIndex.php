<?php

namespace Nmc9\Uploader\Database\Models;

use Illuminate\Database\Eloquent\Model;

class JustIndex extends Model
{

	protected $table = "just_index";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
}
