<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalFarmers = Farmer::count();
        $totalProducts = Product::count();
        $totalRevenue = Payment::sum('amount');
        $totalBilled = Invoice::sum('total_amount');
        $remainingRevenue = max(0, $totalBilled - $totalRevenue);
        
        $lowStockCount = Product::whereColumn('stock_level', '<=', 'low_stock_threshold')->count();
        
        $recentInvoices = Invoice::with('farmer')
            ->latest()
            ->take(5)
            ->get();

        $topProducts = DB::table('invoice_items')
            ->select('product_name as name', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->take(3)
            ->get();

        return response()->json([
            'stats' => [
                'total_farmers' => $totalFarmers,
                'total_products' => $totalProducts,
                'total_revenue' => $totalRevenue,
                'remaining_revenue' => $remainingRevenue,
                'low_stock_count' => $lowStockCount,
            ],
            'recent_invoices' => $recentInvoices,
            'top_products' => $topProducts,
        ]);
    }
}
