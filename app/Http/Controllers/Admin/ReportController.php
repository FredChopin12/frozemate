<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __invoke(Request $request, SalesReport $report)
    {
        $days = in_array($request->integer('rentang'), [7, 14, 30], true) ? $request->integer('rentang') : 14;

        return view('admin.reports.index', [
            'days' => $days,
            'totals' => $report->totals($days),
            'daily' => $report->daily($days),
            'topProducts' => $report->topProducts($days, 8, 'revenue'),
            'categories' => $report->byCategory($days),
            'bundleShare' => $report->bundleShare($days),
        ]);
    }
}
