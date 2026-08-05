<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoolClosure extends Model
{
    protected $fillable = [
        'complex_activity_id',
        'start_date',
        'end_date',
        'closure_date',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'closure_date' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function complexActivity()
    {
        return $this->belongsTo(ComplexActivity::class);
    }
}
