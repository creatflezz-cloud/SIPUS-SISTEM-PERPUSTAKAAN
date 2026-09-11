<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function __construct(protected LoanService $loanService)
    {
    }

    public function index(Request $request): View
    {
        $query = Loan::with(['member', 'items.book'])
            ->whereIn('status', [Loan::STATUS_DIPINJAM, Loan::STATUS_TERLAMBAT]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('member', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%");
                })->orWhereHas('items.book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            });
        }

        $loans = $query->latest()->paginate(10);
        $members = Member::orderBy('name')->get();

        return view('returns.index', compact('loans', 'members'));
    }

    public function process(Request $request, Loan $loan): RedirectResponse
    {
        if (! $loan->isActive()) {
            return back()->with('error', 'Transaksi sudah dikembalikan sebelumnya.');
        }

        $returnDate = $request->validate([
            'return_date' => ['required', 'date'],
        ])['return_date'];

        $result = $this->loanService->returnBook($loan, $returnDate);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message'].' Denda: Rp '.number_format($result['fine'], 0, ',', '.'));
    }
}