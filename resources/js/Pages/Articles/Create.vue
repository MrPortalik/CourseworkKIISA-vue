<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import { uploadArticleContentImage } from '@/lib/articleContentImage'

const props = defineProps({
    categories: Array,
    tags: Array,
})

const page = usePage()
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin')

const bannerPreview = ref(null)
const heroPreview = ref(null)
const contentImageInput = ref(null)
const uploadingImage = ref(false)

const form = useForm({
    title: '',
    content: '',
    is_publishable: false,
    is_published: false,
    category_id: '',
    tag_ids: [],
    banner: null,
    hero_banner: null,
})

const onBannerChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.banner = file
    bannerPreview.value = URL.createObjectURL(file)
}

const onHeroChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.hero_banner = file
    heroPreview.value = URL.createObjectURL(file)
}

const insertContentImage = () => contentImageInput.value?.click()

const onContentImageSelected = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    uploadingImage.value = true
    try {
        const url = await uploadArticleContentImage(file)
        form.content += (form.content ? '\n' : '') + `<img src="${url}" class="content-image-float" alt="" />`
    } finally {
        uploadingImage.value = false
        event.target.value = ''
    }
}

const submit = () => form
    .transform((data) => ({
        ...data,
        category_id: data.category_id || null,
    }))
    .post(route('articles.store'), { forceFormData: true })
</script>

<template>
    <Head title="Создание статьи" />
    <HeaderComponent />

    <div class="article-create content-area">
        <div class="page-header">
            <h1>Создание статьи</h1>
            <Link :href="route('articles.index')" class="back-button">Назад</Link>
        </div>

        <form class="article-form" @submit.prevent="submit">
            <div class="form-group">
                <label class="form-label">Заголовок</label>
                <input v-model="form.title" type="text" class="form-input" required placeholder="Например: Объект 42" />
            </div>

            <div class="form-group">
                <label class="form-label">Обложка (книжная ориентация)</label>
                <input type="file" accept="image/*" @change="onBannerChange" />
                <img v-if="bannerPreview" :src="bannerPreview" alt="" class="preview-book" />
            </div>

            <div class="form-group">
                <label class="form-label">Баннер над заголовком (горизонтальный, необязательно)</label>
                <input type="file" accept="image/*" @change="onHeroChange" />
                <img v-if="heroPreview" :src="heroPreview" alt="" class="preview-hero" />
            </div>

            <div class="form-group">
                <label class="form-label">Категория</label>
                <select v-model="form.category_id" class="form-input">
                    <option value="">Без категории</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Теги</label>
                <label v-for="t in tags" :key="t.id" class="tag-label">
                    <input v-model="form.tag_ids" type="checkbox" :value="t.id" />
                    {{ t.name }}
                </label>
            </div>

            <div class="form-group">
                <div class="content-toolbar">
                    <label class="form-label">Содержание</label>
                    <button type="button" class="toolbar-btn" :disabled="uploadingImage" @click.prevent="insertContentImage">
                        Вставить изображение справа
                    </button>
                    <input ref="contentImageInput" type="file" accept="image/*" class="hidden" @change.stop="onContentImageSelected" />
                </div>
                <textarea v-model="form.content" class="form-textarea" rows="15" required />
            </div>

            <div class="form-group publish-options">
                <label class="checkbox-label">
                    <input v-model="form.is_publishable" type="checkbox" />
                    <span>Предложить к публикации</span>
                </label>
                <label v-if="isAdmin" class="checkbox-label">
                    <input v-model="form.is_published" type="checkbox" />
                    <span>Опубликовать <em class="admin-only">(Только для администрации)</em></span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-button" :disabled="form.processing">Создать</button>
                <Link :href="route('articles.index')" class="cancel-button">Отмена</Link>
            </div>
        </form>
    </div>
</template>

<style scoped>
.article-create.content-area { max-width: 800px; margin: 0 auto; padding: 2rem 1rem; width: 100%; }
.page-header { display: flex; justify-content: space-between; margin-bottom: 2rem; }
.article-form { background: #fff; padding: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; }
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
.form-input, .form-textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 6px; }
.preview-book { aspect-ratio: 9/16; max-width: 180px; object-fit: cover; margin-top: 0.75rem; border-radius: 4px; }
.preview-hero { width: 100%; max-height: 200px; object-fit: cover; margin-top: 0.75rem; border-radius: 6px; }
.tag-label { display: inline-flex; gap: 0.35rem; margin-right: 1rem; }
.publish-options { display: flex; flex-direction: column; gap: 0.65rem; }
.checkbox-label { display: flex; gap: 0.5rem; align-items: flex-start; font-weight: 600; }
.checkbox-label em.admin-only { font-style: normal; font-weight: 500; color: #718096; }
[data-theme="dark"] .checkbox-label em.admin-only { color: #aaa; }
.form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.submit-button { background: #4299e1; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; }
.cancel-button { padding: 0.75rem 1.5rem; text-decoration: none; color: #4a5568; }
.hidden { display: none; }
.toolbar-btn { margin-left: auto; }
.content-toolbar { display: flex; align-items: center; gap: 0.5rem; }
.back-button { color: #4299e1; }
[data-theme="dark"] .article-form {
    background: #141414;
    border-color: #333;
}
[data-theme="dark"] .form-label { color: #f0f0f0; }
</style>
