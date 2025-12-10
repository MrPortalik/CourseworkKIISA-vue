<template>
  <div class="article-edit">
    <Head title="Редактирование статьи" />
    
    <div class="page-header">
      <h1>Редактирование статьи</h1>
      <Link :href="route('articles.show', article.slug)" class="back-button">
        Назад к статье
      </Link>
    </div>
    
    <form @submit.prevent="submit" class="article-form">
      <div class="form-group">
        <label for="title" class="form-label">Заголовок</label>
        <input
          type="text"
          id="title"
          v-model="form.title"
          class="form-input"
          :class="{ 'error': form.errors.title }"
          required
        />
        <div v-if="form.errors.title" class="form-error">
          {{ form.errors.title }}
        </div>
      </div>
      
      <div class="form-group">
        <label for="content" class="form-label">Содержание</label>
        <textarea
          id="content"
          v-model="form.content"
          class="form-textarea"
          :class="{ 'error': form.errors.content }"
          rows="15"
          required
        ></textarea>
        <div v-if="form.errors.content" class="form-error">
          {{ form.errors.content }}
        </div>
      </div>
      
      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.is_published" />
          <span>Опубликовано</span>
        </label>
      </div>
      
      <div class="form-actions">
        <button 
          type="submit" 
          class="submit-button" 
          :disabled="form.processing"
        >
          <span v-if="form.processing">Сохранение...</span>
          <span v-else>Сохранить изменения</span>
        </button>
        <button 
          type="button" 
          @click="deleteArticle" 
          class="delete-button"
        >
          Удалить статью
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  article: Object,
})

const form = useForm({
  title: props.article.title,
  content: props.article.content,
  is_published: props.article.is_published,
})

const submit = () => {
  form.put(route('articles.update', props.article.slug))
}

const deleteArticle = () => {
  if (confirm('Вы уверены, что хотите удалить эту статью?')) {
    router.delete(route('articles.destroy', props.article.slug))
  }
}
</script>

<style scoped>
.article-edit {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.back-button {
  color: #3182ce;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}

.back-button:hover {
  background-color: #f7fafc;
}

.article-form {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2d3748;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #cbd5e0;
  border-radius: 0.375rem;
  font-size: 1rem;
  font-family: inherit;
  transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3182ce;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.form-input.error,
.form-textarea.error {
  border-color: #f56565;
}

.form-textarea {
  resize: vertical;
  min-height: 300px;
  line-height: 1.6;
}

.form-error {
  margin-top: 0.5rem;
  color: #f56565;
  font-size: 0.875rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-weight: 500;
  color: #4a5568;
}

.checkbox-label input {
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 0.25rem;
  border: 1px solid #cbd5e0;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.submit-button {
  background-color: #4299e1;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-button:hover:not(:disabled) {
  background-color: #3182ce;
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.delete-button {
  background-color: #fed7d7;
  color: #c53030;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.delete-button:hover {
  background-color: #feb2b2;
}

@media (max-width: 768px) {
  .article-edit {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .article-form {
    padding: 1.5rem;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>