<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnershipTransfer extends Model
{
    protected $fillable = [
        'member_id', 'from_user_id', 'from_nama',
        'to_user_id', 'to_nama', 'to_no_kp',
        'sebab', 'tarikh_pindah', 'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_pindah' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
