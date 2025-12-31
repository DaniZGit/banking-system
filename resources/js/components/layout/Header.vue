<template>
    <header class="flex h-18 items-center gap-2 p-4">
        <span class="flex aspect-square h-full w-auto items-center justify-center rounded-sm bg-blue-500 text-white">SB</span>
        <div class>
            <div class="flex flex-col">
                <h2 class="text-xl font-semibold">Simple Bank</h2>
                <span class="text-xs">Management System</span>
            </div>
        </div>

        <div class="ml-auto">
            <Button>
                <Menu />
            </Button>
        </div>
    </header>
</template>

<script setup lang="ts">
import { Menu } from 'lucide-vue-next';
import Button from '../ui/button/Button.vue';
</script>

<style scoped></style>
