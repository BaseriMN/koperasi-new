<?php

namespace App\Services;

use App\Models\AccountCategory;
use App\Models\AccountEntry;
use App\Models\DividendRun;
use App\Models\DividendShare;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DividendService
{
    /**
     * Kira semula peruntukan + untung boleh agih + jumlah dividen untuk satu run.
     * Tidak menyentuh lejar — hanya kemaskini nilai run & allocations.
     */
    public function kiraRingkasan(DividendRun $run): void
    {
        $untungBersih = (float) $run->untung_bersih;

        // 1. Kira setiap tabung & kemaskini amaun
        $jumlahPeruntukan = 0;
        foreach ($run->allocations as $alloc) {
            $amaun = $alloc->kiraAmaun($untungBersih);
            $alloc->update(['amaun' => $amaun]);
            $jumlahPeruntukan += $amaun;
        }

        // 2. Untung boleh agih = untung bersih - jumlah peruntukan
        $bolehAgih = max(0, round($untungBersih - $jumlahPeruntukan, 2));

        // 3. Jumlah dividen = untung boleh agih × peratus dividen
        $jumlahDividen = round($bolehAgih * ((float) $run->peratus_dividen / 100), 2);

        $run->update([
            'jumlah_peruntukan' => $jumlahPeruntukan,
            'untung_boleh_agih' => $bolehAgih,
            'jumlah_dividen'    => $jumlahDividen,
        ]);
    }

    /**
     * Jana / kemaskini bahagian dividen setiap ahli.
     * Mengekalkan saham_layak yang telah di-override manual.
     */
    public function janaBahagianAhli(DividendRun $run): void
    {
        $cutoff = $run->tarikh_cutoff->toDateString();

        // Saham layak setiap ahli = (masuk - keluar) di mana tarikh <= cutoff
        $members = Member::query()->get(['id']);

        // Kira saham auto setiap ahli (satu query agregat)
        $sahamAuto = $this->sahamLayakSemua($cutoff);

        DB::transaction(function () use ($run, $members, $sahamAuto) {
            foreach ($members as $m) {
                $auto = (float) ($sahamAuto[$m->id] ?? 0);

                $share = DividendShare::firstOrNew([
                    'dividend_run_id' => $run->id,
                    'member_id'       => $m->id,
                ]);

                // Kekalkan nilai override jika ada; jika tidak, guna auto
                $share->saham_auto = $auto;
                if (! $share->override) {
                    $share->saham_layak = $auto;
                }
                $share->save();
            }
        });

        $this->agihMengikutSaham($run);
    }

    /**
     * Agih jumlah dividen mengikut nisbah saham_layak setiap ahli.
     */
    public function agihMengikutSaham(DividendRun $run): void
    {
        $shares = $run->shares()->get();
        $jumlahSahamLayak = (float) $shares->sum('saham_layak');
        $jumlahDividen = (float) $run->jumlah_dividen;

        foreach ($shares as $share) {
            if ($jumlahSahamLayak > 0) {
                $peratus = ((float) $share->saham_layak / $jumlahSahamLayak) * 100;
                $amaun   = round($jumlahDividen * ((float) $share->saham_layak / $jumlahSahamLayak), 2);
            } else {
                $peratus = 0;
                $amaun   = 0;
            }

            $share->update([
                'peratus'       => round($peratus, 4),
                'amaun_dividen' => $amaun,
            ]);
        }
    }

    /**
     * Override saham_layak seorang ahli, kemudian agih semula.
     */
    public function overrideSaham(DividendShare $share, float $sahamLayak): void
    {
        $share->update([
            'saham_layak' => $sahamLayak,
            'override'    => true,
        ]);

        $this->agihMengikutSaham($share->run);
    }

    /**
     * Muktamadkan run:
     *  1. Rekod jumlah dividen sebagai PERBELANJAAN (satu entri akaun)
     *  2. Agih bahagian setiap ahli ke lejar SAHAM (transaksi masuk)
     *  3. Kunci status
     *
     * @param  int  $userId  pengguna yang memuktamadkan
     */
    public function muktamad(DividendRun $run, int $userId): void
    {
        if ($run->isMuktamad()) {
            return;
        }

        DB::transaction(function () use ($run, $userId) {
            // 1. Rekod perbelanjaan akaun
            $kategori = $this->kategoriDividen();
            AccountEntry::create([
                'category_id'       => $kategori->id,
                'jenis'             => 'perbelanjaan',
                'amaun'             => $run->jumlah_dividen,
                'tarikh'            => now()->toDateString(),
                'rujukan'           => 'DIV-' . $run->tahun,
                'penerima_pembayar' => 'Ahli Koperasi',
                'keterangan'        => "Pembahagian dividen tahun {$run->tahun}",
                'recorded_by'       => $userId,
            ]);

            // 2. Agih ke lejar saham setiap ahli (yang ada amaun > 0)
            foreach ($run->shares()->where('amaun_dividen', '>', 0)->with('member')->get() as $share) {
                $member = $share->member;
                if (! $member) {
                    continue;
                }

                $bakiSemasa = $member->bakiSaham();
                $member->transactions()->create([
                    'jenis'       => 'saham',
                    'arah'        => 'masuk',
                    'amaun'       => $share->amaun_dividen,
                    'baki'        => $bakiSemasa + (float) $share->amaun_dividen,
                    'sumber'      => 'dividen',
                    'rujukan'     => 'DIV-' . $run->tahun,
                    'keterangan'  => "Dividen tahun {$run->tahun}",
                    'recorded_by' => $userId,
                ]);
            }

            // 3. Kunci
            $run->update([
                'status'          => 'dimuktamadkan',
                'tarikh_muktamad' => now()->toDateString(),
            ]);
        });
    }

    /**
     * Saham layak semua ahli (id => jumlah), saham <= cutoff.
     */
    private function sahamLayakSemua(string $cutoff): array
    {
        // Σ(masuk) - Σ(keluar) per member, tarikh <= cutoff
        $rows = Transaction::query()
            ->where('jenis', 'saham')
            ->whereDate('created_at', '<=', $cutoff)
            ->selectRaw("member_id,
                SUM(CASE WHEN arah = 'masuk' THEN amaun ELSE 0 END) as masuk,
                SUM(CASE WHEN arah = 'keluar' THEN amaun ELSE 0 END) as keluar")
            ->groupBy('member_id')
            ->get();

        $hasil = [];
        foreach ($rows as $r) {
            $hasil[$r->member_id] = (float) $r->masuk - (float) $r->keluar;
        }

        return $hasil;
    }

    /**
     * Dapatkan / cipta kategori akaun "Dividen Kepada Ahli" (perbelanjaan).
     */
    private function kategoriDividen(): AccountCategory
    {
        return AccountCategory::firstOrCreate(
            ['jenis' => 'perbelanjaan', 'nama' => 'Dividen Kepada Ahli'],
            ['kod' => 'B-DVD', 'is_active' => true, 'parent_id' => null]
        );
    }
}
