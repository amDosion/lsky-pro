@section('title', '画廊')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gallery.css') }}">
    <style>
        .gallery-v2 {
            width: 100%;
        }

        .gallery-v2 .gallery-toolbar {
            margin-bottom: 10px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        }

        .gallery-v2 .images-grid .grid-item > div {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
        }
    </style>
@endpush

<x-app-layout>
    <div class="gallery-v2">
        <div class="gallery-toolbar">公共画廊</div>
        @if($images->isNotEmpty())
        <div class="images-grid">
            <div class="grid-sizer"></div>
            @foreach($images as $image)
                <div class="grid-item">
                    <div class="relative bg-white rounded-md overflow-hidden">
                        @if($image->extension === 'gif')
                            <span class="absolute top-1 right-1 z-[1] bg-white rounded-md text-sm px-1 py-0">Gif</span>
                        @endif
                        <a target="_blank" href="{{ $image->url }}">
                            <div class="relative overflow-hidden w-full h-32">
                                <img class="grow object-cover object-center w-full h-full" src="{{ $image->thumb_url }}"/>
                            </div>
                        </a>
                        <a target="_blank" href="{{ $image->user->url ?: 'javascript:void(0)' }}" class="flex justify-between items-center px-3 py-2 bg-white overflow-hidden group">
                            <img src="{{ $image->user->avatar }}" class="w-6 h-6 rounded-full">
                            <p class="ml-2 truncate group-hover:text-blue-500">{{ $image->user->name }}</p>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        {{ $images->links() }}
        @else
            <x-no-data message="暂时没有可展示的图片，再等等看吧～" />
        @endif
    </div>

    @push('scripts')
        <script src="{{ asset('js/masonry/masonry.pkgd.min.js') }}"></script>
        <script src="{{ asset('js/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
        <script>
            var $grid = $('.images-grid').masonry({
                itemSelector: '.grid-item',
                columnWidth: '.grid-sizer',
                duration: '0.8s',
                resize: true,
                initLayout: true,
                percentPosition: true,
                horizontalOrder: true,
            });
            $grid.imagesLoaded().progress(function() {
                $grid.masonry('layout');
            });
        </script>
    @endpush
</x-app-layout>
