<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Petugas Perpustakaan',
            'email' => 'admin@sipus.test',
            'password' => 'password',
        ]);

        $categories = Category::insert([
            ['name' => 'Teknologi', 'description' => 'Buku tentang teknologi informasi dan umum.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pemrograman', 'description' => 'Buku tentang bahasa dan teknik pemrograman.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Database', 'description' => 'Buku tentang sistem basis data.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bisnis', 'description' => 'Buku tentang manajemen dan bisnis.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Novel', 'description' => 'Kumpulan novel fiksi.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pendidikan', 'description' => 'Buku tentang dunia pendidikan.', 'created_at' => now(), 'updated_at' => now()],
        ]);
        unset($categories);

        $members = Member::insert([
            ['member_code' => 'MBR-0001', 'name' => 'Andi Pratama', 'gender' => 'L', 'phone' => '081234567801', 'address' => 'Jl. Merdeka No. 1, Bandung', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['member_code' => 'MBR-0002', 'name' => 'Budi Santoso', 'gender' => 'L', 'phone' => '081234567802', 'address' => 'Jl. Sudirman No. 2, Jakarta', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['member_code' => 'MBR-0003', 'name' => 'Citra Lestari', 'gender' => 'P', 'phone' => '081234567803', 'address' => 'Jl. Diponegoro No. 3, Yogyakarta', 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['member_code' => 'MBR-0004', 'name' => 'Dewi Anggraini', 'gender' => 'P', 'phone' => '081234567804', 'address' => 'Jl. Ahmad Yani No. 4, Surabaya', 'status' => 'tidak_aktif', 'created_at' => now(), 'updated_at' => now()],
        ]);
        unset($members);

        $booksData = [
            ['isbn' => '9780131103627', 'title' => 'The C Programming Language', 'author' => 'Brian Kernighan', 'publisher' => 'Prentice Hall', 'publication_year' => 1988, 'category' => 'Pemrograman', 'stock' => 10, 'rack_location' => 'Rak A-1', 'description' => 'Buku klasik yang menjadi rujukan utama untuk belajar bahasa C, ditulis langsung oleh pencipta bahasa C bersama Dennis Ritchie.'],
            ['isbn' => '9780262033848', 'title' => 'Introduction to Algorithms', 'author' => 'Thomas H. Cormen', 'publisher' => 'MIT Press', 'publication_year' => 2009, 'category' => 'Teknologi', 'stock' => 8, 'rack_location' => 'Rak A-2', 'description' => 'Referensi mendalam mengenai algoritma dan struktur data, lengkap dengan analisis kompleksitas yang mudah dipahami.'],
            ['isbn' => '9781118057324', 'title' => 'Buku Bisnis Modern', 'author' => 'John Maxwell', 'publisher' => 'Wiley', 'publication_year' => 2011, 'category' => 'Bisnis', 'stock' => 6, 'rack_location' => 'Rak B-2', 'description' => 'Wawasan strategi bisnis dan kepemimpinan untuk menghadapi dinamika pasar modern.'],
            ['isbn' => '9780201633610', 'title' => 'Design Patterns', 'author' => 'Erich Gamma', 'publisher' => 'Addison-Wesley', 'publication_year' => 1994, 'category' => 'Pemrograman', 'stock' => 5, 'rack_location' => 'Rak A-3', 'description' => 'Polap-pola desain perangkat lunak yang terkenal dengan istilah Gang of Four, wajib bagi pengembang aplikasi.'],
            ['isbn' => '9781491903070', 'title' => 'Fundamentals of Database Systems', 'author' => 'Ramez Elmasri', 'publisher' => 'Pearson', 'publication_year' => 2016, 'category' => 'Database', 'stock' => 7, 'rack_location' => 'Rak C-1', 'description' => 'Buku teks lengkap tentang perancangan, pemodelan, dan manajemen basis data relasional.'],
            ['isbn' => '9780553380163', 'title' => 'A Game of Thrones', 'author' => 'George R.R. Martin', 'publisher' => 'Bantam', 'publication_year' => 1996, 'category' => 'Novel', 'stock' => 4, 'rack_location' => 'Rak D-1', 'description' => 'Novel epik fantasi pembuka serial A Song of Ice and Fire tentang perebutan takhta di benua Westeros.'],
            ['isbn' => '9786022912140', 'title' => 'Pendidikan Karakter', 'author' => 'M. Furqon', 'publisher' => 'Erlangga', 'publication_year' => 2019, 'category' => 'Pendidikan', 'stock' => 2, 'rack_location' => 'Rak E-1', 'description' => 'Panduan penerapan pendidikan karakter di lingkungan sekolah dan keluarga.'],
            ['isbn' => '9780141034539', 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'publisher' => 'Pearson', 'publication_year' => 2008, 'category' => 'Pemrograman', 'stock' => 3, 'rack_location' => 'Rak A-4', 'description' => 'Prinsip-prinsip menulis kode yang bersih, mudah dibaca, dan mudah dipelihara oleh Uncle Bob.'],
            ['isbn' => null, 'title' => 'Belajar PHP untuk Pemula', 'author' => 'Tim SIPUS', 'publisher' => 'Andi Offset', 'publication_year' => 2022, 'category' => 'Pemrograman', 'stock' => 0, 'rack_location' => 'Rak A-5', 'description' => 'Panduan awal mempelajari bahasa pemrograman PHP dari dasar hingga membuat aplikasi sederhana.'],
        ];

        $categoryMap = Category::pluck('id', 'name');
        $bookModels = [];
        foreach ($booksData as $data) {
            $bookModels[] = Book::create([
                'isbn' => $data['isbn'],
                'title' => $data['title'],
                'author' => $data['author'],
                'publisher' => $data['publisher'],
                'publication_year' => $data['publication_year'],
                'category_id' => $categoryMap[$data['category']],
                'stock' => $data['stock'],
                'available_stock' => $data['stock'],
                'rack_location' => $data['rack_location'],
                'description' => $data['description'] ?? null,
            ]);
        }

        $loanService = app(LoanService::class);
        $member1 = Member::where('member_code', 'MBR-0001')->first();
        $member3 = Member::where('member_code', 'MBR-0003')->first();

        if ($member1 && isset($bookModels[0])) {
            $loan = $loanService->createLoan($member1, [
                ['book' => $bookModels[0], 'quantity' => 1],
            ], now()->subDays(5));
            $loanService->returnBook($loan, now()->subDays(1));
        }

        if ($member3 && isset($bookModels[1])) {
            $overdue = $loanService->createLoan($member3, [
                ['book' => $bookModels[1], 'quantity' => 2],
            ], now()->subDays(14));
            $overdue->update(['status' => Loan::STATUS_TERLAMBAT]);
        }

        if ($member1 && isset($bookModels[4])) {
            $loanService->createLoan($member1, [
                ['book' => $bookModels[4], 'quantity' => 1],
            ], now()->subDays(2));
        }
    }
}