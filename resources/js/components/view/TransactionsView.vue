<template>
    <div class="">
        <div class="mb-4 flex items-center">
            <h1 class="flex shrink-0 items-center gap-2 text-2xl font-bold">
                <Activity :size="24" />
                Transactions ({{ transactions.length }})
            </h1>
            <div class="ml-4 flex gap-2">
                <Select v-model="selectedAccount" :required="false" id="target_account" name="target_account">
                    <SelectTrigger class="w-full" :class="{ 'pointer-events-none': fetchingAccounts }">
                        <SelectValue placeholder="Filter by account" :class="{ capitalize: selectedAccount }"> </SelectValue>
                        <Spinner v-show="fetchingAccounts"></Spinner>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="account in accounts" :value="account" class="capitalize">
                            {{ account.type }} account ({{ account.currency }}) - {{ formattedMoney(account.balance) }} €
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button v-show="selectedAccount" variant="ghost" class="rounded-full" @click="onClearFilter"><CircleX /></Button>
            </div>

            <Spinner v-show="fetchingTransaction" class="ml-auto size-8"></Spinner>
        </div>

        <template v-if="transactions.length">
            <div class="flex flex-col gap-4">
                <TransactionItem
                    v-for="transaction in transactions"
                    :key="transaction.id"
                    :customer="customer"
                    :transaction="transaction"
                ></TransactionItem>
            </div>
        </template>
        <template v-else>
            <div class="my-16 flex flex-col items-center gap-2 text-gray-500">
                <div class="inline-flex rounded-full bg-gray-200 p-4">
                    <BanknoteX />
                </div>

                <div>No transactions yet</div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import api from '@/axios';
import { useToasty } from '@/composables/toasty';
import { formattedMoney } from '@/utils/formatMoney';
import { tryCatch } from '@/utils/tryCatch';
import { router } from '@inertiajs/vue3';
import { AxiosResponse } from 'axios';
import { Activity, BanknoteX, CircleX } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { Account } from '../item/AccountItem.vue';
import { Customer } from '../item/CustomerItem.vue';
import TransactionItem, { Transaction } from '../item/TransactionItem.vue';
import Button from '../ui/button/Button.vue';
import Select from '../ui/select/Select.vue';
import SelectContent from '../ui/select/SelectContent.vue';
import SelectItem from '../ui/select/SelectItem.vue';
import SelectTrigger from '../ui/select/SelectTrigger.vue';
import SelectValue from '../ui/select/SelectValue.vue';
import Spinner from '../ui/spinner/Spinner.vue';

const toasty = useToasty();

const { customer } = defineProps<{ customer: Customer }>();

onMounted(async () => {
    await fetchAccounts();

    const params = new URLSearchParams(document.location.search);
    if (params.get('account_id')) {
        selectedAccount.value = accounts.value.find((a) => a.id == params.get('account_id'));
    }

    await fetchTransactions();
});

const transactions = ref<Transaction[]>([]);
const fetchingTransaction = ref(false);
const fetchTransactions = async () => {
    fetchingTransaction.value = true;

    let url = `/api/transactions?customer_id=${customer.id}`;
    if (selectedAccount.value) url += `&account_id=${selectedAccount.value.id}`;
    const [response, err] = await tryCatch<AxiosResponse<{ transactions: Transaction[] }>>(api.get(url));

    if (err) {
        fetchingTransaction.value = false;
        return toasty.error('Failed to fetch transactions');
    }

    if (response?.data.transactions) {
        transactions.value = response.data.transactions;
    }

    fetchingTransaction.value = false;
};

const accounts = ref<Account[]>([]);
const fetchingAccounts = ref(false);
const fetchAccounts = async () => {
    fetchingAccounts.value = true;

    const [response, err] = await tryCatch<AxiosResponse<{ accounts: Account[] }>>(api.get(`/api/accounts?customer_id=${customer.id}`));

    if (err) {
        fetchingAccounts.value = false;
        return toasty.error('Failed to fetch accounts');
    }

    if (response?.data.accounts) {
        accounts.value = response.data.accounts;
    }

    fetchingAccounts.value = false;
};

const selectedAccount = ref<Account>();
watch(selectedAccount, () => {
    if (selectedAccount.value?.id) router.visit(`?account_id=${selectedAccount.value.id}`, { replace: true, preserveState: true });
    fetchTransactions();
});

const onClearFilter = () => {
    selectedAccount.value = undefined;
    router.visit(document.location.pathname, { replace: true, preserveState: true });
};

watch(
    () => document.location.search,
    (newSearch) => {
        const params = new URLSearchParams(newSearch);
        const accountId = params.get('account_id');

        if (accountId) {
            selectedAccount.value = accounts.value.find((a) => a.id == params.get('account_id'));
        } else {
            selectedAccount.value = undefined;
        }
    },
);
</script>

<style scoped></style>
