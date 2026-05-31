@extends('layouts.master')
@section('title', 'Lejar Transaksi')
@section('crumb', 'Simpanan & Saham')

@section('content')
<div class="page-head">
    <div><h1>Lejar Transaksi</h1><p class="lead">Rekod penuh keluar-masuk saham & simpanan ahli.</p></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('saham.pindah.form') }}" class="btn btn-ghost">Pindah Saham</a>
        <a href="{{ route('transaksi.create') }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Rekod Transaksi
        </a>
    </div>
</div>

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-body" style="padding:16px 22px;">
        <form method="GET" action="{{ route('transaksi.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;">
            <select class="select" name="member_id" style="flex:1;min-width:200px;">
                <option value="">Semua ahli</option>
                @foreach ($members as $m)
                    <option value="{{ $m->id }}" {{ (string) request('member_id') === (string) $m->id ? 'selected' : '' }}>{{ $m->no_ahli }} — {{ $m->nama }}</option>
                @endforeach
            </select>
            <select class="select" name="jenis" style="max-width:180px;">
                <option value="">Semua jenis</option>
                <option value="saham" {{ request('jenis')==='saham' ? 'selected' : '' }}>Saham</option>
                <option value="simpanan" {{ request('jenis')==='simpanan' ? 'selected' : '' }}>Simpanan</option>
            </select>
            <button class="btn btn-ghost" type="submit">Tapis</button>
            @if (request('member_id') || request('jenis'))
                <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Transaksi</h3><span class="badge">{{ $transactions->total() }} rekod</span></div>
    <table>
        <thead><tr><th>Tarikh</th><th>Ahli</th><th>Jenis</th><th>Arah</th><th>Amaun</th><th>Baki</th><th>Sumber</th></tr></thead>
        <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td class="cell-sub">{{ $t->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $t->member->no_ahli ?? '—' }}</div>
                        <div class="cell-sub">{{ $t->member->nama ?? '' }}</div>
                    </td>
                    <td>@if($t->jenis==='saham')<span class="badge gold">Saham</span>@else<span class="badge teal">Simpanan</span>@endif</td>
                    <td>@if($t->arah==='masuk')<span class="badge ok">Masuk</span>@else<span class="badge off">Keluar</span>@endif</td>
                    <td style="font-weight:600;">RM {{ number_format($t->amaun, 2) }}</td>
                    <td class="cell-sub">RM {{ number_format($t->baki, 2) }}</td>
                    <td class="cell-sub">{{ $t->sumber }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3v18M7 8h7a3 3 0 010 6H6"/></svg>
                    <div>Tiada transaksi lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:18px;">{{ $transactions->links() }}</div>
@endsection
