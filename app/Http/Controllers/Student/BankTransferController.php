<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentSubmission;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankTransferController extends Controller
{
    public function submitProof(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('provider', 'bank_transfer')
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        abort_if(! $request->hasFile('proof') && empty($validated['note']), 422, 'Upload proof of payment or leave a note.');

        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store('payment-proofs', 'local')
            : null;

        ManualPaymentSubmission::query()->updateOrCreate(
            ['order_id' => $order->id, 'payment_id' => $payment->id],
            ['proof_path' => $proofPath, 'note' => $validated['note'] ?? null, 'status' => 'pending']
        );

        return back()->with('status', 'Proof submitted — an admin will confirm your payment shortly.');
    }
}
