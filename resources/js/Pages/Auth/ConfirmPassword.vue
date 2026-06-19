<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'

const form = useForm({
    password: '',
})

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    })
}
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Подтверждение"
            description="Подтвердите пароль для доступа к защищённым разделам портала КИИСА."
        />

        <h1 class="auth-title">Подтверждение пароля</h1>
        <p class="auth-subtitle">Защищённый раздел</p>

        <p class="auth-intro">
            Это защищённая область сайта. Подтвердите пароль, чтобы продолжить.
        </p>

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-input"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="form.errors.password" />
            </div>

            <button type="submit" class="auth-submit" :disabled="form.processing">
                Подтвердить
            </button>
        </form>
    </GuestLayout>
</template>
