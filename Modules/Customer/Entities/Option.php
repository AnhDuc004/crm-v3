<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;


class Option extends Model
{
    protected $table = 'options';

    protected $fillable = ['name', 'value', 'autoload'];

    public $timestamps = false;

}
