<template>
    <div class="mx-auto flex flex-col bg-white">
        <div class="border-b">
            <Header class="mx-auto max-w-7xl"></Header>
        </div>

        <main class="mx-auto w-full max-w-7xl">
            <slot />
        </main>

        <Toaster />
    </div>
</template>

<script setup lang="ts">
import Header from '@/components/layout/Header.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<style scoped></style>
