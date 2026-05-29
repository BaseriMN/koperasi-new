@extends('layouts.master')
@section('title', 'Pindah Milik Saham')
@section('crumb', 'Lejar Transaksi')

@section('content')
<div class="page-head">
    <div><h1>Pindah Milik Saham</h1><p class="lead">Pindahkan nilai saham dari satu ahli ke ahli lain.</p></div>
    <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="alert success" style="background:rgba(31,111,92,.08);border-color:rgba(31,111,92,.22);color:var(--teal-deep);">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7z"/></svg>
    <span>Sistem akan menjana 2 catatan lejar automatik: <strong>keluar</strong> dari pemberi & <strong>masuk</strong> ke penerima.</span>
</div>

<div class="panel" style="max-width:640px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('saham.pindah') }}">
            @csrf
            <div class="field">
                <label>Daripada (Pemberi)</label>
                <select class="select" name="from_member_id" required>
                    <option value="">— Pilih ahli —</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" {{ (string) old('from_member_id') === (string) $m->id ? 'selected' : '' }}>{{ $m->no_ahli }} — {{ $m->nama }}</option>
                    @endforeach
                </select>
                @error('from_member_id') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Kepada (Penerima)</label>
                <select class="select" name="to_member_id" required>
                    <option value="">— Pilih ahli —</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" {{ (string) old('to_member_id') === (string) $m->id ? 'selected' : '' }}>{{ $m->no_ahli }} — {{ $m->nama }}</option>
                    @endforeach
                </select>
                @error('to_member_id') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Amaun Saham (RM)</label>
                <input class="input" type="number" name="amaun" min="0.01" step="0.01" value="{{ old('amaun') }}" required>
                @error('amaun') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="grid grid-2">
                <div class="field">
                    <label>Tarikh Pindah</label>
                    <input class="input" type="date" name="tarikh_pindah" value="{{ old('tarikh_pindah', now()->toDateString()) }}" required>
                    @error('tarikh_pindah') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>Sebab <span class="hint">(pilihan)</span></label>
                    <input class="input" name="sebab" value="{{ old('sebab') }}" placeholder="Hadiah / Warisan">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit" data-confirm="Sahkan pindah milik saham ini?">Proses Pindah</button>
                <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
