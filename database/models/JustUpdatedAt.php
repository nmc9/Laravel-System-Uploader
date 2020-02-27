<?php

namespace Nmc9\Uploader\Database\Models;

use Illuminate\Database\Eloquent\Model;

class JustUpdatedAt extends Model
{

	protected $table = "just_updated_at";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const CREATED_AT = null;
    const UPDATED_AT = "LastModified";
}
