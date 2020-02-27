<?php

namespace Nmc9\Uploader\Database\Models;

use Illuminate\Database\Eloquent\Model;

class NoTimestamp extends Model
{

	protected $table = "notimestamp";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
}
