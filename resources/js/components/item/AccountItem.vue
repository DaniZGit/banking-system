<template>
    <div class="flex flex-col gap-2 rounded-md border bg-white p-4 transition-shadow hover:shadow-md">
        <div class="flex items-center gap-3">
            <Wallet v-if="account.type == 'personal'" />
            <PiggyBank v-else-if="account.type == 'savings'" :size="24" />
            <BriefcaseBusiness v-else :size="24" />

            <div class="flex flex-col">
                <h5 class="line-clamp-1 font-medium break-all">
                    {{ typeText() }} (<span class="font-bold uppercase">{{ account.currency }}</span
                    >)
                </h5>
                <span class="line-clamp-1 text-xs break-all text-gray-500">ID: {{ account.id }}</span>
            </div>

            <div class="ml-auto flex items-center gap-2 self-start">
                <Badge class="capitalize">{{ account.status }}</Badge>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon-sm" class="h-auto w-auto rounded-full p-1">
                            <Settings />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56" align="start">
                        <DropdownMenuLabel>Account Actions</DropdownMenuLabel>
                        <DropdownMenuGroup>
                            <DropdownMenuItem @click="emit('transactionsClick', account)">
                                <History :size="32"></History>
                                View Transactions
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="account.status != 'active'" @click="onActivate">
                                <Power :size="32"></Power>
                                Activate
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="account.status != 'blocked'" @click="onBlock">
                                <Ban :size="32"></Ban>
                                Block
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="account.status != 'closed'" @click="onClose">
                                <CircleX :size="32"></CircleX>
                                Close
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <div class="flex flex-col rounded-md bg-gray-100 p-2">
            <span class="text-gray-500">Total balance</span>
            <span class="text-xl font-bold">{{ formattedMoney(account.balance) }} €</span>
        </div>

        <div class="flex flex-col rounded-md bg-gray-100 p-2">
            <span class="text-xs text-gray-500">Opened: {{ new Date(account.created_at).toLocaleString() }} </span>
        </div>

        <div class="mt-2 grid grid-cols-3 gap-2">
            <Button
                variant="outline"
                size="sm"
                class="h-auto flex-col py-2"
                :disabled="account.status != 'active'"
                @click="emit('depositClick', account)"
            >
                <ArrowUpFromLine :size="32"></ArrowUpFromLine>
                Deposit
            </Button>
            <Button
                variant="outline"
                size="sm"
                class="h-auto flex-col py-2"
                :disabled="account.status != 'active'"
                @click="emit('withdrawClick', account)"
            >
                <ArrowDownToLine :size="32"></ArrowDownToLine>
                Withdraw
            </Button>
            <Button
                variant="outline"
                size="sm"
                class="h-auto flex-col py-2"
                :disabled="account.status != 'active'"
                @click="emit('transferClick', account)"
            >
                <ArrowRightLeft :size="32"></ArrowRightLeft>
                Transfer
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useToasty } from '@/composables/toasty';
import { useBankStore } from '@/stores/bank';
import { formattedMoney } from '@/utils/formatMoney';
import {
    ArrowDownToLine,
    ArrowRightLeft,
    ArrowUpFromLine,
    Ban,
    BriefcaseBusiness,
    CircleX,
    History,
    PiggyBank,
    Power,
    Settings,
    Wallet,
} from 'lucide-vue-next';
import Badge from '../ui/badge/Badge.vue';
import Button from '../ui/button/Button.vue';
import DropdownMenu from '../ui/dropdown-menu/DropdownMenu.vue';
import DropdownMenuContent from '../ui/dropdown-menu/DropdownMenuContent.vue';
import DropdownMenuGroup from '../ui/dropdown-menu/DropdownMenuGroup.vue';
import DropdownMenuItem from '../ui/dropdown-menu/DropdownMenuItem.vue';
import DropdownMenuLabel from '../ui/dropdown-menu/DropdownMenuLabel.vue';
import DropdownMenuTrigger from '../ui/dropdown-menu/DropdownMenuTrigger.vue';
import { Customer } from './CustomerItem.vue';

export type AccountType = 'personal' | 'savings' | 'business';
export type AccountStatus = 'active' | 'blocked' | 'closed';

export interface Account {
    id: string;
    type: AccountType;
    balance: number;
    currency: string;
    status: AccountStatus;
    created_at: string;
    customer?: Customer;
}

const bankStore = useBankStore();
const toasty = useToasty();

const { account } = defineProps<{ account: Account }>();
const emit = defineEmits<{
    activate: [account: Account];
    block: [account: Account];
    close: [account: Account];
    depositClick: [account: Account];
    withdrawClick: [account: Account];
    transferClick: [account: Account];
    transactionsClick: [account: Account];
}>();

const onActivate = async () => {
    const [response, err] = await bankStore.activateAccount(account);
    if (err) {
        return toasty.error('Failed to activate the account');
    }

    if (response?.data.account) emit('activate', response.data.account);
};

const onBlock = async () => {
    const [response, err] = await bankStore.blockAccount(account);
    if (err) {
        return toasty.error('Failed to block the account');
    }

    if (response?.data.account) emit('block', response.data.account);
};

const onClose = async () => {
    const [response, err] = await bankStore.closeAccount(account);
    if (err) {
        return toasty.error('Failed to close the account');
    }

    if (response?.data.account) emit('close', response.data.account);
};

const typeText = () => {
    if (account.type == 'personal') return 'Personal';
    if (account.type == 'business') return 'Business';
    if (account.type == 'savings') return 'Savings';

    return 'Unknown account';
};
</script>

<style scoped></style>
