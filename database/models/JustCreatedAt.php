<?php

namespace Nmc9\Uploader\Database\Models;

use Illuminate\Database\Eloquent\Model;

class JustCreatedAt extends Model
{

	protected $table = "just_created_at";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const CREATED_AT = "created_at";
    const UPDATED_AT = null;
}
