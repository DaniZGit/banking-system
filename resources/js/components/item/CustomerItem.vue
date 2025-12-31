<template>
    <div class="flex flex-col rounded-md border bg-white p-4 transition-shadow hover:shadow-md">
        <div class="flex items-center gap-3">
            <Avatar class="rounded-full border">
                <AvatarFallback>{{ customer.name.substring(0, 2) }}</AvatarFallback>
            </Avatar>

            <div class="flex flex-col">
                <h5 class="line-clamp-1 font-medium break-all">{{ customer.name }}</h5>
                <span class="line-clamp-1 text-xs break-all text-gray-500">ID: {{ customer.id }}</span>
            </div>

            <div class="ml-auto flex items-center gap-2 self-start">
                <Badge class="capitalize">{{ customer.status }}</Badge>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon-sm" class="h-auto w-auto rounded-full p-1">
                            <Settings />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56" align="start">
                        <DropdownMenuLabel>Customer Actions</DropdownMenuLabel>
                        <DropdownMenuGroup>
                            <DropdownMenuItem @click="onBlock">
                                <Ban :size="32"></Ban>
                                Block
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="onClose">
                                <CircleX :size="32"></CircleX>
                                Close
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <div class="mt-4 mb-2 grid grid-cols-2 gap-2">
            <div class="flex flex-col rounded-md bg-gray-100 p-2">
                <span class="text-sm text-gray-500">Total balance</span>
                <span class="font-bold">{{ formattedMoney(customer.accounts?.reduce((sum, acc) => sum + acc.balance, 0) ?? 0) }} €</span>
            </div>

            <div class="flex flex-col rounded-md bg-gray-100 p-2">
                <span class="text-sm text-gray-500">Active accounts</span>
                <span class="font-bold">{{ customer.accounts?.length }}</span>
            </div>
        </div>

        <div class="flex flex-col rounded-md bg-gray-100 p-2">
            <span class="text-xs text-gray-500">Created on: {{ new Date(customer.created_at).toLocaleString() }}</span>
        </div>

        <div class="mt-4">
            <Button as-child variant="outline" size="sm" class="ml-auto w-full">
                <Link :href="`/customers/${customer.id}`" class="flex items-center gap-2">
                    View Accounts
                    <ChevronRight :size="32" />
                </Link>
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useToasty } from '@/composables/toasty';
import { useBankStore } from '@/stores/bank';
import { formattedMoney } from '@/utils/formatMoney';
import { Link } from '@inertiajs/vue3';
import { Ban, ChevronRight, CircleX, Settings } from 'lucide-vue-next';
import Avatar from '../ui/avatar/Avatar.vue';
import AvatarFallback from '../ui/avatar/AvatarFallback.vue';
import Badge from '../ui/badge/Badge.vue';
import Button from '../ui/button/Button.vue';
import DropdownMenu from '../ui/dropdown-menu/DropdownMenu.vue';
import DropdownMenuContent from '../ui/dropdown-menu/DropdownMenuContent.vue';
import DropdownMenuGroup from '../ui/dropdown-menu/DropdownMenuGroup.vue';
import DropdownMenuItem from '../ui/dropdown-menu/DropdownMenuItem.vue';
import DropdownMenuLabel from '../ui/dropdown-menu/DropdownMenuLabel.vue';
import DropdownMenuTrigger from '../ui/dropdown-menu/DropdownMenuTrigger.vue';
import { Account } from './AccountItem.vue';

export type CustomerStatus = 'active' | 'blocked' | 'closed';

export interface Customer {
    id: string;
    name: string;
    status: CustomerStatus;
    created_at: string;
    accounts?: Account[];
}

const bankStore = useBankStore();
const toasty = useToasty();

const { customer } = defineProps<{ customer: Customer }>();
const emit = defineEmits<{
    block: [customer: Customer];
    close: [customer: Customer];
}>();

const onBlock = async () => {
    const [response, err] = await bankStore.blockCustomer(customer);
    if (err) {
        return toasty.error('Failed to block the customer');
    }

    if (response?.data.customer) emit('block', response.data.customer);
};

const onClose = async () => {
    console.log('block his ass');
    const [response, err] = await bankStore.closeCustomer(customer);
    if (err) {
        return toasty.error('Failed to close the customer');
    }

    if (response?.data.customer) emit('close', response.data.customer);
};
</script>

<style scoped></style>
