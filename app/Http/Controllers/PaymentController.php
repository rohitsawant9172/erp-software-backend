<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::with('farmer', 'invoice')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $payment = Payment::create($validated);
            
            // Deduct from farmer usage
            $farmer = Farmer::find($validated['farmer_id']);
            $farmer->decrement('current_usage', $validated['amount']);

            // If linked to invoice, update status
            if ($invoiceId = ($validated['invoice_id'] ?? null)) {
                $invoice = Invoice::find($invoiceId);
                // Simple logic: if payment >= remaining, mark paid
                // Real logic would sum all payments for this invoice
                $totalPaid = $invoice->payments()->sum('amount');
                if ($totalPaid >= $invoice->total_amount) {
                    $invoice->update(['payment_status' => 'paid']);
                } elseif ($totalPaid > 0) {
                    $invoice->update(['payment_status' => 'partial']);
                }
            }

            return response()->json($payment, 201);
        });
    }

    public function payInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($validated, $invoice) {
            $payment = $invoice->payments()->create([
                'tenant_id' => $invoice->tenant_id,
                'farmer_id' => $invoice->farmer_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
            ]);

            $farmer = $invoice->farmer;
            $farmer->decrement('current_usage', $validated['amount']);

            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid >= $invoice->total_amount) {
                $invoice->update(['payment_status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $invoice->update(['payment_status' => 'partial']);
            }

            return response()->json($payment, 201);
        });
    }
}
