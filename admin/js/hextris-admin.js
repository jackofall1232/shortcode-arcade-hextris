/**
 * Hextris Arcade Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color pickers
        $('.hextris-color-picker').wpColorPicker({
            change: function(event, ui) {
                updateColorPreview();
            }
        });

        // Toggle custom color fields based on color scheme selection
        var colorSchemeSelect = $('#color_scheme');
        var customColorFields = $('.custom-color-field').closest('tr');

        function toggleCustomColors() {
            var scheme = colorSchemeSelect.val();
            if (scheme === 'custom') {
                customColorFields.removeClass('hidden').css('opacity', 1);
            } else {
                customColorFields.addClass('hidden').css('opacity', 0.3);
            }
        }

        colorSchemeSelect.on('change', toggleCustomColors);
        toggleCustomColors();

        // Range slider value display
        $('input[type="range"]').on('input change', function() {
            $(this).siblings('.range-value').text($(this).val());
        });

        // Form validation
        $('form').on('submit', function() {
            var isValid = true;

            // Validate dimension fields
            var dimensionFields = ['#default_width', '#default_height', '#default_max_width'];
            dimensionFields.forEach(function(field) {
                var $field = $(field);
                var value = $field.val().trim();

                // Check if value matches valid CSS dimension pattern
                var validPattern = /^(\d+(\.\d+)?(px|%|em|rem|vh|vw|vmin|vmax)|auto)$/i;
                if (value && !validPattern.test(value)) {
                    isValid = false;
                    $field.css('border-color', '#dc3232');
                    if (!$field.siblings('.error-message').length) {
                        $field.after('<span class="error-message" style="color:#dc3232;margin-left:10px;">Invalid format</span>');
                    }
                } else {
                    $field.css('border-color', '');
                    $field.siblings('.error-message').remove();
                }
            });

            return isValid;
        });

        // Real-time preview of color scheme
        function updateColorPreview() {
            var colors = {
                color1: $('#color_1').val(),
                color2: $('#color_2').val(),
                color3: $('#color_3').val(),
                color4: $('#color_4').val(),
                background: $('#background_color').val(),
                hexFill: $('#hexagon_fill_color').val()
            };

            // Could add a visual preview here if desired
            console.log('Colors updated:', colors);
        }

        // Confirm before leaving with unsaved changes
        var formChanged = false;

        $('form input, form select').on('change', function() {
            formChanged = true;
        });

        $('form').on('submit', function() {
            formChanged = false;
        });

        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        // Collapsible sections (optional enhancement)
        $('.hextris-admin-main h2').css('cursor', 'pointer').on('click', function() {
            var $section = $(this).nextUntil('h2, .submit');
            $section.slideToggle(200);
            $(this).toggleClass('collapsed');
        });

        // Add collapse indicator
        $('.hextris-admin-main h2').append('<span class="collapse-indicator" style="float:right;font-size:12px;">&#9660;</span>');

        $('.hextris-admin-main h2').on('click', function() {
            var $indicator = $(this).find('.collapse-indicator');
            if ($(this).hasClass('collapsed')) {
                $indicator.html('&#9658;');
            } else {
                $indicator.html('&#9660;');
            }
        });
    });

})(jQuery);
