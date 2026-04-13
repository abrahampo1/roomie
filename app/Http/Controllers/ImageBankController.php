<?php

namespace App\Http\Controllers;

use App\Models\BankImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImageBankController extends Controller
{
    public function index(Request $request): View
    {
        $query = auth()->user()->bankImages()->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $images = $query->paginate(24);

        $categories = auth()->user()->bankImages()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('settings.image-bank', compact('images', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'title' => ['required', 'string', 'max:120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:60'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $maxImages = (int) config('services.roomie.bank_images_max_per_user', 100);
        $currentCount = $user->bankImages()->count();
        $incoming = count($request->file('images'));

        if ($currentCount + $incoming > $maxImages) {
            return back()->withErrors([
                'images' => "Solo puedes tener {$maxImages} imágenes. Tienes {$currentCount}, intentas subir {$incoming}.",
            ]);
        }

        $tags = $request->filled('tags')
            ? array_map('trim', explode(',', $request->input('tags')))
            : null;

        foreach ($request->file('images') as $file) {
            $ext = $file->extension();
            $filename = Str::uuid().".{$ext}";
            $path = "bank-images/{$user->id}/{$filename}";
            $file->storeAs("bank-images/{$user->id}", $filename, 'public');

            $dimensions = @getimagesize($file->getRealPath());

            $user->bankImages()->create([
                'title' => $request->input('title'),
                'alt_text' => $request->input('alt_text'),
                'category' => $request->input('category'),
                'tags' => $tags,
                'disk_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
            ]);
        }

        return back()->with('message', $incoming === 1 ? 'Imagen subida.' : "{$incoming} imágenes subidas.");
    }

    public function destroy(BankImage $bankImage): RedirectResponse
    {
        abort_unless($bankImage->user_id === auth()->id(), 403);

        $bankImage->delete();

        return back()->with('message', 'Imagen eliminada.');
    }
}
