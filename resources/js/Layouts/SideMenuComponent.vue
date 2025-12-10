<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    user: {
        type: Object
    }
});
</script>


<template>
    <section class="sideMenu">
        <div class="userMenu" v-if="$page.props.auth.user">
            <figure>
                <Link :href="route('dashboard')">
                    <img src="@/Assets/userLogo.png" alt="Аватар пользователя" class="userLogo" loading="lazy">
                </Link>
            </figure>
            
            <div class="textHolder">
                <p class="nickname">{{ $page.props.auth.user.name }}</p> <!-- Никнейм -->
                <p class="username">{{ $page.props.auth.user.email }}</p> <!-- Юзернейм (айди) -->
            </div>

            <!-- добавить выпадающее меню -->
        </div>

        <div class="userMenu" v-if="!$page.props.auth.user">
            <figure >
                <Link :href="route('register')">
                    <img src="@/Assets/userLogo.png" alt="Аватар пользователя" class="userLogo" loading="lazy">
                </Link>
            </figure>
            <p class="nickname">
                Гость
            </p>

            <!-- добавить выпадающее меню -->
        </div>


        <nav>
        </nav>
    </section>
</template>

<style>

.sideMenu {
    width: 15.73%;
    justify-content: center;
    background-color: #D9D9D9;
    padding: 0;
    display: block;
    height: 100%;
}

.userMenu {
    padding: 15px 20px; 
    display: flex;
}

.nickname {
    font-size: 22px;
}

.username {
    font-size: 16px;
}

.userLogo {
    margin-right: 24px;
    width: 60px;
    height: 60px;
}

</style>