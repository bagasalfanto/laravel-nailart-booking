<?php

namespace App\Http\Controllers\Dashboard\Nailist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nailist\StorePortfolioRequest;
use App\Http\Requests\Nailist\UpdatePortfolioRequest;
use App\Models\Portfolio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PortfolioController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $nailist = Auth::user()->nailist;

        if (! $nailist) {
            return redirect()->route('dashboard.home')
                ->with('toast', ['type' => 'warning', 'title' => 'Profil Belum Lengkap', 'message' => 'Profil nailist belum tersedia.']);
        }

        $portfolios = Portfolio::query()
            ->where('nailist_id', $nailist->id)
            ->latest()
            ->get();

        return view('dashboard.nailist.portfolios.index', compact('portfolios'));
    }

    public function create(): View
    {
        return view('dashboard.nailist.portfolios.create');
    }

    public function store(StorePortfolioRequest $request): RedirectResponse
    {
        $nailist = Auth::user()->nailist;
        $data = $request->validated();

        $path = $request->file('gambar')->store('portfolios', 'public');

        Portfolio::create([
            'nailist_id' => $nailist->id,
            'judul'      => $data['judul'],
            'deskripsi'  => $data['deskripsi'] ?? null,
            'gambar_url' => $path,
        ]);

        return redirect()
            ->route('dashboard.portfolio.index')
            ->with('toast', ['type' => 'success', 'title' => 'Portfolio Ditambahkan', 'message' => 'Portfolio baru berhasil disimpan.']);
    }

    public function edit(Portfolio $portfolio): View
    {
        $this->ensureOwner($portfolio);

        return view('dashboard.nailist.portfolios.edit', compact('portfolio'));
    }

    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio): RedirectResponse
    {
        $this->ensureOwner($portfolio);

        $data = $request->validated();

        $payload = [
            'judul'     => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ];

        if ($request->hasFile('gambar')) {
            // Hapus file lama jika tersimpan di disk public.
            if ($portfolio->gambar_url
                && ! str_starts_with($portfolio->gambar_url, 'http')
                && ! str_starts_with($portfolio->gambar_url, '/storage/dummy/')) {
                Storage::disk('public')->delete($portfolio->gambar_url);
            }

            $payload['gambar_url'] = $request->file('gambar')->store('portfolios', 'public');
        }

        $portfolio->update($payload);

        return redirect()
            ->route('dashboard.portfolio.index')
            ->with('toast', ['type' => 'success', 'title' => 'Perubahan Disimpan', 'message' => 'Portfolio berhasil diperbarui.']);
    }

    public function destroy(Portfolio $portfolio): RedirectResponse
    {
        $this->ensureOwner($portfolio);

        if ($portfolio->gambar_url
            && ! str_starts_with($portfolio->gambar_url, 'http')
            && ! str_starts_with($portfolio->gambar_url, '/storage/dummy/')) {
            Storage::disk('public')->delete($portfolio->gambar_url);
        }

        $portfolio->delete();

        return redirect()
            ->route('dashboard.portfolio.index')
            ->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Portfolio nailist berhasil dihapus.']);
    }

    private function ensureOwner(Portfolio $portfolio): void
    {
        $nailist = Auth::user()->nailist;

        if (! $nailist || $portfolio->nailist_id !== $nailist->id) {
            throw new NotFoundHttpException();
        }
    }
}
