<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import MobileDrawer from '@/Components/MobileDrawer.vue'
import { useMobileSidebar } from '@/composables/useMobileSidebar'
import FeedbackModal from '@/Components/FeedbackModal.vue'
import { ref } from 'vue'

const page = usePage()
const categories = computed(() => page.props.sidebarCategories ?? [])
const objectRanges = computed(() => page.props.objectRanges ?? [])
const pageUrl = computed(() => page.url)
const { isOpen, close } = useMobileSidebar()
const feedbackOpen = ref(false)

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

const onNavClick = () => {
    close()
}

watch(() => page.url, close)
</script>

<template>
    <aside class="sideMenu sideMenu--desktop">
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

        <div class="side-footer">
            <button type="button" class="side-link side-link--feedback" @click="feedbackOpen = true">
                Обратная связь
            </button>
        </div>
    </aside>

    <MobileDrawer :open="isOpen" title="Категории и подборки" @close="close">
        <div v-if="$page.props.auth?.user" class="userMenu userMenu--drawer">
            <Link :href="route('dashboard')" class="avatar-link" @click="onNavClick">
                <UserAvatar :src="avatarSrc" alt="Аватар" :size="48" class="userLogo" />
            </Link>
            <div class="textHolder">
                <Link :href="route('dashboard')" class="nickname sidebar-user-link" @click="onNavClick">{{ $page.props.auth.user.name }}</Link>
                <Link :href="route('dashboard')" class="username sidebar-user-link" @click="onNavClick">id_{{ $page.props.auth.user.id }}</Link>
            </div>
        </div>

        <div v-else class="userMenu guest userMenu--drawer">
            <Link :href="route('login')" @click="onNavClick">
                <UserAvatar alt="Гость" :size="48" class="userLogo" />
            </Link>
            <p class="nickname">Гость</p>
        </div>

        <nav class="side-nav side-nav--drawer">
            <p class="side-nav-title">Подборки</p>
            <Link :href="route('articles.index', { section: 'hits' })" class="side-link" :class="{ 'side-link--active': pageUrl.includes('section=hits') }" @click="onNavClick">Хиты</Link>
            <Link :href="route('articles.index', { section: 'editors_choice' })" class="side-link" :class="{ 'side-link--active': pageUrl.includes('section=editors_choice') }" @click="onNavClick">Выбор редакции</Link>
            <Link :href="route('articles.index', { section: 'new' })" class="side-link" :class="{ 'side-link--active': pageUrl.includes('section=new') }" @click="onNavClick">Новинки</Link>

            <p class="side-nav-title">Категории</p>
            <Link :href="route('articles.index')" class="side-link" :class="{ 'side-link--active': isAllArticlesActive }" @click="onNavClick">Все статьи</Link>
            <Link
                v-for="cat in categories"
                :key="`drawer-${cat.id}`"
                :href="route('articles.index', { category: cat.id })"
                class="side-link"
                :class="{ 'side-link--active': isCategoryActive(cat.id) }"
                @click="onNavClick"
            >
                {{ cat.name }}
            </Link>

            <p class="side-nav-title">Объекты</p>
            <Link
                v-for="range in objectRanges"
                :key="`drawer-${range.index}`"
                :href="route('articles.objects', range.index)"
                class="side-link"
                :class="{ 'side-link--active': route().current() === 'articles.objects' && Number(route().params.range) === range.index }"
                @click="onNavClick"
            >
                {{ range.label }}
            </Link>
        </nav>

        <div class="side-footer side-footer--drawer">
            <button type="button" class="side-link side-link--feedback" @click="feedbackOpen = true; onNavClick()">
                Обратная связь
            </button>
        </div>
    </MobileDrawer>

    <FeedbackModal :open="feedbackOpen" allow-type-select @close="feedbackOpen = false" />
</template>

<style scoped>
.sideMenu {
    width: 240px;
    min-width: 240px;
    background: var(--sidebar-bg, var(--dark_black));
    color: white;
    padding-bottom: 2rem;
    min-height: calc(100vh - 130px);
    display: flex;
    flex-direction: column;
}

.userMenu {
    padding: 16px 14px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.userMenu--drawer {
    margin: 0 0.75rem 0.5rem;
    border-bottom: none;
    padding: 0.5rem 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
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

.userMenu--drawer .userLogo {
    width: 48px;
    height: 48px;
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

.side-nav--drawer {
    padding: 0.25rem 0 0;
}

.side-nav-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255, 255, 255, 0.45);
    padding: 0.75rem 1.25rem 0.35rem;
    margin: 0;
}

.side-nav--drawer .side-nav-title {
    padding-left: 1rem;
}

.side-link {
    display: block;
    padding: 0.5rem 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.15s;
}

.side-nav--drawer .side-link {
    padding-left: 1rem;
    padding-right: 1rem;
    border-radius: 8px;
    margin: 0 0.75rem;
}

.side-link:hover { background: rgba(255, 255, 255, 0.08); }

.side-link--active {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    border-left: 3px solid rgba(255, 255, 255, 0.5);
    padding-left: calc(1.25rem - 3px);
}

.side-nav--drawer .side-link--active {
    padding-left: calc(1rem - 3px);
}

.side-footer {
    margin-top: auto;
    padding: 0.5rem 0 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.side-footer--drawer {
    margin: 0.75rem 0.75rem 0;
    padding-top: 0.75rem;
}

.side-link--feedback {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    cursor: pointer;
    font: inherit;
}

@media (max-width: 768px) {
    .sideMenu--desktop {
        display: none;
    }
}
</style>
