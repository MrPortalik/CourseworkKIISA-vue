<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>


<template>
    <header>
        <figure>
            <Link :href="route('/')" class="logo">
                <img src="@/Assets/Logo.png" alt="Лого">
            </Link>
        </figure>


        <nav>

            <Link :href="route('articles.index')" as="button" class="link">Статьи</Link>

            <Link :href="route('aboutus')" as="button" class="link">О нас</Link>

            <Link
                v-if="$page.props.auth?.user"
                :href="route('dashboard')"
                class="link">
                Личный кабинет
            </Link>

            <Link
                v-if="$page.props.auth?.user"
                :href="route('logout')"
                class="link">
                Выйти
            </Link>

            <Link
                v-if="!$page.props.auth?.user"
                :href="route('login')"
                class="link">
                Войти
            </Link>

            <Link
                v-if="!$page.props.auth?.user"
                :href="route('register')"
                class="link">
                Зарегистрироваться
            </Link>
                
        </nav>
    </header>
</template>

<style>

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-inline: 20px;
    background-color: rgba(224, 224, 224, 0.9);
}

header nav {
    display: flex;
    gap: 70px;
}

.link {
    text-decoration: none;
    font-size: 24px;
}

header figure {
    margin: 15px;
}

header .logo img {
    width: 100px;
    height: 100px;
    display: block;
}
</style>