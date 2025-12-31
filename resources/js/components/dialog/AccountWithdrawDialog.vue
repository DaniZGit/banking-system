<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <form @submit="onSubmit">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <ArrowDownToLine />
                        Withdraw Funds ( {{ typeText() }} )
                    </DialogTitle>
                    <DialogDescription class="text-left"> Current balance: {{ formattedMoney(account.balance) }} € </DialogDescription>
                </DialogHeader>
                <div class="my-4 grid gap-1.5">
                    <label for="amount">
                        Amount (
                        <span class="uppercase">{{ account.currency }} </span>
                        )
                    </label>
                    <!-- <Input v-model="amount" type="number" placeholder="eg. 0.00 €" /> -->
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
                    <Button type="submit" :disabled="!isFieldValid('amount') || isProcessing">
                        <Spinner v-if="isProcessing" class="animate-spin" />
                        Confirm Withdraw
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { useToasty } from '@/composables/toasty';
import { useBankStore } from '@/stores/bank';
import { formattedMoney } from '@/utils/formatMoney';
import { toTypedSchema } from '@vee-validate/zod';
import { ArrowDownToLine } from 'lucide-vue-next';
import { useField, useForm } from 'vee-validate';
import { computed, ref } from 'vue';
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
import Spinner from '../ui/spinner/Spinner.vue';

const bankStore = useBankStore();
const toasty = useToasty();

const isOpen = defineModel<boolean>('open');

const { account } = defineProps<{ account: Account }>();
const emit = defineEmits<{
    withdraw: [account: Account];
}>();

const validationSchema = computed(() =>
    toTypedSchema(
        z.object({
            amount: z.number().positive(),
            // .max(Number((account.balance / 100).toFixed(2)), 'Amount cannot exceed your current balance'),
        }),
    ),
);

const { handleSubmit, errors, isFieldValid } = useForm({
    validationSchema,
});

const { value: amount } = useField<number>('amount');

const isProcessing = ref(false);
const onSubmit = handleSubmit(async (values) => {
    isProcessing.value = true;
    const [response, err] = await bankStore.withdraw(account, values.amount * 100);

    if (err) {
        isProcessing.value = false;
        return toasty.error(`Failed to withdraw funds`);
    }

    console.log('returned data', response);
    if (response?.data.account) {
        emit('withdraw', response.data.account);
    }

    isOpen.value = false;
    isProcessing.value = false;
});

const typeText = () => {
    if (account.type == 'personal') return 'Personal';
    if (account.type == 'business') return 'Business';
    if (account.type == 'savings') return 'Savings';

    return 'Unknown account';
};
</script>

<style scoped></style>
