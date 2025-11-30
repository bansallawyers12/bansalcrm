/**
 * TinyMCE Summernote Compatibility Layer
 * This provides a compatibility layer so existing Summernote code continues to work
 */

(function($) {
    'use strict';

    // Store original summernote if it exists (for fallback)
    var originalSummernote = $.fn.summernote;

    // Override jQuery summernote plugin
    $.fn.summernote = function(method, value) {
        var $this = $(this);
        
        // If TinyMCE is available, use it
        if (typeof tinymce !== 'undefined') {
            // Handle different methods
            if (method === 'code') {
                // Get or set content
                if (value !== undefined) {
                    // Set content
                    var editorId = $this.attr('id') || 'editor_' + Math.random().toString(36).substr(2, 9);
                    if (!$this.attr('id')) {
                        $this.attr('id', editorId);
                    }
                    
                    var editor = tinymce.get(editorId);
                    if (editor) {
                        editor.setContent(value || '');
                    } else {
                        // If editor not initialized, set value directly
                        $this.val(value || '');
                        // Try to initialize if it has the summernote-simple class
                        if ($this.hasClass('summernote-simple') || $this.hasClass('summernote')) {
                            setTimeout(function() {
                                var newEditor = tinymce.get(editorId);
                                if (newEditor) {
                                    newEditor.setContent(value || '');
                                }
                            }, 100);
                        }
                    }
                    return $this;
                } else {
                    // Get content
                    var editorId = $this.attr('id');
                    if (editorId) {
                        var editor = tinymce.get(editorId);
                        if (editor) {
                            return editor.getContent();
                        }
                    }
                    return $this.val();
                }
            } else if (method === 'reset') {
                // Reset editor
                var editorId = $this.attr('id');
                if (editorId) {
                    var editor = tinymce.get(editorId);
                    if (editor) {
                        editor.setContent('');
                    }
                }
                $this.val('');
                return $this;
            } else if (method === 'destroy') {
                // Destroy editor
                var editorId = $this.attr('id');
                if (editorId) {
                    var editor = tinymce.get(editorId);
                    if (editor) {
                        tinymce.remove(editorId);
                    }
                }
                return $this;
            } else if (!method || typeof method === 'object') {
                // Initialize - TinyMCE handles this automatically via tinymce-init.js
                // Just ensure the element has an ID
                if (!$this.attr('id')) {
                    $this.attr('id', 'editor_' + Math.random().toString(36).substr(2, 9));
                }
                return $this;
            }
        }
        
        // Fallback to original summernote if it exists
        if (originalSummernote) {
            return originalSummernote.apply(this, arguments);
        }
        
        return $this;
    };

    // Also handle direct summernote calls on elements
    $(document).ready(function() {
        // Intercept summernote method calls
        var originalVal = $.fn.val;
        $.fn.val = function(value) {
            var result = originalVal.apply(this, arguments);
            
            // If setting value on summernote element, also update TinyMCE
            if (value !== undefined && (this.hasClass('summernote-simple') || this.hasClass('summernote'))) {
                var editorId = this.attr('id');
                if (editorId && typeof tinymce !== 'undefined') {
                    var editor = tinymce.get(editorId);
                    if (editor) {
                        editor.setContent(value || '');
                    }
                }
            }
            
            return result;
        };
    });

})(jQuery);

