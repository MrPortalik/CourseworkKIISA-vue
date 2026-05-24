<script setup>
defineProps({
    variant: { type: String, default: 'accent' },
})
</script>

<template>
    <svg class="edit-pencil" :class="`edit-pencil--${variant}`" viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
        <path
            d="M4 20h4l10.5-10.5a1.5 1.5 0 0 0 0-2.12l-2.38-2.38a1.5 1.5 0 0 0-2.12 0L4 15.88V20z"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linejoin="round"
        />
        <path d="M13.5 6.5l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
</template>

<style scoped>
.edit-pencil {
    display: block;
}
.edit-pencil--accent { color: #0db7ff; }
.edit-pencil--light { color: #ffffff; }
</style>
