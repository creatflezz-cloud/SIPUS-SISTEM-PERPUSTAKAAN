<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public const FINE_PER_DAY = 1000;

    public const DEFAULT_LOAN_DAYS = 7;

    /**
     * Menghitung denda keterlambatan.
     *
     * Denda = jumlah hari terlambat x Rp1.000.
     * Jika tidak terlambat, denda = Rp0.
     */
    public function calculateFine(CarbonInterface $returnDate, CarbonInterface $dueDate): int
    {
        if ($returnDate->lessThanOrEqualTo($dueDate)) {
            return 0;
        }

        $lateDays = abs($returnDate->startOfDay()->diffInDays($dueDate->startOfDay()));

        return $lateDays * self::FINE_PER_DAY;
    }

    /**
     * Memastikan jumlah buku yang diminta tersedia.
     */
    public function checkBookAvailability(Book $book, int $quantity): bool
    {
        return $quantity > 0 && $book->available_stock >= $quantity;
    }

    /**
     * Memvalidasi transaksi peminjaman.
     *
     * $items berbentuk:
     *   [ ['book' => Book, 'quantity' => int], ... ]
     *
     * Mengembalikan ['valid' => bool, 'errors' => array].
     */
    public function validateLoan(Member $member, array $items): array
    {
        $errors = [];

        if (! $member->isActive()) {
            $errors[] = 'Anggota tidak aktif, tidak dapat melakukan peminjaman.';
        }

        if (empty($items)) {
            $errors[] = 'Pilih minimal satu buku untuk dipinjam.';
        }

        $totals = [];
        foreach ($items as $entry) {
            $book = $entry['book'];
            $key = $book->id ?? spl_object_id($book);
            $totals[$key] ??= ['book' => $book, 'quantity' => 0];
            $totals[$key]['quantity'] += (int) $entry['quantity'];
        }

        foreach ($totals as $entry) {
            $book = $entry['book'];
            $quantity = $entry['quantity'];

            if ($quantity <= 0) {
                $errors[] = "Jumlah peminjaman buku '{$book->title}' harus lebih dari 0.";
            }

            if ($book->available_stock <= 0) {
                $errors[] = "Buku '{$book->title}' tidak tersedia.";
            }

            if ($quantity > $book->available_stock) {
                $errors[] = "Jumlah peminjaman '{$book->title}' melebihi stok tersedia ({$book->available_stock}).";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Membuat transaksi peminjaman dan mengurangi stok tersedia.
     */
    public function createLoan(Member $member, array $items, CarbonInterface|string|null $loanDate = null, int $loanDays = self::DEFAULT_LOAN_DAYS): Loan
    {
        $validation = $this->validateLoan($member, $items);

        if (! $validation['valid']) {
            throw new \InvalidArgumentException(implode(' ', $validation['errors']));
        }

        $loanDateCarbon = \Illuminate\Support\Carbon::parse($loanDate ?? now());
        $dueDate = $loanDateCarbon->copy()->addDays($loanDays);

        return DB::transaction(function () use ($member, $items, $loanDateCarbon, $dueDate) {
            $loan = Loan::create([
                'member_id' => $member->id,
                'loan_date' => $loanDateCarbon,
                'due_date' => $dueDate,
                'status' => Loan::STATUS_DIPINJAM,
                'total_fine' => 0,
            ]);

            foreach ($items as $entry) {
                $book = $entry['book'];
                $quantity = (int) $entry['quantity'];

                LoanItem::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book->id,
                    'quantity' => $quantity,
                ]);

                $book->decrement('available_stock', $quantity);
            }

            return $loan;
        });
    }

    /**
     * Memproses pengembalian, menghitung denda, dan menambah kembali stok.
     *
     * Mengembalikan ['success' => bool, 'message' => string, 'late_days' => int|null, 'fine' => int|null].
     */
    public function returnBook(Loan $loan, CarbonInterface|string|null $returnDate = null): array
    {
        if ($loan->status === Loan::STATUS_DIKEMBALIKAN) {
            return [
                'success' => false,
                'message' => 'Transaksi sudah dikembalikan sebelumnya.',
                'late_days' => null,
                'fine' => null,
            ];
        }

        $returnDateCarbon = \Illuminate\Support\Carbon::parse($returnDate ?? now());

        $fine = $this->calculateFine($returnDateCarbon, $loan->due_date);
        $lateDays = $fine > 0 ? (int) abs($returnDateCarbon->startOfDay()->diffInDays($loan->due_date->startOfDay())) : 0;

        DB::transaction(function () use ($loan, $returnDateCarbon, $fine) {
            foreach ($loan->items as $item) {
                $item->book->increment('available_stock', $item->quantity);
            }

            $loan->update([
                'return_date' => $returnDateCarbon,
                'status' => Loan::STATUS_DIKEMBALIKAN,
                'total_fine' => $fine,
            ]);
        });

        return [
            'success' => true,
            'message' => 'Buku berhasil dikembalikan.',
            'late_days' => $lateDays,
            'fine' => $fine,
        ];
    }
}