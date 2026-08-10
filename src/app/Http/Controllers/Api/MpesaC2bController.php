<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MpesaC2bTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaC2bController extends Controller
{
    /**
     * Handle M-Pesa C2B Confirmation Callback
     */
    public function handleConfirmation(Request $request)
    {
        // Log payload for auditing
        Log::info('M-Pesa C2B Callback Received:', $request->all());

        try {
            // Extract and cast all incoming JSON fields to strings
            $data = [
                'transaction_type'     => (string) $request->input('TransactionType', ''),
                'trans_id'             => (string) $request->input('TransID', ''),
                'trans_time'           => (string) $request->input('TransTime', ''),
                'trans_amount'         => (string) $request->input('TransAmount', ''),
                'business_short_code'  => (string) $request->input('BusinessShortCode', ''),
                'bill_ref_number'      => (string) $request->input('BillRefNumber', ''),
                'invoice_number'       => (string) $request->input('InvoiceNumber', ''),
                'org_account_balance'  => (string) $request->input('OrgAccountBalance', ''),
                'third_party_trans_id' => (string) $request->input('ThirdPartyTransID', ''),
                'msisdn'               => (string) $request->input('MSISDN', ''),
                'first_name'           => (string) $request->input('FirstName', ''),
                'middle_name'          => (string) $request->input('MiddleName', ''),
                'last_name'            => (string) $request->input('LastName', ''),
            ];

            // Prevent duplicate entries by updating existing or creating new record
            MpesaC2bTransaction::updateOrCreate(
                ['trans_id' => $data['trans_id']],
                $data
            );

            // Acknowledge receipt to Safaricom Daraja
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Confirmation received successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('M-Pesa C2B Save Error: ' . $e->getMessage());

            // Return HTTP 200 to Safaricom even on error to stop infinite callback retries
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Internal Processing Error'
            ], 200);
        }
    }
}