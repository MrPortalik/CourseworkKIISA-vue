<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleTaxonomyPickers from '@/Components/ArticleTaxonomyPickers.vue'
import CoauthorInvitePanel from '@/Components/CoauthorInvitePanel.vue'
import { uploadArticleContentImage } from '@/lib/articleContentImage'

const props = defineProps({
    article: Object,
    categories: Array,
    tags: Array,
    pendingCoauthors: { type: Array, default: () => [] },
    acceptedCoauthors: { type: Array, default: () => [] },
})

const page = usePage()
const isAdmin = computed(() => page.props.auth.user?.role === 'admin')
const isOwn = computed(() => page.props.auth.user?.id === props.article.user_id)

const bannerPreview = ref(props.article.banner || null)
const heroPreview = ref(props.article.hero_banner || null)
const contentImageInput = ref(null)
const uploadingImage = ref(false)

const form = useForm({
    title: props.article.title,
    content: props.article.content,
    is_published: props.article.is_published,
    is_publishable: props.article.is_publishable,
    is_hit: props.article.is_hit ?? false,
    is_editors_choice: props.article.is_editors_choice ?? false,
    is_new: props.article.is_new ?? false,
    category_ids: props.article.categories?.length
        ? props.article.categories.map((c) => c.id)
        : (props.article.category_id ? [props.article.category_id] : []),
    tag_ids: props.article.tags?.map((t) => t.id) ?? [],
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

const onContentImageSelected = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    uploadingImage.value = true
    try {
        const url = await uploadArticleContentImage(file)
        form.content += `\n<img src="${url}" class="content-image-float" alt="" />`
    } finally {
        uploadingImage.value = false
        event.target.value = ''
    }
}

const submit = () => form
    .transform((data) => ({
        ...data,
        category_ids: data.category_ids?.length ? data.category_ids : [],
        _method: 'PUT',
    }))
    .post(route('articles.update', props.article.slug), { forceFormData: true })

const deleteArticle = () => {
    if (confirm('Удалить статью?')) router.delete(route('articles.destroy', props.article.slug))
}
</script>

<template>
    <Head title="Редактирование" />
    <HeaderComponent />

    <div class="article-edit content-area">
        <div class="page-header">
            <h1>Редактирование</h1>
            <Link :href="route('articles.show', article.slug)" class="back-button">Назад</Link>
        </div>

        <form class="article-form" @submit.prevent="submit">
            <div class="form-group">
                <label class="form-label">Заголовок</label>
                <input v-model="form.title" type="text" class="form-input" required />
            </div>

            <div class="form-group">
                <label class="form-label">Обложка (книжная)</label>
                <input type="file" accept="image/*" @change="onBannerChange" />
                <img v-if="bannerPreview" :src="bannerPreview" class="preview-book" alt="" />
            </div>

            <div class="form-group">
                <label class="form-label">Баннер над заголовком</label>
                <input type="file" accept="image/*" @change="onHeroChange" />
                <img v-if="heroPreview" :src="heroPreview" class="preview-hero" alt="" />
            </div>

            <ArticleTaxonomyPickers
                :categories="categories"
                :tags="tags"
                v-model:category-ids="form.category_ids"
                v-model:tag-ids="form.tag_ids"
            />

            <div class="form-group">
                <label class="form-label">Со-авторы</label>
                <CoauthorInvitePanel
                    :article-slug="article.slug"
                    :pending-coauthors="pendingCoauthors"
                    :accepted-coauthors="acceptedCoauthors"
                />
            </div>

            <div class="form-group">
                <textarea v-model="form.content" class="form-textarea" rows="15" required />
                <input ref="contentImageInput" type="file" accept="image/*" class="hidden" @change.stop="onContentImageSelected" />
                <button type="button" class="toolbar-btn" @click.prevent="contentImageInput?.click()">Вставить изображение</button>
            </div>

            <div class="form-group publish-options">
                <label v-if="!isAdmin || isOwn" class="checkbox-label">
                    <input v-model="form.is_publishable" type="checkbox" />
                    <span>Предложить к публикации</span>
                </label>
                <template v-if="isAdmin">
                    <label class="checkbox-label">
                        <input v-model="form.is_published" type="checkbox" />
                        <span>Опубликовать <em class="admin-only">(Только для администрации)</em></span>
                    </label>
                    <div class="admin-flags">
                        <label class="checkbox-label"><input v-model="form.is_hit" type="checkbox" /> Хит</label>
                        <label class="checkbox-label"><input v-model="form.is_editors_choice" type="checkbox" /> Выбор редакции</label>
                    </div>
                </template>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-button" :disabled="form.processing">Сохранить</button>
                <button type="button" class="delete-button" @click="deleteArticle">Удалить</button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.article-edit { max-width: 800px; margin: 0 auto; padding: 2rem; }
.article-form { background: #fff; padding: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; }
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
.form-input, .form-textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 6px; }
.preview-book { aspect-ratio: 9/16; max-width: 160px; object-fit: cover; margin-top: 0.5rem; }
.preview-hero { width: 100%; max-height: 180px; object-fit: cover; margin-top: 0.5rem; border-radius: 6px; }
.publish-options { display: flex; flex-direction: column; gap: 0.65rem; }
.admin-flags { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.25rem; }
.checkbox-label { display: flex; gap: 0.5rem; align-items: flex-start; font-weight: 600; }
.checkbox-label em.admin-only { font-style: normal; font-weight: 500; color: #718096; }
[data-theme="dark"] .checkbox-label em.admin-only { color: #aaa; }
.form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.submit-button { background: #4299e1; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; }
.delete-button { background: #fed7d7; color: #c53030; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; }
.hidden { display: none; }
.back-button { color: #3182ce; }
.tag-label { display: inline-flex; gap: 0.35rem; margin-right: 1rem; }
[data-theme="dark"] .article-form {
    background: #141414;
    border-color: #333;
}
[data-theme="dark"] .form-label { color: #f0f0f0; }
.article-edit.content-area {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 1rem;
}
</style>
