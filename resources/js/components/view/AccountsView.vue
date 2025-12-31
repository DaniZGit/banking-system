<template>
    <div class="">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="flex items-center gap-2 text-2xl font-bold">
                <WalletCards :size="24" />
                Accounts ({{ accounts.length }})
            </h1>
            <Button @click="accountDialogIsOpen = true">New Account</Button>
        </div>

        <template v-if="accounts.length">
            <div class="grid grid-cols-3 gap-4">
                <AccountItem
                    v-for="account in accounts"
                    :key="account.id"
                    :account="account"
                    @activate="emit('accountStatusChanged', $event)"
                    @block="emit('accountStatusChanged', $event)"
                    @close="emit('accountStatusChanged', $event)"
                    @deposit-click="onAccountDepositeClick"
                    @withdraw-click="onAccountWithdrawClick"
                    @transfer-click="onAccountTransferClick"
                    @transactions-click="emit('accountTransactionsView', $event)"
                ></AccountItem>
            </div>
        </template>
        <template v-else>
            <div class="my-16 flex flex-col items-center gap-2 text-gray-500">
                <div class="inline-flex rounded-full bg-gray-200 p-4">
                    <Wallet />
                </div>

                <div>No accounts yet</div>
            </div>
        </template>
    </div>

    <AccountCreateDialog v-model:open="accountDialogIsOpen" :customer="customer" @create="emit('accountCreated', customer.id, $event)" />
    <AccountDepositDialog v-if="selectedAccount" v-model:open="accountDepositDialogIsOpen" :account="selectedAccount" @deposit="onAccountDeposit" />
    <AccountWithdrawDialog
        v-if="selectedAccount"
        v-model:open="accountWithdrawDialogIsOpen"
        :account="selectedAccount"
        @withdraw="onAccountWithdraw"
    />
    <AccountTransferDialog
        v-if="selectedAccount"
        v-model:open="accountTransferDialogIsOpen"
        :account="selectedAccount"
        :accounts="accounts"
        @transfer="onAccountTransfer"
    />
</template>

<script setup lang="ts">
import { Wallet, WalletCards } from 'lucide-vue-next';
import { ref } from 'vue';
import AccountCreateDialog from '../dialog/AccountCreateDialog.vue';
import AccountDepositDialog from '../dialog/AccountDepositDialog.vue';
import AccountTransferDialog from '../dialog/AccountTransferDialog.vue';
import AccountWithdrawDialog from '../dialog/AccountWithdrawDialog.vue';
import AccountItem, { Account } from '../item/AccountItem.vue';
import { Customer } from '../item/CustomerItem.vue';
import Button from '../ui/button/Button.vue';

const { customer, accounts: propAccounts } = defineProps<{ customer: Customer; accounts: Account[] }>();
const accounts = ref(propAccounts);

const emit = defineEmits<{
    accountCreated: [customerId: Customer['id'], account: Account];
    accountStatusChanged: [account: Account];
    accountTransactionsView: [account: Account];
}>();

const accountDialogIsOpen = ref(false);

const selectedAccount = ref<Account>();
const accountDepositDialogIsOpen = ref(false);
const onAccountDepositeClick = (account: Account) => {
    selectedAccount.value = account;
    accountDepositDialogIsOpen.value = true;
};

const onAccountDeposit = (account: Account) => {
    updateAccountBalance(account);
};

const accountWithdrawDialogIsOpen = ref(false);
const onAccountWithdrawClick = (account: Account) => {
    selectedAccount.value = account;
    accountWithdrawDialogIsOpen.value = true;
};

const onAccountWithdraw = (account: Account) => {
    updateAccountBalance(account);
};

const accountTransferDialogIsOpen = ref(false);
const onAccountTransferClick = (account: Account) => {
    selectedAccount.value = account;
    accountTransferDialogIsOpen.value = true;
};

const onAccountTransfer = (sourceAccount: Account, targetAccount: Account) => {
    updateAccountBalance(sourceAccount);
    updateAccountBalance(targetAccount);
};

const updateAccountBalance = (account: Account) => {
    const index = accounts.value.findIndex((a) => a.id == account.id);
    if (index > -1) {
        accounts.value[index].balance = account.balance;
    }
};
</script>

<style scoped></style>
