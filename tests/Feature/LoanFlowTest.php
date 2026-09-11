<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsPetugas(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    protected function createFixtures(string $memberStatus = Member::STATUS_AKTIF, int $stock = 5, int $available = 5): array
    {
        $member = Member::create([
            'member_code' => 'MBR-FLOW-1',
            'name' => 'Anggota Flow',
            'gender' => 'L',
            'status' => $memberStatus,
        ]);

        $category = Category::create(['name' => 'Teknologi']);
        $book = Book::create([
            'isbn' => '978-FLOW-1',
            'title' => 'Buku Flow',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'stock' => $stock,
            'available_stock' => $available,
        ]);

        return [$member, $book];
    }

    protected function loanPayload(int $memberId, array $items): array
    {
        return [
            'member_id' => $memberId,
            'loan_date' => '2026-09-02',
            'loan_days' => 7,
            'items' => $items,
        ];
    }

    public function test_peminjaman_valid_stok_berkurang(): void
    {
        [$member, $book] = $this->createFixtures();
        $initialAvailable = $book->available_stock;

        $this->actingAsPetugas()
            ->post(route('loans.store'), $this->loanPayload($member->id, [
                ['book_id' => $book->id, 'quantity' => 2],
            ]))
            ->assertRedirect(route('loans.create'))
            ->assertSessionHas('success');

        $this->assertSame($initialAvailable - 2, $book->fresh()->available_stock);
        $loan = Loan::where('member_id', $member->id)->first();
        $this->assertNotNull($loan);
        $this->assertSame(Loan::STATUS_DIPINJAM, $loan->status);
        $this->assertSame('2026-09-09', $loan->due_date->format('Y-m-d'));
        $this->assertDatabaseHas('loan_items', ['book_id' => $book->id, 'quantity' => 2]);
    }

    public function test_peminjaman_anggota_tidak_aktif_gagal(): void
    {
        [$member, $book] = $this->createFixtures(Member::STATUS_TIDAK_AKTIF);

        $this->actingAsPetugas()
            ->post(route('loans.store'), $this->loanPayload($member->id, [
                ['book_id' => $book->id, 'quantity' => 1],
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('loans', ['member_id' => $member->id]);
    }

    public function test_peminjaman_buku_stok_habis_gagal(): void
    {
        [$member, $book] = $this->createFixtures(stock: 0, available: 0);

        $this->actingAsPetugas()
            ->post(route('loans.store'), $this->loanPayload($member->id, [
                ['book_id' => $book->id, 'quantity' => 1],
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('loans', ['member_id' => $member->id]);
        $this->assertSame(0, $book->fresh()->available_stock);
    }

    public function test_peminjaman_melebihi_stok_gagal(): void
    {
        [$member, $book] = $this->createFixtures(stock: 3, available: 3);

        $this->actingAsPetugas()
            ->post(route('loans.store'), $this->loanPayload($member->id, [
                ['book_id' => $book->id, 'quantity' => 5],
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('loans', ['member_id' => $member->id]);
        $this->assertSame(3, $book->fresh()->available_stock);
    }

    public function test_peminjaman_stok_tidak_mencukupi_kombinasi_gagal(): void
    {
        [$member, $book] = $this->createFixtures(stock: 4, available: 4);

        $this->actingAsPetugas()
            ->post(route('loans.store'), $this->loanPayload($member->id, [
                ['book_id' => $book->id, 'quantity' => 3],
                ['book_id' => $book->id, 'quantity' => 3],
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing('loans', ['member_id' => $member->id]);
        $this->assertSame(4, $book->fresh()->available_stock);
    }

    public function test_peminjaman_kuantitas_nol_gagal(): void
    {
        [$member, $book] = $this->createFixtures();

        $this->actingAsPetugas()
            ->post(route('loans.store'), $this->loanPayload($member->id, [
                ['book_id' => $book->id, 'quantity' => 0],
            ]))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertDatabaseMissing('loans', ['member_id' => $member->id]);
    }

    public function test_peminjaman_data_wajib_kosong_gagal(): void
    {
        $this->actingAsPetugas()
            ->post(route('loans.store'), [
                'member_id' => null,
                'loan_date' => '',
                'loan_days' => '',
                'items' => [],
            ])
            ->assertSessionHasErrors(['member_id']);
    }

    public function test_pengembalian_tepat_waktu_denda_nol_stok_kembali(): void
    {
        [$member, $book] = $this->createFixtures();
        $service = app(LoanService::class);

        $loan = $service->createLoan($member, [
            ['book' => $book, 'quantity' => 2],
        ], '2026-09-02');

        $this->assertSame(3, $book->fresh()->available_stock);

        $this->actingAsPetugas()
            ->post(route('returns.process', $loan), ['return_date' => '2026-09-09'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $loan->fresh();
        $this->assertSame('2026-09-09', $fresh->return_date->format('Y-m-d'));
        $this->assertSame(Loan::STATUS_DIKEMBALIKAN, $fresh->status);
        $this->assertEquals(0, $fresh->total_fine);
        $this->assertSame(5, $book->fresh()->available_stock);
    }

    public function test_pengembalian_terlambat_denda_dihitung(): void
    {
        [$member, $book] = $this->createFixtures();
        $service = app(LoanService::class);

        $loan = $service->createLoan($member, [
            ['book' => $book, 'quantity' => 1],
        ], '2026-09-02');

        $this->actingAsPetugas()
            ->post(route('returns.process', $loan), ['return_date' => '2026-09-12'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $loan->fresh();
        $this->assertSame(Loan::STATUS_DIKEMBALIKAN, $fresh->status);
        $this->assertEquals(3000, $fresh->total_fine);
        $this->assertSame(5, $book->fresh()->available_stock);
    }

    public function test_pengembalian_transaksi_sudah_dikembalikan_gagal(): void
    {
        [$member, $book] = $this->createFixtures();
        $service = app(LoanService::class);

        $loan = $service->createLoan($member, [
            ['book' => $book, 'quantity' => 1],
        ], '2026-09-02');

        $service->returnBook($loan, '2026-09-09');

        $this->actingAsPetugas()
            ->post(route('returns.process', $loan), ['return_date' => '2026-09-10'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(5, $book->fresh()->available_stock);
    }

    public function test_riwayat_transaksi_menampilkan_dan_filter_status(): void
    {
        [$member, $book] = $this->createFixtures();
        $service = app(LoanService::class);

        $returned = $service->createLoan($member, [['book' => $book, 'quantity' => 1]], '2026-09-02');
        $service->returnBook($returned, '2026-09-09');

        $active = $service->createLoan($member, [['book' => $book, 'quantity' => 1]], '2026-09-10');

        $this->actingAsPetugas()
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('#'.$active->id)
            ->assertSee('#'.$returned->id);

        $response = $this->actingAsPetugas()
            ->get(route('transactions.index', ['status' => 'dipinjam']));
        $response->assertOk()
            ->assertSee('#'.$active->id)
            ->assertDontSeeHtml('09-09-2026');
    }

    public function test_laporan_menampilkan_data_dengan_filter_tanggal(): void
    {
        [$member, $book] = $this->createFixtures();
        $service = app(LoanService::class);

        $service->createLoan($member, [['book' => $book, 'quantity' => 1]], '2026-08-10');

        $this->actingAsPetugas()
            ->get(route('reports.index', ['date_from' => '2026-09-01', 'date_to' => '2026-09-30']))
            ->assertOk();

        $this->actingAsPetugas()
            ->get(route('reports.export-csv', ['date_from' => '2026-09-01', 'date_to' => '2026-09-30']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}