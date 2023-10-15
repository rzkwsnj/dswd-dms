<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Citizen extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'citizens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'photo',
        'batch',
        'control_no',
        'scid',
        'identifier',
        'first_name',
        'middle_name',
        'last_name',
        'extra_name',
        'representative',
        'region_id',
        'province_id',
        'city_id',
        'barangay_id',
        'address',
        'extra_address',
        'gender',
        'birthday',
        'disability_status',
        'ip_status',
        'correction_remarks',
        'food_status',
        'medicine_vitamin_status',
        'medical_health_check_status',
        'clothing_status',
        'debit_payment_status',
        'livelihood_activities_status',
        'other_status',
        'citizen_status',
        'replacement',
        'quarter_of_separation',
        'detailed_remarks',
        'remarks',
        'date_downloaded',
        'additional',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'date',
        'date_downloaded' => 'date',
    ];

    public function regions(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function provinces(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function cities(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function barangays(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }
}
