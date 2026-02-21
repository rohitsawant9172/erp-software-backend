<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\TallyExportService;
use Illuminate\Http\Request;

class InvoiceActionController extends Controller
{
    public function tally(Invoice $invoice, TallyExportService $service)
    {
        $xml = $service->exportInvoiceToXML($invoice);
        
        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="invoice_' . $invoice->invoice_no . '.xml"',
        ]);
    }
    public function syncTally(Invoice $invoice, TallyExportService $service)
    {
        try {
            $result = $service->sendToTallyAPI($invoice);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tally HTTP API Connection Refused. Please ensure Tally is running on port 9000.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Invoice $invoice)
    {
        return $invoice->load(['farmer', 'items', 'shop']);
    }
}
