// jQuery 由同步 <script> 标签加载到 window，这里导出全局引用
// 所有 import $ from 'jquery' 和 require('jquery') 都会解析到这里
const jQuery = window.jQuery;
export default jQuery;
export { jQuery as $ };
