@extends('layouts.master')
@section('title', 'Laporan Audit')
@section('crumb', 'Pengurusan')

@section('content')
<div class="page-head">
    <div><h1>Laporan Audit</h1><p class="lead">Semakan rekod kewangan koperasi untuk juruaudit.</p></div>
    <button class="btn btn-ghost" onclick="window.print()">
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-4a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
        Cetak
    </button>
</div>
<div class="grid grid-3" style="margin-bottom:24px;">
    <div class="stat"><div class="k">Jumlah Simpanan</div><div class="v" style="font-size:24px;">RM {{ number_format($stats['simpanan'], 0) }}</div><div class="meta">{{ $stats['rekod_simpanan'] }} rekod</div></div>
    <div class="stat"><div class="k">Pinjaman Diluluskan</div><div class="v" style="font-size:24px;">RM {{ number_format($stats['pinjaman_lulus'], 0) }}</div><div class="meta">{{ $stats['rekod_pinjaman'] }} permohonan</div></div>
    <div class="stat"><div class="k">Pinjaman Menunggu</div><div class="v">{{ $stats['pinjaman_pending'] }}</div><div class="meta" style="color:var(--gold);">belum diproses</div></div>
</div>
<div class="panel">
    <div class="panel-head"><h3>Rekod Kewangan</h3><span class="badge gold">Akses Juruaudit</span></div>
    <table>
        <thead><tr><th>Rujukan</th><th>Kategori</th><th>Amaun</th><th>Tarikh</th><th>Status</th></tr></thead>
        <tbody>
            @forelse ($records as $r)
                <tr>
                    <td style="font-weight:600;">{{ $r['ref'] }}</td>
                    <td>{{ $r['kategori'] }}</td>
                    <td>RM {{ number_format($r['amaun'], 2) }}</td>
                    <td class="cell-sub">{{ optional($r['tarikh'])->translatedFormat('d M Y') }}</td>
                    <td><span class="badge ok"><span class="dot"></span>Disahkan</span></td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h12l4 4v12H4z"/><path d="M8 13l2.5 2.5L16 10"/></svg>
                    <div>Tiada rekod kewangan untuk diaudit.</div>
                </div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
