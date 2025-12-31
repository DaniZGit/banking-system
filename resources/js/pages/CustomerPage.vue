<template>
    <div class="m-4">
        <Button variant="ghost" @click="router.visit('/')"> <ChevronLeft /> Back </Button>

        <div class="mt-2 flex flex-col self-stretch rounded-md border p-4">
            <h1 class="flex items-center gap-2 text-2xl font-bold">
                <User :size="24" />
                {{ customer.name }}
                <Badge class="capitalize">{{ customer.status }}</Badge>
            </h1>
            <span class="text-sm text-gray-500">ID: {{ customer.id }}</span>

            <div class="mt-4 flex items-center gap-4">
                <div class="flex w-1/4 flex-col items-start justify-start rounded-md bg-gray-100 p-4">
                    <span class="text-left text-sm text-gray-500">Total balance</span>
                    <span class="text-left font-bold"> {{ formattedMoney(totalBalance) }} € </span>
                </div>

                <div class="flex w-1/4 flex-col rounded-md bg-gray-100 p-4">
                    <span class="text-sm text-gray-500">Active accounts</span>
                    <span class="font-bold">{{ customer.accounts?.length }}</span>
                </div>

                <Button
                    variant="outline"
                    size="sm"
                    class="ml-auto size-28 h-auto flex-col py-2"
                    :disabled="customer.status != 'active'"
                    @click="onBlock"
                >
                    <Ban :size="28"></Ban>
                    Block
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    class="size-32 h-auto flex-col py-2"
                    :disabled="customer.status == 'closed' || accounts.some((a) => a.status != 'closed')"
                    @click="onClose"
                >
                    <CircleX :size="32"></CircleX>
                    Close
                </Button>
            </div>
        </div>

        <div class="mt-4">
            <Tabs v-model="activeTab">
                <TabsList>
                    <TabsTrigger value="accounts"> Accounts </TabsTrigger>
                    <TabsTrigger value="transactions"> Transactions </TabsTrigger>
                </TabsList>
                <TabsContent value="accounts">
                    <AccountsView
                        :customer="customer"
                        :accounts="accounts"
                        @account-created="onAccountCreate"
                        @account-status-changed="onAccountStatusChange"
                        @account-transactions-view="onAccountTransactionView"
                    ></AccountsView>
                </TabsContent>
                <TabsContent value="transactions">
                    <TransactionsView :customer="customer"></TransactionsView>
                </TabsContent>
            </Tabs>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Account } from '@/components/item/AccountItem.vue';
import { Customer } from '@/components/item/CustomerItem.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import Tabs from '@/components/ui/tabs/Tabs.vue';
import TabsContent from '@/components/ui/tabs/TabsContent.vue';
import TabsList from '@/components/ui/tabs/TabsList.vue';
import TabsTrigger from '@/components/ui/tabs/TabsTrigger.vue';
import AccountsView from '@/components/view/AccountsView.vue';
import TransactionsView from '@/components/view/TransactionsView.vue';
import { useToasty } from '@/composables/toasty';
import { useBankStore } from '@/stores/bank';
import { formattedMoney } from '@/utils/formatMoney';
import { router } from '@inertiajs/vue3';
import { Ban, ChevronLeft, CircleX, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const bankStore = useBankStore();
const toasty = useToasty();

const { customer: propCustomer, accounts: propAccounts } = defineProps<{ customer: Customer; accounts: Account[] }>();
const customer = ref(propCustomer);
const accounts = ref(propAccounts);

const activeTab = ref<'accounts' | 'transactions'>('accounts');
const totalBalance = computed(() => accounts.value.reduce((sum, acc) => sum + acc.balance, 0) ?? 0);

const onBlock = async () => {
    const [response, err] = await bankStore.blockCustomer(customer.value);

    if (err) {
        return toasty.error('Failed to block the customer');
    }

    if (response?.data.customer) {
        customer.value.status = response.data.customer.status;
    }
};

const onClose = async () => {
    const [response, err] = await bankStore.closeCustomer(customer.value);

    if (err) {
        return toasty.error('Failed to close the customer');
    }

    if (response?.data.customer) {
        customer.value.status = response.data.customer.status;
    }
};

const onAccountCreate = (customerId: Customer['id'], account: Account) => {
    accounts.value.push(account);
};

const onAccountStatusChange = (account: Account) => {
    const index = accounts.value.findIndex((a) => a.id == account.id);
    if (index > -1) {
        accounts.value[index].status = account.status;
    }
};

const onAccountTransactionView = (account: Account) => {
    router.visit(`?account_id=${account.id}`, { replace: true, preserveState: true });

    activeTab.value = 'transactions';
};
</script>

<style scoped></style>
