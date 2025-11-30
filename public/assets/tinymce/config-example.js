/**
 * TinyMCE Configuration Examples
 * 
 * This file contains example configurations for replacing Summernote and CKEditor
 */

// Simple configuration (replaces Summernote simple mode)
// Use with class: "tinymce-simple"
var tinymceSimpleConfig = {
    selector: '.tinymce-simple',
    height: 150,
    menubar: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
    ],
    toolbar: 'bold italic underline | bullist numlist | link | removeformat',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
};

// Full configuration (replaces CKEditor)
// Use with class: "tinymce-full" or id: "editor1"
var tinymceFullConfig = {
    selector: '#editor1, .tinymce-full',
    height: 400,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount',
        'emoticons', 'directionality', 'pagebreak', 'nonbreaking', 'save'
    ],
    toolbar: 'undo redo | formatselect | ' +
        'bold italic underline strikethrough | forecolor backcolor | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist | outdent indent | ' +
        'removeformat | link image media table | code preview fullscreen | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
    branding: false,
    promotion: false
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        // Initialize simple editors
        tinymce.init(tinymceSimpleConfig);
        
        // Initialize full editors
        tinymce.init(tinymceFullConfig);
    }
});

