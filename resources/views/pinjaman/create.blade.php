@extends('layouts.master')
@section('title', 'Mohon Pinjaman')
@section('crumb', 'Permohonan Pinjaman')

@section('content')
<div class="page-head">
    <div><h1>Mohon Pinjaman</h1><p class="lead">Lengkapkan butiran permohonan pinjaman anda.</p></div>
    <a href="{{ route('pinjaman.index') }}" class="btn btn-ghost">Kembali</a>
</div>
<div class="panel" style="max-width:640px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('pinjaman.store') }}">
            @csrf
            <div class="field">
                <label>Jumlah Pinjaman (RM)</label>
                <input class="input" type="number" name="amount" min="100" step="50" value="{{ old('amount') }}" required>
                @error('amount') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Tempoh Bayaran (bulan)</label>
                <input class="input" type="number" name="tempoh" min="1" max="120" value="{{ old('tempoh') }}" required>
                @error('tempoh') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Tujuan Pinjaman</label>
                <textarea class="textarea" name="tujuan" required>{{ old('tujuan') }}</textarea>
                @error('tujuan') <div class="err">{{ $message }}</div> @enderror
            </div>
            <div class="form-actions">
                <button class="btn btn-gold" type="submit">Hantar Permohonan</button>
                <a href="{{ route('pinjaman.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
