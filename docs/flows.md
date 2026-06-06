# Диаграммы потоков — портал КИИСА

На основе `routes/web.php`, `routes/auth.php`, контроллеров в `app/Http/Controllers/` и страниц в `resources/js/Pages/`.

---

## TaskFlow: Создание и редактирование статьи

```mermaid
flowchart TB
 subgraph UI["Vue-страницы"]
 CREATE["Articles/Create.vue<br/>GET /articles/create"]
 EDIT["Articles/Edit.vue<br/>GET /articles/{slug}/edit"]
 DRAFTS["Articles/Drafts.vue<br/>GET /articles/drafts"]
 SHOW["Articles/Show.vue<br/>GET /articles/{slug}"]
 end

 subgraph FORM["Форма автора"]
 F1["Заголовок, категории, теги"]
 F2["Обложка banner / hero_banner"]
 F3["Редактор содержания"]
 F4["Приглашение соавторов"]
 F5{"Чекбокс «Предложить к публикации»"}
 end

 subgraph API["ArticleController"]
 STORE["POST /articles → store"]
 UPDATE["PUT /articles/{slug} → update"]
 UPLOAD["POST /articles/content-images"]
 DESTROY["DELETE /articles/{slug}"]
 end

 subgraph STORAGE["Файлы public/storage"]
 BANNERS["banners/"]
 HERO["hero_banners/"]
 CONTENT["articles/content/"]
 end

 subgraph DB["База данных"]
 ART["articles + categories + tags"]
 COAUTH["CoauthorInvitationService"]
 end

 CREATE --> FORM
 EDIT --> FORM
 F3 --> UPLOAD
 UPLOAD --> CONTENT
 UPLOAD -->|"JSON { url }"| F3
 FORM --> STORE
 FORM --> UPDATE
 STORE --> BANNERS
 STORE --> HERO
 UPDATE --> BANNERS
 UPDATE --> HERO
 STORE --> ART
 UPDATE --> ART
 STORE --> COAUTH
 F5 -->|"false"| DRAFTS
 F5 -->|"true, is_publishable"| ART
 STORE --> SHOW
 UPDATE --> SHOW
 DRAFTS --> EDIT
 SHOW --> EDIT
 SHOW --> DESTROY
 DESTROY -->|"редирект"| INDEX["Articles/Index.vue<br/>GET /articles"]
```

---

## TaskFlow: Модерация и публикация

```mermaid
flowchart TB
 subgraph AUTHOR["Действия автора"]
 SUBMIT["Сохранить с is_publishable = true"]
 DRAFT["Сохранить черновик<br/>is_publishable = false"]
 end

 subgraph STATE["Состояние статьи"]
 S1["Черновик<br/>is_published = false"]
 S2["На модерации<br/>is_publishable = true<br/>is_published = false"]
 S3["Опубликована<br/>is_published = true"]
 S4["Отклонена<br/>publication_rejection_reason"]
 end

 subgraph ADMIN_UI["Admin/Index.vue + Articles/Show.vue"]
 PANEL["GET /admin"]
 PREVIEW["Просмотр статьи"]
 BTN_OK["Кнопка «Принять»"]
 BTN_NO["Кнопка «Отклонить» + reason"]
 end

 subgraph MOD["ArticleModerationController"]
 APPROVE["POST /admin/articles/{article}/approve"]
 REJECT["POST /admin/articles/{article}/reject"]
 end

 subgraph DIRECT["Прямая публикация админом"]
 ADMIN_EDIT["ArticleController update<br/>is_published = true"]
 ADMIN_CREATE["ArticleController store<br/>is_published = true"]
 end

 subgraph NOTIFY["Уведомления"]
 N1["ArticlePublicationApprovedNotification → автор"]
 N2["ArticlePublicationRejectedNotification → автор"]
 N3["ArticlePublishedNotification → подписчики"]
 end

 DRAFT --> S1
 SUBMIT --> S2
 S2 --> PANEL
 PANEL --> PREVIEW
 PREVIEW --> BTN_OK
 PREVIEW --> BTN_NO
 BTN_OK --> APPROVE
 BTN_NO --> REJECT
 APPROVE --> S3
 REJECT --> S4
 S4 -->|"редактирование"| EDIT["GET /articles/{slug}/edit"]
 EDIT --> SUBMIT
 APPROVE --> N1
 APPROVE --> N3
 REJECT --> N2
 ADMIN_EDIT --> S3
 ADMIN_CREATE --> S3
 ADMIN_EDIT --> N1
 ADMIN_CREATE --> N3
```

---

## TaskFlow: Поиск и фильтрация статей

```mermaid
flowchart TB
 subgraph ENTRY["Точки входа"]
 LIST["Articles/Index.vue<br/>GET /articles"]
 OBJECTS["GET /articles/objects/{range}<br/>диапазон номеров объекта"]
 PREVIEW["GET /articles/search-preview<br/>JSON автокомплит"]
 end

 subgraph FILTERS["Параметры URL"]
 Q["q — текст поиска"]
 CAT["category — одна категория"]
 CATS["categories[] — несколько"]
 TAGS["tags[] + tags_match all/any"]
 SEC["section: hits / editors_choice / new"]
 SEARCH["search=1 — режим «точное/похожее»"]
 end

 subgraph SERVICE["ArticleSearchService"]
 RANK["rankArticles — ранжирование"]
 SPLIT["splitForListing — exact + related"]
 end

 subgraph BACKEND["ArticleController::buildArticlesListing"]
 PUB["Article::published()"]
 APPLY["applyListingFilters"]
 PAGINATE["ArticlesListingPaginator<br/>стр.1: 21, далее: 63"]
 SLIDERS["categorySliders — 15 статей/категория"]
 end

 subgraph OUTPUT["Результат"]
 GRID["ArticleCard + сетка"]
 SLIDER_UI["CategoryArticleSliders"]
 EXACT["exactArticles + otherArticles"]
 end

 LIST --> FILTERS
 OBJECTS --> PUB
 FILTERS --> PUB
 PUB --> APPLY
 Q --> PREVIEW
 PREVIEW --> RANK
 Q --> RANK
 SEARCH --> SPLIT
 SPLIT --> EXACT
 RANK --> PAGINATE
 APPLY --> PAGINATE
 PAGINATE --> GRID
 LIST -->|"page=1, без фильтров"| SLIDERS
 SLIDERS --> SLIDER_UI
 EXACT --> GRID
```

---

## TaskFlow: Комментарии к статье

```mermaid
flowchart TB
 subgraph VIEW["Articles/Show.vue"]
 PAGE["GET /articles/{slug}<br/>ArticleController::show"]
 THREAD["CommentThread — дерево до 3 уровней"]
 FORM["Форма нового комментария / ответа"]
 end

 subgraph LOAD["Загрузка данных"]
 ROOT["Comment parent_id = null"]
 CHILD["children → children.user"]
 VOTES_CNT["upvotes_count / downvotes_count"]
 USER_VOTES["userCommentVotes для auth"]
 end

 subgraph API["CommentController"]
 STORE["POST /articles/{slug}/comments<br/>body + parent_id?"]
 DELETE["DELETE /comments/{comment}"]
 end

 subgraph RULES["Правила"]
 AUTH["middleware auth"]
 OWNER["Удаление: автор комментария или admin"]
 end

 PAGE --> LOAD
 LOAD --> THREAD
 THREAD --> FORM
 FORM --> AUTH
 AUTH --> STORE
 STORE -->|"Comment::create"| DB[("comments")]
 STORE -->|"back()"| PAGE
 THREAD --> DELETE
 DELETE --> OWNER
 DELETE --> DB
```

---

## TaskFlow: Рейтинг статей (1–5)

```mermaid
flowchart TB
 subgraph VIEW["Articles/Show.vue"]
 SHOW["GET /articles/{slug}"]
 STARS["StarRating компонент"]
 CAN["canRate = auth && не автор статьи"]
 end

 subgraph DATA["ArticleController::show"]
 AVG["loadAvg votes → average_rating"]
 COUNT["loadCount votes → ratings_count"]
 USER["userRating текущего пользователя"]
 end

 subgraph API["RatingController"]
 STORE["POST /articles/{slug}/rate<br/>rating 1–5"]
 UNRATE["DELETE /articles/{slug}/rate"]
 end

 subgraph LOGIC["Логика ArticleVote"]
 SAME["Та же оценка → удалить vote"]
 CHANGE["Другая оценка → updateOrCreate"]
 FORBID["403 — автор не оценивает свою статью"]
 end

 SHOW --> DATA
 DATA --> STARS
 CAN --> STARS
 STARS --> STORE
 STARS --> UNRATE
 STORE --> FORBID
 STORE --> SAME
 STORE --> CHANGE
 SAME --> SHOW
 CHANGE --> SHOW
```

---

## TaskFlow: Голосование за комментарии

```mermaid
flowchart TB
 subgraph UI["CommentThread.vue"]
 UP["👍 upvote"]
 DOWN["👎 downvote"]
 end

 subgraph API["CommentVoteController"]
 VOTE["POST /comments/{comment}/vote<br/>vote: 1 или -1"]
 end

 subgraph RULES["Правила"]
 AUTH["middleware auth"]
 SELF["403 — нельзя голосовать за свой комментарий"]
 TOGGLE["Повторный клик → снять голос"]
 SWITCH["Смена up↔down → updateOrCreate"]
 end

 subgraph DB_TABLE["comment_votes"]
 REC["comment_id + user_id + vote"]
 end

 UP --> AUTH
 DOWN --> AUTH
 AUTH --> VOTE
 VOTE --> SELF
 VOTE --> TOGGLE
 VOTE --> SWITCH
 TOGGLE --> REC
 SWITCH --> REC
 REC -->|"back()"| UI
```

---

## TaskFlow: Аутентификация и регистрация

```mermaid
flowchart TB
 subgraph GUEST_UI["Страницы Auth/*.vue"]
 LOGIN["Login.vue<br/>GET/POST /login"]
 REG["Register.vue<br/>GET/POST /register"]
 FORGOT["ForgotPassword.vue<br/>GET/POST /forgot-password"]
 RESET["ResetPassword.vue<br/>GET/POST /reset-password/{token}"]
 VERIFY["VerifyEmail.vue<br/>GET /verify-email"]
 end

 subgraph SESSION["AuthenticatedSessionController"]
 LOGIN_OK["store → session regenerate"]
 LOGOUT1["POST /logout"]
 LOGOUT2["GET /logout → redirect /"]
 end

 subgraph REGISTER["RegisteredUserController"]
 REG_OK["store → User::create"]
 EVENT["event Registered"]
 AUTO["Auth::login"]
 end

 subgraph VERIFY_FLOW["Подтверждение email"]
 PROMPT["EmailVerificationPromptController"]
 LINK["VerifyEmailController<br/>GET /verify-email/{id}/{hash}"]
 RESEND["POST /email/verification-notification"]
 end

 subgraph PROFILE["Profile/Edit.vue"]
 EDIT["GET /profile"]
 PATCH["PATCH /profile"]
 PASS["PUT /password"]
 DEL["DELETE /profile"]
 CONFIRM["ConfirmPassword.vue"]
 end

 subgraph HOME["После входа"]
 DASH["GET /dashboard → redirect"]
 AUTHOR["Authors/Show.vue<br/>GET /authors/{user}"]
 end

 LOGIN --> LOGIN_OK
 LOGIN_OK --> DASH
 DASH --> AUTHOR
 REG --> REG_OK
 REG_OK --> EVENT
 EVENT --> AUTO
 AUTO --> DASH
 LOGIN_OK --> VERIFY
 VERIFY --> PROMPT
 PROMPT --> LINK
 PROMPT --> RESEND
 LINK --> DASH
 EDIT --> PATCH
 EDIT --> PASS
 EDIT --> DEL
 DEL --> CONFIRM
 LOGOUT1 --> LOGIN
 LOGOUT2 --> LOGIN
 FORGOT --> RESET
 RESET --> LOGIN
```

---

## TaskFlow: Админ — таксономия (категории и теги)

```mermaid
flowchart TB
 subgraph NAV["Навигация из Admin/Index.vue"]
 ADMIN["GET /admin"]
 CAT_PAGE["Admin/Categories.vue<br/>GET /admin/categories"]
 TAG_PAGE["Admin/Taxonomy.vue<br/>GET /admin/tags"]
 end

 subgraph CATEGORIES["AdminTaxonomyController — категории"]
 C_STORE["POST /admin/categories"]
 C_UPDATE["PUT /admin/categories/{category}"]
 C_DELETE["DELETE /admin/categories/{category}"]
 end

 subgraph TAGS["AdminTaxonomyController — теги"]
 T_STORE["POST /admin/tags"]
 T_UPDATE["PUT /admin/tags/{tag}"]
 T_DELETE["DELETE /admin/tags/{tag}"]
 end

 subgraph FIELDS["Поля"]
 CF["name, description"]
 TF["name, description, slug"]
 end

 subgraph CASCADE["Каскад при удалении"]
 C_NULL["articles.category_id → null"]
 C_PIVOT["article_category — detach"]
 T_DETACH["tag→articles detach"]
 end

 subgraph GUARD["middleware auth + verified + admin"]
 end

 ADMIN --> CAT_PAGE
 ADMIN --> TAG_PAGE
 GUARD --> CAT_PAGE
 GUARD --> TAG_PAGE
 CAT_PAGE --> C_STORE
 CAT_PAGE --> C_UPDATE
 CAT_PAGE --> C_DELETE
 TAG_PAGE --> T_STORE
 TAG_PAGE --> T_UPDATE
 TAG_PAGE --> T_DELETE
 C_STORE --> CF
 T_STORE --> TF
 C_DELETE --> C_NULL
 C_DELETE --> C_PIVOT
 T_DELETE --> T_DETACH
```

---

## TaskFlow: Соавторы

```mermaid
flowchart TB
 subgraph INVITE_UI["Create/Edit.vue + CoauthorInvitePanel"]
 SEARCH["GET /users/search?q="]
 PICK["Выбор пользователя"]
 FORM_IDS["coauthor_user_ids при сохранении"]
 EXTRA["POST /articles/{slug}/coauthors<br/>отдельное приглашение"]
 end

 subgraph SERVICE["CoauthorInvitationService"]
 MANY["inviteMany при store/update"]
 ONE["invite"]
 RECORD["ArticleCoauthor status=pending"]
 SKIP["Пропуск: автор, дубликат pending/accepted"]
 end

 subgraph NOTIFY["Уведомление"]
 INV["CoauthorInvitationNotification"]
 PAGE["Notifications/Index.vue"]
 end

 subgraph RESPONSE["Ответ приглашённого"]
 ACCEPT["POST /coauthors/{coauthor}/accept"]
 DECLINE["POST /coauthors/{coauthor}/decline"]
 end

 subgraph STATUS["Статусы ArticleCoauthor"]
 PENDING["pending"]
 ACCEPTED["accepted → в карточке статьи"]
 DECLINED["declined"]
 end

 SEARCH --> PICK
 PICK --> FORM_IDS
 PICK --> EXTRA
 FORM_IDS --> MANY
 EXTRA --> ONE
 MANY --> ONE
 ONE --> SKIP
 ONE --> RECORD
 RECORD --> INV
 INV --> PAGE
 PAGE --> ACCEPT
 PAGE --> DECLINE
 ACCEPT --> ACCEPTED
 DECLINE --> DECLINED
 ACCEPTED --> SHOW["Articles/Show.vue — список coauthors"]
```

---

## TaskFlow: Уведомления и подписки на авторов

```mermaid
flowchart TB
 subgraph SUBSCRIBE["Подписка на автора"]
 PROFILE["Authors/Show.vue"]
 SUB["POST /authors/{user}/subscribe"]
 UNSUB["DELETE /authors/{user}/subscribe"]
 MILESTONE["SubscriberMilestoneNotification<br/>1, 100, 1000…"]
 end

 subgraph PUB_NOTIFY["При публикации статьи"]
 APPROVE["ArticleModerationController::approve"]
 ADMIN_PUB["ArticleController admin publish"]
 PUB_N["ArticlePublishedNotification → подписчикам"]
 end

 subgraph PAGE["Notifications/Index.vue"]
 INDEX["GET /notifications"]
 TAB1["Вкладка «Лента»"]
 TAB2["Вкладка «Подписки»"]
 READ1["POST /notifications/{id}/read"]
 READALL["POST /notifications/read-all"]
 end

 subgraph TYPES["Типы уведомлений"]
 T1["coauthor_invitation"]
 T2["publication_approved / rejected"]
 T3["article_published"]
 T4["subscriber_milestone"]
 end

 subgraph REDIRECT["Маршрут-редирект"]
 OLD["GET /subscriptions → /notifications"]
 end

 PROFILE --> SUB
 PROFILE --> UNSUB
 SUB --> MILESTONE
 SUB --> TAB2
 APPROVE --> PUB_N
 ADMIN_PUB --> PUB_N
 PUB_N --> TAB1
 INDEX --> TAB1
 INDEX --> TAB2
 TAB1 --> READ1
 TAB1 --> READALL
 T1 -->|"accept/decline"| COAUTH["CoauthorController"]
 OLD --> INDEX
```

---

## UserFlow: Гость (неавторизованный)

```mermaid
flowchart TB
 subgraph LANDING["Главная"]
 HOME["Welcome.vue<br/>GET /"]
 ABOUT["AboutUs.vue<br/>GET /aboutus"]
 FAQ["Faq/Index.vue<br/>GET /faq"]
 end

 subgraph ARTICLES["Каталог статей"]
 LIST["Articles/Index.vue<br/>GET /articles"]
 SEARCH["Поиск q, фильтры category/tags/section"]
 PREVIEW["Автокомплит search-preview"]
 OBJECTS["Диапазон объектов /articles/objects/{range}"]
 CARD["ArticleCard → клик"]
 end

 subgraph READ["Чтение"]
 SHOW["Articles/Show.vue<br/>GET /articles/{slug}"]
 CONTENT["Контент, теги, соавторы"]
 COMMENTS["Комментарии — только просмотр"]
 RATING["Рейтинг — без auth недоступен"]
 end

 subgraph AUTH_ENTRY["Вход в систему"]
 LOGIN["Login.vue<br/>GET /login"]
 REG["Register.vue<br/>GET /register"]
 end

 subgraph RESTRICT["Ограничения гостя"]
 NO_DRAFT["Черновики → 404"]
 NO_WRITE["Нет комментариев / оценок / подписок"]
 end

 HOME --> LIST
 HOME --> ABOUT
 HOME --> FAQ
 HOME --> LOGIN
 HOME --> REG
 LIST --> SEARCH
 SEARCH --> PREVIEW
 LIST --> OBJECTS
 LIST --> CARD
 CARD --> SHOW
 SHOW --> CONTENT
 SHOW --> COMMENTS
 SHOW --> RATING
 SHOW --> NO_DRAFT
 SHOW --> NO_WRITE
```

---

## UserFlow: Зарегистрированный автор

```mermaid
flowchart TB
 subgraph ONBOARD["Вход и профиль"]
 LOGIN["POST /login"]
 VERIFY["Подтверждение email<br/>middleware verified"]
 DASH["GET /dashboard"]
 PROFILE["Authors/Show.vue<br/>GET /authors/{user}"]
 BIO["POST /profile/dashboard<br/>bio + avatar"]
 end

 subgraph WRITE["Создание контента"]
 CREATE["Articles/Create.vue<br/>GET /articles/create"]
 SAVE["POST /articles"]
 DRAFTS["Articles/Drafts.vue<br/>GET /articles/drafts"]
 EDIT["Articles/Edit.vue<br/>GET /articles/{slug}/edit"]
 UPDATE["PUT /articles/{slug}"]
 DELETE["DELETE /articles/{slug}"]
 end

 subgraph PUBLISH["Путь к публикации"]
 CHECK{"«Предложить к публикации»?"}
 DRAFT["Черновик is_publishable=false"]
 MOD["Ожидание модерации"]
 OK["Опубликована"]
 REJ["Отклонена + причина в уведомлении"]
 end

 subgraph COAUTH["Соавторы"]
 INVITE["CoauthorInvitePanel"]
 NOTIF["Уведомление приглашённому"]
 end

 subgraph ACCOUNT["Аккаунт"]
 SETTINGS["Profile/Edit.vue<br/>GET /profile"]
 LOGOUT["GET /logout"]
 end

 LOGIN --> VERIFY
 VERIFY --> DASH
 DASH --> PROFILE
 PROFILE --> BIO
 PROFILE --> CREATE
 CREATE --> SAVE
 SAVE --> CHECK
 CHECK -->|нет| DRAFT
 CHECK -->|да| MOD
 DRAFT --> DRAFTS
 DRAFTS --> EDIT
 EDIT --> UPDATE
 MOD --> OK
 MOD --> REJ
 REJ --> EDIT
 OK --> SHOW["Articles/Show.vue"]
 CREATE --> INVITE
 INVITE --> NOTIF
 PROFILE --> SETTINGS
 PROFILE --> LOGOUT
```

---

## UserFlow: Авторизованный читатель

```mermaid
flowchart TB
 subgraph BROWSE["Навигация"]
 LIST["GET /articles"]
 SHOW["Articles/Show.vue<br/>GET /articles/{slug}"]
 AUTHOR["Authors/Show.vue<br/>GET /authors/{user}"]
 end

 subgraph ENGAGE["Взаимодействие со статьёй"]
 RATE["POST /articles/{slug}/rate<br/>1–5 звёзд"]
 COMMENT["POST /articles/{slug}/comments"]
 REPLY["Ответ parent_id"]
 VOTE["POST /comments/{id}/vote<br/>±1"]
 DEL_OWN["DELETE /comments/{id}<br/>свой комментарий"]
 end

 subgraph FOLLOW["Подписки"]
 SUB["POST /authors/{user}/subscribe"]
 UNSUB["DELETE /authors/{user}/subscribe"]
 FEED["GET /notifications<br/>вкладка «Подписки»"]
 ALERT["ArticlePublishedNotification"]
 end

 subgraph NOTIFY["Уведомления"]
 INDEX["Notifications/Index.vue"]
 READ["Отметить прочитанным"]
 end

 subgraph LIMITS["Ограничения"]
 NO_SELF_RATE["Не оценивает свою статью"]
 NO_SELF_VOTE["Не голосует за свой комментарий"]
 NO_SELF_SUB["Не подписывается на себя"]
 end

 LIST --> SHOW
 SHOW --> RATE
 SHOW --> COMMENT
 COMMENT --> REPLY
 SHOW --> VOTE
 SHOW --> DEL_OWN
 SHOW --> AUTHOR
 AUTHOR --> SUB
 AUTHOR --> UNSUB
 SUB --> FEED
 ALERT --> INDEX
 INDEX --> READ
 RATE --> NO_SELF_RATE
 VOTE --> NO_SELF_VOTE
 SUB --> NO_SELF_SUB
```

---

## UserFlow: Соавтор

```mermaid
flowchart TB
 subgraph INVITE["Получение приглашения"]
 MAIL["CoauthorInvitationNotification"]
 LIST["Notifications/Index.vue<br/>GET /notifications"]
 CARD["Карточка coauthor_invitation"]
 end

 subgraph DECIDE["Решение"]
 ACCEPT["POST /coauthors/{coauthor}/accept"]
 DECLINE["POST /coauthors/{coauthor}/decline"]
 READ["POST /notifications/{id}/read"]
 end

 subgraph RESULT["Результат"]
 OK["status = accepted"]
 NO["status = declined"]
 SHOW["Articles/Show.vue — в блоке coauthors"]
 PROFILE["Authors/Show.vue — статья в списке автора"]
 end

 subgraph EDIT_ACCESS["Доступ к черновику"]
 PREVIEW["GET /articles/{slug}<br/>неопубликованная — видна соавтору?"]
 NOTE["show: только user_id или admin<br/>соавтор видит через профиль автора"]
 end

 MAIL --> LIST
 LIST --> CARD
 CARD --> ACCEPT
 CARD --> DECLINE
 ACCEPT --> READ
 DECLINE --> READ
 ACCEPT --> OK
 DECLINE --> NO
 OK --> SHOW
 OK --> PROFILE
 PROFILE --> EDIT_ACCESS
```

---

## UserFlow: Администратор

```mermaid
flowchart TB
 subgraph ACCESS["Доступ"]
 LOGIN["Вход role=admin"]
 GUARD["middleware admin"]
 end

 subgraph PANEL["Admin/Index.vue"]
 ADMIN["GET /admin"]
 PENDING["Ожидают публикации<br/>is_publishable && !is_published"]
 ALL_DRAFTS["Все неопубликованные"]
 APPROVE["POST /admin/articles/{article}/approve"]
 REJECT["POST /admin/articles/{article}/reject"]
 end

 subgraph TAXONOMY["Таксономия"]
 CATS["Admin/Categories.vue<br/>GET /admin/categories"]
 TAGS["Admin/Taxonomy.vue<br/>GET /admin/tags"]
 CRUD["POST/PUT/DELETE категорий и тегов"]
 end

 subgraph ARTICLE_POWER["Расширенные права на статьи"]
 DIRECT["Create/Edit: is_published напрямую"]
 FLAGS["is_hit, is_editors_choice, is_new"]
 ALL_DRAFTS_LIST["Articles/Drafts.vue — все черновики"]
 MOD_ON_SHOW["Articles/Show.vue — модерация на странице"]
 EDIT_ANY["Редактирование/удаление любой статьи"]
 end

 subgraph EFFECT["Эффекты действий"]
 PUB["Публикация + published_at"]
 N1["ArticlePublicationApprovedNotification"]
 N2["ArticlePublishedNotification → подписчики"]
 N3["ArticlePublicationRejectedNotification"]
 end

 LOGIN --> GUARD
 GUARD --> ADMIN
 ADMIN --> PENDING
 ADMIN --> ALL_DRAFTS
 PENDING --> APPROVE
 PENDING --> REJECT
 ADMIN --> CATS
 ADMIN --> TAGS
 CATS --> CRUD
 TAGS --> CRUD
 GUARD --> DIRECT
 DIRECT --> FLAGS
 GUARD --> ALL_DRAFTS_LIST
 GUARD --> MOD_ON_SHOW
 GUARD --> EDIT_ANY
 APPROVE --> PUB
 APPROVE --> N1
 APPROVE --> N2
 REJECT --> N3
```

---

## Справка по ключевым маршрутам

| Область | Маршруты | Контроллер / страница |
|--------|----------|------------------------|
| Статьи | `/articles`, `/articles/{slug}`, `/articles/create`, `/articles/drafts` | `ArticleController`, `Articles/*` |
| Модерация | `/admin`, `/admin/articles/{article}/approve\|reject` | `AdminController`, `ArticleModerationController`, `Admin/Index.vue` |
| Комментарии | `POST/DELETE comments` | `CommentController`, `CommentThread` |
| Рейтинг | `POST/DELETE /articles/{slug}/rate` | `RatingController`, `StarRating` |
| Auth | `/login`, `/register`, `/verify-email`, `/profile` | `Auth/*`, `Profile/Edit.vue` |
| Подписки | `/authors/{user}/subscribe`, `/notifications` | `SubscriptionController`, `NotificationController` |
| Соавторы | `/users/search`, `/coauthors/{id}/accept\|decline` | `CoauthorController`, `CoauthorInvitePanel` |
