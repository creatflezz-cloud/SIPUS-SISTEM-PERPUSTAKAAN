<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalBooks = Book::count();
        $totalMembers = Member::count();
        $availableBooks = Book::sum('available_stock');
        $borrowedBooks = Book::sum(DB::raw('stock - available_stock'));
        $activeLoans = Loan::whereIn('status', [Loan::STATUS_DIPINJAM, Loan::STATUS_TERLAMBAT])->count();
        $lateLoans = Loan::where('status', Loan::STATUS_TERLAMBAT)->count();
        $totalFine = Loan::sum('total_fine');

        $todayLoans = Loan::whereDate('loan_date', today())->count();
        $todayReturns = Loan::whereDate('return_date', today())->count();

        $monthlyLoans = Loan::query()
            ->selectRaw("DATE_FORMAT(loan_date, '%Y-%m') as month, COUNT(*) as total")
            ->where('loan_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months[] = [
                'label' => now()->subMonths($i)->isoFormat('MMM'),
                'count' => $monthlyLoans[$key] ?? 0,
            ];
        }

        $popularBooks = DB::table('loan_items')
            ->join('books', 'books.id', '=', 'loan_items.book_id')
            ->select('books.id', 'books.title', 'books.author', 'books.photo', 'books.available_stock', DB::raw('SUM(loan_items.quantity) as total_borrowed'))
            ->groupBy('books.id', 'books.title', 'books.author', 'books.photo', 'books.available_stock')
            ->orderByDesc('total_borrowed')
            ->limit(5)
            ->get();

        $recentLoans = Loan::with(['member', 'items.book'])
            ->latest()
            ->limit(6)
            ->get();

        $newestMembers = Member::latest()->limit(5)->get();

        $lowStockBooks = Book::where('available_stock', '<=', 5)
            ->orderByRaw('stock - available_stock DESC')
            ->limit(5)
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('dashboard', compact(
            'totalBooks',
            'totalMembers',
            'availableBooks',
            'borrowedBooks',
            'activeLoans',
            'lateLoans',
            'totalFine',
            'todayLoans',
            'todayReturns',
            'months',
            'popularBooks',
            'recentLoans',
            'newestMembers',
            'lowStockBooks',
            'categories'
        ));
    }
}