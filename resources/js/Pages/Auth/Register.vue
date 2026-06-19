<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import LegalDocumentModal from '@/Components/Legal/LegalDocumentModal.vue'
import CatCheckbox from '@/Components/UI/CatCheckbox.vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import { ref, computed } from 'vue'
import { privacyPolicySections, privacyPolicyTitle } from '@/content/legal/privacyPolicy'
import { termsOfUseSections, termsOfUseTitle } from '@/content/legal/termsOfUse'
import { legalStandaloneHref } from '@/lib/legalPages'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    accept_terms: false,
    accept_privacy: false,
})

const activeDocKey = ref(null)

const docs = {
    terms: {
        title: termsOfUseTitle,
        sections: termsOfUseSections,
        crossLinks: [{ key: 'privacy', label: 'Политика конфиденциальности' }],
    },
    privacy: {
        title: privacyPolicyTitle,
        sections: privacyPolicySections,
        crossLinks: [{ key: 'terms', label: 'Пользовательское соглашение' }],
    },
}

const activeDoc = computed(() => (activeDocKey.value ? docs[activeDocKey.value] : null))
const activeDocStandaloneHref = computed(() => (
    activeDocKey.value ? legalStandaloneHref(activeDocKey.value) : null
))

const openDoc = (key) => {
    activeDocKey.value = key
}

const closeDoc = () => {
    activeDocKey.value = null
}

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Регистрация"
            description="Создайте аккаунт на портале КИИСА для публикации статей, рейтингов и участия в сообществе."
        />

        <h1 class="auth-title">Регистрация</h1>
        <p class="auth-subtitle">Создайте аккаунт на портале КИИСА</p>

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-group">
                <label for="name" class="form-label">Имя</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-input"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="form-input"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="legal-checks">
                <label class="legal-check">
                    <CatCheckbox v-model="form.accept_terms" />
                    <span>
                        Я принимаю
                        <button type="button" class="legal-inline-link" @click="openDoc('terms')">пользовательское соглашение</button>
                    </span>
                </label>
                <InputError :message="form.errors.accept_terms" />

                <label class="legal-check">
                    <CatCheckbox v-model="form.accept_privacy" />
                    <span>
                        Я согласен с
                        <button type="button" class="legal-inline-link" @click="openDoc('privacy')">политикой конфиденциальности</button>
                    </span>
                </label>
                <InputError :message="form.errors.accept_privacy" />
            </div>

            <button type="submit" class="auth-submit" :disabled="form.processing">
                Зарегистрироваться
            </button>

            <p class="auth-footer">
                Уже есть аккаунт?
                <Link :href="route('login')" class="auth-link">Войти</Link>
            </p>
        </form>

        <LegalDocumentModal
            v-if="activeDoc"
            :title="activeDoc.title"
            :sections="activeDoc.sections"
            :cross-links="activeDoc.crossLinks"
            :standalone-href="activeDocStandaloneHref"
            :open="!!activeDoc"
            @close="closeDoc"
            @navigate="openDoc"
        />
    </GuestLayout>
</template>

<style scoped>
.auth-title {
    margin: 0 0 0.35rem;
    font-size: 2rem;
    text-align: center;
}
.auth-subtitle {
    margin: 0 0 1.75rem;
    text-align: center;
    font-size: 1rem;
    color: #718096;
}
.auth-form { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group { margin-bottom: 1rem; }
.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.4rem;
    font-size: 0.95rem;
}
.form-input {
    width: 100%;
    padding: 0.7rem 0.85rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
    background: #fff;
    color: inherit;
}
.legal-checks {
    display: grid;
    gap: 0.65rem;
    margin: 0.5rem 0 0.75rem;
}
.legal-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.92rem;
    line-height: 1.45;
    cursor: pointer;
}
.legal-inline-link {
    background: none;
    border: none;
    padding: 0;
    color: #0db7ff;
    font: inherit;
    font-weight: 600;
    text-decoration: underline;
    cursor: pointer;
}
.auth-submit {
    width: 100%;
    margin-top: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    background: #4299e1;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}
.auth-submit:hover:not(:disabled) {
    background: #3182ce;
}
.auth-submit:disabled { opacity: 0.6; cursor: wait; }
.auth-footer {
    margin: 1.25rem 0 0;
    text-align: center;
    font-size: 0.95rem;
    color: #718096;
}
.auth-link {
    color: #0db7ff;
    font-weight: 600;
    text-decoration: none;
    margin-left: 0.25rem;
}
.auth-link:hover { text-decoration: underline; }
[data-theme="dark"] .auth-subtitle,
[data-theme="dark"] .auth-footer { color: #aaa; }
[data-theme="dark"] .form-input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .auth-submit {
    background: #ffffff;
    color: #151515;
}
[data-theme="dark"] .auth-submit:hover:not(:disabled) {
    background: #f0f0f0;
}
[data-theme="dark"] .legal-inline-link {
    color: #67e8f9;
}
</style>
