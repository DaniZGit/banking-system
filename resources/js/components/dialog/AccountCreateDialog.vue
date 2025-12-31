<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <form @submit="onSubmit">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Wallet />
                        Open a New Account
                    </DialogTitle>
                    <DialogDescription class="text-left"> Open a new account for {{ customer.name }} </DialogDescription>
                </DialogHeader>
                <div class="mt-4 grid gap-1.5">
                    <label for="type">Type</label>
                    <Select v-model="type" id="type" name="type" placeholder="Enter customer name">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select a type" :class="{ capitalize: type }" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="type in ['personal', 'savings', 'business']" :value="type" class="capitalize">
                                {{ type }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <small class="text-red-500">{{ errors.type }}</small>
                </div>
                <div class="mt-2 mb-4 grid gap-1.5">
                    <label for="currency">Currency</label>
                    <Select v-model="currency" id="currency" name="currency" placeholder="Enter customer name">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select a currency" :class="{ uppercase: currency }" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="currency in ['EUR', 'USD', 'YEN']" :value="currency" class="uppercase"> {{ currency }} </SelectItem>
                        </SelectContent>
                    </Select>
                    <small class="text-red-500">{{ errors.currency }}</small>
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="!isFieldValid('type') || !isFieldValid('currency') || isProcessing">
                        <Spinner v-if="isProcessing" class="animate-spin" />
                        Create
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import api from '@/axios';
import { useToasty } from '@/composables/toasty';
import { tryCatch } from '@/utils/tryCatch';
import { toTypedSchema } from '@vee-validate/zod';
import { AxiosResponse } from 'axios';
import { Wallet } from 'lucide-vue-next';
import { useField, useForm } from 'vee-validate';
import { ref } from 'vue';
import z from 'zod';
import { Account } from '../item/AccountItem.vue';
import { Customer } from '../item/CustomerItem.vue';
import Button from '../ui/button/Button.vue';
import Dialog from '../ui/dialog/Dialog.vue';
import DialogClose from '../ui/dialog/DialogClose.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogDescription from '../ui/dialog/DialogDescription.vue';
import DialogFooter from '../ui/dialog/DialogFooter.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import Select from '../ui/select/Select.vue';
import SelectContent from '../ui/select/SelectContent.vue';
import SelectItem from '../ui/select/SelectItem.vue';
import SelectTrigger from '../ui/select/SelectTrigger.vue';
import SelectValue from '../ui/select/SelectValue.vue';
import Spinner from '../ui/spinner/Spinner.vue';

const toasty = useToasty();

const isOpen = defineModel<boolean>('open');
const { customer } = defineProps<{ customer: Customer }>();
const emit = defineEmits<{
    create: [account: Account];
}>();

const validationSchema = toTypedSchema(
    z.object({
        type: z.string(),
        currency: z.string().length(3),
    }),
);

const { handleSubmit, errors, isFieldValid } = useForm({
    validationSchema,
});

const { value: type } = useField<string>('type');
const { value: currency } = useField<string>('currency');

const isProcessing = ref(false);
const onSubmit = handleSubmit(async (values) => {
    isProcessing.value = true;
    const [response, err] = await tryCatch<AxiosResponse<{ account: Account }>>(api.post(`/api/customers/${customer.id}/accounts`, values));
    if (err) {
        isProcessing.value = false;
        return toasty.error('Failed to Open a new Account');
    }

    console.log('returned data', response);
    if (response?.data.account) {
        emit('create', response.data.account);
    }

    isOpen.value = false;
    isProcessing.value = false;
});
</script>

<style scoped></style>
