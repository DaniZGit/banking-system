<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <form @submit="onSubmit">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Wallet />
                        Transfer Funds
                    </DialogTitle>
                    <DialogDescription class="text-left"> Current balance: {{ formattedMoney(account.balance) }} € </DialogDescription>
                </DialogHeader>
                <div class="mt-4 grid gap-1.5">
                    <label for="target_account" class="flex">Transfer To </label>
                    <Select v-model="target_account_id" id="target_account" name="target_account">
                        <SelectTrigger class="w-full" :class="{ 'pointer-events-none': fetchingAccounts }">
                            <SelectValue placeholder="Select a target account" :class="{ capitalize: target_account_id }"> </SelectValue>
                            <Spinner v-show="fetchingAccounts"></Spinner>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup v-for="(accs, customerName) in customerAccounts">
                                <SelectLabel>{{ customerName }}</SelectLabel>
                                <SelectItem v-for="acc in accs" :value="acc.id" class="capitalize">
                                    {{ acc.type }} account ({{ acc.currency }}) - {{ formattedMoney(acc.balance) }} €
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <small class="text-red-500">{{ errors.target_account_id }}</small>
                </div>
                <div class="my-4 grid gap-1.5">
                    <label for="amount">
                        Amount ( <span class="uppercase">{{ account.currency }} </span> )
                    </label>
                    <NumberField
                        v-model="amount"
                        id="amount"
                        :format-options="{
                            minimumFractionDigits: 2,
                            currency: 'EUR',
                            currencyDisplay: 'symbol',
                            currencySign: 'standard',
                            style: 'currency',
                        }"
                        :stepSnapping="false"
                        class="text-left"
                    >
                        <NumberFieldContent>
                            <NumberFieldInput placeholder="eg. 10.00 €" class="pl-2 text-left" />
                        </NumberFieldContent>
                    </NumberField>

                    <small class="text-red-500">{{ errors.amount }}</small>
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="!isFieldValid('target_account_id') || !isFieldValid('amount') || isProcessing">
                        <Spinner v-if="isProcessing" class="animate-spin" />
                        Confirm Transfer
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import api from '@/axios';
import { useToasty } from '@/composables/toasty';
import { useBankStore } from '@/stores/bank';
import { formattedMoney } from '@/utils/formatMoney';
import { tryCatch } from '@/utils/tryCatch';
import { toTypedSchema } from '@vee-validate/zod';
import { AxiosResponse } from 'axios';
import { Wallet } from 'lucide-vue-next';
import { useField, useForm } from 'vee-validate';
import { computed, onMounted, ref, watch } from 'vue';
import z from 'zod';
import { Account } from '../item/AccountItem.vue';
import Button from '../ui/button/Button.vue';
import Dialog from '../ui/dialog/Dialog.vue';
import DialogClose from '../ui/dialog/DialogClose.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogDescription from '../ui/dialog/DialogDescription.vue';
import DialogFooter from '../ui/dialog/DialogFooter.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import NumberField from '../ui/number-field/NumberField.vue';
import NumberFieldContent from '../ui/number-field/NumberFieldContent.vue';
import NumberFieldInput from '../ui/number-field/NumberFieldInput.vue';
import Select from '../ui/select/Select.vue';
import SelectContent from '../ui/select/SelectContent.vue';
import SelectGroup from '../ui/select/SelectGroup.vue';
import SelectItem from '../ui/select/SelectItem.vue';
import SelectLabel from '../ui/select/SelectLabel.vue';
import SelectTrigger from '../ui/select/SelectTrigger.vue';
import SelectValue from '../ui/select/SelectValue.vue';
import Spinner from '../ui/spinner/Spinner.vue';

const bankStore = useBankStore();
const toasty = useToasty();

const isOpen = defineModel<boolean>('open');
const { account } = defineProps<{ account: Account }>();
const emit = defineEmits<{
    transfer: [sourceAccount: Account, targetAccount: Account];
}>();

onMounted(async () => {
    await fetchAccounts();
});

watch(isOpen, async (newIsOpen) => {
    if (newIsOpen) await fetchAccounts();
});

const accounts = ref<Account[]>([]);
const fetchingAccounts = ref(false);
const fetchAccounts = async () => {
    fetchingAccounts.value = true;
    const [response, err] = await tryCatch<AxiosResponse<{ accounts: Account[] }>>(api.get(`/api/accounts?currency=${account.currency}`));
    if (err) {
        fetchingAccounts.value = false;
        return toasty.error(`Failed to fetch Accounts`);
    }

    if (response?.data.accounts) {
        accounts.value = response.data.accounts;
    }

    fetchingAccounts.value = false;
};

const customerAccounts = computed(() => {
    return accounts.value.reduce(
        (groups, acc) => {
            if (!acc.customer || acc.id == account.id) return groups;

            const customerName = `${acc.customer.name} - ${acc.customer.id}`;
            if (!(customerName in groups)) {
                groups[customerName] = [];
            }

            groups[customerName].push(acc);

            return groups;
        },
        {} as { [key: string]: Account[] },
    );
});

const validationSchema = computed(() =>
    toTypedSchema(
        z.object({
            target_account_id: z.string(),
            amount: z
                .number()
                .positive()
                .max(Number((account.balance / 100).toFixed(2)), 'Amount cannot exceed your current balance'),
        }),
    ),
);

const { handleSubmit, errors, isFieldValid } = useForm({
    validationSchema,
});

const { value: target_account_id } = useField<string>('target_account_id');
const { value: amount } = useField<number>('amount');

const isProcessing = ref(false);
const onSubmit = handleSubmit(async (values) => {
    isProcessing.value = true;
    const [response, err] = await bankStore.transfer(account, values.target_account_id, values.amount * 100);

    if (err) {
        isProcessing.value = false;
        return toasty.error(`Failed to transfer funds`);
    }

    console.log('returned data', response);
    if (response?.data.source_account && response?.data.target_account) {
        emit('transfer', response.data.source_account, response.data.target_account);
    }

    isOpen.value = false;
    isProcessing.value = false;
});
</script>

<style scoped></style>
