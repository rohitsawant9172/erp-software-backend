<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        return Invoice::with('farmer')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:paid,partial,unpaid',
            'payment_method' => 'nullable|string',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $farmer = Farmer::lockForUpdate()->find($validated['farmer_id']);
            
            // 1. Calculate totals
            $subTotal = 0;
            $taxAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                
                // Stock check on product level (for backward compatibility tracking)
                if ($product->stock_level < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $remainingQuantity = $item['quantity'];

                // FEFO Logic (First Expiry First Out)
                $batches = $product->batches()
                                   ->where('stock_level', '>', 0)
                                   ->orderBy('expiry_date', 'asc')
                                   ->lockForUpdate()
                                   ->get();

                foreach ($batches as $batch) {
                    if ($remainingQuantity <= 0) break;
                    
                    $deduct = min($batch->stock_level, $remainingQuantity);
                    $batch->decrement('stock_level', $deduct);
                    $remainingQuantity -= $deduct;
                    
                    // Recalculate based on batch price if needed, falling back to product price
                    $rate = $batch->sale_price ?? $product->price;
                    $lineTaxRate = $product->gst_rate;
                    $lineSubTotal = $rate * $deduct;
                    $lineTax = ($lineSubTotal * $lineTaxRate) / 100;
                    $lineTotal = $lineSubTotal + $lineTax;

                    $subTotal += $lineSubTotal;
                    $taxAmount += $lineTax;

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'batch_id' => $batch->id,
                        'product_name' => $product->name,
                        'quantity' => $deduct,
                        'rate' => $rate,
                        'tax_rate' => $lineTaxRate,
                        'tax_amount' => $lineTax,
                        'total_amount' => $lineTotal,
                    ];
                }

                if ($remainingQuantity > 0) {
                    throw new \Exception("Insufficient batch stock for product: {$product->name}. Re-sync inventory.");
                }

                // 2. Reduce global product stock
                $product->decrement('stock_level', $item['quantity']);
            }

            $discount = $validated['discount_amount'] ?? 0;
            $totalAmount = ($subTotal + $taxAmount) - $discount;

            // 3. Generate Invoice No (Shop specific prefix?)
            $invoiceNo = 'INV-' . strtoupper(Str::random(8));

            // 4. Create Invoice
            $invoice = Invoice::create([
                'farmer_id' => $farmer->id,
                'invoice_no' => $invoiceNo,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // 5. Create Items
            foreach ($itemsData as $data) {
                $invoice->items()->create($data);
            }

            // 6. Update Farmer Credit if unpaid/partial
            if ($validated['payment_status'] !== 'paid') {
                $farmer->increment('current_usage', $totalAmount);
                
                // Credit limit check
                if ($farmer->credit_limit > 0 && $farmer->current_usage > $farmer->credit_limit) {
                    // Optional: Warning or block based on strictness
                }
            }

            return response()->json($invoice->load('items', 'farmer'), 201);
        });
    }

    public function show(Invoice $invoice)
    {
        return $invoice->load('items', 'farmer', 'shop');
    }

    public function salesReport()
    {
        return Invoice::select(
            DB::raw('DATE(created_at) as date'), 
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_invoices')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();
    }

    public function downloadTallyXML(Invoice $invoice, \App\Services\TallyExportService $service)
    {
        $xml = $service->exportInvoiceToXML($invoice);
        
        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="invoice_' . $invoice->invoice_no . '.xml"',
        ]);
    }
}
