<script setup lang="ts">
import CustomerCreateDialog from '@/components/dialog/CustomerCreateDialog.vue';
import type { Customer } from '@/components/item/CustomerItem.vue';
import CustomerItem from '@/components/item/CustomerItem.vue';
import Button from '@/components/ui/button/Button.vue';
import { useBankStore } from '@/stores/bank';
import { UserRoundX, Users } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const { customers } = defineProps<{
    customers: Array<Customer>;
}>();
const bankStore = useBankStore();

onMounted(() => {
    if (customers) bankStore.customers = customers;

    console.log('on mounted', customers);
});

const customerDialogIsOpen = ref(false);
const onCustomerCreate = (customer: Customer) => {
    bankStore.customers.unshift(customer);
};

const onCustomerStatusChange = (customer: Customer) => {
    const index = bankStore.customers.findIndex((c) => c.id == customer.id);
    if (index > -1) {
        bankStore.customers[index].status = customer.status;
    }
};
</script>

<template>
    <div class="m-4 self-stretch">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="flex items-center gap-2 text-2xl font-bold">
                <Users :size="24" />
                Customers ({{ bankStore.customers.length }})
            </h1>
            <Button @click="customerDialogIsOpen = true">New Customer</Button>
        </div>
        <template v-if="bankStore.customers.length">
            <div class="grid w-full grid-cols-2 gap-4">
                <CustomerItem
                    v-for="customer in bankStore.customers"
                    :key="customer.id"
                    :customer="customer"
                    @block="onCustomerStatusChange"
                    @close="onCustomerStatusChange"
                ></CustomerItem>
            </div>
        </template>
        <template v-else>
            <div class="my-16 flex flex-col items-center gap-2 text-gray-500">
                <div class="inline-flex rounded-full bg-gray-200 p-4">
                    <UserRoundX />
                </div>

                <div>No customers yet</div>
            </div>
        </template>

        <CustomerCreateDialog v-model:open="customerDialogIsOpen" @create="onCustomerCreate"></CustomerCreateDialog>
    </div>
</template>
