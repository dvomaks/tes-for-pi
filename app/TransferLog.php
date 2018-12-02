<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferLog extends Model
{
    protected $fillable = [
        'user_id', 'transfer_date', 'resource', 'transferred_bytes'
    ];

    protected $casts = [
        'total_bytes' => 'integer',
    ];
}
