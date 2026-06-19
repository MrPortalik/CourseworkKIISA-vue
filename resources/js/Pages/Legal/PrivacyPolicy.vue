<script setup>
import LegalPageShell from '@/Components/Legal/LegalPageShell.vue'
import { privacyPolicySections, privacyPolicyTitle } from '@/content/legal/privacyPolicy'
</script>

<template>
    <LegalPageShell
        page-key="privacy"
        :title="privacyPolicyTitle"
        description="Политика конфиденциальности портала КИИСА: какие данные собираются, как используются и как защищаются."
        :sections="privacyPolicySections"
    />
</template>
