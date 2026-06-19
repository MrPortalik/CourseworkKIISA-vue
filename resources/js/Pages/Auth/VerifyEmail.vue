<script setup>
import { computed } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'

const props = defineProps({
    status: { type: String, default: null },
})

const form = useForm({})

const submit = () => {
    form.post(route('verification.send'))
}

const verificationLinkSent = computed(() => props.status === 'verification-link-sent')
</script>

<template>
    <GuestLayout>
        <PageHead
            title="Подтверждение email"
            description="Подтвердите адрес электронной почты для полного доступа к функциям портала КИИСА."
        />

        <h1 class="auth-title">Подтверждение email</h1>
        <p class="auth-subtitle">Проверьте почту</p>

        <p class="auth-intro">
            Спасибо за регистрацию! Перед началом работы подтвердите email — перейдите по ссылке из письма.
            Если письмо не пришло, мы можем отправить его повторно.
        </p>

        <div v-if="verificationLinkSent" class="auth-status">
            Новая ссылка для подтверждения отправлена на ваш email.
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <button type="submit" class="auth-submit" :disabled="form.processing">
                Отправить письмо повторно
            </button>

            <div class="auth-actions-row auth-footer">
                <Link :href="route('dashboard')" class="auth-link">В личный кабинет</Link>
                <Link :href="route('logout')" method="post" as="button" class="auth-link">
                    Выйти
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
