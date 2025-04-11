<?php

namespace App\Models;

class SettingNotification extends BaseModel
{
    protected $table = "setting_notifications";
    protected $fillable = [
    ];
    protected $hidden = [
        'updated_at',  'updated_by'
    ];
    public $timestamps = true;
}
