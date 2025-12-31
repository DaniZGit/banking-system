<template>
    <div class="flex gap-4 rounded-md border p-4">
        <span class="self-center">
            <ArrowLeftRight v-if="transaction.type == 'transfer'" />
            <ArrowUpToLine v-else-if="transaction.type == 'deposit'" :size="24" />
            <ArrowDownToLine v-else :size="24" />
        </span>

        <div class="flex flex-col">
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold capitalize">{{ transaction.type }}</span>
                <Badge class="capitalize">{{ transaction.status }}</Badge>
            </div>
            <span v-if="transaction.source_account" class="text-gray-500 capitalize">
                <span class="font-bold">From:</span> {{ transaction.source_account?.customer?.name }} ( {{ transaction.source_account?.type }} account
                ) - {{ transaction.source_account?.id }}
            </span>
            <span v-if="transaction.target_account" class="text-gray-500 capitalize">
                <span class="font-bold">To:</span> {{ transaction.target_account?.customer?.name }} ( {{ transaction.target_account?.type }} account )
                - {{ transaction.target_account?.id }}
            </span>
            <p v-if="transaction.status == 'rejected'"><span class="font-bold">Reason:</span> {{ transaction.rejection_reason }}</p>
        </div>
        <div class="ml-auto flex flex-col justify-between">
            <span class="text-right text-xl font-bold">{{ amountSign() }} {{ formattedMoney(transaction.amount) }} €</span>
            <span class="text-xs text-gray-500">{{ new Date(transaction.created_at).toLocaleString() }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formattedMoney } from '@/utils/formatMoney';
import { ArrowDownToLine, ArrowLeftRight, ArrowUpToLine } from 'lucide-vue-next';
import Badge from '../ui/badge/Badge.vue';
import { Account } from './AccountItem.vue';
import { Customer } from './CustomerItem.vue';

export type TransactionType = 'deposit' | 'withdrawal' | 'transfer';
export type TransactionStatus = 'success' | 'rejected';

export interface Transaction {
    id: string;
    amount: number;
    type: TransactionType;
    status: TransactionStatus;
    source_account: Account | null;
    target_account: Account | null;
    rejection_reason: string | null;
    created_at: string;
}

const { customer, transaction } = defineProps<{ customer: Customer; transaction: Transaction }>();

const amountSign = () => {
    if (transaction.source_account?.customer?.id == customer.id && transaction.target_account?.customer?.id == customer.id) return '';

    if (transaction.source_account?.customer?.id == customer.id) {
        return transaction.type == 'deposit' ? '+' : '-';
    } else if (transaction.target_account?.customer?.id == customer.id) {
        return '+';
    }

    return '';
};
</script>

<style scoped></style>
