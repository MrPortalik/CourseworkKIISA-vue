# Карта сайта — портал КИИСА

Документация структуры приложения на основе `routes/web.php`, `routes/auth.php` и страниц `resources/js/Pages/`.

**SEO sitemap (публичные URL):** `public/sitemap.xml`  
**Диаграммы для Word:** `docs/diagrams/svg/sitemap-*.svg` — перегенерация: `node scripts/generate-sitemap-diagrams.mjs`

---

## Легенда доступа

| Уровень | Описание |
|---------|----------|
| **Публичный** | Доступ без авторизации |
| **auth** | Требуется вход |
| **verified** | Вход + подтверждённый email |
| **admin** | Роль admin или owner |

Динамические URL (`/articles/{slug}`, `/authors/{user}`) не включены в `public/sitemap.xml` — они индексируются через контент и внутренние ссылки.

---

## Публичные страницы (SEO)

| URL | Имя маршрута | Vue-страница | В sitemap.xml |
|-----|--------------|--------------|---------------|
| `/` | `/` | Welcome.vue | ✓ |
| `/articles` | articles.index | Articles/Index.vue | ✓ |
| `/articles/objects/{range}` | articles.objects | Articles/Index.vue | — |
| `/articles/{slug}` | articles.show | Articles/Show.vue | — |
| `/aboutus` | aboutus | AboutUs.vue | ✓ |
| `/faq` | faq.index | Faq/Index.vue | ✓ |

Параметры `/articles`: `section` (hits, editors_choice, new), `category`, `q` (поиск).

---

## Аутентификация (guest / auth)

| URL | Имя маршрута | Vue-страница | Доступ |
|-----|--------------|--------------|--------|
| `/login` | login | Auth/Login.vue | guest |
| `/register` | register | Auth/Register.vue | guest |
| `/forgot-password` | password.request | Auth/ForgotPassword.vue | guest |
| `/reset-password/{token}` | password.reset | Auth/ResetPassword.vue | guest |
| `/verify-email` | verification.notice | Auth/VerifyEmail.vue | auth |
| `/verify-email/{id}/{hash}` | verification.verify | — | auth, signed |
| `/confirm-password` | password.confirm | Auth/ConfirmPassword.vue | auth |
| `/logout` | logout | — (GET, редирект) | verified |

---

## Личный кабинет и профиль (verified)

| URL | Имя маршрута | Vue-страница | Доступ |
|-----|--------------|--------------|--------|
| `/dashboard` | dashboard | Dashboard.vue (profile) | auth, verified |
| `/authors/{user}` | authors.show | Authors/Show.vue | verified |
| `/profile` | profile.edit | Profile/Edit.vue | verified |
| `/notifications` | notifications.index | Notifications/Index.vue | verified |
| `/subscriptions` | — | редирект → `/notifications` | verified |

POST: `/profile/dashboard` (bio), `/profile` (update), DELETE `/profile` (destroy), подписки, уведомления (read/read-all/destroy).

---

## Статьи — действия автора (auth)

| URL | Имя маршрута | Vue-страница | Доступ |
|-----|--------------|--------------|--------|
| `/articles/drafts` | articles.drafts | Articles/Drafts.vue | auth |
| `/articles/create` | articles.create | Articles/Create.vue | auth |
| `/articles/{slug}/edit` | articles.edit | Articles/Edit.vue | auth |
| `/articles/search-preview` | articles.search-preview | — (JSON) | auth |

POST/PUT/DELETE: store, update, destroy, content-images, coauthors, rate, comments, reports.

---

## Соавторы и жалобы (auth)

| URL | Имя маршрута | Доступ |
|-----|--------------|--------|
| `/users/search` | users.search | auth |
| `/articles/{slug}/coauthors` | articles.coauthors.invite | auth |
| `/coauthors/{coauthor}/accept` | coauthors.accept | auth |
| `/coauthors/{coauthor}/decline` | coauthors.decline | auth |
| `/reports` | reports.store | auth (FeedbackModal, жалобы) |
| `/messages/{message}/reply` | messages.reply | auth |

Типы жалоб: `article_complaint`, `user_complaint`, `feedback`, `site_complaint`.

---

## Админ-панель (auth + admin)

| URL | Имя маршрута | Vue-страница |
|-----|--------------|--------------|
| `/admin` | admin.index | Admin/Index.vue |
| `/admin/categories` | admin.categories | Admin/Categories.vue |
| `/admin/tags` | admin.taxonomy | Admin/Taxonomy.vue |
| `/admin/reports` | admin.reports.index | Admin/Reports.vue |
| `/admin/users` | admin.users.index | Admin/Users.vue |
| `/admin/users/{user}` | admin.users.show | Admin/UserShow.vue |

POST: модерация статей (approve/reject), CRUD категорий и тегов, respond на жалобы, promote/demote/block/unblock/destroy/message пользователей.

Блокировка: middleware `EnsureUserIsNotBlocked` — разлогин и редирект на login с `block_error`.

---

## SiteMap: Общая структура и публичные страницы

```mermaid
flowchart TB
 ROOT["Портал КИИСА<br/>kiisa.ru"]

 subgraph PUBLIC["Публичные страницы"]
 HOME["/ — Welcome.vue<br/>Главная"]
 ABOUT["/aboutus — AboutUs.vue<br/>О нас"]
 FAQ["/faq — Faq/Index.vue<br/>FAQ"]
 end

 subgraph ARTICLES_PUB["Каталог статей"]
 INDEX["/articles — Articles/Index.vue"]
 HITS["?section=hits — Хиты"]
 EDITORS["?section=editors_choice<br/>Выбор редакции"]
 NEW["?section=new — Новинки"]
 CAT["?category={id}<br/>По категории"]
 OBJ["/articles/objects/{range}<br/>По объёму объекта"]
 SHOW["/articles/{slug}<br/>Articles/Show.vue"]
 end

 subgraph GUEST_NAV["Навигация гостя"]
 LOGIN["/login"]
 REG["/register"]
 end

 ROOT --> HOME
 ROOT --> ABOUT
 ROOT --> FAQ
 ROOT --> INDEX
 INDEX --> HITS
 INDEX --> EDITORS
 INDEX --> NEW
 INDEX --> CAT
 INDEX --> OBJ
 INDEX --> SHOW
 HOME --> LOGIN
 HOME --> REG
```

---

## SiteMap: Статьи — создание и взаимодействие

```mermaid
flowchart TB
 subgraph AUTH_REQ["Требуется auth"]
 DRAFTS["/articles/drafts<br/>Articles/Drafts.vue"]
 CREATE["/articles/create<br/>Articles/Create.vue"]
 EDIT["/articles/{slug}/edit<br/>Articles/Edit.vue"]
 SEARCH["/articles/search-preview<br/>JSON автодополнение"]
 end

 subgraph ACTIONS["Действия на Show.vue"]
 RATE["POST rate / DELETE unrate<br/>1–5 звёзд"]
 COMMENT["POST comments"]
 VOTE["POST comments/{id}/vote"]
 DEL_C["DELETE comments/{id}"]
 REPORT["POST /reports<br/>жалоба на статью"]
 end

 subgraph COAUTH["Соавторы"]
 USR_SRCH["GET /users/search"]
 INVITE["POST coauthors invite"]
 ACCEPT["POST coauthors/accept"]
 DECLINE["POST coauthors/decline"]
 end

 subgraph FILES["Загрузка контента"]
 IMG["POST /articles/content-images"]
 end

 DRAFTS --> EDIT
 CREATE --> IMG
 EDIT --> IMG
 CREATE --> INVITE
 EDIT --> INVITE
 INVITE --> USR_SRCH
 SHOW["Articles/Show.vue"] --> RATE
 SHOW --> COMMENT
 SHOW --> VOTE
 SHOW --> DEL_C
 SHOW --> REPORT
 SHOW --> EDIT
 CREATE --> DRAFTS
```

---

## SiteMap: Аутентификация и личный кабинет

```mermaid
flowchart TB
 subgraph GUEST["Гость"]
 L["/login — Login.vue"]
 R["/register — Register.vue"]
 FP["/forgot-password"]
 RP["/reset-password/{token}"]
 end

 subgraph VERIFIED["auth + verified"]
 DASH["/dashboard — Dashboard.vue<br/>Личный кабинет автора"]
 AUTH_SHOW["/authors/{user}<br/>Authors/Show.vue"]
 PROF["/profile — Profile/Edit.vue<br/>Настройки аккаунта"]
 OUT["GET /logout"]
 end

 subgraph PROFILE_ACT["Настройки профиля"]
 BIO["POST /profile/dashboard<br/>bio, аватар"]
 UPD["PATCH /profile"]
 DEL["DELETE /profile"]
 end

 subgraph BLOCK["Блокировка пользователя"]
 MID["EnsureUserIsNotBlocked"]
 BL["is_blocked → logout"]
 BL_MSG["block_error на login"]
 end

 L --> DASH
 R --> VE["/verify-email"]
 VE --> DASH
 DASH --> AUTH_SHOW
 DASH --> PROF
 PROF --> BIO
 PROF --> UPD
 PROF --> DEL
 DASH --> OUT
 MID --> BL
 BL --> BL_MSG
 BL_MSG --> L
```

---

## SiteMap: Уведомления и подписки

```mermaid
flowchart TB
 subgraph NOTIFY["Уведомления"]
 NIDX["/notifications<br/>Notifications/Index.vue"]
 NREAD["POST notifications/{id}/read"]
 NALL["POST notifications/read-all"]
 NDEL["DELETE notifications/{id}"]
 end

 subgraph SUBS["Подписки на авторов"]
 SUB["POST /authors/{user}/subscribe"]
 UNSUB["DELETE /authors/{user}/subscribe"]
 REDIR["/subscriptions → /notifications"]
 end

 subgraph TYPES["Типы уведомлений"]
 PUB["ArticlePublished"]
 MOD_OK["PublicationApproved"]
 MOD_NO["PublicationRejected"]
 COINV["CoauthorInvitation"]
 BLOCK_N["AccountBlocked"]
 EDIT_N["ArticleEdited"]
 ADMIN_M["AdminMessage"]
 end

 AUTH_SHOW["Authors/Show.vue"] --> SUB
 AUTH_SHOW --> UNSUB
 SUB --> NIDX
 REDIR --> NIDX
 PUB --> NIDX
 MOD_OK --> NIDX
 MOD_NO --> NIDX
 COINV --> NIDX
 BLOCK_N --> NIDX
 EDIT_N --> NIDX
 ADMIN_M --> NIDX
 NIDX --> NREAD
 NIDX --> NALL
 NIDX --> NDEL
```

---

## SiteMap: Админ-панель

```mermaid
flowchart TB
 subgraph ACCESS["Доступ admin / owner"]
 GUARD["middleware admin"]
 NAV["AdminNav.vue"]
 end

 subgraph MOD["Модерация — Admin/Index.vue"]
 ADMIN["GET /admin"]
 APPR["POST approve"]
 REJ["POST reject + reason"]
 end

 subgraph TAX["Таксономия"]
 CATS["GET /admin/categories<br/>Admin/Categories.vue"]
 TAGS["GET /admin/tags<br/>Admin/Taxonomy.vue"]
 CRUD_C["POST/PUT/DELETE categories"]
 CRUD_T["POST/PUT/DELETE tags"]
 end

 subgraph REPORTS["Жалобы — Admin/Reports.vue"]
 REP_IDX["GET /admin/reports"]
 REP_RSP["POST reports/{id}/respond"]
 FB["feedback, site_complaint"]
 ART_REP["article_complaint"]
 USER_REP["user_complaint"]
 end

 subgraph USERS["Пользователи"]
 USR_IDX["GET /admin/users<br/>Admin/Users.vue"]
 USR_SHOW["GET /admin/users/{user}<br/>Admin/UserShow.vue"]
 PROMOTE["POST promote / demote"]
 BLOCK["POST block + reason + until"]
 UNBLOCK["POST unblock"]
 MSG["POST message → AdminMessage"]
 DESTROY["DELETE user"]
 end

 GUARD --> NAV
 NAV --> ADMIN
 NAV --> REP_IDX
 NAV --> USR_IDX
 NAV --> CATS
 NAV --> TAGS
 ADMIN --> APPR
 ADMIN --> REJ
 CATS --> CRUD_C
 TAGS --> CRUD_T
 REP_IDX --> REP_RSP
 REP_IDX --> FB
 REP_IDX --> ART_REP
 REP_IDX --> USER_REP
 USR_IDX --> USR_SHOW
 USR_SHOW --> PROMOTE
 USR_SHOW --> BLOCK
 USR_SHOW --> UNBLOCK
 USR_SHOW --> MSG
 USR_SHOW --> DESTROY
 BLOCK --> BL_N["AccountBlockedNotification"]
```
