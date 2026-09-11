<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $loanQuery = Loan::query();
        if ($dateFrom) {
            $loanQuery->whereDate('loan_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $loanQuery->whereDate('loan_date', '<=', $dateTo);
        }

        $report = [
            'books' => [
                'total' => Book::count(),
                'available' => Book::sum('available_stock'),
                'borrowed' => Book::sum('stock') - Book::sum('available_stock'),
            ],
            'members' => [
                'total' => Member::count(),
                'active' => Member::where('status', Member::STATUS_AKTIF)->count(),
                'inactive' => Member::where('status', Member::STATUS_TIDAK_AKTIF)->count(),
            ],
            'transactions' => [
                'total_loans' => (clone $loanQuery)->count(),
                'total_returns' => (clone $loanQuery)->where('status', Loan::STATUS_DIKEMBALIKAN)->count(),
                'total_late' => (clone $loanQuery)->where('status', Loan::STATUS_TERLAMBAT)->count(),
                'total_fine' => (clone $loanQuery)->sum('total_fine'),
            ],
        ];

        $loans = (clone $loanQuery)->with(['member', 'items.book'])->latest()->limit(100)->get();

        return view('reports.index', compact('report', 'loans', 'dateFrom', 'dateTo'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Loan::with(['member', 'items.book']);
        if ($dateFrom) {
            $query->whereDate('loan_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('loan_date', '<=', $dateTo);
        }

        $loans = $query->latest()->get();

        $filename = 'laporan-transaksi-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($loans) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID Transaksi',
                'Nama Anggota',
                'Buku',
                'Tanggal Pinjam',
                'Jatuh Tempo',
                'Tanggal Kembali',
                'Denda',
                'Status',
            ]);

            foreach ($loans as $loan) {
                $titles = $loan->items->map(fn ($item) => $item->book->title.' x'.$item->quantity)->implode(', ');
                fputcsv($handle, [
                    $loan->id,
                    $loan->member->name,
                    $titles,
                    $loan->loan_date?->format('d-m-Y') ?? '',
                    $loan->due_date?->format('d-m-Y') ?? '',
                    $loan->return_date?->format('d-m-Y') ?? '',
                    number_format((float) $loan->total_fine, 0, ',', '.'),
                    strtoupper($loan->status),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Loan::with(['member', 'items.book']);
        if ($dateFrom) {
            $query->whereDate('loan_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('loan_date', '<=', $dateTo);
        }

        $loans = $query->latest()->get();
        $total = $loans->sum('total_fine');

        $html = view('reports.pdf', compact('loans', 'total', 'dateFrom', 'dateTo'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

        return response()->streamDownload(fn () => print($pdf->output()), 'laporan-transaksi-'.now()->format('Ymd-His').'.pdf', ['Content-Type' => 'application/pdf']);
    }
}