<script setup>
import LegalPageShell from '@/Components/Legal/LegalPageShell.vue'
import { portalRulesSections, portalRulesTitle } from '@/content/legal/portalRules'
</script>

<template>
    <LegalPageShell
        page-key="publication"
        :title="portalRulesTitle"
        description="Правила платформы КИИСА о публикации материалов: требования к статьям, модерация и права авторов."
        :sections="portalRulesSections"
    />
</template>
