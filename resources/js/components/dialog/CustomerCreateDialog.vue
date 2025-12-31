<template>
    <Dialog v-model:open="isOpen">
        <DialogContent>
            <form @submit="onSubmit">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <User />
                        Create a Customer
                    </DialogTitle>
                    <DialogDescription class="text-left"> Add a new customer to the banking system </DialogDescription>
                </DialogHeader>
                <div class="my-4 grid gap-1.5">
                    <label for="name">Customer Name</label>
                    <Input v-model="name" id="name" name="name" placeholder="Enter customer name" />
                    <small class="text-red-500">{{ errors.name }}</small>
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="!isFieldValid('name') || isProcessing">
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
import { User } from 'lucide-vue-next';
import { useField, useForm } from 'vee-validate';
import { ref } from 'vue';
import z from 'zod';
import { Customer } from '../item/CustomerItem.vue';
import Button from '../ui/button/Button.vue';
import Dialog from '../ui/dialog/Dialog.vue';
import DialogClose from '../ui/dialog/DialogClose.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogDescription from '../ui/dialog/DialogDescription.vue';
import DialogFooter from '../ui/dialog/DialogFooter.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import Input from '../ui/input/Input.vue';
import Spinner from '../ui/spinner/Spinner.vue';

const toasty = useToasty();

const isOpen = defineModel<boolean>('open');
const emit = defineEmits<{
    create: [customer: Customer];
}>();

const validationSchema = toTypedSchema(
    z.object({
        name: z.string().min(3).max(255),
    }),
);

const { handleSubmit, errors, isFieldValid } = useForm({
    validationSchema,
});

const { value: name } = useField<string>('name');

const isProcessing = ref(false);
const onSubmit = handleSubmit(async (values) => {
    isProcessing.value = true;
    console.log('fortm submittti');
    const [response, err] = await tryCatch<AxiosResponse<{ customer: Customer }>>(api.post('/api/customers', values));
    if (err) {
        isProcessing.value = false;
        return toasty.error('Failed to create a new Customer');
    }

    console.log('returned data', response);
    if (response?.data.customer) {
        emit('create', response.data.customer);
    }

    isOpen.value = false;
    isProcessing.value = false;
});
</script>

<style scoped></style>
