<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function createCategory(): Category
    {
        return Category::create(['name' => 'Pemrograman']);
    }

    protected function actingAsPetugas(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    protected function validPayload(array $overrides = []): array
    {
        $payload = [
            'isbn' => '9781234567890',
            'title' => 'Buku Valid',
            'author' => 'Penulis Hebat',
            'publisher' => 'Penerbit Sejahtera',
            'publication_year' => 2020,
            'stock' => 10,
            'rack_location' => 'Rak A-1',
        ];

        if (! array_key_exists('category_id', $overrides)) {
            $overrides['category_id'] = $this->createCategory()->id;
        }

        return array_merge($payload, $overrides);
    }

    public function test_tambah_buku_data_valid(): void
    {
        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload())
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'title' => 'Buku Valid',
            'available_stock' => 10,
        ]);
    }

    public function test_tambah_buku_judul_kosong_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['title' => '']))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseMissing('books', ['author' => 'Penulis Hebat']);
    }

    public function test_tambah_buku_penulis_kosong_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['author' => '']))
            ->assertSessionHasErrors('author');
    }

    public function test_tambah_buku_stok_negatif_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['stock' => -1]))
            ->assertSessionHasErrors('stock');
    }

    public function test_tambah_buku_isbn_duplikat_gagal(): void
    {
        $category = $this->createCategory();
        Book::create([
            'isbn' => '9781111111111',
            'title' => 'Buku Pertama',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 1,
            'available_stock' => 1,
        ]);

        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['isbn' => '9781111111111', 'category_id' => $category->id]))
            ->assertSessionHasErrors('isbn');
    }

    public function test_tambah_buku_kategori_wajib_diisi(): void
    {
        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['category_id' => null]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_tambah_buku_tahun_terbit_tidak_valid(): void
    {
        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['publication_year' => 1200]))
            ->assertSessionHasErrors('publication_year');
    }

    public function test_edit_buku(): void
    {
        $category = $this->createCategory();
        $book = Book::create([
            'isbn' => '9782222222222',
            'title' => 'Judul Awal',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 5,
            'available_stock' => 5,
        ]);

        $payload = $this->validPayload([
            'isbn' => '9782222222222',
            'title' => 'Judul Update',
            'category_id' => $category->id,
            'stock' => 8,
        ]);

        $this->actingAsPetugas()
            ->put(route('books.update', $book), $payload)
            ->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Judul Update',
            'stock' => 8,
            'available_stock' => 8,
        ]);
    }

    public function test_edit_buku_stok_kurang_dari_dipinjam_gagal(): void
    {
        $category = $this->createCategory();
        $book = Book::create([
            'isbn' => '9783333333333',
            'title' => 'Buku Dipinjam',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 10,
            'available_stock' => 6,
        ]);

        $this->actingAsPetugas()
            ->put(route('books.update', $book), $this->validPayload([
                'isbn' => '9783333333333',
                'title' => 'Buku Dipinjam',
                'category_id' => $category->id,
                'stock' => 3,
            ]))
            ->assertSessionHasErrors('stock');
    }

    public function test_hapus_buku(): void
    {
        $category = $this->createCategory();
        $book = Book::create([
            'isbn' => null,
            'title' => 'Buku Dihapus',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 2,
            'available_stock' => 2,
        ]);

        $this->actingAsPetugas()
            ->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_cari_buku_dan_filter_kategori(): void
    {
        $kategoriA = $this->createCategory();
        $kategoriB = Category::create(['name' => 'Novel']);

        Book::create(['title' => 'Buku PHP', 'author' => 'X', 'category_id' => $kategoriA->id, 'stock' => 1, 'available_stock' => 1]);
        Book::create(['title' => 'Novel Cinta', 'author' => 'Y', 'category_id' => $kategoriB->id, 'stock' => 1, 'available_stock' => 1]);

        $response = $this->actingAsPetugas()
            ->get(route('books.index', ['category_id' => $kategoriA->id]))
            ->assertOk();

        $response->assertSee('Buku PHP');
        $response->assertDontSee('Novel Cinta');

        $this->actingAsPetugas()
            ->get(route('books.index', ['search' => 'Novel']))
            ->assertSee('Novel Cinta')
            ->assertDontSee('Buku PHP');
    }

    public function test_tambah_buku_dengan_sampul_berhasil(): void
    {
        Storage::fake('public');

        $cover = UploadedFile::fake()->image('cover.png', 200, 280);

        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload(['photo' => $cover]))
            ->assertRedirect(route('books.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('books', ['title' => 'Buku Valid', 'photo' => 'books/'.$cover->hashName()]);
        Storage::disk('public')->assertExists('books/'.$cover->hashName());
    }

    public function test_tambah_buku_sampul_bukan_gambar_gagal(): void
    {
        Storage::fake('public');

        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload([
                'photo' => UploadedFile::fake()->create('dokumen.pdf', 10),
            ]))
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('books', ['title' => 'Buku Valid']);
    }

    public function test_tambah_buku_sampul_terlalu_besar_gagal(): void
    {
        Storage::fake('public');

        $this->actingAsPetugas()
            ->post(route('books.store'), $this->validPayload([
                'photo' => UploadedFile::fake()->image('big.jpg')->size(3000),
            ]))
            ->assertSessionHasErrors('photo');
    }

    public function test_ganti_sampul_buku_menghapus_file_lama(): void
    {
        Storage::fake('public');

        $category = $this->createCategory();
        $book = Book::create([
            'title' => 'Buku Sampul',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 2,
            'available_stock' => 2,
            'photo' => 'books/lama.png',
        ]);

        $coverBaru = UploadedFile::fake()->image('baru.jpg', 300, 420);

        $this->actingAsPetugas()
            ->put(route('books.update', $book), $this->validPayload([
                'isbn' => null,
                'title' => 'Buku Sampul',
                'category_id' => $category->id,
                'photo' => $coverBaru,
            ]))
            ->assertRedirect(route('books.index'));

        Storage::disk('public')->assertMissing('books/lama.png');
        Storage::disk('public')->assertExists('books/'.$coverBaru->hashName());
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'photo' => 'books/'.$coverBaru->hashName(),
        ]);
    }

    public function test_hapus_sampul_buku_menghapus_file(): void
    {
        Storage::fake('public');

        $category = $this->createCategory();
        $book = Book::create([
            'title' => 'Buku Tanpa Sampul',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 2,
            'available_stock' => 2,
            'photo' => 'books/sampul.png',
        ]);

        $this->actingAsPetugas()
            ->put(route('books.update', $book), $this->validPayload([
                'isbn' => null,
                'title' => 'Buku Tanpa Sampul',
                'category_id' => $category->id,
                'remove_photo' => true,
            ]))
            ->assertRedirect(route('books.index'));

        Storage::disk('public')->assertMissing('books/sampul.png');
        $this->assertDatabaseHas('books', ['id' => $book->id, 'photo' => null]);
    }

    public function test_hapus_buku_menghapus_file_sampul(): void
    {
        Storage::fake('public');

        $category = $this->createCategory();
        $book = Book::create([
            'title' => 'Buku Dihapus',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 2,
            'available_stock' => 2,
            'photo' => 'books/akan-hilang.png',
        ]);

        $this->actingAsPetugas()
            ->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));

        Storage::disk('public')->assertMissing('books/akan-hilang.png');
    }

    public function test_halaman_detail_buku(): void
    {
        $category = $this->createCategory();
        $book = Book::create([
            'isbn' => null,
            'title' => 'Buku Detail Hebat',
            'author' => 'Penulis Detail',
            'category_id' => $category->id,
            'stock' => 4,
            'available_stock' => 3,
            'description' => 'Deskripsi menarik untuk dibaca.',
        ]);

        $this->actingAsPetugas()
            ->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('Buku Detail Hebat')
            ->assertSee('Deskripsi menarik untuk dibaca.')
            ->assertSee('3 tersisa', false);
    }
}