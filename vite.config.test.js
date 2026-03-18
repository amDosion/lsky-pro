import { defineConfig } from 'vite';
import path from 'path';
import inject from '@rollup/plugin-inject';

// 纯 JS 构建测试：输出 IIFE 格式到 public/js/app.js
export default defineConfig({
    plugins: [
        inject({
            include: ['resources/**/*.js'],
            exclude: ['resources/js/bootstrap.js', 'resources/js/jquery-shim.js'],
            $: 'jquery',
            jQuery: 'jquery',
        }),
    ],
    resolve: {
        alias: {
            'jquery': path.resolve('/opt/1panel/apps/lsky-pro', 'resources/js/jquery-shim.js'),
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
        rollupOptions: {
            external: [],
        },
    },
});
