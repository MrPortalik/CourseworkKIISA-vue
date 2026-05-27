<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import SettingsPanel from '@/Components/SettingsPanel.vue'

const page = usePage()
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin')
const unreadCount = computed(() => page.props.unreadNotificationsCount ?? 0)
const pageUrl = computed(() => page.url)

const isActive = (patterns) => {
    const current = route().current()
    if (!current) return false
    return patterns.some((p) => current === p || current.startsWith(p.replace('*', '')))
}

const sectionActive = (section) => pageUrl.value.includes(`section=${section}`)

const isArticlesSection = computed(() => {
    const current = route().current()
    return current?.startsWith('articles.') ?? false
})
</script>

<template>
    <header class="site-header">
        <figure>
            <Link :href="route('/')" class="logo">
                <img src="/Assets/logoWhite.png" alt="Лого" />
            </Link>
        </figure>

        <nav>
            <div class="nav-main">
                <Link :href="route('articles.index')" class="header-link" :class="{ 'header-link--active': isArticlesSection }">Статьи</Link>
                <Link :href="route('aboutus')" class="header-link" :class="{ 'header-link--active': isActive(['aboutus', 'faq.index']) }">О нас</Link>
                <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="header-link" :class="{ 'header-link--active': isActive(['dashboard', 'authors.show']) }">Личный кабинет</Link>
                <Link v-if="isAdmin" :href="route('admin.index')" class="header-link" :class="{ 'header-link--active': isActive(['admin.index', 'admin.categories']) }">Админ-панель</Link>
            </div>

            <div class="nav-right">
                <SettingsPanel />
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('notifications.index')"
                    class="header-link bell-link"
                    :class="{ 'header-link--active': isActive(['notifications.index']) }"
                    title="Уведомления"
                >
                    <svg class="bell-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2C9.24 2 7 4.24 7 7V11.29C7 11.87 6.76 12.43 6.34 12.85L4.93 14.26C4.34 14.85 4.76 16 5.64 16H18.36C19.24 16 19.66 14.85 19.07 14.26L17.66 12.85C17.24 12.43 17 11.87 17 11.29V7C17 4.24 14.76 2 12 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10 18C10.45 19.16 11.22 20 12 20C12.78 20 13.55 19.16 14 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
                </Link>
                <Link v-if="$page.props.auth?.user" :href="route('logout')" class="header-link" method="get" as="button">Выйти</Link>
                <Link v-if="!$page.props.auth?.user" :href="route('login')" class="header-link">Войти</Link>
                <Link v-if="!$page.props.auth?.user" :href="route('register')" class="header-link">Зарегистрироваться</Link>
            </div>
        </nav>
    </header>
</template>

<style>
.site-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-inline: 20px;
    background-color: var(--header-bg, var(--dark_black));
}
.site-header nav {
    display: flex;
    flex: 1;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    margin-left: 2rem;
}
.nav-main, .nav-right {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    flex-wrap: wrap;
    min-height: 44px;
}
.header-link {
    text-decoration: none;
    font-size: 1.25rem;
    color: white;
    position: relative;
    transition: color 0.2s, box-shadow 0.2s;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    min-height: 36px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 2px solid transparent;
}
.header-link--active {
    color: #ffffff;
    box-shadow: 0 0 0 2px #ffffff;
}
.bell-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    min-width: 36px;
    padding: 4px 8px;
}
.bell-icon { width: 28px; height: 28px; }
.bell-link .badge {
    position: absolute; top: -4px; right: -8px;
    background: #e53e3e; color: white; font-size: 0.65rem;
    padding: 2px 6px; border-radius: 9999px; min-width: 18px; text-align: center;
}
.site-header figure { margin: 15px; }
.site-header .logo img { width: 100px; height: 100px; display: block; }
</style>
