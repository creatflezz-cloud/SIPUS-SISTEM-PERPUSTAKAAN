<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Loan::with(['member', 'items.book']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('member', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('member_code', 'like', "%{$search}%");
                })->orWhereHas('items.book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status') && in_array($request->input('status'), [Loan::STATUS_DIPINJAM, Loan::STATUS_TERLAMBAT, Loan::STATUS_DIKEMBALIKAN], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('loan_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('loan_date', '<=', $request->input('date_to'));
        }

        $loans = $query->latest()->paginate(15);

        return view('transactions.index', compact('loans'));
    }
}