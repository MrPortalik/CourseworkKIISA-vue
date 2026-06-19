<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'

defineProps({
    status: { type: String, default: null },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Забыли пароль"
            description="Восстановите доступ к аккаунту на портале КИИСА: укажите email для ссылки сброса пароля."
        />

        <h1 class="auth-title">Сброс пароля</h1>
        <p class="auth-subtitle">Восстановление доступа к аккаунту</p>

        <p class="auth-intro">
            Укажите email, который вы использовали при регистрации. Мы отправим ссылку для создания нового пароля.
        </p>

        <div v-if="status" class="auth-status">{{ status }}</div>

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <button type="submit" class="auth-submit" :disabled="form.processing">
                Отправить ссылку
            </button>

            <p class="auth-footer">
                <Link :href="route('login')" class="auth-link">← Вернуться ко входу</Link>
            </p>
        </form>
    </GuestLayout>
</template>
