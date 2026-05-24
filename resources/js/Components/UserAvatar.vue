<script setup>
defineOptions({ inheritAttrs: false })

defineProps({
    src: { type: String, default: null },
    alt: { type: String, default: 'Пользователь' },
    size: { type: [Number, String], default: 40 },
})
</script>

<template>
    <span
        class="user-avatar"
        v-bind="$attrs"
        :style="{ width: `${size}px`, height: `${size}px` }"
        role="img"
        :aria-label="alt"
    >
        <img v-if="src" :src="src" :alt="alt" class="user-avatar-img" />
        <span v-else class="user-avatar-fallback">
            <svg viewBox="0 0 24 24" fill="none" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                <circle cx="12" cy="8" r="4" fill="currentColor" opacity="0.85" />
                <path
                    d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                />
            </svg>
        </span>
    </span>
</template>

<style scoped>
.user-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    vertical-align: middle;
    box-sizing: border-box;
}
.user-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.user-avatar-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e2e8f0;
    color: #718096;
}
.user-avatar-fallback svg {
    width: 52%;
    height: 52%;
    flex-shrink: 0;
}
[data-theme="dark"] .user-avatar-fallback {
    background: #2a2a2a;
    color: #b0b0b0;
}
</style>
