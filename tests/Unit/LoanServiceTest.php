<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Member;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LoanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoanService();
    }

    public static function fineProvider(): array
    {
        return [
            'tepat waktu' => ['2026-09-09', '2026-09-09', 0],
            'kembali lebih awal' => ['2026-09-07', '2026-09-09', 0],
            'terlambat 1 hari' => ['2026-09-10', '2026-09-09', 1000],
            'terlambat 3 hari' => ['2026-09-12', '2026-09-09', 3000],
            'terlambat 7 hari' => ['2026-09-16', '2026-09-09', 7000],
        ];
    }

    #[DataProvider('fineProvider')]
    public function test_calculate_fine(string $returnDate, string $dueDate, int $expected): void
    {
        $this->assertSame($expected, $this->service->calculateFine(
            Carbon::parse($returnDate),
            Carbon::parse($dueDate)
        ));
    }

    public function test_calculate_fine_tidak_terlambat_sama_dan_sebelum_jatuh_tempo(): void
    {
        $dueDate = Carbon::parse('2026-09-09');

        $this->assertSame(0, $this->service->calculateFine(Carbon::parse('2026-09-09'), $dueDate));
        $this->assertSame(0, $this->service->calculateFine(Carbon::parse('2026-09-08'), $dueDate));
    }

    public function test_calculate_fine_terlambat_hitung_hari_dan_denda(): void
    {
        $dueDate = Carbon::parse('2026-09-09');

        $this->assertSame(1000, $this->service->calculateFine(Carbon::parse('2026-09-10'), $dueDate));
        $this->assertSame(3000, $this->service->calculateFine(Carbon::parse('2026-09-12'), $dueDate));
    }

    public function test_check_book_availability_stok_cukup(): void
    {
        $book = new Book(['available_stock' => 5]);

        $this->assertTrue($this->service->checkBookAvailability($book, 1));
        $this->assertTrue($this->service->checkBookAvailability($book, 5));
    }

    public function test_check_book_availability_stok_kurang_atau_nol(): void
    {
        $book = new Book(['available_stock' => 2]);

        $this->assertFalse($this->service->checkBookAvailability($book, 3));
        $this->assertFalse($this->service->checkBookAvailability($book, 0));
        $this->assertFalse($this->service->checkBookAvailability($book, -1));

        $habis = new Book(['available_stock' => 0]);
        $this->assertFalse($this->service->checkBookAvailability($habis, 1));
    }

    public function test_validate_loan_valid(): void
    {
        $member = new Member(['status' => Member::STATUS_AKTIF]);
        $book = new Book(['title' => 'Buku A', 'available_stock' => 3]);

        $result = $this->service->validateLoan($member, [
            ['book' => $book, 'quantity' => 2],
        ]);

        $this->assertTrue($result['valid']);
        $this->assertCount(0, $result['errors']);
    }

    public function test_validate_loan_anggota_tidak_aktif_ditolak(): void
    {
        $member = new Member(['status' => Member::STATUS_TIDAK_AKTIF]);
        $book = new Book(['title' => 'Buku A', 'available_stock' => 3]);

        $result = $this->service->validateLoan($member, [
            ['book' => $book, 'quantity' => 1],
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('tidak aktif', $result['errors'][0]);
    }

    public function test_validate_loan_buku_tidak_tersedia_ditolak(): void
    {
        $member = new Member(['status' => Member::STATUS_AKTIF]);
        $book = new Book(['title' => 'Buku Habis', 'available_stock' => 0]);

        $result = $this->service->validateLoan($member, [
            ['book' => $book, 'quantity' => 1],
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('tidak tersedia', $result['errors'][0]);
    }

    public function test_validate_loan_kuantitas_nol_atau_negatif_ditolak(): void
    {
        $member = new Member(['status' => Member::STATUS_AKTIF]);
        $book = new Book(['title' => 'Buku A', 'available_stock' => 3]);

        $result = $this->service->validateLoan($member, [
            ['book' => $book, 'quantity' => 0],
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('lebih dari 0', $result['errors'][0]);
    }

    public function test_validate_loan_melebihi_stok_ditolak(): void
    {
        $member = new Member(['status' => Member::STATUS_AKTIF]);
        $book = new Book(['title' => 'Buku A', 'available_stock' => 2]);

        $result = $this->service->validateLoan($member, [
            ['book' => $book, 'quantity' => 3],
        ]);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('melebihi stok', $result['errors'][0]);
    }

    public function test_validate_loan_items_kosong_ditolak(): void
    {
        $member = new Member(['status' => Member::STATUS_AKTIF]);

        $result = $this->service->validateLoan($member, []);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('minimal satu buku', $result['errors'][0]);
    }

    public function test_create_loan_mengurangi_stok_dan_menyimpan_tanggal(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-TEST-1',
            'name' => 'Anggota Uji',
            'gender' => 'L',
            'status' => Member::STATUS_AKTIF,
        ]);

        $category = \App\Models\Category::create(['name' => 'Pemrograman']);
        $book = Book::create([
            'isbn' => 'TEST-0001',
            'title' => 'Buku Uji',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'stock' => 5,
            'available_stock' => 5,
        ]);

        $loan = $this->service->createLoan($member, [
            ['book' => $book, 'quantity' => 2],
            ['book' => $book, 'quantity' => 1],
        ], '2026-09-02');

        $this->assertSame(\App\Models\Loan::STATUS_DIPINJAM, $loan->status);
        $this->assertSame('2026-09-09', $loan->due_date->format('Y-m-d'));
        $this->assertSame(2, $book->fresh()->available_stock);
        $this->assertSame(3, $loan->items()->sum('quantity'));
    }

    public function test_create_loan_data_invalid_membatalkan_perubahan(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-TEST-2',
            'name' => 'Anggota Tidak Aktif',
            'gender' => 'P',
            'status' => Member::STATUS_TIDAK_AKTIF,
        ]);

        $category = \App\Models\Category::create(['name' => 'Novel']);
        $book = Book::create([
            'isbn' => 'TEST-0002',
            'title' => 'Buku Cadangan',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'stock' => 3,
            'available_stock' => 3,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->service->createLoan($member, [
                ['book' => $book, 'quantity' => 1],
            ]);
        } finally {
            $this->assertSame(3, $book->fresh()->available_stock);
        }
    }

    public function test_return_book_tidak_terlambat_denda_nol_dan_stok_kembali(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-TEST-3',
            'name' => 'Anggota Kembali Tepat',
            'gender' => 'L',
            'status' => Member::STATUS_AKTIF,
        ]);

        $category = \App\Models\Category::create(['name' => 'Bisnis']);
        $book = Book::create([
            'isbn' => 'TEST-0003',
            'title' => 'Buku Tepat Waktu',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'stock' => 4,
            'available_stock' => 4,
        ]);

        $loan = $this->service->createLoan($member, [
            ['book' => $book, 'quantity' => 2],
        ], '2026-09-02');

        $this->assertSame(2, $book->fresh()->available_stock);

        $result = $this->service->returnBook($loan, '2026-09-09');

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['fine']);
        $this->assertSame(0, $result['late_days']);
        $this->assertSame(4, $book->fresh()->available_stock);
        $this->assertSame('dikembalikan', $loan->fresh()->status);
        $this->assertSame('2026-09-09', $loan->fresh()->return_date->format('Y-m-d'));
    }

    public function test_return_book_terlambat_menghitung_denda_dan_mengembalikan_stok(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-TEST-4',
            'name' => 'Anggota Terlambat',
            'gender' => 'L',
            'status' => Member::STATUS_AKTIF,
        ]);

        $category = \App\Models\Category::create(['name' => 'Database']);
        $book = Book::create([
            'isbn' => 'TEST-0004',
            'title' => 'Buku Terlambat',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'stock' => 6,
            'available_stock' => 6,
        ]);

        $loan = $this->service->createLoan($member, [
            ['book' => $book, 'quantity' => 2],
        ], '2026-09-02');

        $this->assertSame(4, $book->fresh()->available_stock);

        $result = $this->service->returnBook($loan, '2026-09-12');

        $this->assertTrue($result['success']);
        $this->assertSame(3, $result['late_days']);
        $this->assertSame(3000, $result['fine']);
        $this->assertSame(6, $book->fresh()->available_stock);
        $this->assertSame('dikembalikan', $loan->fresh()->status);
        $this->assertEquals(3000, $loan->fresh()->total_fine);
    }

    public function test_return_book_yang_sudah_dikembalikan_ditolak(): void
    {
        $member = Member::create([
            'member_code' => 'MBR-TEST-5',
            'name' => 'Anggota Dua Kali',
            'gender' => 'L',
            'status' => Member::STATUS_AKTIF,
        ]);

        $category = \App\Models\Category::create(['name' => 'Pendidikan']);
        $book = Book::create([
            'isbn' => 'TEST-0005',
            'title' => 'Buku Dua Kali',
            'author' => 'Penulis',
            'category_id' => $category->id,
            'stock' => 5,
            'available_stock' => 5,
        ]);

        $loan = $this->service->createLoan($member, [
            ['book' => $book, 'quantity' => 1],
        ], '2026-09-02');

        $this->service->returnBook($loan, '2026-09-09');

        $result = $this->service->returnBook($loan, '2026-09-10');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('sudah dikembalikan', $result['message']);
        $this->assertSame(5, $book->fresh()->available_stock);
    }
}