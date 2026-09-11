<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::query()->with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('stock_status') && in_array($request->input('stock_status'), ['available', 'low', 'out'], true)) {
            $query->where(function ($q) use ($request) {
                $status = $request->input('stock_status');
                if ($status === 'available') {
                    $q->where('available_stock', '>', 0);
                } elseif ($status === 'low') {
                    $q->where('available_stock', '>', 0)->where('available_stock', '<=', 5);
                } else {
                    $q->where('available_stock', 0);
                }
            });
        }

        $books = $query->latest()->paginate(10);
        $categories = Category::orderBy('name')->get();

        return view('books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('books.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['available_stock'] = $data['stock'];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('books', 'public');
        }

        Book::create($data);

        return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Book $book): View
    {
        $book->load('category');

        $related = Book::query()
            ->where('category_id', $book->category_id)
            ->whereKeyNot($book->id)
            ->where('available_stock', '>', 0)
            ->take(4)
            ->get();

        return view('books.show', compact('book', 'related'));
    }

    public function edit(Book $book): View
    {
        $categories = Category::orderBy('name')->get();

        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $this->validated($request, $book);

        $borrowed = $book->stock - $book->available_stock;
        $data['available_stock'] = $data['stock'] - $borrowed;

        if ($data['available_stock'] < 0) {
            return back()->withInput()->withErrors(['stock' => 'Stok tidak boleh kurang dari jumlah buku yang sedang dipinjam.']);
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($book->photo);
            $data['photo'] = $request->file('photo')->store('books', 'public');
        } elseif ($request->boolean('remove_photo') && $book->photo) {
            $this->deletePhoto($book->photo);
            $data['photo'] = null;
        }

        $book->update($data);

        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $borrowed = $book->stock - $book->available_stock;
        if ($borrowed > 0) {
            return back()->with('error', 'Buku tidak dapat dihapus karena sedang dipinjam.');
        }

        $this->deletePhoto($book->photo);
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus.');
    }

    private function validated(Request $request, ?Book $book = null): array
    {
        $data = $request->validate([
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book)],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'category_id' => ['required', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'rack_location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'author.required' => 'Penulis wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'isbn.unique' => 'ISBN sudah digunakan.',
            'photo.image' => 'File sampul harus berupa gambar.',
            'photo.mimes' => 'Sampul harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran sampul maksimal 2 MB.',
        ]);

        unset($data['photo'], $data['remove_photo']);

        return $data;
    }

    private function deletePhoto(?string $photo): void
    {
        if ($photo) {
            Storage::disk('public')->delete($photo);
        }
    }
}