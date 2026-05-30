<?php

namespace App\Http\Controllers;

use App\Models\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountCategoryController extends Controller
{
    /**
     * Senarai kategori bagi satu jenis (pendapatan/perbelanjaan),
     * disusun induk + anak.
     */
    public function index(string $jenis)
    {
        $this->guardJenis($jenis);

        $categories = AccountCategory::where('jenis', $jenis)
            ->utama()
            ->with(['children' => fn ($q) => $q->withCount('entries')])
            ->withCount('entries')
            ->orderBy('susunan')->orderBy('nama')
            ->get();

        return view('akaun.kategori.index', compact('categories', 'jenis'));
    }

    public function create(string $jenis)
    {
        $this->guardJenis($jenis);

        // Calon induk: kategori utama yang aktif bagi jenis ini
        $parents = AccountCategory::where('jenis', $jenis)->utama()->aktif()
            ->orderBy('nama')->get(['id', 'nama']);

        return view('akaun.kategori.create', compact('jenis', 'parents'));
    }

    public function store(Request $request, string $jenis)
    {
        $this->guardJenis($jenis);

        $data = $this->validateCategory($request, $jenis);

        AccountCategory::create($data + ['jenis' => $jenis]);

        return redirect()->route('akaun.kategori.index', $jenis)
            ->with('success', 'Kategori berjaya ditambah.');
    }

    public function edit(string $jenis, AccountCategory $kategori)
    {
        $this->guardJenis($jenis);

        $parents = AccountCategory::where('jenis', $jenis)->utama()->aktif()
            ->where('id', '!=', $kategori->id)   // elak jadi induk sendiri
            ->orderBy('nama')->get(['id', 'nama']);

        return view('akaun.kategori.edit', compact('jenis', 'kategori', 'parents'));
    }

    public function update(Request $request, string $jenis, AccountCategory $kategori)
    {
        $this->guardJenis($jenis);

        $data = $this->validateCategory($request, $jenis, $kategori);

        $kategori->update($data);

        return redirect()->route('akaun.kategori.index', $jenis)
            ->with('success', 'Kategori berjaya dikemaskini.');
    }

    public function destroy(string $jenis, AccountCategory $kategori)
    {
        $this->guardJenis($jenis);

        // Tak boleh padam jika ada rekod atau ada sub-kategori — nyahaktif sahaja.
        if ($kategori->entries()->exists() || $kategori->children()->exists()) {
            $kategori->update(['is_active' => false]);

            return redirect()->route('akaun.kategori.index', $jenis)
                ->with('success', 'Kategori mempunyai rekod/sub — dinyahaktifkan, bukan dipadam.');
        }

        $kategori->delete();

        return redirect()->route('akaun.kategori.index', $jenis)
            ->with('success', 'Kategori berjaya dipadam.');
    }

    // ---- Helpers ----
    private function guardJenis(string $jenis): void
    {
        abort_unless(in_array($jenis, ['pendapatan', 'perbelanjaan'], true), 404);
    }

    private function validateCategory(Request $request, string $jenis, ?AccountCategory $kategori = null): array
    {
        return $request->validate([
            'nama'       => ['required', 'string', 'max:120'],
            'kod'        => ['nullable', 'string', 'max:30'],
            'parent_id'  => [
                'nullable',
                Rule::exists('account_categories', 'id')->where('jenis', $jenis)->whereNull('parent_id'),
            ],
            'berulang'   => ['boolean'],
            'is_active'  => ['boolean'],
            'susunan'    => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ]);
    }
}
