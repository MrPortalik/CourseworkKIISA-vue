<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import SettingsPanel from '@/Components/SettingsPanel.vue'
import MobileDrawer from '@/Components/MobileDrawer.vue'
import CookieConsent from '@/Components/Legal/CookieConsent.vue'

const page = usePage()
const canAccessAdmin = computed(() => ['admin', 'owner', 'moderator'].includes(page.props.auth?.user?.role))
const unreadCount = computed(() => page.props.unreadNotificationsCount ?? 0)
const pageUrl = computed(() => page.url)
const navOpen = ref(false)

const isActive = (patterns) => {
    const current = route().current()
    if (!current) return false
    return patterns.some((p) => current === p || current.startsWith(p.replace('*', '')))
}

const isArticlesSection = computed(() => {
    const current = route().current()
    return current?.startsWith('articles.') ?? false
})

const closeNav = () => {
    navOpen.value = false
}

watch(() => page.url, closeNav)
</script>

<template>
    <header class="site-header">
        <div class="header-left">
            <button
                type="button"
                class="burger-btn"
                aria-label="Открыть меню"
                :aria-expanded="navOpen"
                @click="navOpen = true"
            >
                <span class="burger-bar" />
                <span class="burger-bar" />
                <span class="burger-bar" />
            </button>

            <figure class="header-logo">
                <Link :href="route('/')" class="logo" @click="closeNav">
                    <img src="/Assets/logoWhite.png" alt="Лого" />
                </Link>
            </figure>
        </div>

        <nav class="header-nav" aria-label="Основная навигация">
            <div class="nav-main">
                <Link :href="route('articles.index')" class="header-link" :class="{ 'header-link--active': isArticlesSection }">Статьи</Link>
                <Link :href="route('aboutus')" class="header-link" :class="{ 'header-link--active': isActive(['aboutus', 'faq.index']) }">О нас</Link>
                <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="header-link" :class="{ 'header-link--active': isActive(['dashboard', 'authors.show']) }">Личный кабинет</Link>
                <Link v-if="canAccessAdmin" :href="route('admin.index')" class="header-link" :class="{ 'header-link--active': route().current()?.startsWith('admin.') }">
                    Админ-панель
                </Link>
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
                <Link v-if="$page.props.auth?.user" :href="route('logout')" class="header-link nav-auth-link" method="get" as="button">Выйти</Link>
                <Link v-if="!$page.props.auth?.user" :href="route('login')" class="header-link nav-auth-link">Войти</Link>
                <Link v-if="!$page.props.auth?.user" :href="route('register')" class="header-link nav-auth-link">Зарегистрироваться</Link>
            </div>
        </nav>

        <MobileDrawer :open="navOpen" title="Навигация" placement="top" @close="closeNav">
            <nav class="mobile-nav-links">
                <Link :href="route('articles.index')" class="mobile-nav-link" :class="{ 'mobile-nav-link--active': isArticlesSection }" @click="closeNav">Статьи</Link>
                <Link :href="route('aboutus')" class="mobile-nav-link" :class="{ 'mobile-nav-link--active': isActive(['aboutus', 'faq.index']) }" @click="closeNav">О нас</Link>
                <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="mobile-nav-link" :class="{ 'mobile-nav-link--active': isActive(['dashboard', 'authors.show']) }" @click="closeNav">Личный кабинет</Link>
                <Link v-if="canAccessAdmin" :href="route('admin.index')" class="mobile-nav-link" :class="{ 'mobile-nav-link--active': route().current()?.startsWith('admin.') }" @click="closeNav">
                    Админ-панель
                </Link>

                <div class="mobile-nav-divider" />

                <Link v-if="$page.props.auth?.user" :href="route('logout')" class="mobile-nav-link" method="get" as="button" @click="closeNav">Выйти</Link>
                <Link v-if="!$page.props.auth?.user" :href="route('login')" class="mobile-nav-link" @click="closeNav">Войти</Link>
                <Link v-if="!$page.props.auth?.user" :href="route('register')" class="mobile-nav-link" @click="closeNav">Зарегистрироваться</Link>
            </nav>
        </MobileDrawer>
    </header>
    <CookieConsent />
</template>

<style>
.site-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-inline: 20px;
    background-color: var(--header-bg, var(--dark_black));
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow-x: clip;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.burger-btn {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 44px;
    height: 44px;
    padding: 0;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    cursor: pointer;
    flex-shrink: 0;
}

.burger-bar {
    display: block;
    width: 22px;
    height: 2px;
    margin: 0 auto;
    background: #fff;
    border-radius: 1px;
}

.site-header nav.header-nav {
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

.bell-link .badge,
.badge--inline {
    background: #e53e3e;
    color: white;
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 9999px;
    min-width: 18px;
    text-align: center;
}

.bell-link .badge {
    position: absolute;
    top: -4px;
    right: -8px;
}

.badge--inline {
    margin-left: 0.35rem;
    vertical-align: middle;
}

.site-header figure.header-logo {
    margin: 15px 0;
}

.site-header .logo img {
    width: 100px;
    height: 100px;
    display: block;
}

.mobile-nav-links {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0 0.75rem;
}

.mobile-nav-link {
    display: block;
    padding: 0.85rem 1rem;
    color: rgba(255, 255, 255, 0.92);
    text-decoration: none;
    font-size: 1.05rem;
    border-radius: 8px;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    text-align: left;
    width: 100%;
    box-sizing: border-box;
}

.mobile-nav-link:hover,
.mobile-nav-link--active {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.mobile-nav-divider {
    height: 1px;
    margin: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.12);
}

@media (max-width: 768px) {
    .site-header {
        padding-inline: 12px;
    }

    .burger-btn {
        display: flex;
    }

    .site-header nav.header-nav {
        flex: 0 0 auto;
        margin-left: 0;
        gap: 0.5rem;
    }

    .nav-main,
    .nav-auth-link {
        display: none !important;
    }

    .nav-right {
        gap: 0.35rem;
        flex-wrap: nowrap;
    }

    .header-link {
        font-size: 1rem;
        padding: 4px 6px;
    }

    .site-header figure.header-logo {
        margin: 8px 0;
    }

    .site-header .logo img {
        width: 56px;
        height: 56px;
    }
}
</style>
