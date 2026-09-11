<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(protected LoanService $loanService)
    {
    }

    public function create(): View
    {
        $members = Member::where('status', Member::STATUS_AKTIF)->orderBy('name')->get();
        $books = Book::where('available_stock', '>', 0)->with('category')->orderBy('title')->get();

        return view('loans.create', compact('members', 'books'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'loan_date' => ['required', 'date'],
            'loan_days' => ['required', 'integer', 'min:1', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'member_id.required' => 'Anggota wajib dipilih.',
            'loan_date.required' => 'Tanggal peminjaman wajib diisi.',
            'loan_days.required' => 'Lama pinjam wajib diisi.',
            'items.required' => 'Pilih minimal satu buku.',
        ]);

        $member = Member::findOrFail($data['member_id']);

        $loanItems = [];
        foreach ($data['items'] as $item) {
            $loanItems[] = [
                'book' => Book::findOrFail($item['book_id']),
                'quantity' => (int) $item['quantity'],
            ];
        }

        try {
            $loan = $this->loanService->createLoan(
                $member,
                $loanItems,
                $data['loan_date'],
                (int) $data['loan_days']
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('loans.create')
            ->with('success', 'Peminjaman berhasil dibuat. ID Transaksi: #' . $loan->id);
    }
}