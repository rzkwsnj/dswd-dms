<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasFactory;

    protected $table = 'citizen_payouts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'citizen_id',
        'reference',
        'payout_period_year',
        'payout_period_semester',
        'date',
        'method',
        'amount',
        'currency',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class);
    }
}
