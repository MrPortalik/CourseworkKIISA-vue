# Схемы пользовательских и задачных потоков — портал КИИСА

Документ описывает основные сценарии работы с информационным порталом КИИСА (Laravel + Vue/Inertia).

---

## 1. Пользовательские потоки (User Flow)

### 1.1 Гость

```mermaid
flowchart TD
    A[Главная /] --> B[Список статей /articles]
    A --> C[О проекте /aboutus]
    A --> D[FAQ /faq]
    B --> E{Поиск / фильтры}
    E -->|Текст q| F[Результаты поиска]
    E -->|Категория| G[Фильтр по категории]
    E -->|Теги| H[Фильтр по тегам]
    E -->|Раздел| I[Хиты / Выбор редакции / Новое]
    F --> J[Просмотр статьи]
    G --> J
    H --> J
    I --> J
    J --> K[Читать контент и комментарии]
    A --> L[Регистрация]
    A --> M[Вход]
    L --> N[Подтверждение email]
    M --> O[Личный кабинет / dashboard]
```

### 1.2 Автор (зарегистрированный пользователь)

```mermaid
flowchart TD
    A[Вход в систему] --> B[Профиль автора /authors/id]
    B --> C[Редактирование биографии]
    B --> D[Подписчики уведомляются о публикациях]
    A --> E[Создать статью /articles/create]
    E --> F[Заполнить заголовок, категории, теги]
    F --> G[Загрузить баннер / hero-баннер]
    F --> H[Редактор содержания + изображения]
    F --> I[Пригласить соавторов]
    E --> J{Действие при сохранении}
    J -->|Черновик| K[Статья не опубликована]
    J -->|Предложить к публикации| L[is_publishable = true]
    K --> M[Черновики /articles/drafts]
    M --> N[Редактировать /articles/slug/edit]
    L --> O[Ожидание модерации]
    O --> P{Решение админа}
    P -->|Одобрено| Q[Статья опубликована + уведомление]
    P -->|Отклонено| R[Причина отклонения + уведомление]
    R --> N
    Q --> S[Публичная страница статьи]
```

### 1.3 Соавтор

```mermaid
flowchart TD
    A[Приглашение от автора] --> B[Уведомление]
    B --> C{Ответ}
    C -->|Принять| D[Статус accepted]
    C -->|Отклонить| E[Статус declined]
    D --> F[Отображение в карточке статьи]
```

### 1.4 Администратор

```mermaid
flowchart TD
    A[Вход как admin] --> B[Панель /admin]
    B --> C[Модерация статей на публикацию]
    B --> D[Категории /admin/categories]
    B --> E[Теги /admin/tags]
    C --> F{Решение}
    F -->|Одобрить| G[Публикация + уведомление автору]
    F -->|Отклонить| H[Снятие с публикации + причина]
    G --> I[Флаги: хит, выбор редакции]
    D --> J[CRUD категорий]
    E --> K[CRUD тегов с описанием]
```

### 1.5 Авторизованный читатель

```mermaid
flowchart TD
    A[Просмотр статьи] --> B{Действия}
    B --> C[Оценить статью 1–5]
    B --> D[Оставить комментарий]
    B --> E[Голос за/против комментария]
    A --> F[Подписаться на автора]
    F --> G[Уведомления о новых публикациях]
    G --> H[/notifications]
```

---

## 2. Задачные потоки (Task Flow)

### 2.1 Создание и редактирование статьи

```mermaid
sequenceDiagram
    participant А as Автор
    participant UI as Create/Edit.vue
    participant API as ArticleController
    participant FS as storage/app/public
    participant DB as База данных

    А->>UI: Заполняет форму
    А->>UI: Загружает изображение в текст
    UI->>API: POST /articles/content-images
    API->>FS: store(articles/content)
    API-->>UI: { url: /storage/... }
    UI->>UI: Вставка маркера [изображение:id]
    А->>UI: Отправка формы
    UI->>API: POST/PUT /articles
    API->>FS: store(banners, hero_banners)
    API->>DB: Сохранение статьи, тегов, категорий
    API->>API: Приглашение соавторов
    API-->>UI: Редирект на articles.show
```

### 2.2 Модерация публикации

```mermaid
sequenceDiagram
    participant А as Автор
    participant AD as Админ
    participant AC as ArticleController
    participant MC as ArticleModerationController
    participant N as Уведомления

    А->>AC: Создание/редактирование с «Предложить к публикации»
    AC->>AC: is_publishable = true, is_published = false
    AD->>MC: POST /admin/articles/{id}/approve
    MC->>MC: is_published = true, published_at
    MC->>N: ArticlePublicationApprovedNotification
    MC->>N: Подписчикам — ArticlePublishedNotification

    AD->>MC: POST /admin/articles/{id}/reject + reason
    MC->>MC: is_publishable = false, причина отклонения
    MC->>N: ArticlePublicationRejectedNotification
```

### 2.3 Поиск и фильтрация статей

```mermaid
flowchart LR
    A[Запрос /articles] --> B{Параметры URL}
    B -->|q| C[ArticleSearchService: ранжирование]
    B -->|category| D[Фильтр по основной/доп. категории]
    B -->|categories[]| E[Фильтр по нескольким категориям]
    B -->|tags[]| F[Фильтр по тегам AND/OR]
    B -->|section| G[hits / editors_choice / new]
    C --> H[Пагинация 21/63]
    D --> H
    E --> H
    F --> H
    G --> H
    H --> I[ArticleCard + слайдеры категорий]
    A --> J[GET /articles/search-preview]
    J --> K[Быстрый автокомплит заголовков]
```

### 2.4 Комментарии и рейтинг

```mermaid
sequenceDiagram
    participant U as Пользователь
    participant S as Articles/Show
    participant RC as RatingController
    participant CC as CommentController
    participant CV as CommentVoteController

    U->>S: Открывает опубликованную статью
    alt Не автор статьи
        U->>RC: POST /articles/{slug}/rate
        RC->>RC: Сохранение оценки 1–5
    end
    U->>CC: POST /articles/{slug}/comments
    CC->>CC: Создание комментария (дерево ответов)
    U->>CV: POST /comments/{id}/vote
    CV->>CV: upvote / downvote
    U->>CC: DELETE /comments/{id}
```

### 2.5 Хранение и отдача изображений (VPS)

```mermaid
flowchart TD
    A[Загрузка файла] --> B[store на диск public]
    B --> C[storage/app/public/...]
    C --> D{public/storage симлинк?}
    D -->|Да| E[GET /storage/... через nginx]
    D -->|Нет| F[404 — файл не отдаётся]
    E --> G[Отображение в статье]
```

---

## 3. Роли и доступ

| Роль | Основные маршруты |
|------|-------------------|
| Гость | `/`, `/articles`, `/articles/{slug}` (только опубликованные), `/login`, `/register` |
| Автор | `+` `/articles/create`, `/articles/drafts`, редактирование своих статей |
| Соавтор | Принятие приглашений, отображение в статье |
| Админ | `+` `/admin`, модерация, таксономия, публикация напрямую |
