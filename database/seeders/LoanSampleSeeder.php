<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoanSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (Loan::exists()) {
            return;
        }

        // Loan terikat pada user (pemohon). Ambil beberapa user / member sedia ada.
        $pelulus = User::whereHas('roles', fn ($q) => $q->where('slug', 'super-user'))->value('id');

        // Guna user pertama yang ada sebagai pemohon contoh (jika tiada member-user link)
        $users = User::limit(3)->pluck('id');
        if ($users->isEmpty()) {
            return; // tiada user untuk dijadikan pemohon
        }

        $samples = [
            ['amount' => 5000,  'tempoh' => 24, 'tujuan' => 'Pembelian peralatan rumah',     'status' => 'approved'],
            ['amount' => 12000, 'tempoh' => 36, 'tujuan' => 'Modal perniagaan kecil',         'status' => 'pending'],
            ['amount' => 3000,  'tempoh' => 12, 'tujuan' => 'Perbelanjaan persekolahan anak', 'status' => 'approved'],
            ['amount' => 8000,  'tempoh' => 24, 'tujuan' => 'Pembaikan kenderaan',            'status' => 'rejected'],
        ];

        foreach ($samples as $i => $s) {
            $loan = Loan::create([
                'user_id'     => $users[$i % $users->count()],
                'amount'      => $s['amount'],
                'tempoh'      => $s['tempoh'],
                'tujuan'      => $s['tujuan'],
                'status'      => $s['status'],
                'catatan'     => $s['status'] === 'rejected' ? 'Baki saham tidak mencukupi' : null,
                'reviewed_by' => $s['status'] === 'pending' ? null : $pelulus,
                'reviewed_at' => $s['status'] === 'pending' ? null : now(),
            ]);
        }
    }
}
