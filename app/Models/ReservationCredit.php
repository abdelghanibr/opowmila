<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationCredit extends Model
{
    protected $fillable = [
        'reservation_id',
        'user_id',
        'complex_activity_id',
        'closure_date',
        'credited_amount',
        'status',
        'used_in_reservation_id',
        'note',
    ];
}