import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from '@rollup/plugin-inject';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';

export default defineConfig({
    plugins: [
        inject({
            include: ['resources/**/*.js'],
            exclude: ['resources/js/bootstrap.js', 'resources/js/jquery-shim.js'],
            $: 'jquery',
            jQuery: 'jquery',
        }),
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                'resources/css/fontawesome.less',
                'resources/css/common.less',
                'resources/css/gallery.less',
                'resources/css/context-js.less',
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                { src: 'node_modules/jquery/dist/jquery.min.js', dest: 'js/vendor' },
                { src: 'node_modules/echarts/dist/echarts.min.js', dest: 'js/echarts' },
                { src: 'node_modules/clipboard/dist/clipboard.min.js', dest: 'js/clipboard' },
                { src: 'node_modules/copy-image-clipboard/dist/index.browser.js', dest: 'js/clipboard' },
                { src: 'node_modules/dragselect/dist/ds.min.js', dest: 'js/dragselect' },
                { src: 'node_modules/masonry-layout/dist/masonry.pkgd.min.js', dest: 'js/masonry' },
                { src: 'node_modules/imagesloaded/imagesloaded.pkgd.min.js', dest: 'js/imagesloaded' },
                { src: 'node_modules/justifiedGallery/dist/js/jquery.justifiedGallery.min.js', dest: 'js/justified-gallery' },
                { src: 'node_modules/justifiedGallery/dist/css/justifiedGallery.min.css', dest: 'css/justified-gallery' },
                { src: 'node_modules/viewerjs/dist/viewer.min.js', dest: 'js/viewer-js' },
                { src: 'node_modules/viewerjs/dist/viewer.min.css', dest: 'css/viewer-js' },
                { src: 'node_modules/blueimp-file-upload/js/jquery.fileupload.js', dest: 'js/blueimp-file-upload' },
                { src: 'node_modules/blueimp-file-upload/js/jquery.iframe-transport.js', dest: 'js/blueimp-file-upload' },
                { src: 'node_modules/blueimp-file-upload/js/vendor/jquery.ui.widget.js', dest: 'js/blueimp-file-upload' },
                { src: 'node_modules/blueimp-load-image/js/load-image.all.min.js', dest: 'js/blueimp-load-image' },
                { src: 'node_modules/github-markdown-css/github-markdown.css', dest: 'css/markdown-css' },
                { src: 'node_modules/github-markdown-css/github-markdown-light.css', dest: 'css/markdown-css' },
                { src: 'resources/js/context-js.js', dest: 'js/context-js' },
                { src: 'node_modules/@fortawesome/fontawesome-free/webfonts/*', dest: 'webfonts' },
            ],
        }),
    ],
    resolve: {
        alias: {
            '~': '/node_modules',
            'jquery': path.resolve(__dirname, 'resources/js/jquery-shim.js'),
        },
    },
    css: {
        preprocessorOptions: {
            less: { math: 'always' },
        },
    },
});
