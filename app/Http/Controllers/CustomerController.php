<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCreateAccountRequest;
use App\Http\Requests\CustomerCreateRequest;
use App\Http\Requests\CustomerIndexRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Services\BankService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        protected BankService $bankService,
    ) {}

    public function show(Customer $customer): Response
    {
        return Inertia::render('CustomerPage', [
            'customer' => $customer,
            'accounts' => $customer->accounts
        ]);
    }

    public function create(CustomerCreateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $customer = Customer::create([
            "name" => $validated["name"],
            "status" => "active"
        ]);

        $customer->load('accounts');

        return response()->json([
            "customer" => $customer
        ]);
    }

    public function createAccount(CustomerCreateAccountRequest $request, Customer $customer): JsonResponse
    {
        $validated = $request->validated();
        $account = Account::create([
            "type" => $validated["type"],
            "currency" => $validated["currency"],
            "status" => "active",
            "customer_id" => $customer->id
        ]);
        
        return response()->json([
            "account" => $account->refresh()
        ]);
    }

    public function block(Customer $customer): JsonResponse
    {
        // Block customer and their accounts
        $this->bankService->blockCustomer($customer);

        return response()->json([
            "customer" => $customer,
        ]);
    }

    public function close(Customer $customer): JsonResponse
    {
        // Close customer
        $this->bankService->closeCustomer($customer);

        return response()->json([
            "customer" => $customer
        ]);
    }
}
