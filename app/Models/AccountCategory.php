<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AccountEntry;


class AccountCategory extends Model
{
    protected $fillable = [
        'parent_id', 'jenis', 'nama', 'kod',
        'berulang', 'is_active', 'keterangan', 'susunan',
    ];

    protected function casts(): array
    {
        return [
            'berulang'  => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // ---- Relationships ----
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AccountCategory::class, 'parent_id')->orderBy('susunan')->orderBy('nama');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AccountEntry::class, 'category_id');
    }

    // ---- Scopes ----
    public function scopePendapatan(Builder $q): Builder
    {
        return $q->where('jenis', 'pendapatan');
    }

    public function scopePerbelanjaan(Builder $q): Builder
    {
        return $q->where('jenis', 'perbelanjaan');
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeUtama(Builder $q): Builder
    {
        return $q->whereNull('parent_id');
    }

    // ---- Helpers ----
    public function isParent(): bool
    {
        return $this->parent_id === null;
    }

    /** Nama penuh termasuk induk: "Aktiviti Perniagaan › Perniagaan 1" */
    public function namaPenuh(): string
    {
        return $this->parent ? "{$this->parent->nama} › {$this->nama}" : $this->nama;
    }

    /** Jumlah keseluruhan entri kategori ini (+ anak jika induk). */
    public function jumlah(): float
    {
        $ids = $this->isParent()
            ? $this->children()->pluck('id')->push($this->id)
            : collect([$this->id]);

        return (float) AccountEntry::whereIn('category_id', $ids)->sum('amaun');
    }
}
