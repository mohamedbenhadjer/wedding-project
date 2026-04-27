<?php
// Database
const DB_HOST = 'localhost';
const DB_NAME = 'forever_together';
const DB_USER = 'root';
const DB_PASS = '';

// Paths
const BASE_URL        = '';
const UPLOADS_DIR     = __DIR__ . '/../public/uploads';
const UPLOADS_URL     = 'uploads';
const VIDEOS_DIR      = __DIR__ . '/../storage/videos';
const VIDEOS_PUB_DIR  = __DIR__ . '/../public/videos';
const VIDEOS_URL      = 'videos';

const MAX_UPLOAD_BYTES = 8 * 1024 * 1024;
const ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

const FFMPEG_BIN = 'ffmpeg';
