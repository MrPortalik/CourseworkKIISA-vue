<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <HeaderComponent />
    <div class="auth-page content-area">
        <div class="auth-card">
            <Link :href="route('/')" class="auth-logo">
                <img src="/Assets/logoBlack.png" alt="КИИСА" class="auth-logo-light" />
                <img src="/Assets/logoWhite.png" alt="КИИСА" class="auth-logo-dark" />
            </Link>
            <slot />
        </div>
    </div>
</template>

<style scoped>
.auth-page {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 3rem 1.5rem 4rem;
    min-height: calc(100vh - 130px);
}
.auth-card {
    width: 100%;
    max-width: 440px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2rem 2rem 2.25rem;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
}
.auth-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 1.75rem;
}
.auth-logo img {
    width: 88px;
    height: 88px;
    object-fit: contain;
}
.auth-logo-dark { display: none; }
[data-theme="dark"] .auth-card {
    background: #141414;
    border-color: #333;
}
[data-theme="dark"] .auth-logo-light { display: none; }
[data-theme="dark"] .auth-logo-dark { display: block; }

@media (max-width: 768px) {
    .auth-page {
        padding: 1.25rem 1rem 2rem;
        min-height: calc(100vh - 72px);
    }

    .auth-card {
        padding: 1.5rem 1.25rem 1.75rem;
    }
}
</style>
