<script setup>

import { useForm } from '@inertiajs/vue3'

import PageHead from '@/Components/PageHead.vue'

import HeaderComponent from '@/Layouts/HeaderComponent.vue'

import AdminNav from '@/Components/Admin/AdminNav.vue'



const props = defineProps({

    settings: Object,

})



const form = useForm({

    hit_ratings_threshold: props.settings.hit_ratings_threshold,

    hit_ratings_days: props.settings.hit_ratings_days,

})



const save = () => {

    form.put(route('admin.moderator-settings.update'), {

        preserveScroll: true,

    })

}

</script>



<template>

    <PageHead

        title="Настройки модерации"

        description="Параметры автоматической модерации и особых категорий на портале КИИСА."

    />

    <HeaderComponent />



    <section class="admin-panel content-area">

        <div class="admin-top">

            <h1>Админ-панель</h1>

            <AdminNav />

        </div>



        <div class="section">

            <h2>Настройки модерации</h2>



            <ul class="settings-list">

                <li class="settings-item">

                    <div class="settings-item-head">

                        <h3>Хиты</h3>

                        <p class="settings-intro">

                            Статья может быть отмечена как «Хит» вручную при редактировании или автоматически,

                            если за указанный период набирает нужное количество оценок.

                        </p>

                    </div>



                    <form class="settings-form" @submit.prevent="save">

                        <label class="field">

                            <span>Количество оценок для попадания в «Хиты»</span>

                            <input

                                v-model.number="form.hit_ratings_threshold"

                                type="number"

                                min="0"

                                max="10000"

                                class="field-input"

                                required

                            />

                        </label>



                        <label class="field">

                            <span>Период (дней), за который учитываются оценки</span>

                            <input

                                v-model.number="form.hit_ratings_days"

                                type="number"

                                min="0"

                                max="365"

                                class="field-input"

                                required

                            />

                        </label>



                        <p class="settings-note">

                            Установите 0 в любом поле, чтобы отключить автоматическое назначение «Хита».

                        </p>



                        <button type="submit" class="btn-save btn-accent" :disabled="form.processing">

                            Сохранить

                        </button>

                    </form>

                </li>

            </ul>

        </div>

    </section>

</template>



<style scoped>

.admin-panel {

    max-width: 1100px;

    margin: 2rem auto;

    padding: 0 1.5rem 3rem;

}

.admin-panel h1 { margin: 0 0 0.75rem; }

.admin-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-end;

    flex-wrap: wrap;

    gap: 0.5rem;

    margin-bottom: 2rem;

}

.section { margin-bottom: 2.5rem; }

.section h2 { margin: 0 0 0.75rem; }

.settings-list {

    list-style: none;

    padding: 0;

    margin: 0;

    display: flex;

    flex-direction: column;

    gap: 1rem;

}

.settings-item {

    border: 1px solid #e2e8f0;

    border-radius: 10px;

    padding: 1.25rem;

    background: #fff;

}

.settings-item-head h3 {

    margin: 0 0 0.5rem;

    font-size: 1.1rem;

}

.settings-intro,

.settings-note {

    margin: 0;

    color: #718096;

    line-height: 1.5;

    font-size: 0.925rem;

}

.settings-note { margin-top: 0.5rem; }

.settings-form {

    display: flex;

    flex-direction: column;

    gap: 1rem;

    margin-top: 1rem;

}

.field {

    display: flex;

    flex-direction: column;

    gap: 0.4rem;

    font-weight: 600;

}

.field-input {

    padding: 0.65rem 0.75rem;

    border: 1px solid #cbd5e0;

    border-radius: 8px;

    font: inherit;

    font-weight: 400;

}

.btn-save {

    align-self: flex-start;

    border: none;

    padding: 0.65rem 1.25rem;

    border-radius: 8px;

    cursor: pointer;

    font-weight: 600;

}

[data-theme="dark"] .admin-panel { color: #f0f0f0; }

[data-theme="dark"] .settings-item {

    background: var(--theme_black);

    border-color: #333;

}

[data-theme="dark"] .settings-intro,

[data-theme="dark"] .settings-note {

    color: #aaa;

}

[data-theme="dark"] .field-input {

    background: #1a1a1a;

    border-color: #404040;

    color: #f0f0f0;

}



@media (max-width: 768px) {

    .admin-panel { margin: 1rem auto; padding: 0 1rem 2rem; }

    .admin-top { flex-direction: column; align-items: flex-start; }

}

</style>


