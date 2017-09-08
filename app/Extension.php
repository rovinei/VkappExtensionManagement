<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{

    protected static $AVAILABLE_EXT = 1;
    protected static $INUSE_EXT = 2;
    protected $table = "extensions";
    protected $primaryKey = "id";
    protected $fillable = [
        'extension',
        'status',
        'last_registered',
        'token',
        'customer_name'
    ];
}
