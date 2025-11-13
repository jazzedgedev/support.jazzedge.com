(function($) {
    'use strict';
    
    var JeEventManager = {
        
        init: function() {
            this.bindEvents();
            this.initDatePickers();
        },
        
        initDatePickers: function() {
            // Initialize jQuery UI datepicker
            $('.jeem-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                minDate: 0, // Today or later
                showButtonPanel: true
            });
        },
        
        bindEvents: function() {
            var self = this;
            
            // Open bulk copy modal button (use delegated event in case button loads after script)
            $(document).on('click', '#jeem-open-bulk-copy-modal', function(e) {
                e.preventDefault();
                self.openBulkCopyModal();
            });
            
            // Modal close buttons (use delegated event for dynamic content)
            $(document).on('click', '.jeem-modal-close, .jeem-modal-cancel', function() { self.closeModal(); });
            
            // Form submission (use delegated event to ensure it works even if form is added dynamically)
            $(document).on('submit', '#jeem-bulk-copy-event-form', function(e) { 
                e.preventDefault();
                self.handleBulkCopyEvent(e); 
            });
            
            // Event selector change
            $(document).on('change', '#jeem-bulk-copy-event-select', function() {
                var eventId = $(this).val();
                if (eventId) {
                    $('#jeem-bulk-copy-source-id').val(eventId);
                    $('#jeem-bulk-copy-source-ids').val(eventId);
                    $('#jeem-bulk-copy-multiple').val('false');
                    // Update preview if date/time are already set
                    if ($('#jeem-bulk-start-date').val() && $('#jeem-bulk-start-time').val()) {
                        JeEventManager.generateBulkPreview();
                    }
                }
            });
            
            // Close modal on outside click
            $(window).on('click', function(event) {
                if ($(event.target).hasClass('jeem-modal')) {
                    self.closeModal();
                }
            });
        },
        
        openBulkCopyModal: function() {
            console.log('JE Event Manager: Opening bulk copy modal');
            
            // Check if modal exists
            if ($('#jeem-bulk-copy-event-modal').length === 0) {
                console.error('JE Event Manager: Modal not found!');
                alert('Error: Modal not found. Please refresh the page.');
                return;
            }
            
            // Load events list
            this.loadEventsList();
            
            // Reset form
            $('#jeem-bulk-copy-event-select').val('');
            $('#jeem-bulk-copy-source-id').val('');
            $('#jeem-bulk-copy-source-ids').val('');
            $('#jeem-bulk-copy-multiple').val('false');
            $('#jeem-bulk-start-date').val('');
            $('#jeem-bulk-start-time').val('10:00');
            $('#jeem-bulk-interval').val('2');
            $('#jeem-bulk-interval-unit').val('weeks');
            $('#jeem-bulk-count').val('10');
            $('#jeem-bulk-preview').hide();
            
            // Show modal
            $('#jeem-bulk-copy-event-modal').fadeIn(200);
            console.log('JE Event Manager: Modal shown');
        },
        
        loadEventsList: function() {
            var $select = $('#jeem-bulk-copy-event-select');
            $select.html('<option value="">Loading events...</option>');
            
            $.ajax({
                url: jeEventManager.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'jeem_get_events_list',
                    nonce: jeEventManager.nonce
                },
                success: function(response) {
                    if (response && response.success && response.data) {
                        $select.html('<option value="">-- Select an Event --</option>');
                        response.data.forEach(function(event) {
                            $select.append('<option value="' + event.id + '">' + event.title + '</option>');
                        });
                    } else {
                        $select.html('<option value="">Error loading events</option>');
                    }
                },
                error: function() {
                    $select.html('<option value="">Error loading events</option>');
                }
            });
        },
        
        generateBulkPreview: function() {
            var startDate = $('#jeem-bulk-start-date').val();
            var startTime = $('#jeem-bulk-start-time').val();
            var interval = parseInt($('#jeem-bulk-interval').val()) || 2;
            var intervalUnit = $('#jeem-bulk-interval-unit').val();
            var count = parseInt($('#jeem-bulk-count').val()) || 10;
            
            if (!startDate || !startTime) {
                $('#jeem-bulk-preview').hide();
                return;
            }
            
            // Combine date and time - handle 12-hour format
            var time24 = this.convertTo24Hour(startTime);
            var startDateTime = new Date(startDate + 'T' + time24);
            
            if (isNaN(startDateTime.getTime())) {
                $('#jeem-bulk-preview').hide();
                return;
            }
            
            var dates = [];
            var currentDate = new Date(startDateTime);
            
            // Show up to 15 dates in preview for performance (10-20 range)
            var previewCount = Math.min(count, 15);
            
            for (var i = 0; i < previewCount; i++) {
                // Create end date (1 hour after start)
                var endDate = new Date(currentDate);
                endDate.setHours(endDate.getHours() + 1);
                
                var startStr = currentDate.toLocaleDateString('en-US', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                var endStr = endDate.toLocaleDateString('en-US', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                dates.push({
                    start: startStr,
                    end: endStr
                });
                
                // Calculate next date
                if (i < count - 1) {
                    if (intervalUnit === 'days') {
                        currentDate.setDate(currentDate.getDate() + interval);
                    } else if (intervalUnit === 'weeks') {
                        currentDate.setDate(currentDate.getDate() + (interval * 7));
                    } else if (intervalUnit === 'months') {
                        currentDate.setMonth(currentDate.getMonth() + interval);
                    }
                }
            }
            
            var previewHtml = '<ul style="list-style: none; padding: 0; margin: 0;">';
            dates.forEach(function(dateObj) {
                previewHtml += '<li style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                previewHtml += '<strong>Start:</strong> ' + dateObj.start + '<br>';
                previewHtml += '<strong>End:</strong> ' + dateObj.end + ' (1 hour later)';
                previewHtml += '</li>';
            });
            if (count > 15) {
                previewHtml += '<li style="padding: 5px 0; font-style: italic; color: #666;">... and ' + (count - 15) + ' more</li>';
            }
            previewHtml += '</ul>';
            
            $('#jeem-preview-dates').html(previewHtml);
            $('#jeem-bulk-preview').show();
        },
        
        convertTo24Hour: function(time12) {
            // If already in 24-hour format, return as-is
            if (time12.indexOf('AM') === -1 && time12.indexOf('PM') === -1) {
                return time12;
            }
            
            var time = time12.trim();
            var period = time.slice(-2);
            var timeStr = time.slice(0, -2).trim();
            var parts = timeStr.split(':');
            var hours = parseInt(parts[0], 10);
            var minutes = parts[1] ? parseInt(parts[1], 10) : 0;
            
            if (period === 'PM' && hours !== 12) {
                hours += 12;
            } else if (period === 'AM' && hours === 12) {
                hours = 0;
            }
            
            return (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes;
        },
        
        closeModal: function() {
            $('.jeem-modal').fadeOut(200);
        },
        
        handleBulkCopyEvent: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var self = this; // Store reference to JeEventManager object
            
            var eventSelect = $('#jeem-bulk-copy-event-select').val();
            var sourceId = $('#jeem-bulk-copy-source-id').val();
            var startDate = $('#jeem-bulk-start-date').val();
            var startTime = $('#jeem-bulk-start-time').val();
            var interval = $('#jeem-bulk-interval').val();
            var intervalUnit = $('#jeem-bulk-interval-unit').val();
            var count = $('#jeem-bulk-count').val();
            
            console.log('Bulk copy form submitted:', {
                eventSelect: eventSelect,
                sourceId: sourceId,
                startDate: startDate,
                startTime: startTime,
                interval: interval,
                intervalUnit: intervalUnit,
                count: count
            });
            
            if (!eventSelect || !sourceId || !startDate || !startTime || !interval || !count) {
                alert('Please fill in all required fields');
                console.log('Validation failed - missing fields');
                return false;
            }
            
            // Combine date and time
            var time24 = self.convertTo24Hour(startTime);
            var formattedDate = startDate + ' ' + time24 + ':00';
            
            // Confirm message
            var message = 'This will create ' + count + ' events. Continue?';
            
            if (!confirm(message)) {
                return;
            }
            
            $.ajax({
                url: jeEventManager.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'jeem_bulk_copy_event',
                    nonce: jeEventManager.nonce,
                    source_id: sourceId,
                    source_ids: sourceId,
                    copy_multiple: 'false',
                    start_date: formattedDate,
                    interval: interval,
                    interval_unit: intervalUnit,
                    count: count
                },
                beforeSend: function() {
                    $('#jeem-bulk-copy-event-form').addClass('jeem-loading');
                    console.log('Sending AJAX request...');
                },
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response && response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        var errorMsg = (response && response.data && response.data.message) ? response.data.message : 'Unknown error';
                        alert('Error: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr, status, error);
                    var errorMsg = 'Request failed';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMsg = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        try {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            if (jsonResponse.data && jsonResponse.data.message) {
                                errorMsg = jsonResponse.data.message;
                            }
                        } catch (e) {
                            errorMsg = xhr.statusText || error;
                        }
                    }
                    alert('Error: ' + errorMsg);
                },
                complete: function() {
                    $('#jeem-bulk-copy-event-form').removeClass('jeem-loading');
                }
            });
            
            return false;
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        JeEventManager.init();
        
        // Bind preview update for bulk copy form (use delegated events)
        $(document).on('change input', '#jeem-bulk-start-date, #jeem-bulk-start-time, #jeem-bulk-interval, #jeem-bulk-interval-unit, #jeem-bulk-count', function() {
            JeEventManager.generateBulkPreview();
        });
        
        // Debug: Check if button exists
        setTimeout(function() {
            if ($('#jeem-open-bulk-copy-modal').length === 0) {
                console.warn('JE Event Manager: Bulk Copy button not found');
            } else {
                console.log('JE Event Manager: Bulk Copy button found');
            }
        }, 1000);
    });
    
})(jQuery);



