import api from '@/axios';
import { Account } from '@/components/item/AccountItem.vue';
import { Customer } from '@/components/item/CustomerItem.vue';
import { tryCatch } from '@/utils/tryCatch';
import type { AxiosResponse } from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useBankStore = defineStore('bank', () => {
    const customers = ref<Customer[]>([]);

    const blockCustomer = async (customer: Customer) => {
        const response = await tryCatch<AxiosResponse<{ customer: Customer }>>(api.patch(`/api/customers/${customer.id}/block`));
        return response;
    };

    const closeCustomer = async (customer: Customer) => {
        const response = await tryCatch<AxiosResponse<{ customer: Customer }>>(api.patch(`/api/customers/${customer.id}/close`));
        return response;
    };

    const activateAccount = async (account: Account) => {
        const response = await tryCatch<AxiosResponse<{ account: Account }>>(api.patch(`/api/accounts/${account.id}/activate`));
        return response;
    };

    const blockAccount = async (account: Account) => {
        const response = await tryCatch<AxiosResponse<{ account: Account }>>(api.patch(`/api/accounts/${account.id}/block`));
        return response;
    };

    const closeAccount = async (account: Account) => {
        const response = await tryCatch<AxiosResponse<{ account: Account }>>(api.patch(`/api/accounts/${account.id}/close`));
        return response;
    };

    const deposit = async (account: Account, amount: number) => {
        const response = await tryCatch<AxiosResponse<{ account: Account }>>(api.post(`/api/accounts/${account.id}/deposit`, { amount: amount }));
        return response;
    };

    const withdraw = async (account: Account, amount: number) => {
        const response = await tryCatch<AxiosResponse<{ account: Account }>>(api.post(`/api/accounts/${account.id}/withdraw`, { amount: amount }));
        return response;
    };

    const transfer = async (account: Account, targetAccountId: string, amount: number) => {
        const response = await tryCatch<AxiosResponse<{ source_account: Account; target_account: Account }>>(
            api.post(`/api/accounts/${account.id}/transfer`, { target_account_id: targetAccountId, amount: amount }),
        );
        return response;
    };

    const setCustomers = (newCustomers: Customer[]) => {
        customers.value = newCustomers;
    };

    return { customers, setCustomers, blockCustomer, closeCustomer, activateAccount, blockAccount, closeAccount, deposit, withdraw, transfer };
});
