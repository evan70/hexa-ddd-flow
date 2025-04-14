.
├── bin
│   ├── init-db.php
│   └── init-db.sh
├── config
│   ├── dependencies.php
│   ├── routes.php
│   └── settings.php
├── data
│   ├── articles.sqlite
│   └── users.sqlite
├── .idea
│   ├── 14.iml
│   ├── AugmentWebviewStateStore.xml
│   ├── .gitignore
│   ├── modules.xml
│   ├── php.xml
│   └── workspace.xml
├── public
│   ├── build
│   │   ├── assets
│   │   │   ├── css
│   │   │   │   ├── custom.css
│   │   │   │   └── tailwind.css
│   │   │   ├── js
│   │   │   ├── docs-hero.jpg
│   │   │   ├── hero-bg.jpg
│   │   │   ├── main-5b195972.js
│   │   │   ├── main-778f4a44.js
│   │   │   ├── main-82f30e06.css
│   │   │   ├── main-8b0eca94.js
│   │   │   ├── main-bd137437.js
│   │   │   ├── main-cf9a3744.css
│   │   │   ├── main-d6ead0fe.css
│   │   │   ├── main-f2efcb47.css
│   │   │   ├── testimonial-1.jpg
│   │   │   ├── testimonial-2.jpg
│   │   │   └── testimonial-3.jpg
│   │   ├── .htaccess
│   │   └── manifest.json
│   ├── .htaccess
│   └── index.php
├── resources
│   ├── css
│   │   └── app.css
│   ├── fonts
│   ├── images
│   │   ├── docs-hero.jpg
│   │   ├── hero-bg.jpg
│   │   ├── testimonial-1.jpg
│   │   ├── testimonial-2.jpg
│   │   └── testimonial-3.jpg
│   ├── js
│   │   ├── darkMode.js
│   │   └── main.js
│   └── views
│       ├── articles
│       │   ├── detail.twig
│       │   └── list.twig
│       ├── errors
│       │   ├── 404.twig
│       │   └── 500.twig
│       ├── users
│       │   └── list.twig
│       ├── home.twig
│       └── layout.twig
├── src
│   ├── Domain
│   │   ├── ValueObject
│   │   │   └── Uuid.php
│   │   ├── ArticleFactory.php
│   │   ├── Article.php
│   │   ├── UserFactory.php
│   │   ├── User.php
│   │   └── UuidGenerator.php
│   ├── Infrastructure
│   │   ├── Controller
│   │   │   ├── ArticleController.php
│   │   │   └── UserController.php
│   │   ├── External
│   │   │   └── ApiArticleRepository.php
│   │   ├── Helper
│   │   │   └── ViteAssetHelper.php
│   │   ├── Middleware
│   │   │   ├── ErrorHandlerMiddleware.php
│   │   │   └── UuidValidatorMiddleware.php
│   │   ├── Persistence
│   │   │   ├── DatabaseArticleRepository.php
│   │   │   └── DatabaseUserRepository.php
│   │   └── Twig
│   │       └── UuidExtension.php
│   └── Ports
│       ├── ArticleRepositoryInterface.php
│       └── UserRepositoryInterface.php
├── var
│   └── cache
│       └── twig
├── composer.json
├── composer.lock
├── dev.sh
├── directory_tree-slim4.md
├── .env.example
├── .gitignore
├── package.json
├── pnpm-lock.yaml
├── postcss.config.js
├── README.md
├── tailwind.config.js
└── vite.config.js

33 directories, 76 files
