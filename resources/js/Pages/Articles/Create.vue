<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleTaxonomyPickers from '@/Components/Articles/ArticleTaxonomyPickers.vue'
import ArticleContentField from '@/Components/Articles/ArticleContentField.vue'
import CoauthorInvitePanel from '@/Components/Articles/CoauthorInvitePanel.vue'
import { uploadArticleContentImage } from '@/lib/articleContentImage'
import {
    buildArticleHtml,
    createImageId,
    getReferencedImageIds,
    insertImageAfterParagraph,
} from '@/lib/articleContent'

const props = defineProps({
    categories: Array,
    tags: Array,
})

const page = usePage()
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin')

const bannerPreview = ref(null)
const heroPreview = ref(null)
const contentImageInput = ref(null)
const contentFieldRef = ref(null)
const uploadingImage = ref(false)
const embeddedImages = ref([])
const pendingCoauthorIds = ref([])
const insertAtParagraph = ref(0)

const form = useForm({
    title: '',
    content: '',
    is_publishable: false,
    is_published: false,
    category_ids: [],
    tag_ids: [],
    coauthor_user_ids: [],
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

const imageLabels = computed(() =>
    Object.fromEntries(embeddedImages.value.map((img) => [img.id, img.fileName])),
)

const openImagePicker = (paragraphIndex) => {
    insertAtParagraph.value = paragraphIndex
    contentImageInput.value?.click()
}

const insertContentImage = () => {
    const idx = contentFieldRef.value?.getParagraphIndexAtCursor?.() ?? 0
    openImagePicker(idx)
}

const onInsertAtParagraph = (paragraphIndex) => {
    openImagePicker(paragraphIndex)
}

const onContentImageSelected = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    uploadingImage.value = true
    try {
        const url = await uploadArticleContentImage(file)
        const imageId = createImageId()
        embeddedImages.value.push({
            id: imageId,
            src: url,
            className: 'content-image-float',
            alt: '',
            fileName: file.name,
        })
        form.content = insertImageAfterParagraph(
            form.content,
            insertAtParagraph.value,
            imageId,
        )
    } finally {
        uploadingImage.value = false
        event.target.value = ''
    }
}

const submit = () => form
    .transform((data) => {
        const referenced = getReferencedImageIds(data.content)
        const images = embeddedImages.value.filter((img) => referenced.has(img.id))

        return {
            ...data,
            content: buildArticleHtml(data.content, images),
            category_ids: data.category_ids?.length ? data.category_ids : [],
            coauthor_user_ids: pendingCoauthorIds.value,
        }
    })
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

            <ArticleTaxonomyPickers
                :categories="categories"
                :tags="tags"
                v-model:category-ids="form.category_ids"
                v-model:tag-ids="form.tag_ids"
            />

            <div class="form-group">
                <label class="form-label">Со-авторы</label>
                <CoauthorInvitePanel v-model:pending-user-ids="pendingCoauthorIds" />
            </div>

            <div class="form-group">
                <div class="content-toolbar">
                    <label class="form-label">Содержание</label>
                    <button type="button" class="toolbar-btn btn-accent" :disabled="uploadingImage" @mousedown.prevent="insertContentImage">
                        Добавить в текущий выделенный абзац
                    </button>
                    <input ref="contentImageInput" type="file" accept="image/*" class="hidden" @change.stop="onContentImageSelected" />
                </div>
                <ArticleContentField
                    ref="contentFieldRef"
                    v-model:content="form.content"
                    :image-labels="imageLabels"
                    :rows="15"
                    required
                    @insert-at-paragraph="onInsertAtParagraph"
                />
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
.form-input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 6px; }
.preview-book { aspect-ratio: 9/16; max-width: 180px; object-fit: cover; margin-top: 0.75rem; border-radius: 4px; }
.preview-hero { width: 100%; max-height: 200px; object-fit: cover; margin-top: 0.75rem; border-radius: 6px; }
.publish-options { display: flex; flex-direction: column; gap: 0.65rem; }
.checkbox-label { display: flex; gap: 0.5rem; align-items: flex-start; font-weight: 600; }
.checkbox-label em.admin-only { font-style: normal; font-weight: 500; color: #718096; }
[data-theme="dark"] .checkbox-label em.admin-only { color: #aaa; }
.form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.submit-button { background: #4299e1; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; }
.cancel-button { padding: 0.75rem 1.5rem; text-decoration: none; color: #4a5568; }
.hidden { display: none; }
.content-toolbar { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
.content-toolbar .form-label { margin-bottom: 0; flex: 1; }
.content-toolbar .toolbar-btn { margin-left: auto; margin-top: 0.25rem; margin-bottom: 0.25rem; }
.back-button { color: #4299e1; }
[data-theme="dark"] .article-form {
    background: #141414;
    border-color: #333;
}
[data-theme="dark"] .form-label { color: #f0f0f0; }
</style>
