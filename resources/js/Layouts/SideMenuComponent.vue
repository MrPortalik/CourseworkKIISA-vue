<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'

const page = usePage()
const categories = computed(() => page.props.sidebarCategories ?? [])
const objectRanges = computed(() => page.props.objectRanges ?? [])
const pageUrl = computed(() => page.url)

const isCategoryActive = (id) => {
    try {
        return new URL(pageUrl.value, window.location.origin).searchParams.get('category') === String(id)
    } catch {
        return false
    }
}

const isAllArticlesActive = computed(() =>
    route().current() === 'articles.index'
    && !pageUrl.value.includes('category=')
    && !pageUrl.value.includes('section=')
)

const avatarSrc = computed(() => page.props.auth?.user?.avatar ?? null)
</script>

<template>
    <aside class="sideMenu">
        <div v-if="$page.props.auth?.user" class="userMenu">
            <Link :href="route('dashboard')" class="avatar-link">
                <UserAvatar :src="avatarSrc" alt="Аватар" :size="56" class="userLogo" />
            </Link>
            <div class="textHolder">
                <Link :href="route('dashboard')" class="nickname sidebar-user-link">{{ $page.props.auth.user.name }}</Link>
                <Link :href="route('dashboard')" class="username sidebar-user-link">id_{{ $page.props.auth.user.id }}</Link>
            </div>
        </div>

        <div v-else class="userMenu guest">
            <Link :href="route('login')">
                <UserAvatar alt="Гость" :size="56" class="userLogo" />
            </Link>
            <p class="nickname">Гость</p>
        </div>

        <nav class="side-nav">
            <p class="side-nav-title">Подборки</p>
            <Link :href="route('articles.index', { section: 'hits' })" class="side-link" :class="{ 'side-link--active': pageUrl.includes('section=hits') }">Хиты</Link>
            <Link :href="route('articles.index', { section: 'editors_choice' })" class="side-link" :class="{ 'side-link--active': pageUrl.includes('section=editors_choice') }">Выбор редакции</Link>
            <Link :href="route('articles.index', { section: 'new' })" class="side-link" :class="{ 'side-link--active': pageUrl.includes('section=new') }">Новинки</Link>

            <p class="side-nav-title">Категории</p>
            <Link :href="route('articles.index')" class="side-link" :class="{ 'side-link--active': isAllArticlesActive }">Все статьи</Link>
            <Link
                v-for="cat in categories"
                :key="cat.id"
                :href="route('articles.index', { category: cat.id })"
                class="side-link"
                :class="{ 'side-link--active': isCategoryActive(cat.id) }"
            >
                {{ cat.name }}
            </Link>

            <p class="side-nav-title">Объекты</p>
            <Link
                v-for="range in objectRanges"
                :key="range.index"
                :href="route('articles.objects', range.index)"
                class="side-link"
                :class="{ 'side-link--active': route().current() === 'articles.objects' && Number(route().params.range) === range.index }"
            >
                {{ range.label }}
            </Link>
        </nav>
    </aside>
</template>

<style scoped>
.sideMenu {
    width: 240px;
    min-width: 240px;
    background: var(--sidebar-bg, var(--dark_black));
    color: white;
    padding-bottom: 2rem;
    min-height: calc(100vh - 130px);
}
.userMenu {
    padding: 16px 14px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.userMenu.guest {
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.avatar-link { flex-shrink: 0; }
.userLogo {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}
.textHolder {
    min-width: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.sidebar-user-link {
    color: white;
    text-decoration: none;
    display: block;
    word-break: break-word;
    line-height: 1.25;
}
.sidebar-user-link:hover {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: underline;
}
.nickname {
    font-size: 1rem;
    margin: 0;
    font-weight: 600;
}
.username {
    font-size: 0.78rem;
    margin: 0;
    color: rgba(255, 255, 255, 0.65);
}
.side-nav { padding: 1rem 0; }
.side-nav-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255, 255, 255, 0.45);
    padding: 0.75rem 1.25rem 0.35rem;
    margin: 0;
}
.side-link {
    display: block;
    padding: 0.5rem 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.15s;
}
.side-link:hover { background: rgba(255, 255, 255, 0.08); }
.side-link--active {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    border-left: 3px solid rgba(255, 255, 255, 0.5);
    padding-left: calc(1.25rem - 3px);
}
</style>
