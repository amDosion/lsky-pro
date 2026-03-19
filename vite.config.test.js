import { defineConfig } from 'vite';
import path from 'path';

// JS IIFE 构建：jQuery 打包在内，toastr 正确获取 jQuery
export default defineConfig({
    resolve: {
        alias: {
            '~': '/node_modules',
        },
    },
    build: {
        outDir: 'public',
        emptyOutDir: false,
        copyPublicDir: false,
        lib: {
            entry: 'resources/js/app.js',
            name: 'LskyApp',
            formats: ['iife'],
            fileName: () => 'js/app.js',
        },
    },
});
