<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionIndexRequest;
use App\Models\Transaction;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController extends Controller
{
    public function index(TransactionIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $transactions = Transaction::query();
        if (isset($validated['customer_id'])) {
            $customerId = $validated['customer_id'];
            $transactions->where(function ($q) use ($customerId) {
              $q->whereRelation("sourceAccount", "customer_id", $customerId)->orWhereRelation("targetAccount", "customer_id", $customerId);
            });
        }

        if (isset($validated['account_id'])) {
            $accountId = $validated['account_id'];
            $transactions->where(function ($q) use ($accountId) {
              $q->where("source_account_id", $accountId)->orWhere("target_account_id", $accountId);
            });
        }

        $transactions = $transactions->with(['sourceAccount.customer', 'targetAccount.customer'])->orderBy("created_at", "DESC")->get();
        
        return response()->json([
            "transactions" => $transactions
        ]);
    }
}
