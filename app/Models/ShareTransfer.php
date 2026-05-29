<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTransfer extends Model
{
    protected $fillable = [
        'from_member_id', 'to_member_id', 'amaun',
        'sebab', 'tarikh_pindah', 'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'amaun'         => 'decimal:2',
            'tarikh_pindah' => 'date',
        ];
    }

    public function fromMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'from_member_id');
    }

    public function toMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'to_member_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
