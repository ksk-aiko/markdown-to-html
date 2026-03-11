# Markdown to HTML Converter
Project Plan

---

# 1. Project Overview

Markdown を HTML に変換する Web アプリケーションを開発する。  
ユーザーはブラウザ上で Markdown を入力し、HTML を生成し、プレビューまたはダウンロードできる。

このプロジェクトは以下の目的を持つ。

- Markdown → HTML 変換の Web サービス構築
- Monaco Editor を使用したリッチエディタの実装
- Docker + NGINX + PHP の Web アーキテクチャの理解
- AWS EC2 へのデプロイ

---

# 2. Goal

本プロジェクトのゴール

- Webブラウザから Markdown を入力できる
- Markdown を HTML に変換できる
- HTML をページ上に表示できる
- HTML ファイルとしてダウンロードできる
- インターネットからアクセスできる

---

# 3. Target User

| User | Purpose |
|-----|------|
| Developer | Markdown → HTML 変換 |
| Blogger | HTML記事生成 |
| Student | Markdownレポート変換 |

---

# 4. Features

## Core Features

- Markdown input
- Markdown → HTML conversion
- HTML preview
- HTML download

## Editor

- Monaco Editor integration
- Markdown syntax highlight

## Output

- Preview mode
- Download mode

---

# 5. Tech Stack

## Frontend

- HTML
- CSS
- JavaScript
- Monaco Editor

## Backend

- PHP
- Parsedown

## Infrastructure

- Docker
- NGINX
- PHP-FPM
- AWS EC2

## Version Control

- Git
- GitHub

---

# 6. System Architecture


Browser
│
│ HTTP
▼
NGINX (Reverse Proxy)
│
▼
PHP-FPM
│
▼
Markdown Parser
(Parsedown)


---

# 7. Development Tasks

## 7.1 Frontend

- [ ] Monaco Editor integration
- [ ] Markdown input UI
- [ ] output mode selector
- [ ] convert button
- [ ] preview area

---

## 7.2 Backend

- [ ] Markdown conversion class
- [ ] convert endpoint
- [ ] preview logic
- [ ] download logic

---

## 7.3 Parser

- [ ] Parsedown install
- [ ] Markdown convert method

---

## 7.4 Infrastructure

- [ ] Docker compose setup
- [ ] PHP container
- [ ] NGINX container
- [ ] network configuration

---

## 7.5 Deployment

- [ ] EC2 instance setup
- [ ] Docker deploy
- [ ] NGINX domain config
- [ ] subdomain setup

---

# 8. Directory Structure


mdtohtml
│
├─ docker-compose.yml
│
├─ nginx
│ └─ default.conf
│
├─ php
│ └─ Dockerfile
│
└─ src
│
├─ public
│ ├─ index.php
│ └─ convert.php
│
├─ app
│ └─ MarkdownConverter.php
│
└─ vendor


---

# 9. Data Flow


User
│
│ Markdown input
▼
Monaco Editor
│
│ Submit
▼
PHP Server
│
│ Markdown Parser
▼
HTML generated
│
├─ Preview
│
└─ Download


---

# 10. Security Considerations

| Risk | Countermeasure |
|----|----|
| XSS | HTML sanitize |
| Command Injection | Input validation |
| Large Input | Size limit |

---

# 11. Performance

| Process | Target |
|------|------|
| Markdown conversion | < 1s |
| Page load | < 2s |

---

# 12. Definition of Done

プロジェクト完成条件

- Markdown入力できる
- HTML変換できる
- HTML previewできる
- HTML downloadできる
- Web公開されている
- GitHub公開されている

---

# 13. GitHub Repository


https://github.com/username/mdtohtml


---

# 14. Future Improvements

- Live preview
- Syntax highlighting
- HTML template
- Markdown file upload
- Drag & Drop
