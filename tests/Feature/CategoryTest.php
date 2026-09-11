<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsPetugas(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    public function test_tambah_kategori_valid(): void
    {
        $this->actingAsPetugas()
            ->post(route('categories.store'), [
                'name' => 'Teknologi',
                'description' => 'Buku teknologi',
            ])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Teknologi']);
    }

    public function test_tambah_kategori_nama_kosong_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('categories.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_tambah_kategori_nama_duplikat_gagal(): void
    {
        Category::create(['name' => 'Database']);

        $this->actingAsPetugas()
            ->post(route('categories.store'), ['name' => 'Database'])
            ->assertSessionHasErrors('name');
    }

    public function test_edit_kategori(): void
    {
        $category = Category::create(['name' => 'Lama']);

        $this->actingAsPetugas()
            ->put(route('categories.update', $category), ['name' => 'Baru'])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Baru']);
    }

    public function test_hapus_kategori_yang_memiliki_buku_gagal(): void
    {
        $category = Category::create(['name' => 'Pemrograman']);
        Book::create([
            'title' => 'Buku Kategori',
            'author' => 'A',
            'category_id' => $category->id,
            'stock' => 1,
            'available_stock' => 1,
        ]);

        $this->actingAsPetugas()
            ->delete(route('categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_hapus_kategori_tanpa_buku_berhasil(): void
    {
        $category = Category::create(['name' => 'Kosong']);

        $this->actingAsPetugas()
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}