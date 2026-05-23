# Backend Requirements - AppifyLab Feed Application

## Overview
Laravel 13 API backend for a social feed application with authentication, posts, comments, and likes.

## Tech Stack
- Laravel 13 / PHP 8.3+
- SQLite (development)
- JWT (JSON Web Token) via `php-open-source-saver/jwt-auth`
- Controller → DTO → Action → Model architecture

---

## 1. Authentication & Authorization

### Models
- **User** (extend existing): `id`, `first_name`, `last_name`, `email`, `password`, `timestamps`

### Endpoints
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | No | Register new user |
| POST | `/api/auth/login` | No | Login |
| POST | `/api/auth/logout` | Yes | Logout |
| GET | `/api/auth/me` | Yes | Get authenticated user |

### Rules
- Registration: first_name, last_name, email, password (min 8 chars)
- Login: email, password
- JWT authentication via `php-open-source-saver/jwt-auth` (HS256)
- Token returned on register/login, sent as `Authorization: Bearer {token}`
- Token TTL: 120 minutes (configurable via `JWT_TTL`)
- Refresh TTL: 20160 minutes (14 days)

---

## 2. Posts (Feed)

### Model
- **Post**: `id`, `user_id` (FK), `content` (text), `image` (nullable string), `visibility` (enum: public/private), `timestamps`, `soft_deletes`

### Endpoints
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/posts` | Yes | List feed (newest first) |
| POST | `/api/posts` | Yes | Create post |
| GET | `/api/posts/{post}` | Yes | Show single post |
| PUT | `/api/posts/{post}` | Yes | Update own post |
| DELETE | `/api/posts/{post}` | Yes | Delete own post |

### Rules
- Feed shows all **public** posts from all users + own **private** posts
- Sorted newest first
- Private posts visible only to author
- Only post author can update/delete
- Image upload optional

---

## 3. Comments & Replies

### Model
- **Comment**: `id`, `user_id` (FK), `post_id` (FK), `parent_id` (nullable FK → comments for replies), `content` (text), `timestamps`, `soft_deletes`

### Endpoints
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/posts/{post}/comments` | Yes | List comments for a post |
| POST | `/api/posts/{post}/comments` | Yes | Create comment |
| POST | `/api/comments/{comment}/replies` | Yes | Reply to comment |
| PUT | `/api/comments/{comment}` | Yes | Update own comment |
| DELETE | `/api/comments/{comment}` | Yes | Delete own comment |

### Rules
- Comments belong to a post
- Replies belong to a parent comment (nested 1 level)
- Only comment author can update/delete

---

## 4. Likes (Polymorphic)

### Model
- **Like**: `id`, `user_id` (FK), `likeable_id`, `likeable_type`, `timestamps` (unique constraint on [user_id, likeable_id, likeable_type])

### Endpoints
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/likes/toggle` | Yes | Toggle like on post/comment |
| GET | `/api/posts/{post}/likes` | Yes | Who liked a post |
| GET | `/api/comments/{comment}/likes` | Yes | Who liked a comment |

### Rules
- Polymorphic: can like Post or Comment (including replies)
- Toggle: same request creates or deletes like
- User can see who liked any post/comment

---

## Architecture

```txt
app/
├── Actions/
│   ├── Auth/
│   │   ├── RegisterAction.php
│   │   ├── LoginAction.php
│   │   └── LogoutAction.php
│   ├── Post/
│   │   ├── IndexAction.php
│   │   ├── StoreAction.php
│   │   ├── ShowAction.php
│   │   ├── UpdateAction.php
│   │   └── DeleteAction.php
│   ├── Comment/
│   │   ├── IndexAction.php
│   │   ├── StoreAction.php
│   │   ├── UpdateAction.php
│   │   └── DeleteAction.php
│   └── Like/
│       └── ToggleAction.php
│
├── DTOs/
│   ├── Auth/
│   │   ├── RegisterDTO.php
│   │   └── LoginDTO.php
│   ├── Post/
│   │   └── PostDTO.php
│   ├── Comment/
│   │   └── CommentDTO.php
│   └── Like/
│       └── LikeDTO.php
│
├── Http/Controllers/Api/
│   ├── Auth/
│   │   └── AuthController.php
│   ├── Post/
│   │   └── PostController.php
│   ├── Comment/
│   │   └── CommentController.php
│   └── Like/
│       └── LikeController.php
│
├── Models/
│   ├── User.php
│   ├── Post.php
│   ├── Comment.php
│   └── Like.php
│
└── Supports/
    └── ApiResponse.php
```
