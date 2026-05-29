@extends('layouts.master')
@section('title', 'Pindah Milik Keahlian')
@section('crumb', 'Keahlian')

@section('content')
<div class="page-head">
    <div>
        <h1>Pindah Milik Keahlian</h1>
        <p class="lead">No. Ahli <span class="badge gold" style="font-family:'Fraunces',serif;">{{ $member->no_ahli }}</span> akan <strong>kekal</strong>. Hanya pemilik & maklumat peribadi bertukar.</p>
    </div>
    <a href="{{ route('members.show', $member) }}" class="btn btn-ghost">Kembali</a>
</div>

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="panel">
        <div class="panel-head"><h3>Pemilik Semasa</h3></div>
        <div class="panel-body">
            <div class="field"><label>Nama</label><div>{{ $member->nama }}</div></div>
            <div class="field"><label>No. KP</label><div>{{ $member->no_kp ?? '—' }}</div></div>
            <div class="field" style="margin-bottom:0;"><label>Telefon</label><div>{{ $member->telefon ?? '—' }}</div></div>
        </div>
    </div>
    <div class="panel" style="border-color:var(--gold-soft);">
        <div class="panel-head"><h3>Pemilik Baharu</h3><span class="badge gold">Baharu</span></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('member.pindah', $member) }}">
                @csrf
                <div class="field">
                    <label>Nama Pemilik Baharu</label>
                    <input class="input" name="to_nama" value="{{ old('to_nama') }}" required>
                    @error('to_nama') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label>No. KP</label>
                    <input class="input" name="to_no_kp" value="{{ old('to_no_kp') }}">
                </div>
                <div class="field">
                    <label>Telefon</label>
                    <input class="input" name="to_telefon" value="{{ old('to_telefon') }}">
                </div>
                <div class="field">
                    <label>Akaun Login <span class="hint">(pilihan)</span></label>
                    <select class="select" name="to_user_id">
                        <option value="">— Tiada akaun —</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ (string) old('to_user_id') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Alamat</label>
                    <textarea class="textarea" name="to_alamat">{{ old('to_alamat') }}</textarea>
                </div>
                <div class="grid grid-2">
                    <div class="field">
                        <label>Tarikh Pindah</label>
                        <input class="input" type="date" name="tarikh_pindah" value="{{ old('tarikh_pindah', now()->toDateString()) }}" required>
                        @error('tarikh_pindah') <div class="err">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label>Sebab <span class="hint">(pilihan)</span></label>
                        <input class="input" name="sebab" value="{{ old('sebab') }}" placeholder="Kematian / Serahan">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-gold" type="submit" data-confirm="Sahkan pindah milik keahlian {{ $member->no_ahli }}? Saham & simpanan kekal pada nombor ahli ini.">Proses Pindah Milik</button>
                    <a href="{{ route('members.show', $member) }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
