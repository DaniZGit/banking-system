<?php

namespace App\Services;

use App\Exceptions\BankServiceException;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class BankService
{
    public function deposit(Account $account, int $amount): Account
    {
        if ($amount <= 0) throw new BankServiceException("Amount must be greater than zero");

        try {
            return DB::transaction(function () use($account, $amount) {
                $lockedAccount = Account::lockForUpdate()->find($account->id);
                if (!$lockedAccount->canOperate()) {
                    throw new BankServiceException("Account cannot perform deposits");
                }
                
                $lockedAccount->increment('balance', $amount);
                $this->createTransaction("deposit", $lockedAccount, null, $amount, "success");

                return $lockedAccount->refresh();
            });
        }  catch (BankServiceException $e) {
            $this->createTransaction("transfer", null, $account, $amount, "rejected", $e->getMessage());
            throw $e;
        }

    }

    public function withdraw(Account $account, int $amount): Account
    {
        if ($amount <= 0) throw new BankServiceException("Amount must be greater than zero");

        try {
            return DB::transaction(function () use($account, $amount) {
                $lockedAccount = Account::lockForUpdate()->find($account->id);
                if (!$lockedAccount->canOperate()) {
                    throw new BankServiceException("Account cannot perform actions");
                }

                if ($lockedAccount->balance < $amount) {
                    throw new BankServiceException("Insufficient funds");
                }

                $lockedAccount->decrement('balance', $amount);
                $this->createTransaction("withdrawal", null, $lockedAccount, $amount, "success");

                return $lockedAccount->refresh();
            });
        } catch (BankServiceException $e) {
            $this->createTransaction("transfer", null, $account, $amount, "rejected", $e->getMessage());
            throw $e;
        }

    }

    public function transfer(Account $sourceAccount, Account $targetAccount, int $amount): Array
    {
        if ($sourceAccount->id === $targetAccount->id) throw new BankServiceException("Cannot transfer to itself");
        if ($amount <= 0) throw new BankServiceException("Amount must be greater than zero");

        try {
            return DB::transaction(function () use($sourceAccount, $targetAccount, $amount) {
                $lockedSourceAccount = Account::lockForUpdate()->find($sourceAccount->id);
                $lockedTargetAccount = Account::lockForUpdate()->find($targetAccount->id);
    
                if (!$lockedSourceAccount->canOperate()) {
                    throw new BankServiceException("Source account cannot perform actions");
                }
                if (!$lockedTargetAccount->canOperate()) {
                    throw new BankServiceException("Target account cannot perform actions");
                }
    
                if ($lockedSourceAccount->balance < $amount) {
                    throw new BankServiceException("Insufficient funds");
                }
    
                $lockedSourceAccount->decrement('balance', $amount);
                $lockedTargetAccount->increment('balance', $amount);
                $this->createTransaction("transfer", $lockedSourceAccount, $lockedTargetAccount, $amount, "success");
    
                return [
                    "source_account" => $lockedSourceAccount->refresh(),
                    "target_account" => $lockedTargetAccount->refresh(),
                ];
            });
        } catch (BankServiceException $e) {
            $this->createTransaction("transfer", $targetAccount, $sourceAccount, $amount, "rejected", $e->getMessage());
            throw $e;
        }
    }

    public function blockCustomer(Customer $customer): void
    {
        DB::transaction(function () use($customer) {
            $customer->accounts()->update(["status" => "blocked"]);

            $customer->update(["status" => "blocked"]);
            $customer->save();
        });
    }

    public function closeCustomer(Customer $customer): void
    {
        $activeAccountsCount = $customer->accounts->where("status", "!=", "closed")->count();
        if ($activeAccountsCount > 0) throw new BankServiceException("All accounts must first be closed");

        $customer->update(["status" => "closed"]);
        $customer->save();
    }

    public function activateAccount(Account $account): void
    {
        $account->update(["status" => "active"]);
        $account->save();
    }

    public function blockAccount(Account $account): void
    {
        $account->update(["status" => "blocked"]);
        $account->save();
    }

    public function closeAccount(Account $account): void
    {
        if ($account->balance != 0) throw new BankServiceException("Only empty accounts can be closed");  // Only empty accounts can be closed

        $account->update(["status" => "closed"]);
        $account->save();
    }

    public function createTransaction($type, Account | null $targetAccount, Account | null $sourceAccount, $amount, string $status, string | null $reason = null)
    {
        Transaction::create([
            'type' => $type,
            'amount' => $amount,
            'source_account_id' => $sourceAccount->id ?? null,
            'target_account_id' => $targetAccount->id ?? null,
            'status' => $status,
            'rejection_reason' => $reason,
        ]);
    }
}