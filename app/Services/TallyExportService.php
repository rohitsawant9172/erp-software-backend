<?php

namespace App\Services;

use App\Models\Invoice;
use SimpleXMLElement;

class TallyExportService
{
    public function exportInvoiceToXML(Invoice $invoice)
    {
        $invoice->load(['farmer', 'items', 'shop']);

        $xml = new SimpleXMLElement('<ENVELOPE/>');
        $header = $xml->addChild('HEADER');
        $header->addChild('TALLYREQUEST', 'Import Data');

        $body = $xml->addChild('BODY');
        $importData = $body->addChild('IMPORTDATA');
        $requestDesc = $importData->addChild('REQUESTDESC');
        $requestDesc->addChild('REPORTNAME', 'Vouchers');
        $staticVariables = $requestDesc->addChild('STATICVARIABLES');
        $staticVariables->addChild('SVCURRENTCOMPANY', $invoice->shop->name ?? 'AGROSYSTEMS');

        $requestData = $importData->addChild('REQUESTDATA');
        $tallyMessage = $requestData->addChild('TALLYMESSAGE');
        $voucher = $tallyMessage->addChild('VOUCHER');
        $voucher->addAttribute('VCHTYPE', 'Sales');
        $voucher->addAttribute('ACTION', 'Create');

        $voucher->addChild('DATE', date('Ymd', strtotime($invoice->created_at)));
        $voucher->addChild('VOUCHERTYPENAME', 'Sales');
        $voucher->addChild('VOUCHERNUMBER', $invoice->invoice_no);
        $voucher->addChild('PARTYLEDGERNAME', $invoice->farmer->name);
        $voucher->addChild('PERSISTEDVIEW', 'Accounting Voucher View');

        // Party Ledger entry (Debit)
        $allLedgerEntries = $voucher->addChild('ALLLEDGERENTRIES.LIST');
        $allLedgerEntries->addChild('LEDGERNAME', $invoice->farmer->name);
        $allLedgerEntries->addChild('ISDEEMEDPOSITIVE', 'Yes');
        $allLedgerEntries->addChild('AMOUNT', '-' . $invoice->total_amount);

        // Sales Ledger entry (Credit)
        $salesEntry = $voucher->addChild('ALLLEDGERENTRIES.LIST');
        $salesEntry->addChild('LEDGERNAME', 'Sales');
        $salesEntry->addChild('ISDEEMEDPOSITIVE', 'No');
        $salesEntry->addChild('AMOUNT', $invoice->sub_total);

        // Tax Ledger entry (Credit)
        if ($invoice->tax_amount > 0) {
            $taxEntry = $voucher->addChild('ALLLEDGERENTRIES.LIST');
            $taxEntry->addChild('LEDGERNAME', 'GST');
            $taxEntry->addChild('ISDEEMEDPOSITIVE', 'No');
            $taxEntry->addChild('AMOUNT', $invoice->tax_amount);
        }

        return $xml->asXML();
    }

    /**
     * Sends the generated XML payload directly to the Tally HTTP API.
     * Tally typically listens on port 9000.
     */
    public function sendToTallyAPI(Invoice $invoice)
    {
        $tallyUrl = env('TALLY_URL', 'http://127.0.0.1:9000');
        $xmlPayload = $this->exportInvoiceToXML($invoice);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'text/xml',
        ])->send('POST', $tallyUrl, [
            'body' => $xmlPayload
        ]);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'tally_response' => $response->body()
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to connect/sync with Tally API',
            'tally_response' => $response->body()
        ];
    }
}
