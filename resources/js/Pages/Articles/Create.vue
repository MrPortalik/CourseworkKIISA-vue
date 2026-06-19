<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import { computed, ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleTaxonomyPickers from '@/Components/Articles/ArticleTaxonomyPickers.vue'
import ArticleContentField from '@/Components/Articles/ArticleContentField.vue'
import CoauthorInvitePanel from '@/Components/Articles/CoauthorInvitePanel.vue'
import PublicationRulesBanner from '@/Components/Articles/PublicationRulesBanner.vue'
import InputError from '@/Components/InputError.vue'
import CatCheckbox from '@/Components/UI/CatCheckbox.vue'
import { imageAccept, validateImageFile } from '@/lib/imageUpload'
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
    isFirstPublication: { type: Boolean, default: false },
})

const page = usePage()
const isAdmin = computed(() => ['admin', 'owner'].includes(page.props.auth?.user?.role))

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
    is_hit: false,
    is_editors_choice: false,
    category_ids: [],
    tag_ids: [],
    coauthor_user_ids: [],
    banner: null,
    hero_banner: null,
    accept_portal_rules: false,
})

const fileError = ref('')

const onBannerChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    const error = validateImageFile(file)
    if (error) {
        fileError.value = error
        e.target.value = ''
        return
    }
    fileError.value = ''
    form.banner = file
    bannerPreview.value = URL.createObjectURL(file)
}

const onHeroChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    const error = validateImageFile(file)
    if (error) {
        fileError.value = error
        e.target.value = ''
        return
    }
    fileError.value = ''
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
    const error = validateImageFile(file)
    if (error) {
        fileError.value = error
        event.target.value = ''
        return
    }
    fileError.value = ''
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
    <PageHead
        title="Создание статьи"
        description="Напишите и оформите новую статью для портала КИИСА: черновик, теги и отправка на модерацию."
    />
    <HeaderComponent />

    <div class="article-create content-area">
        <div class="page-header">
            <h1>Создание статьи</h1>
            <Link :href="route('articles.index')" class="back-button">Назад</Link>
        </div>

        <PublicationRulesBanner />

        <form class="article-form" @submit.prevent="submit">
            <p v-if="fileError" class="file-error">{{ fileError }}</p>
            <div class="form-group">
                <label class="form-label">Заголовок</label>
                <input v-model="form.title" type="text" class="form-input" :class="{ 'form-input--invalid': form.errors.title }" required placeholder="Например: Объект 42" />
                <InputError :message="form.errors.title" class="field-error" />
            </div>

            <div class="form-group">
                <label class="form-label">Обложка (книжная ориентация)</label>
                <input type="file" :accept="imageAccept" @change="onBannerChange" />
                <InputError :message="form.errors.banner" class="field-error" />
                <img v-if="bannerPreview" :src="bannerPreview" alt="" class="preview-book" />
            </div>

            <div class="form-group">
                <label class="form-label">Баннер над заголовком (горизонтальный, необязательно)</label>
                <input type="file" :accept="imageAccept" @change="onHeroChange" />
                <InputError :message="form.errors.hero_banner" class="field-error" />
                <img v-if="heroPreview" :src="heroPreview" alt="" class="preview-hero" />
            </div>

            <ArticleTaxonomyPickers
                class="taxonomy-section"
                :categories="categories"
                :tags="tags"
                :is-admin="isAdmin"
                v-model:category-ids="form.category_ids"
                v-model:tag-ids="form.tag_ids"
                v-model:is-hit="form.is_hit"
                v-model:is-editors-choice="form.is_editors_choice"
            />
            <InputError :message="form.errors.category_ids || form.errors.tag_ids" class="field-error taxonomy-error" />

            <div class="form-group coauthors-section">
                <label class="form-label">Со-авторы</label>
                <CoauthorInvitePanel v-model:pending-user-ids="pendingCoauthorIds" />
            </div>

            <div class="form-group">
                <div class="content-toolbar">
                    <label class="form-label">Содержание</label>
                    <button type="button" class="toolbar-btn btn-accent" :disabled="uploadingImage" @mousedown.prevent="insertContentImage">
                        Добавить в текущий выделенный абзац
                    </button>
                    <input ref="contentImageInput" type="file" :accept="imageAccept" class="hidden" @change.stop="onContentImageSelected" />
                </div>
                <ArticleContentField
                    ref="contentFieldRef"
                    v-model:content="form.content"
                    :image-labels="imageLabels"
                    :rows="15"
                    required
                    @insert-at-paragraph="onInsertAtParagraph"
                />
                <InputError :message="form.errors.content" class="field-error" />
            </div>

            <div class="form-group publish-options">
                <CatCheckbox v-model="form.is_publishable">
                    Предложить к публикации
                </CatCheckbox>
                <label v-if="isFirstPublication && form.is_publishable" class="portal-rules-check">
                    <CatCheckbox v-model="form.accept_portal_rules" />
                    <span>
                        Подтверждаю согласие с
                        <Link :href="route('publication-rules')" class="portal-rules-link" target="_blank">правилами портала</Link>
                        при первой публикации
                    </span>
                </label>
                <InputError :message="form.errors.accept_portal_rules" />
                <CatCheckbox v-if="isAdmin" v-model="form.is_published">
                    Опубликовать <em class="admin-only">(Только для администрации)</em>
                </CatCheckbox>
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
.file-error { color: #c53030; margin: 0 0 1rem; font-weight: 600; }
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 6px; }
.form-input--invalid { border-color: #fc8181; }
.field-error :deep(p) { color: #c53030; margin: 0.35rem 0 0; font-size: 0.875rem; font-weight: 600; }
.coauthors-section { margin-top: 1.25rem; }
.taxonomy-error { margin-top: -0.5rem; margin-bottom: 0.75rem; }
.preview-book { aspect-ratio: 9/16; max-width: 180px; object-fit: cover; margin-top: 0.75rem; border-radius: 4px; }
.preview-hero { width: 100%; max-height: 200px; object-fit: cover; margin-top: 0.75rem; border-radius: 6px; }
.publish-options { display: flex; flex-direction: column; gap: 0.65rem; }
.publish-options :deep(.cat-checkbox__label) { font-weight: 600; }
.portal-rules-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.95rem;
    line-height: 1.45;
    cursor: pointer;
}
.portal-rules-link {
    color: #0db7ff;
    font: inherit;
    font-weight: 600;
    text-decoration: underline;
}
.publish-options em.admin-only { font-style: normal; font-weight: 500; color: #718096; }
[data-theme="dark"] .publish-options em.admin-only { color: #aaa; }
[data-theme="dark"] .portal-rules-link { color: #67e8f9; }
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
