<script setup>
import LegalPageShell from '@/Components/Legal/LegalPageShell.vue'
import { termsOfUseSections, termsOfUseTitle } from '@/content/legal/termsOfUse'
</script>

<template>
    <LegalPageShell
        page-key="terms"
        :title="termsOfUseTitle"
        description="Пользовательское соглашение информационного портала КИИСА: регистрация, использование сервиса и ответственность."
        :sections="termsOfUseSections"
    />
</template>
