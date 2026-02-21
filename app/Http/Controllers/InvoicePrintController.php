<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicePrintController extends Controller
{
    public function print(Request $request, Invoice $invoice)
    {
        // Simple token validation if provided
        if ($request->has('token')) {
            // In a real app, verify this token against Passport/Sanctum
        }

        $invoice->load(['farmer', 'items', 'shop']);
        
        return view('print.invoice', ['invoice' => $invoice]);
    }
}
