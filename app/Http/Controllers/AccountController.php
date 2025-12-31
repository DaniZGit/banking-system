<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountDepositRequest;
use App\Http\Requests\AccountIndexRequest;
use App\Http\Requests\AccountTransferRequest;
use App\Http\Requests\AccountWithdrawRequest;
use App\Models\Account;
use App\Services\BankService;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountController extends Controller
{
    public function __construct(
        protected BankService $bankService,
    ) {}

    public function index(AccountIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $accounts = Account::with('customer');
        if (isset($validated['currency'])) {
            $accounts->where('currency', $validated["currency"]);
        }

        if (isset($validated['customer_id'])) {
            $accounts->where('customer_id', $validated["customer_id"]);
        }

        $accounts = $accounts->get();
        
        return response()->json([
            "accounts" => $accounts
        ]);
    }

    public function deposit(AccountDepositRequest $request, Account $account): JsonResponse
    {
        $validated = $request->validated();   
        $account = $this->bankService->deposit($account, $validated["amount"]);

        return response()->json([
            "account" => $account
        ]);
    }

    public function withdraw(AccountWithdrawRequest $request, Account $account): JsonResponse
    {
        $validated = $request->validated();   
        $account = $this->bankService->withdraw($account, $validated["amount"]);

        return response()->json([
            "account" => $account
        ]);
    }

    public function transfer(AccountTransferRequest $request, Account $account): JsonResponse
    {
        $validated = $request->validated();

        $targetAccount = Account::find($validated['target_account_id']);
        $data = $this->bankService->transfer($account, $targetAccount, $validated["amount"]);

        return response()->json([
            "source_account" => $data["source_account"],
            "target_account" => $data["target_account"],
        ]);
    }

    public function activate(Account $account): JsonResponse
    {
        // Block account and their accounts
        $this->bankService->activateAccount($account);

        return response()->json([
            "account" => $account,
        ]);
    }

    public function block(Account $account): JsonResponse
    {
        // Block account and their accounts
        $this->bankService->blockAccount($account);

        return response()->json([
            "account" => $account,
        ]);
    }

    public function close(Account $account): JsonResponse
    {
        // Close account
        $this->bankService->closeAccount($account);

        return response()->json([
            "account" => $account
        ]);
    }
}
