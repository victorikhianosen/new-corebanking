<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    protected $connection = 'mysql';

    protected $guarded = [];

    protected $casts = [
        'database_password' => 'encrypted',
    ];
}