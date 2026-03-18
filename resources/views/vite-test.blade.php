@extends('layouts.vite-test')

@section('content')
<div class="p-8 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Vite + jQuery Shim 测试页面</h1>
    
    <div class="space-y-4">
        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">1. jQuery 测试</h2>
            <button id="test-jquery" class="px-4 py-2 bg-blue-500 text-white rounded">点击测试 jQuery</button>
            <div id="jquery-result" class="mt-2 text-gray-600" style="display:none">jQuery 正常工作！版本：<span id="jq-ver"></span></div>
        </div>
        
        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">2. Toastr 测试</h2>
            <button id="test-toastr" class="px-4 py-2 bg-green-500 text-white rounded">点击测试 Toastr</button>
        </div>
        
        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">3. SweetAlert2 测试</h2>
            <button id="test-swal" class="px-4 py-2 bg-purple-500 text-white rounded">点击测试 Swal</button>
        </div>
        
        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">4. Axios 测试</h2>
            <button id="test-axios" class="px-4 py-2 bg-orange-500 text-white rounded">点击测试 Axios</button>
            <div id="axios-result" class="mt-2 text-gray-600"></div>
        </div>
        
        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">5. Alpine.js 测试</h2>
            <div x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-teal-500 text-white rounded">点击切换</button>
                <div x-show="open" class="mt-2 text-green-600">Alpine.js 正常工作！</div>
            </div>
        </div>
        
        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">6. Switch 组件测试（jQuery 内联）</h2>
            <label class="switch">
                <input type="checkbox" name="test_switch" checked>
                <span>开关测试</span>
            </label>
        </div>

        <div class="p-4 bg-white rounded shadow">
            <h2 class="font-bold mb-2">7. FontAwesome 图标</h2>
            <i class="fas fa-check text-green-500 text-2xl"></i>
            <i class="fas fa-times text-red-500 text-2xl"></i>
            <i class="fas fa-upload text-blue-500 text-2xl"></i>
            <i class="fas fa-image text-purple-500 text-2xl"></i>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#test-jquery').click(function() {
        $('#jquery-result').show();
        $('#jq-ver').text($.fn.jquery);
    });
    
    $('#test-toastr').click(function() {
        if (typeof toastr !== 'undefined') {
            toastr.success('Toastr 正常工作！');
        } else {
            alert('Toastr 未加载');
        }
    });
    
    $('#test-swal').click(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'SweetAlert2', text: '正常工作！', icon: 'success' });
        } else {
            alert('Swal 未加载');
        }
    });
    
    $('#test-axios').click(function() {
        if (typeof axios !== 'undefined') {
            $('#axios-result').text('Axios 已加载，版本: ' + axios.VERSION);
        } else {
            $('#axios-result').text('Axios 未加载');
        }
    });
</script>
@endpush
