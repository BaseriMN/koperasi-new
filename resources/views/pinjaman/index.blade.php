@extends('layouts.master')
@section('title', 'Permohonan Pinjaman')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div><h1>Permohonan Pinjaman</h1><p class="lead">Mohon pinjaman baharu atau semak permohonan sedia ada.</p></div>
    @if (auth()->user()->hasAnyRole(['ahli','super-user']))
        <a href="{{ route('pinjaman.create') }}" class="btn btn-gold">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Mohon Pinjaman
        </a>
    @endif
</div>

<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat"><div class="k">Menunggu</div><div class="v">{{ $stats['pending'] }}</div><div class="meta" style="color:var(--gold);">perlu kelulusan</div></div>
    <div class="stat"><div class="k">Diluluskan (bulan ini)</div><div class="v">{{ $stats['approved'] }}</div><div class="meta">▲ aktif</div></div>
    <div class="stat"><div class="k">Jumlah Menunggu</div><div class="v" style="font-size:26px;">RM {{ number_format($stats['requested'], 0) }}</div></div>
</div>

<div class="panel">
    <div class="panel-head"><h3>Senarai Permohonan</h3>
        @if ($canApprove)<span class="badge teal">Mod Kelulusan</span>@endif
    </div>
    <table>
        <thead><tr><th>Pemohon</th><th>Jumlah</th><th>Tempoh</th><th>Status</th><th style="text-align:right;">Tindakan</th></tr></thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td>
                        <div class="cell-main">
                            <div class="av">{{ strtoupper(substr($loan->user->name ?? '?', 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:600;">{{ $loan->user->name ?? '—' }}</div>
                                <div class="cell-sub">{{ Str::limit($loan->tujuan, 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:600;">RM {{ number_format($loan->amount, 2) }}</td>
                    <td>{{ $loan->tempoh }} bln</td>
                    <td>
                        @if ($loan->status === 'pending')<span class="badge gold"><span class="dot"></span>Menunggu</span>
                        @elseif ($loan->status === 'approved')<span class="badge ok"><span class="dot"></span>Diluluskan</span>
                        @else<span class="badge off"><span class="dot"></span>Ditolak</span>@endif
                    </td>
                    <td style="text-align:right;">
                        @if ($canApprove && $loan->status === 'pending')
                            <div style="display:inline-flex;gap:8px;">
                                <form method="POST" action="{{ route('pinjaman.decide', $loan) }}">
                                    @csrf <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-gold btn-sm" type="submit">Lulus</button>
                                </form>
                                <form method="POST" action="{{ route('pinjaman.decide', $loan) }}" data-confirm="Tolak permohonan ini?">
                                    @csrf <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-danger btn-sm" type="submit">Tolak</button>
                                </form>
                            </div>
                        @else
                            <span class="cell-sub">{{ $loan->reviewer->name ?? '—' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
                    <div>Tiada permohonan pinjaman lagi.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:18px;">{{ $loans->links() }}</div>
@endsection
