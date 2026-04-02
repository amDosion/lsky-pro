(function (window) {
    const escapeHtml = function (value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const copyText = async function (text) {
        const value = String(text ?? '').trim();
        if (!value) return false;
        if (!navigator.clipboard || !navigator.clipboard.writeText) return false;
        try {
            await navigator.clipboard.writeText(value);
            return true;
        } catch (error) {
            return false;
        }
    };

    const normalizeText = function (value) {
        return String(value ?? '')
            .replace(/\s+/g, ' ')
            .trim();
    };

    const buildAttributeString = function (attributes) {
        return Object.entries(attributes || {})
            .filter(function (entry) {
                const value = entry[1];
                return value !== null && value !== undefined && value !== false;
            })
            .map(function (entry) {
                const name = entry[0];
                const value = entry[1];
                if (value === true) {
                    return ' ' + name;
                }
                return ' ' + name + '="' + escapeHtml(value) + '"';
            })
            .join('');
    };

    const resolveImagePreviewUrl = function (image) {
        return normalizeText(image && (image.preview_url || image.thumb_url || image.url));
    };

    const resolveImageThumbUrl = function (image) {
        return resolveImagePreviewUrl(image);
    };

    const resolveImageOpenUrl = function (image) {
        return normalizeText(image && (image.url || image.preview_url || image.thumb_url));
    };

    const renderImageGridCard = function (config) {
        const tag = config && config.tag ? String(config.tag) : 'a';
        const attributes = Object.assign({}, config && config.attributes ? config.attributes : {});
        const image = config && config.image ? config.image : {};
        const alt = normalizeText(config && config.alt ? config.alt : (image.filename || image.origin_name || image.name || ''));
        const previewUrl = resolveImagePreviewUrl(image);
        const thumbUrl = resolveImageThumbUrl(image);
        const width = Math.max(Number(config && config.width !== undefined ? config.width : image.width || 200), 1);
        const height = Math.max(Number(config && config.height !== undefined ? config.height : image.height || 200), 1);
        const contentHtml = String(config && config.contentHtml ? config.contentHtml : '');

        return '<' + tag + buildAttributeString(attributes) + '>'
            + contentHtml
            + '<img alt="' + escapeHtml(alt) + '" data-original="' + escapeHtml(previewUrl) + '" src="' + escapeHtml(thumbUrl) + '" width="' + width + '" height="' + height + '" loading="lazy">'
            + '</' + tag + '>';
    };

    const renderImageListRow = function (config) {
        const tag = config && config.tag ? String(config.tag) : 'div';
        const attributes = Object.assign({}, config && config.attributes ? config.attributes : {});
        const contentHtml = String(config && config.contentHtml ? config.contentHtml : '');

        return '<' + tag + buildAttributeString(attributes) + '>' + contentHtml + '</' + tag + '>';
    };

    const renderThumbButtons = function (items, activeIndex, mapper) {
        return (Array.isArray(items) ? items : []).map(function (item, index) {
            const mapped = typeof mapper === 'function' ? (mapper(item, index) || {}) : {};
            const buttonClass = mapped.buttonClass || 'images-carousel-thumb';
            const imageClass = mapped.imageClass || '';
            const title = escapeHtml(mapped.title || '');
            const src = escapeHtml(mapped.src || '');
            const alt = escapeHtml(mapped.alt || '');
            return '<button type="button" class="' + buttonClass + (index === activeIndex ? ' active' : '') + '" data-index="' + index + '" title="' + title + '"><img class="' + imageClass + '" src="' + src + '" alt="' + alt + '"></button>';
        }).join('');
    };

    const normalizeLoopIndex = function (index, length) {
        const total = Number(length || 0);
        if (total <= 0) return 0;
        const normalized = Number(index || 0) % total;
        return normalized < 0 ? normalized + total : normalized;
    };

    const setPanelScrollLocked = function (root, locked) {
        const element = root instanceof Element ? root : null;
        if (!element) return;

        const host = element.closest('.lsky-main');
        if (!host) return;

        host.classList.toggle('media-carousel-open', !!locked);
    };

    const resolveIntelligenceState = function (source) {
        const container = source && typeof source === 'object' ? source : {};
        const intelligence = container.intelligence && typeof container.intelligence === 'object'
            ? container.intelligence
            : container;

        return {
            intelligence: intelligence && typeof intelligence === 'object' ? intelligence : {},
            ready: Boolean(intelligence && intelligence.ready === true),
            fallback: Boolean(intelligence && intelligence.fallback === true),
            displaySummary: normalizeText(intelligence && intelligence.display_summary),
        };
    };

    const hasReadyIntelligence = function (source) {
        return resolveIntelligenceState(source).ready;
    };

    const getIntelligenceDisplaySummary = function (source) {
        const state = resolveIntelligenceState(source);
        if (state.fallback) {
            return '';
        }

        if (state.displaySummary) {
            return state.displaySummary;
        }

        const intelligence = state.intelligence || {};
        const summary = normalizeText(intelligence.summary);
        if (summary) {
            return summary;
        }

        const caption = normalizeText(intelligence.caption);
        if (caption) {
            return caption;
        }

        return '';
    };

    window.LskyMediaCarousel = {
        escapeHtml: escapeHtml,
        copyText: copyText,
        renderThumbButtons: renderThumbButtons,
        normalizeLoopIndex: normalizeLoopIndex,
        setPanelScrollLocked: setPanelScrollLocked,
        resolveImagePreviewUrl: resolveImagePreviewUrl,
        resolveImageThumbUrl: resolveImageThumbUrl,
        resolveImageOpenUrl: resolveImageOpenUrl,
        renderImageGridCard: renderImageGridCard,
        renderImageListRow: renderImageListRow,
        hasReadyIntelligence: hasReadyIntelligence,
        getIntelligenceDisplaySummary: getIntelligenceDisplaySummary,
    };
})(window);
