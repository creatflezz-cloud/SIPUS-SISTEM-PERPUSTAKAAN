<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_katalog_dapat_diakses_tanpa_login(): void
    {
        $category = Category::create(['name' => 'Novel']);
        Book::create([
            'title' => 'Buku Katalog Terbuka',
            'author' => 'Penulis X',
            'category_id' => $category->id,
            'stock' => 3,
            'available_stock' => 3,
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Buku Katalog Terbuka');
    }

    public function test_katalog_dapat_difilter_kategori(): void
    {
        $kategoriA = Category::create(['name' => 'Database']);
        $kategoriB = Category::create(['name' => 'Novel']);

        Book::create(['title' => 'Belajar MySQL', 'author' => 'A', 'category_id' => $kategoriA->id, 'stock' => 1, 'available_stock' => 1]);
        Book::create(['title' => 'Novel Fiksi', 'author' => 'B', 'category_id' => $kategoriB->id, 'stock' => 1, 'available_stock' => 1]);

        $this->get(route('catalog.index', ['category_id' => $kategoriA->id]))
            ->assertOk()
            ->assertSee('Belajar MySQL')
            ->assertDontSee('Novel Fiksi');
    }

    public function test_katalog_pencarian_judul(): void
    {
        $category = Category::create(['name' => 'Pemrograman']);
        Book::create(['title' => 'Laravel Dasar', 'author' => 'A', 'category_id' => $category->id, 'stock' => 1, 'available_stock' => 1]);

        $this->get(route('catalog.index', ['search' => 'Laravel']))
            ->assertOk()
            ->assertSee('Laravel Dasar');
    }

    public function test_halaman_detail_katalog(): void
    {
        $category = Category::create(['name' => 'Bisnis']);
        $book = Book::create([
            'title' => 'Buku Inspirasi Bisnis',
            'author' => 'Penulis Bisnis',
            'category_id' => $category->id,
            'stock' => 5,
            'available_stock' => 5,
            'description' => 'Panduan membangun usaha dari nol.',
        ]);

        $this->get(route('catalog.show', $book))
            ->assertOk()
            ->assertSee('Buku Inspirasi Bisnis')
            ->assertSee('Panduan membangun usaha dari nol.');
    }
}