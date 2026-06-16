<script setup>
import { Link, useForm, router, usePage } from '@inertiajs/vue3'
import PageHead from '@/Components/PageHead.vue'
import { computed, ref } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleTaxonomyPickers from '@/Components/Articles/ArticleTaxonomyPickers.vue'
import CoauthorInvitePanel from '@/Components/Articles/CoauthorInvitePanel.vue'
import PublicationRulesBanner from '@/Components/Articles/PublicationRulesBanner.vue'
import { imageAccept, validateImageFile } from '@/lib/imageUpload'
import { uploadArticleContentImage } from '@/lib/articleContentImage'
import ArticleContentField from '@/Components/Articles/ArticleContentField.vue'
import {
    buildArticleHtml,
    createImageId,
    extractContentImages,
    getReferencedImageIds,
    htmlToPlainText,
    insertImageAfterParagraph,
} from '@/lib/articleContent'

const props = defineProps({
    article: Object,
    categories: Array,
    tags: Array,
    pendingCoauthors: { type: Array, default: () => [] },
    acceptedCoauthors: { type: Array, default: () => [] },
})

const page = usePage()
const isAdmin = computed(() => ['admin', 'owner'].includes(page.props.auth.user?.role))
const canModerate = computed(() => ['admin', 'owner', 'moderator'].includes(page.props.auth.user?.role))
const fileError = ref('')
const isOwn = computed(() => page.props.auth.user?.id === props.article.user_id)
const isCoauthor = computed(() =>
    props.acceptedCoauthors.some((c) => c.id === page.props.auth.user?.id),
)
const canSetPublishable = computed(() => !canModerate.value || isOwn.value || isCoauthor.value)

const bannerPreview = ref(props.article.banner || null)
const heroPreview = ref(props.article.hero_banner || null)
const contentImageInput = ref(null)
const contentFieldRef = ref(null)
const uploadingImage = ref(false)
const insertAtParagraph = ref(0)

const embeddedImages = ref(extractContentImages(props.article.content))

const form = useForm({
    title: props.article.title,
    content: htmlToPlainText(props.article.content),
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

const onInsertAtParagraph = (paragraphIndex) => {
    openImagePicker(paragraphIndex)
}

const insertContentImage = () => {
    const idx = contentFieldRef.value?.getParagraphIndexAtCursor?.() ?? 0
    openImagePicker(idx)
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
            _method: 'PUT',
        }
    })
    .post(route('articles.update', props.article.slug), { forceFormData: true })

const deleteArticle = () => {
    if (confirm('Удалить статью?')) router.delete(route('articles.destroy', props.article.slug))
}
</script>

<template>
    <PageHead
        title="Редактирование"
        description="Измените текст, категории и теги статьи на портале КИИСА перед публикацией."
    />
    <HeaderComponent />

    <div class="article-edit content-area">
        <div class="page-header">
            <h1>Редактирование</h1>
            <Link :href="route('articles.show', article.slug)" class="back-button">Назад</Link>
        </div>

        <PublicationRulesBanner />

        <form class="article-form" @submit.prevent="submit">
            <p v-if="fileError" class="file-error">{{ fileError }}</p>
            <div class="form-group">
                <label class="form-label">Заголовок</label>
                <input v-model="form.title" type="text" class="form-input" required />
            </div>

            <div class="form-group">
                <label class="form-label">Обложка (книжная)</label>
                <input type="file" :accept="imageAccept" @change="onBannerChange" />
                <img v-if="bannerPreview" :src="bannerPreview" class="preview-book" alt="" />
            </div>

            <div class="form-group">
                <label class="form-label">Баннер над заголовком</label>
                <input type="file" :accept="imageAccept" @change="onHeroChange" />
                <img v-if="heroPreview" :src="heroPreview" class="preview-hero" alt="" />
            </div>

            <ArticleTaxonomyPickers
                :categories="categories"
                :tags="tags"
                :is-admin="isAdmin"
                v-model:category-ids="form.category_ids"
                v-model:tag-ids="form.tag_ids"
                v-model:is-hit="form.is_hit"
                v-model:is-editors-choice="form.is_editors_choice"
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
                <div class="content-toolbar">
                    <label class="form-label">Содержание</label>
                    <button
                        type="button"
                        class="toolbar-btn btn-accent"
                        :disabled="uploadingImage"
                        @mousedown.prevent="insertContentImage"
                    >
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
            </div>

            <div class="form-group publish-options">
                <label v-if="canSetPublishable" class="checkbox-label">
                    <input v-model="form.is_publishable" type="checkbox" />
                    <span>Предложить к публикации</span>
                </label>
                <template v-if="canModerate">
                    <label class="checkbox-label">
                        <input v-model="form.is_published" type="checkbox" />
                        <span>Опубликовать <em class="admin-only">(Только для администрации)</em></span>
                    </label>
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
.file-error { color: #c53030; margin: 0 0 1rem; font-weight: 600; }
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 6px; }
.content-toolbar { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
.content-toolbar .form-label { margin-bottom: 0; flex: 1; }
.content-toolbar .toolbar-btn { margin-left: auto; margin-top: 0.25rem; margin-bottom: 0.25rem; }
.hidden { display: none; }
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
