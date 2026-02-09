/* global jQuery, leadAggregator */
window.leadAggregatorBuild = 'lead-url-cleanup-2';
document.documentElement.setAttribute('data-lead-aggregator-build', window.leadAggregatorBuild);
console.log('Lead Aggregator frontend loaded', window.leadAggregatorBuild);
jQuery(function ($) {
    var icons = {
        plus: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
        edit: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651-9.9 9.9-3.182.53.53-3.182 9.9-9.9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 16.5 4.5"/></svg>',
        trash: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>',
        download: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>',
        check: '<svg class="la-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        refresh: '<svg class="la-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-3-6.7"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v7h-7"/></svg>',
        x: '<svg class="la-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        clock: '<svg class="la-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>',
        action: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"/></svg>',
        external: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>',
        calendar: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>',
        columns: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/></svg>',
        addToFollowup: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/></svg>',
        clearFilters: '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>'
    };

    var statusOptions = ['New', 'Contacted', 'Qualified', 'Proposal', 'Won', 'Lost'];
    var followupOptions = ['Not set', 'Scheduled', 'Completed', 'Canceled'];
    var stagePresets = {
        professional_services: {
            label: 'Professional Services',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Qualified', outcome: 'open' },
                { name: 'Proposal Sent', outcome: 'open' },
                { name: 'Negotiation', outcome: 'open' },
                { name: 'Won', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        real_estate: {
            label: 'Real Estate',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Needs Analysis', outcome: 'open' },
                { name: 'Showing Scheduled', outcome: 'open' },
                { name: 'Offer Submitted', outcome: 'open' },
                { name: 'Under Contract', outcome: 'open' },
                { name: 'Closed Won', outcome: 'won' },
                { name: 'Closed Lost', outcome: 'lost' }
            ]
        },
        home_services: {
            label: 'Home Services',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Estimate Scheduled', outcome: 'open' },
                { name: 'Estimate Sent', outcome: 'open' },
                { name: 'Follow-up', outcome: 'open' },
                { name: 'Booked', outcome: 'open' },
                { name: 'Completed', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        medical_wellness: {
            label: 'Medical & Wellness',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Appointment Scheduled', outcome: 'open' },
                { name: 'Appointment Completed', outcome: 'open' },
                { name: 'Follow-up', outcome: 'open' },
                { name: 'Converted', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        education_training: {
            label: 'Education & Training',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Discovery Call', outcome: 'open' },
                { name: 'Trial/Intro', outcome: 'open' },
                { name: 'Enrollment Sent', outcome: 'open' },
                { name: 'Enrolled', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        automotive: {
            label: 'Automotive',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Appointment Scheduled', outcome: 'open' },
                { name: 'Quote Sent', outcome: 'open' },
                { name: 'Test Drive', outcome: 'open' },
                { name: 'Sold', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        local_retail: {
            label: 'Local Retail & Specialty',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'In-Store Visit', outcome: 'open' },
                { name: 'Quote Sent', outcome: 'open' },
                { name: 'Purchased', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        construction_trades: {
            label: 'Construction & Trades',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Site Visit', outcome: 'open' },
                { name: 'Estimate Sent', outcome: 'open' },
                { name: 'Negotiation', outcome: 'open' },
                { name: 'Contract Signed', outcome: 'open' },
                { name: 'In Progress', outcome: 'open' },
                { name: 'Completed', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        technology_saas: {
            label: 'Technology & SaaS',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Discovery', outcome: 'open' },
                { name: 'Demo', outcome: 'open' },
                { name: 'Trial', outcome: 'open' },
                { name: 'Proposal', outcome: 'open' },
                { name: 'Negotiation', outcome: 'open' },
                { name: 'Won', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        hospitality_events: {
            label: 'Hospitality & Events',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Site Tour', outcome: 'open' },
                { name: 'Proposal Sent', outcome: 'open' },
                { name: 'Follow-up', outcome: 'open' },
                { name: 'Booking Confirmed', outcome: 'open' },
                { name: 'Event Completed', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        franchise_multi: {
            label: 'Franchise & Multi-Location',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Discovery', outcome: 'open' },
                { name: 'Application Sent', outcome: 'open' },
                { name: 'Disclosure Review', outcome: 'open' },
                { name: 'Financing', outcome: 'open' },
                { name: 'Signed', outcome: 'open' },
                { name: 'Opened', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        ecommerce_online: {
            label: 'E-commerce & Online',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Qualified', outcome: 'open' },
                { name: 'Proposal Sent', outcome: 'open' },
                { name: 'Negotiation', outcome: 'open' },
                { name: 'Won', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        },
        financial_planner: {
            label: 'Financial Planner',
            stages: [
                { name: 'New Lead', outcome: 'open' },
                { name: 'Contacted', outcome: 'open' },
                { name: 'Discovery Call', outcome: 'open' },
                { name: 'Risk Profile', outcome: 'open' },
                { name: 'Proposal', outcome: 'open' },
                { name: 'Follow-up', outcome: 'open' },
                { name: 'Client Onboarded', outcome: 'won' },
                { name: 'Lost', outcome: 'lost' }
            ]
        }
    };

    function buildSelect(name, value, options) {
        var current = (value || options[0]).toLowerCase();
        var html = '<select name="' + name + '">';
        options.forEach(function (option) {
            var optionValue = option.toLowerCase();
            var selected = optionValue === current ? ' selected' : '';
            html += '<option value="' + optionValue + '"' + selected + '>' + option + '</option>';
        });
        html += '</select>';
        return html;
    }

    function buildFollowupCustomFields(customConfig, lead) {
        if (!customConfig || !customConfig.followup || !customConfig.labels) {
            return '';
        }
        var html = '';
        for (var i = 1; i <= 10; i += 1) {
            var key = 'custom_' + i;
            if (!customConfig.followup[key]) {
                continue;
            }
            var label = customConfig.labels[key] || ('Custom Field ' + i);
            var value = lead && lead[key] ? lead[key] : '';
            html += '<div><label>' + label + ' <input type="text" name="' + key + '" value="' + value + '"></label></div>';
        }
        return html;
    }

    function pipelineStageLabel(value) {
        var key = (value || 'new').toLowerCase();
        var map = {
            open: 'new',
            new: 'new',
            contacted: 'contacted',
            qualified: 'qualified',
            proposal: 'proposal',
            won: 'won',
            lost: 'lost'
        };
        var normalized = map[key] || 'new';
        return normalized.charAt(0).toUpperCase() + normalized.slice(1);
    }

    function normalizePipelineStageValue(value) {
        var key = (value || 'new').toLowerCase();
        var map = {
            open: 'new',
            new: 'new',
            contacted: 'contacted',
            qualified: 'qualified',
            proposal: 'proposal',
            won: 'won',
            lost: 'lost'
        };
        return map[key] || 'new';
    }

    function normalizeFollowupStatusValue(value) {
        var key = (value || 'not_set').toLowerCase();
        var map = {
            not_set: 'not_set',
            scheduled: 'scheduled',
            completed: 'completed',
            canceled: 'canceled'
        };
        return map[key] || 'not_set';
    }

    function followupStatusLabel(value) {
        var key = (value || 'not_set').toLowerCase();
        var map = {
            not_set: 'Not set',
            scheduled: 'Scheduled',
            completed: 'Completed',
            canceled: 'Canceled'
        };
        return map[key] || 'Not set';
    }

    function parseLeadDate(value) {
        if (!value) {
            return null;
        }
        var normalized = String(value).trim().replace(' ', 'T');
        if (!/[zZ]|[+-]\d{2}:?\d{2}$/.test(normalized)) {
            normalized += 'Z';
        }
        var date = new Date(normalized);
        return isNaN(date.getTime()) ? null : date;
    }

    function formatDisplayDate(value) {
        if (!value) {
            return '';
        }
        var date = parseLeadDate(value);
        if (!date) {
            return value;
        }
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        var year = String(date.getFullYear());
        var hours = date.getHours();
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var ampm = hours >= 12 ? 'pm' : 'am';
        var hour12 = hours % 12;
        if (hour12 === 0) {
            hour12 = 12;
        }
        return month + '/' + day + '/' + year + ', ' + hour12 + ':' + minutes + ampm;
    }

    function formatDisplayTime(date) {
        var hours = date.getHours();
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var ampm = hours >= 12 ? 'pm' : 'am';
        var hour12 = hours % 12;
        if (hour12 === 0) {
            hour12 = 12;
        }
        return hour12 + ':' + minutes + ampm;
    }

    function formatRelativeDate(value) {
        if (!value) {
            return '';
        }
        var date = parseLeadDate(value);
        if (!date) {
            return value;
        }
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var target = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var diffDays = Math.round((target - today) / 86400000);
        var label = '';
        if (diffDays === 0) {
            label = 'Today';
        } else if (diffDays === 1) {
            label = 'Tomorrow';
        } else if (diffDays === -1) {
            label = 'Yesterday';
        } else if (diffDays > 1) {
            label = 'In ' + diffDays + ' days';
        } else {
            label = Math.abs(diffDays) + ' days ago';
        }
        return label + ' · ' + formatDisplayTime(date);
    }

    function laShowCopiedFeedback($wrap) {
        if (!$wrap || !$wrap.length) {
            return;
        }
        var timer = $wrap.data('copied-timer');
        if (timer) {
            clearTimeout(timer);
        }
        $wrap.addClass('is-copied');
        timer = setTimeout(function () {
            $wrap.removeClass('is-copied');
            $wrap.data('copied-timer', null);
        }, 900);
        $wrap.data('copied-timer', timer);
    }

    function laToast(message) {
        var $toast = $('#la-toast');
        if (!$toast.length) {
            $toast = $('<div id="la-toast" class="la-toast"></div>');
            $('body').append($toast);
        }
        var timer = $toast.data('toast-timer');
        if (timer) {
            clearTimeout(timer);
        }
        $toast.text(message || 'Copied to clipboard').addClass('is-visible');
        timer = setTimeout(function () {
            $toast.removeClass('is-visible');
            $toast.data('toast-timer', null);
        }, 1400);
        $toast.data('toast-timer', timer);
    }
    function apiRequest(method, endpoint, data) {
        return $.ajax({
            method: method,
            url: leadAggregator.restUrl + endpoint,
            data: data ? JSON.stringify(data) : null,
            contentType: 'application/json',
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', leadAggregator.nonce);
            }
        });
    }

    function renderInbox($el, options) {
        options = options || {};
        var tagId = options.tagId || null;
        var tagName = options.tagName || '';
        var showFollowupAction = !!options.showFollowupAction;
        $el.html('<p>Loading leads...</p>');
        var leadsRequest = tagId ? apiRequest('GET', 'tags/' + tagId + '/leads') : apiRequest('GET', 'leads');
        $.when(leadsRequest, apiRequest('GET', 'custom-fields'), apiRequest('GET', 'me')).done(function (leadsRes, customFieldsRes, meRes) {
            var leads = leadsRes[0] || [];
            var customFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].fields) ? customFieldsRes[0].fields : {};
            var followupFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].followup) ? customFieldsRes[0].followup : {};
            var me = meRes && meRes[0] ? meRes[0] : {};
            var readOnly = (me.access_level || 'full') === 'read';
            var selectedLeadIds = [];
            var storageKey = 'leadAggregatorLeadsFilters';
            var initialFilters = options.filters || {};
            if (!options.filters && !tagId) {
                try {
                    initialFilters = JSON.parse(window.localStorage.getItem(storageKey) || '{}') || {};
                } catch (err) {
                    initialFilters = {};
                }
            }
            var initialSearch = initialFilters.search || '';
            var initialStatus = initialFilters.status || '';
            var initialFollowup = initialFilters.followup || '';

            var columnDefs = [
                { key: 'name', label: 'Name' },
                { key: 'email', label: 'Email' },
                { key: 'company', label: 'Company' },
                { key: 'phone', label: 'Phone' },
                { key: 'status', label: 'Pipeline Stage' },
                { key: 'followup', label: 'Followup Date' },
                { key: 'due', label: 'Followup Due' },
                { key: 'source', label: 'Source' },
                { key: 'last_actioned', label: 'Last Actioned' },
                { key: 'last_contacted', label: 'Last Contacted' },
                { key: 'address_city', label: 'City' },
                { key: 'address_state', label: 'State' },
                { key: 'address_zip', label: 'Zip' },
                { key: 'address_country', label: 'Country' }
            ];
            for (var i = 1; i <= 10; i += 1) {
                var key = 'custom_' + i;
                columnDefs.push({
                    key: key,
                    label: customFields[key] || ('Custom Field ' + i)
                });
            }

            var defaultColumns = ['name', 'status', 'followup', 'due', 'source'];
            var storedColumns = [];
            try {
                storedColumns = JSON.parse(window.localStorage.getItem('leadAggregatorLeadColumns') || '[]');
            } catch (err) {
                storedColumns = [];
            }
            activeColumns = (storedColumns && storedColumns.length) ? storedColumns : defaultColumns.slice();
            if (activeColumns.indexOf('stage') !== -1) {
                activeColumns = activeColumns.filter(function (key) { return key !== 'stage'; });
                if (activeColumns.indexOf('status') === -1) {
                    activeColumns.splice(2, 0, 'status');
                }
            }

            function getColumnLabel(key) {
                var found = columnDefs.find(function (col) { return col.key === key; });
                return found ? found.label : key;
            }

            function isOverdue(lead) {
                if (!lead || !lead.due_at) {
                    return false;
                }
                var dueDate = parseLeadDate(lead.due_at);
                if (!dueDate) {
                    return false;
                }
                var followupStatus = normalizeFollowupStatusValue(lead.followup_status);
                if (followupStatus === 'completed' || followupStatus === 'canceled') {
                    return false;
                }
                return dueDate < new Date();
            }

            function isDue(lead) {
                if (!lead || !lead.due_at) {
                    return false;
                }
                var dueDate = parseLeadDate(lead.due_at);
                if (!dueDate) {
                    return false;
                }
                var followupStatus = normalizeFollowupStatusValue(lead.followup_status);
                if (followupStatus === 'completed' || followupStatus === 'canceled') {
                    return false;
                }
                return dueDate <= new Date();
            }

            function buildDateCell(value, overdueFlag) {
                if (!value) {
                    return '';
                }
                var exact = formatDisplayDate(value);
                var relative = formatRelativeDate(value);
                var className = 'la-date-stack' + (overdueFlag ? ' la-date-overdue' : '');
                return '<div class="' + className + '"><span class="la-date-relative">' + relative + '</span><span class="la-muted">' + exact + '</span></div>';
            }

            function statusFilterLabel(value) {
                if (!value) {
                    return 'All';
                }
                return pipelineStageLabel(value);
            }

            function followupFilterLabel(value) {
                if (!value) {
                    return 'All';
                }
                if (value === 'due') {
                    return 'Due';
                }
                if (value === 'overdue') {
                    return 'Overdue';
                }
                return followupStatusLabel(value);
            }

            function isFollowedToday(lead) {
                if (!lead) {
                    return false;
                }
                var raw = lead.last_actioned || lead.last_contacted || '';
                if (!raw && normalizeFollowupStatusValue(lead.followup_status) === 'completed' && lead.followup_at) {
                    raw = lead.followup_at;
                }
                if (!raw) {
                    return false;
                }
                var date = parseLeadDate(raw);
                if (!date) {
                    return false;
                }
                var today = new Date();
                return date.getFullYear() === today.getFullYear() &&
                    date.getMonth() === today.getMonth() &&
                    date.getDate() === today.getDate();
            }

            function updateMetricButtons(filter, statusFilter, followupStatusFilter) {
                if (!options.statsEl || !options.statsEl.length) {
                    return;
                }
                var $buttons = options.statsEl.find('.la-stat-button');
                $buttons.removeClass('is-active').attr('aria-pressed', 'false');
                var isDefault = !filter && !statusFilter && !followupStatusFilter;
                var followupKey = String(followupStatusFilter || '').toLowerCase();
                if (isDefault) {
                    $buttons.filter('[data-filter="total"]').addClass('is-active').attr('aria-pressed', 'true');
                } else if (followupKey === 'due') {
                    $buttons.filter('[data-filter="followup"]').addClass('is-active').attr('aria-pressed', 'true');
                } else if (followupKey === 'followed') {
                    $buttons.filter('[data-filter="followed"]').addClass('is-active').attr('aria-pressed', 'true');
                } else if (followupKey === 'overdue') {
                    $buttons.filter('[data-filter="overdue"]').addClass('is-active').attr('aria-pressed', 'true');
                }
            }

            function renderCell(lead, key) {
                if (key === 'name') {
                    var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                    var email = lead.email ? '<span class="la-name-email">' + lead.email + '</span>' : '';
                    return '<button type="button" class="la-link la-lead-link" data-lead-id="' + lead.id + '"><span class="la-name-primary">' + (name.trim() || 'Lead #' + lead.id) + '</span>' + email + '</button>';
                }
                if (key === 'followup') {
                    return buildDateCell(lead.followup_at, false);
                }
                if (key === 'due') {
                    return buildDateCell(lead.due_at, isOverdue(lead));
                }
                if (key === 'last_actioned') {
                    return formatDisplayDate(lead.last_actioned);
                }
                if (key === 'last_contacted') {
                    return formatDisplayDate(lead.last_contacted);
                }
                if (key === 'status') {
                    var stageKey = normalizePipelineStageValue(lead.status);
                    return '<span class="la-pill la-pill--' + stageKey + '">' + pipelineStageLabel(lead.status) + '</span>';
                }
                return lead[key] ? lead[key] : '';
            }

            function renderRows(filter, statusFilter, followupStatusFilter) {
                if (!activeColumns.length) {
                    activeColumns = defaultColumns.slice();
                    window.localStorage.setItem('leadAggregatorLeadColumns', JSON.stringify(activeColumns));
                }
                var statusLabel = statusFilterLabel(statusFilter);
                var followupLabel = followupFilterLabel(followupStatusFilter);
                var hasActiveFilters = !!(filter || statusFilter || followupStatusFilter);
                var html = '<div class="la-section-header">' +
                    '<div class="la-section-actions la-section-actions--leads">';
                html += '<div class="la-leads-toolbar">';
                html += '<div class="la-intent-row">';
                html += '<input type="text" class="la-search la-search--primary" placeholder="Search leads" value="' + (filter || '') + '">';
                html += '<div class="la-intent-actions">';
                if (!readOnly) {
                    html += '<button type="button" class="la-btn la-add-lead">' + icons.plus + 'Add Lead</button>';
                }
                html += '<div class="la-column-toggle-wrap"><button type="button" class="la-btn la-btn--ghost la-column-toggle">' + icons.columns + 'Columns</button>';
                html += '<div class="la-column-menu">';
                columnDefs.forEach(function (col) {
                    var checked = activeColumns.indexOf(col.key) !== -1 ? ' checked' : '';
                    html += '<label><input type="checkbox" data-key="' + col.key + '"' + checked + '> ' + col.label + '</label>';
                });
                html += '</div></div>';
                html += '</div>';
                html += '</div>';
                html += '<div class="la-context-row">';
                html += '<div class="la-filter-pill-group">';
                if (tagId) {
                    html += '<button type="button" class="la-filter-pill la-filter-tag" data-tag-clear="1">Tag: ' + tagName + ' ×</button>';
                }
                html += '<div class="la-filter-pill-wrap" data-filter="status">';
                html += '<button type="button" class="la-filter-pill" aria-haspopup="true" aria-expanded="false">Pipeline: <span class="la-filter-value">' + statusLabel + '</span> ▾</button>';
                html += '<div class="la-filter-menu">';
                html += '<button type="button" data-value="">All</button>';
                statusOptions.forEach(function (option) {
                    var value = option.toLowerCase();
                    var selected = value === String(statusFilter || '').toLowerCase() ? ' data-selected="1"' : '';
                    html += '<button type="button" data-value="' + value + '"' + selected + '>' + option + '</button>';
                });
                html += '</div></div>';
                html += '<div class="la-filter-pill-wrap" data-filter="followup">';
                html += '<button type="button" class="la-filter-pill" aria-haspopup="true" aria-expanded="false">Follow-ups: <span class="la-filter-value">' + followupLabel + '</span> ▾</button>';
                html += '<div class="la-filter-menu">';
                html += '<button type="button" data-value="">All</button>';
                followupOptions.forEach(function (option) {
                    var value = option.toLowerCase().replace(/\s+/g, '_');
                    var selected = value === String(followupStatusFilter || '').toLowerCase() ? ' data-selected="1"' : '';
                    html += '<button type="button" data-value="' + value + '"' + selected + '>' + option + '</button>';
                });
                html += '<button type="button" data-value="due"' + (String(followupStatusFilter || '').toLowerCase() === 'due' ? ' data-selected="1"' : '') + '>Due</button>';
                html += '<button type="button" data-value="overdue"' + (String(followupStatusFilter || '').toLowerCase() === 'overdue' ? ' data-selected="1"' : '') + '>Overdue</button>';
                html += '</div></div>';
                var clearDisabled = hasActiveFilters ? '' : ' disabled';
                html += '<button type="button" class="la-filter-pill la-filter-clear"' + clearDisabled + '>' + icons.clearFilters + 'Clear</button>';
                html += '<div class="la-filter-count">Showing 0 leads</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div></div>';

                if (!readOnly && selectedLeadIds.length) {
                    html += '<div class="la-bulk-toolbar">';
                    html += '<div class="la-bulk-count">' + selectedLeadIds.length + ' selected</div>';
                    html += '<div class="la-bulk-actions">';
                    html += '<button type="button" class="la-btn la-btn--danger la-bulk-delete">' + icons.trash + 'Delete Selected</button>';
                    html += '<select class="la-bulk-followup-status">';
                    html += '<option value="">Set follow-up status</option>';
                    followupOptions.forEach(function (option) {
                        var value = option.toLowerCase().replace(/\s+/g, '_');
                        html += '<option value="' + value + '">' + option + '</option>';
                    });
                    html += '</select>';
                    html += '<button type="button" class="la-btn la-btn--ghost la-bulk-followup-apply">Apply</button>';
                    html += '</div></div>';
                }

                html += '<table class="la-table"><thead><tr>';
                if (!readOnly) {
                    html += '<th><input type="checkbox" class="la-select-all"></th>';
                }
                activeColumns.forEach(function (key) {
                    html += '<th>' + getColumnLabel(key) + '</th>';
                });
                if (showFollowupAction && !readOnly) {
                    html += '<th>Action</th>';
                }
                html += '</tr></thead><tbody>';

                var filtered = leads;
                if (filter) {
                    var query = filter.toLowerCase();
                    filtered = leads.filter(function (lead) {
                        var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                        return (
                            name.toLowerCase().indexOf(query) !== -1 ||
                            (lead.email || '').toLowerCase().indexOf(query) !== -1 ||
                            (lead.company || '').toLowerCase().indexOf(query) !== -1
                        );
                    });
                }
                if (statusFilter) {
                    var normalized = normalizePipelineStageValue(statusFilter);
                    filtered = filtered.filter(function (lead) {
                        return normalizePipelineStageValue(lead.status) === normalized;
                    });
                }
                if (followupStatusFilter) {
                    var followupKey = String(followupStatusFilter || '').toLowerCase();
                    if (followupKey === 'due') {
                        filtered = filtered.filter(function (lead) {
                            return isDue(lead);
                        });
                    } else if (followupKey === 'followed') {
                        filtered = filtered.filter(function (lead) {
                            return isFollowedToday(lead);
                        });
                    } else if (followupKey === 'overdue') {
                        filtered = filtered.filter(function (lead) {
                            return isOverdue(lead);
                        });
                    } else {
                        var normalizedFollowup = normalizeFollowupStatusValue(followupStatusFilter);
                        filtered = filtered.filter(function (lead) {
                            return normalizeFollowupStatusValue(lead.followup_status) === normalizedFollowup;
                        });
                    }
                }

                var countLabel = 'Showing ' + filtered.length + ' lead' + (filtered.length === 1 ? '' : 's');

                if (filtered.length === 0) {
                    html += '<tr><td class="la-empty" colspan="' + (activeColumns.length + (readOnly ? 0 : 1) + (showFollowupAction && !readOnly ? 1 : 0)) + '">No leads yet.</td></tr>';
                } else {
                    filtered.forEach(function (lead) {
                        html += '<tr>';
                        if (!readOnly) {
                            var checked = selectedLeadIds.indexOf(String(lead.id)) !== -1 ? ' checked' : '';
                            html += '<td><input type="checkbox" class="la-lead-select" data-id="' + lead.id + '"' + checked + '></td>';
                        }
                        activeColumns.forEach(function (key) {
                            html += '<td>' + renderCell(lead, key) + '</td>';
                        });
                if (showFollowupAction && !readOnly && normalizePipelineStageValue(lead.status) === 'new' && normalizeFollowupStatusValue(lead.followup_status) === 'not_set') {
                    html += '<td><button type="button" class="la-btn la-btn--ghost la-followup-add" data-id="' + lead.id + '">' + icons.addToFollowup + 'Followup</button></td>';
                        } else if (showFollowupAction && !readOnly) {
                            html += '<td></td>';
                        }
                        html += '</tr>';
                    });
                }
                html += '</tbody></table>';
                $el.html(html);

                updateMetricButtons(filter, statusFilter, followupStatusFilter);
                $el.find('.la-filter-count').text(countLabel);
                if (!tagId) {
                    window.localStorage.setItem(storageKey, JSON.stringify({
                        search: filter || '',
                        status: statusFilter || '',
                        followup: followupStatusFilter || ''
                    }));
                }

                if (!readOnly && filtered.length && selectedLeadIds.length === filtered.length) {
                    $el.find('.la-select-all').prop('checked', true);
                }

                $el.find('.la-search').on('input', function () {
                    var currentStatus = statusFilter || '';
                    var currentFollowupStatus = followupStatusFilter || '';
                    renderRows($(this).val(), currentStatus, currentFollowupStatus);
                });

                $el.find('.la-filter-pill').on('click', function (event) {
                    var $wrap = $(this).closest('.la-filter-pill-wrap');
                    if (!$wrap.length) {
                        return;
                    }
                    event.stopPropagation();
                    var isOpen = $wrap.hasClass('is-open');
                    $el.find('.la-filter-pill-wrap').removeClass('is-open');
                    $wrap.toggleClass('is-open', !isOpen);
                });

                $el.find('.la-filter-menu button').on('click', function () {
                    var $wrap = $(this).closest('.la-filter-pill-wrap');
                    var filterType = $wrap.data('filter');
                    var value = $(this).data('value');
                    var currentSearch = $el.find('.la-search').val();
                    var currentStatus = filterType === 'status' ? value : (statusFilter || '');
                    var currentFollowup = filterType === 'followup' ? value : (followupStatusFilter || '');
                    $el.find('.la-filter-pill-wrap').removeClass('is-open');
                    renderRows(currentSearch, currentStatus, currentFollowup);
                });

                $(document).off('click.leadAggregatorFilters').on('click.leadAggregatorFilters', function (event) {
                    if (!$(event.target).closest('.la-filter-pill-wrap').length) {
                        $el.find('.la-filter-pill-wrap').removeClass('is-open');
                    }
                });

                $el.find('.la-filter-clear').on('click', function () {
                    if (this.disabled) {
                        return;
                    }
                    renderRows('', '', '');
                });

                $el.find('.la-filter-tag').on('click', function () {
                    renderInbox($el, { showFollowupAction: showFollowupAction, statsEl: options.statsEl });
                });

                $el.find('.la-lead-select').on('change', function () {
                    var id = String($(this).data('id'));
                    if (this.checked) {
                        if (selectedLeadIds.indexOf(id) === -1) {
                            selectedLeadIds.push(id);
                        }
                    } else {
                        selectedLeadIds = selectedLeadIds.filter(function (item) { return item !== id; });
                    }
                    var currentSearch = $el.find('.la-search').val();
                    var currentStatus = statusFilter || '';
                    var currentFollowupStatus = followupStatusFilter || '';
                    renderRows(currentSearch, currentStatus, currentFollowupStatus);
                });

                if (!readOnly) {
                    $el.find('.la-select-all').on('change', function () {
                        if (this.checked) {
                            selectedLeadIds = filtered.map(function (lead) { return String(lead.id); });
                        } else {
                            selectedLeadIds = [];
                        }
                        var currentSearch = $el.find('.la-search').val();
                        var currentStatus = statusFilter || '';
                        var currentFollowupStatus = followupStatusFilter || '';
                        renderRows(currentSearch, currentStatus, currentFollowupStatus);
                    });

                    $el.find('.la-bulk-delete').on('click', function () {
                        if (!selectedLeadIds.length) {
                            return;
                        }
                        if (!confirm('Delete selected leads? This cannot be undone.')) {
                            return;
                        }
                        apiRequest('POST', 'leads/bulk-delete', { ids: selectedLeadIds }).done(function () {
                            selectedLeadIds = [];
                            renderInbox($el, options);
                        });
                    });

                $el.find('.la-bulk-followup-apply').on('click', function () {
                    var statusValue = $el.find('.la-bulk-followup-status').val();
                    if (!statusValue || !selectedLeadIds.length) {
                        return;
                    }
                    var requests = selectedLeadIds.map(function (leadId) {
                        return apiRequest('PUT', 'leads/' + leadId, { followup_status: statusValue });
                    });
                    $.when.apply($, requests).always(function () {
                        renderInbox($el, options);
                    });
                });
                }

                $el.find('.la-column-toggle').on('click', function () {
                    $el.find('.la-column-menu').toggleClass('is-open');
                });

                $el.find('.la-column-menu input[type="checkbox"]').on('change', function () {
                    var key = $(this).data('key');
                    if (this.checked) {
                        if (activeColumns.indexOf(key) === -1) {
                            activeColumns.push(key);
                        }
                    } else {
                        activeColumns = activeColumns.filter(function (item) { return item !== key; });
                    }
                    window.localStorage.setItem('leadAggregatorLeadColumns', JSON.stringify(activeColumns));
                    var currentSearch = $el.find('.la-search').val();
                    var currentStatus = statusFilter || '';
                    var currentFollowupStatus = followupStatusFilter || '';
                    renderRows(currentSearch, currentStatus, currentFollowupStatus);
                });

                $el.find('.la-add-lead').on('click', function () {
                    openLeadModal();
                });

                $el.find('.la-followup-add').on('click', function () {
                    var leadId = $(this).data('id');
                    if (!leadId) {
                        return;
                    }
                    // Set follow-up quickly without opening modal.
                    apiRequest('PUT', 'leads/' + leadId, {
                        followup_at: new Date().toISOString(),
                        followup_status: 'scheduled'
                    }).done(function () {
                        if (window.leadAggregatorOpenTab) {
                            window.leadAggregatorOpenTab('followups');
                        }
                        $.when(
                            apiRequest('GET', 'leads/' + leadId)
                        ).done(function (leadRes) {
                            if (leadRes) {
                                openFollowupManageModal(leadRes, { labels: customFields, followup: followupFields });
                            }
                            $(document).trigger('leadAggregator:refresh');
                        });
                    });
                });

            }

            renderRows(initialSearch, initialStatus, initialFollowup);
        }).fail(function () {
            $el.html('<p>Unable to load leads.</p>');
        });
    }

    function renderLeadForm($el) {
        var html = '<div class="la-section-header"><h3>Add Lead</h3></div>' +
            '<form class="la-form">' +
            '<div><label>First Name <input type="text" name="first_name"></label></div>' +
            '<div><label>Last Name <input type="text" name="last_name"></label></div>' +
            '<div><label>Email <input type="email" name="email"></label></div>' +
            '<div><label>Phone <input type="text" name="phone"></label></div>' +
            '<div><label>Company <input type="text" name="company"></label></div>' +
            '<div><label>Followup Date <input type="datetime-local" name="followup_at"></label></div>' +
            '<div><label>Due Date <input type="datetime-local" name="due_at"></label></div>' +
            '<button type="submit" class="la-btn">' + icons.plus + 'Add Lead</button>' +
            '<div class="la-message"></div>' +
            '</form>';

        $el.html(html);
        $el.find('form').on('submit', function (e) {
            e.preventDefault();
            var data = {};
            $(this).serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });
            apiRequest('POST', 'leads', data).done(function (response) {
                if (!response || !response.lead_id) {
                    $el.find('.la-message').text('Lead created, but could not confirm. Please refresh.');
                    return;
                }
                $el.find('.la-message').text('Lead created!');
                e.target.reset();
                $(document).trigger('leadAggregator:refresh');
            }).fail(function (xhr) {
                $el.find('.la-message').text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to create lead.');
            });
        });
    }

    function openLeadModal() {
        var modal = $(
            '<div class="la-modal-overlay">' +
                '<div class="la-modal la-modal--wide">' +
                    '<div class="la-modal-header">' +
                        '<h4>Add Lead</h4>' +
                        '<button type="button" class="la-modal-close">×</button>' +
                    '</div>' +
                    '<div class="la-modal-body">' +
                        '<form class="la-form la-lead-modal-form">' +
                            '<div><label>First Name <input type="text" name="first_name"></label></div>' +
                            '<div><label>Last Name <input type="text" name="last_name"></label></div>' +
                            '<div><label>Email <input type="email" name="email"></label></div>' +
                            '<div><label>Phone <input type="text" name="phone"></label></div>' +
                            '<div><label>Company <input type="text" name="company"></label></div>' +
                            '<div><label>Followup Date <input type="datetime-local" name="followup_at"></label></div>' +
                            '<div><label>Due Date <input type="datetime-local" name="due_at"></label></div>' +
                            '<div class="la-modal-actions">' +
                                '<button type="button" class="la-btn la-btn--ghost la-modal-cancel">Cancel</button>' +
                                '<button type="submit" class="la-btn">' + icons.plus + 'Add Lead</button>' +
                            '</div>' +
                            '<div class="la-message"></div>' +
                        '</form>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append(modal);

        function close() {
            modal.remove();
        }

        modal.find('.la-modal-close, .la-modal-cancel').on('click', close);

        modal.on('click', function (event) {
            if ($(event.target).is('.la-modal-overlay')) {
                close();
            }
        });

        modal.find('.la-lead-modal-form').on('submit', function (e) {
            e.preventDefault();
            var data = {};
            $(this).serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });
            apiRequest('POST', 'leads', data).done(function (response) {
                if (!response || !response.lead_id) {
                    modal.find('.la-message').text('Lead created, but could not confirm. Please refresh.');
                    return;
                }
                close();
                $(document).trigger('leadAggregator:refresh');
            }).fail(function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to create lead.';
                modal.find('.la-message').text(message);
            });
        });
    }

    // TODO: Remove if we permanently drop the quick follow-up modal.
    function openFollowupModal(lead, customConfig) {
        function toLocalInputValue(dateObj) {
            var year = dateObj.getFullYear();
            var month = String(dateObj.getMonth() + 1).padStart(2, '0');
            var day = String(dateObj.getDate()).padStart(2, '0');
            var hours = String(dateObj.getHours()).padStart(2, '0');
            var minutes = String(dateObj.getMinutes()).padStart(2, '0');
            return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
        }

        var followupValue = '';
        if (lead.followup_at) {
            followupValue = String(lead.followup_at).replace(' ', 'T');
        } else {
            var now = new Date();
            now.setSeconds(0, 0);
            followupValue = toLocalInputValue(now);
        }

        var statusSelect = buildSelect('status', normalizePipelineStageValue(lead.status), statusOptions);
        var followupSelect = buildSelect('followup_status', lead.followup_status || 'scheduled', followupOptions);
        var customFieldsHtml = buildFollowupCustomFields(customConfig, lead);

        var modal = $(
            '<div class="la-modal-overlay">' +
                '<div class="la-modal">' +
                    '<div class="la-modal-header">' +
                        '<h4>Set Follow-up</h4>' +
                        '<button type="button" class="la-modal-close">×</button>' +
                    '</div>' +
                    '<div class="la-modal-body">' +
                        '<form class="la-form la-followup-modal-form la-form--two-col">' +
                            '<div><label>Pipeline Stage ' + statusSelect + '</label></div>' +
                            '<div><label>Follow-up Status ' + followupSelect + '</label></div>' +
                            '<div><label>Followup Date <input type="datetime-local" name="followup_at" value="' + followupValue + '"></label></div>' +
                            '<div><label>Due Date <input type="datetime-local" name="due_at" value=""></label></div>' +
                            customFieldsHtml +
                            '<div class="la-modal-actions">' +
                                '<button type="button" class="la-btn la-btn--ghost la-modal-cancel">Cancel</button>' +
                                '<button type="submit" class="la-btn">' + icons.edit + 'Save</button>' +
                            '</div>' +
                            '<div class="la-message"></div>' +
                        '</form>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append(modal);

        function close() {
            modal.remove();
        }

        modal.find('.la-modal-close, .la-modal-cancel').on('click', close);

        modal.on('click', function (event) {
            if ($(event.target).is('.la-modal-overlay')) {
                close();
            }
        });

        modal.find('.la-followup-modal-form').on('submit', function (e) {
            e.preventDefault();
            var data = {};
            $(this).serializeArray().forEach(function (item) {
                data[item.name] = item.value;
            });
            apiRequest('PUT', 'leads/' + lead.id, data).done(function () {
                close();
                $(document).trigger('leadAggregator:refresh');
            }).fail(function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update lead.';
                modal.find('.la-message').text(message);
            });
        });
    }

        function openFollowupManageModal(lead, customConfig) {
        function toLocalInputValue(dateObj) {
            var year = dateObj.getFullYear();
            var month = String(dateObj.getMonth() + 1).padStart(2, '0');
            var day = String(dateObj.getDate()).padStart(2, '0');
            var hours = String(dateObj.getHours()).padStart(2, '0');
            var minutes = String(dateObj.getMinutes()).padStart(2, '0');
            return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
        }

        var followupValue = lead.followup_at ? String(lead.followup_at).replace(' ', 'T') : '';
        var dueValue = lead.due_at ? String(lead.due_at).replace(' ', 'T') : '';
        if (!followupValue) {
            var now = new Date();
            now.setSeconds(0, 0);
            followupValue = toLocalInputValue(now);
        }

        var statusSelect = buildSelect('status', normalizePipelineStageValue(lead.status), statusOptions);
        var followupSelect = buildSelect('followup_status', lead.followup_status || 'scheduled', followupOptions);
        var customFieldsHtml = buildFollowupCustomFields(customConfig, lead);

        var contactName = ((lead.first_name || '') + ' ' + (lead.last_name || '')).trim();
        var contactEmail = (lead.email || '').trim();
        var contactPhone = (lead.phone || '').trim();
        var mailtoLink = contactEmail ? ('mailto:' + encodeURIComponent(contactEmail)) : '#';

        var modal = $(
            '<div class="la-modal-overlay">' +
                '<div class="la-modal la-modal--wide">' +
                '<div class="la-modal-header">' +
                        '<div class="la-modal-title">' +
                            '<h4>Follow-up</h4>' +
                        '</div>' +
                        '<button type="button" class="la-modal-close">×</button>' +
                    '</div>' +
                    '<div class="la-modal-body">' +
                        '<form class="la-form la-followup-modal-form">' +
                            '<div class="la-modal-section la-modal-section--primary">' +
                                '<div class="la-modal-section-title">Follow-up</div>' +
                                '<div class="la-modal-grid">' +
                                    '<div><label>Follow-up Status ' + followupSelect + '</label></div>' +
                                    '<div><label>Pipeline Stage ' + statusSelect + '</label></div>' +
                                    '<div><label>Followup Date <input type="datetime-local" name="followup_at" value="' + followupValue + '"></label></div>' +
                                    '<div><label>Due Date <input type="datetime-local" name="due_at" value="' + dueValue + '"></label></div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="la-modal-section">' +
                                '<div class="la-modal-section-title">Contact</div>' +
                                '<div class="la-modal-grid">' +
                                    '<div class="la-copy-wrap">' +
                                        '<label>Name <input type="text" class="la-copy-field" value="' + (contactName || 'N/A') + '" readonly data-copy="' + (contactName || '') + '"></label>' +
                                    '</div>' +
                                    '<div class="la-copy-wrap">' +
                                        '<label>Email <input type="text" class="la-copy-field" value="' + (contactEmail || 'N/A') + '" readonly data-copy="' + (contactEmail || '') + '"></label>' +
                                    '</div>' +
                                    '<div>' +
                                        '<label>Phone <input type="text" value="' + (contactPhone || 'N/A') + '" disabled></label>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="la-modal-section">' +
                                '<div class="la-modal-section-title">Qualifier</div>' +
                                '<div class="la-modal-grid">' +
                                    customFieldsHtml +
                                '</div>' +
                            '</div>' +
                            '<div class="la-modal-section la-modal-section--tags">' +
                                '<div class="la-modal-section-title">Tags</div>' +
                                '<div class="la-tags-applied">' +
                                    '<div class="la-tags-applied-list"></div>' +
                                    '<button type="button" class="la-tags-toggle" style="display:none;"></button>' +
                                '</div>' +
                                '<div class="la-tags-input">' +
                                    '<select class="la-tag-input-select"><option value="">Add tags...</option></select>' +
                                '</div>' +
                                '<div class="la-message la-tag-message"></div>' +
                            '</div>' +
                            '<div class="la-modal-section la-modal-section--notes">' +
                                '<div class="la-modal-section-title">Note</div>' +
                                '<label><textarea name="note" rows="4" placeholder="Add a note"></textarea></label>' +
                                '<div class="la-helper-text">Internal notes (not sent to lead).</div>' +
                            '</div>' +
                            '<div class="la-modal-actions">' +
                                '<div class="la-modal-actions-grid">' +
                                    '<button type="button" class="la-btn la-btn--ghost la-quick-action">Apply Quick Action</button>' +
                                    '<button type="button" class="la-btn la-btn--ghost la-open-contact la-action-open">' +
                                        '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' +
                                            '<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />' +
                                        '</svg>' +
                                        'Open Contact</button>' +
                                    '<a class="la-btn la-btn--ghost la-mailto la-action-email"' + (contactEmail ? ' href="' + mailtoLink + '" target="_blank" rel="noopener noreferrer"' : ' href="#" aria-disabled="true"') + '>' +
                                        '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' +
                                            '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />' +
                                        '</svg>' +
                                        'Send Email</a>' +
                                    '<button type="button" class="la-btn la-btn--ghost la-modal-cancel la-action-cancel">' +
                                        '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' +
                                            '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />' +
                                        '</svg>' +
                                        'Cancel</button>' +
                                    '<button type="submit" class="la-btn la-action-save">' + icons.edit + 'Save</button>' +
                                '</div>' +
                            '</div>' +
                            '<div class="la-message"></div>' +
                        '</form>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append(modal);

        function close() {
            modal.remove();
        }

        modal.find('.la-modal-close, .la-modal-cancel').on('click', close);

        modal.on('click', function (event) {
            if ($(event.target).is('.la-modal-overlay')) {
                close();
            }
        });

        modal.find('.la-open-contact').on('click', function () {
            close();
            if (window.leadAggregatorOpenLeadDetail) {
                window.leadAggregatorOpenLeadDetail(lead.id);
            }
        });

        modal.find('.la-quick-action').on('click', function () {
            var $button = $(this);
            $button.prop('disabled', true);
            apiRequest('GET', 'quick-action/settings').done(function (settings) {
                settings = settings || {};
                var status = settings.status || 'contacted';
                var followupStatus = settings.followup_status || 'scheduled';
                modal.find('select[name="status"]').val(status);
                modal.find('select[name="followup_status"]').val(followupStatus);
                apiRequest('PUT', 'leads/' + lead.id, {
                    status: status,
                    followup_status: followupStatus
                }).done(function () {
                    modal.find('.la-message').text('Quick action applied.');
                    $(document).trigger('leadAggregator:refresh');
                }).fail(function () {
                    modal.find('.la-message').text('Unable to apply quick action.');
                }).always(function () {
                    $button.prop('disabled', false);
                });
            }).fail(function () {
                modal.find('.la-message').text('Quick action settings not found.');
                $button.prop('disabled', false);
            });
        });

        modal.find('.la-copy-field').on('click', function () {
            var $wrap = $(this).closest('.la-copy-wrap');
            var value = $(this).data('copy') || $(this).val();
            if (!value) {
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(function () {
                    laShowCopiedFeedback($wrap);
                    laToast('Copied to clipboard');
                }).catch(function () {
                    modal.find('.la-message').text('Unable to copy.');
                });
            } else {
                try {
                    this.select();
                    document.execCommand('copy');
                    laShowCopiedFeedback($wrap);
                    laToast('Copied to clipboard');
                } catch (err) {
                    modal.find('.la-message').text('Unable to copy.');
                }
            }
        });

        function renderAppliedTags(tags, selectedTags, expanded) {
            var maxVisible = expanded ? selectedTags.length : 6;
            var html = '';
            selectedTags.forEach(function (tagId, index) {
                var tag = tags.find(function (t) { return t.id === tagId; });
                if (!tag || index >= maxVisible) {
                    return;
                }
                html += '<span class="la-tag-selected-chip" data-tag-id="' + tag.id + '">' +
                    tag.name + '<button type="button" class="la-tag-remove">' + icons.x + '</button></span>';
            });
            if (!html) {
                html = '<span class="la-muted">No tags yet</span>';
            }
            modal.find('.la-tags-applied-list').html(html);
            var remaining = selectedTags.length - maxVisible;
            var toggle = modal.find('.la-tags-toggle');
            if (remaining > 0) {
                toggle.text('+' + remaining + ' more').show();
            } else if (expanded && selectedTags.length > 6) {
                toggle.text('Show less').show();
            } else {
                toggle.hide();
            }
        }

        function renderTagSelect(tags, selectedTags) {
            var select = modal.find('.la-tag-input-select');
            var html = '<option value="">Add tags...</option>';
            tags.forEach(function (tag) {
                if (selectedTags.indexOf(tag.id) !== -1) {
                    return;
                }
                html += '<option value="' + tag.id + '">' + tag.name + '</option>';
            });
            select.html(html);
        }

        function saveFollowupTags(selectedTags) {
            return apiRequest('PUT', 'leads/' + lead.id, { tags: selectedTags }).fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update tags.';
                modal.find('.la-tag-message').text(message);
            });
        }

        $.when(apiRequest('GET', 'tags'), apiRequest('GET', 'leads/' + lead.id)).done(function (tagsRes, leadRes) {
            var tags = (tagsRes[0] || []).map(function (tag) {
                return {
                    id: parseInt(tag.id, 10),
                    name: tag.name
                };
            });
            var leadData = leadRes[0] || lead;
            var selectedTags = (leadData.tags || []).map(function (tagId) {
                return parseInt(tagId, 10);
            }).filter(function (tagId) {
                return !isNaN(tagId);
            });
            var expanded = false;
            var select = modal.find('.la-tag-input-select');

            function refreshTags() {
                renderAppliedTags(tags, selectedTags, expanded);
                renderTagSelect(tags, selectedTags);
            }

            function applyTag(tag) {
                if (!tag || selectedTags.indexOf(tag.id) !== -1) {
                    return;
                }
                selectedTags.push(tag.id);
                saveFollowupTags(selectedTags);
                select.val('');
                refreshTags();
            }

            refreshTags();

            modal.find('.la-tags-toggle').on('click', function () {
                expanded = !expanded;
                refreshTags();
            });

            modal.find('.la-tags-applied-list').on('click', '.la-tag-remove', function () {
                var tagId = parseInt($(this).closest('.la-tag-selected-chip').data('tag-id'), 10);
                if (isNaN(tagId)) {
                    return;
                }
                selectedTags = selectedTags.filter(function (id) { return id !== tagId; });
                saveFollowupTags(selectedTags);
                refreshTags();
            });

            select.on('change', function () {
                var id = parseInt($(this).val(), 10);
                if (!id) {
                    return;
                }
                var tag = tags.find(function (item) { return item.id === id; });
                applyTag(tag);
            });
        });

        modal.find('.la-followup-modal-form').on('submit', function (e) {
            e.preventDefault();
            var data = {};
            $(this).serializeArray().forEach(function (item) {
                if (item.name !== 'note') {
                    data[item.name] = item.value;
                }
            });
            var now = new Date();
            now.setSeconds(0, 0);
            data.followup_at = toLocalInputValue(now);
            var noteValue = $(this).find('textarea[name="note"]').val();

            apiRequest('PUT', 'leads/' + lead.id, data).done(function () {
                if (noteValue) {
                    apiRequest('POST', 'leads/' + lead.id + '/notes', { note: noteValue }).always(function () {
                        close();
                        $(document).trigger('leadAggregator:refresh');
                    });
                } else {
                    close();
                    $(document).trigger('leadAggregator:refresh');
                }
            }).fail(function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update lead.';
                modal.find('.la-message').text(message);
            });
        });
    }

    function renderLeadDetail($el, leadId) {
        if (!leadId) {
            $el.html('<p>No lead selected.</p>');
            return;
        }

        function formatDateTimeLocal(date) {
            var pad = function (value) { return value < 10 ? '0' + value : String(value); };
            return date.getFullYear() + '-' +
                pad(date.getMonth() + 1) + '-' +
                pad(date.getDate()) + 'T' +
                pad(date.getHours()) + ':' +
                pad(date.getMinutes());
        }

        function formatNoteDate(dateString) {
            if (!dateString) {
                return '';
            }
            var normalized = String(dateString).replace(' ', 'T');
            var date = new Date(normalized);
            if (isNaN(date.getTime())) {
                return dateString;
            }
            return date.toLocaleString([], {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        $el.html('<p>Loading lead...</p>');
        $.when(
            apiRequest('GET', 'leads/' + leadId),
            apiRequest('GET', 'leads/' + leadId + '/notes'),
            apiRequest('GET', 'tags'),
            apiRequest('GET', 'custom-fields'),
            apiRequest('GET', 'me')
        ).done(function (leadRes, notesRes, tagsRes, customFieldsRes, meRes) {
            var lead = leadRes[0];
            var notes = notesRes[0] || [];
            var tags = (tagsRes[0] || []).map(function (tag) {
                return {
                    id: parseInt(tag.id, 10),
                    name: tag.name
                };
            });
            var customFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].fields) ? customFieldsRes[0].fields : {};
            var me = meRes && meRes[0] ? meRes[0] : {};
            var readOnly = (me.access_level || 'full') === 'read';
            var fullName = (lead.first_name || '') + ' ' + (lead.last_name || '');
            var displayName = fullName.trim() || ('Lead #' + lead.id);
            var email = lead.email || '—';
            var phone = lead.phone || '—';
            var selectedTags = (lead.tags || []).map(function (tagId) {
                return parseInt(tagId, 10);
            }).filter(function (tagId) {
                return !isNaN(tagId);
            });
            console.log('[Lead Aggregator] Lead tags loaded', {
                leadId: lead.id,
                leadTags: lead.tags,
                selectedTags: selectedTags
            });
            var exportCsvUrl = leadAggregator.restUrl + 'leads/' + lead.id + '/export?_wpnonce=' + encodeURIComponent(leadAggregator.nonce);
            var exportCalendarUrl = leadAggregator.restUrl + 'leads/' + lead.id + '/calendar?_wpnonce=' + encodeURIComponent(leadAggregator.nonce);

            var customFieldInputs = '';
            for (var i = 1; i <= 10; i += 1) {
                var key = 'custom_' + i;
                var label = customFields[key] || ('Custom Field ' + i);
                customFieldInputs += '<div><label>' + label + ' <input type="text" name="' + key + '" value="' + (lead[key] || '') + '"></label></div>';
            }

            var html = '<div class="la-detail-header"><h3>Lead Details</h3>' +
                '<div class="la-detail-actions">' +
                '<a class="la-btn la-btn--ghost" href="' + exportCsvUrl + '">' + icons.download + 'Export CSV</a>' +
                '<a class="la-btn la-btn--ghost" href="' + exportCalendarUrl + '" target="_blank">' + icons.calendar + 'Add to Calendar</a>' +
                (readOnly ? '' : '<button type="button" class="la-btn la-btn--danger la-delete-lead" data-lead-id="' + lead.id + '">' + icons.trash + 'Delete Lead</button>') +
                '</div>' +
                '</div>';
            html += '<div class="la-detail-grid">';

            var statusSelect = buildSelect('status', normalizePipelineStageValue(lead.status), statusOptions);
            var followupSelect = buildSelect('followup_status', lead.followup_status || 'scheduled', followupOptions);

            html += '<form class="la-form la-update-form">' +
                '<div class="la-detail-columns">' +
                    '<div class="la-detail-col la-detail-col-left">' +
                        '<div class="la-detail-contact">' +
                            '<div><label>First Name <input type="text" name="first_name" value="' + (lead.first_name || '') + '"></label></div>' +
                            '<div><label>Last Name <input type="text" name="last_name" value="' + (lead.last_name || '') + '"></label></div>' +
                            '<div><label>Email <input type="email" name="email" value="' + (lead.email || '') + '"></label></div>' +
                            '<div><label>Phone <input type="text" name="phone" value="' + (lead.phone || '') + '"></label></div>' +
                            '<div><label>Company <input type="text" name="company" value="' + (lead.company || '') + '"></label></div>' +
                            '<div><label>Source <input type="text" name="source" value="' + (lead.source || '') + '"></label></div>' +
                            '<div><label>Street Address <input type="text" name="address_street" value="' + (lead.address_street || '') + '"></label></div>' +
                            '<div><label>City <input type="text" name="address_city" value="' + (lead.address_city || '') + '"></label></div>' +
                            '<div><label>State <input type="text" name="address_state" value="' + (lead.address_state || '') + '"></label></div>' +
                            '<div><label>Zip <input type="text" name="address_zip" value="' + (lead.address_zip || '') + '"></label></div>' +
                            '<div><label>Country <input type="text" name="address_country" value="' + (lead.address_country || '') + '"></label></div>' +
                            '<div><label>Last Actioned <input type="datetime-local" name="last_actioned" value="' + (lead.last_actioned ? String(lead.last_actioned).replace(" ", "T") : '') + '"></label></div>' +
                            '<div><label>Last Contacted <input type="datetime-local" name="last_contacted" value="' + (lead.last_contacted ? String(lead.last_contacted).replace(" ", "T") : '') + '"></label></div>' +
                        '</div>' +
                        '<div class="la-custom-field-panel">' +
                            '<h4>Custom Fields</h4>' +
                            '<div class="la-custom-field-grid">' +
                            customFieldInputs +
                            '</div>' +
                        '</div>' +
                        '<div class="la-status-panel">' +
                            '<h4>Pipeline Stage & Follow-up Status</h4>' +
                            '<div class="la-status-fields">' +
                                '<div><label>Pipeline Stage ' + statusSelect + '</label></div>' +
                                '<div><label>Follow-up Status ' + followupSelect + '</label></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="la-detail-col la-detail-col-right">' +
                        '<div class="la-tag-panel">' +
                            '<h4>Tags</h4>' +
                            '<div class="la-tag-picker">' +
                                '<div class="la-tag-header"><label>Tags</label></div>' +
                                '<div class="la-tag-selected-wrap"><div class="la-tag-section-title">Assigned tags</div><div class="la-tag-selected"></div></div>' +
                                '<div class="la-tag-list-wrap"><div class="la-tag-section-title">All tags</div><div class="la-tag-list"></div></div>' +
                                '<input type="text" name="tag_search" class="la-tag-search" placeholder="Search or add tag">' +
                                '<span class="la-tag-help">Type to filter, press Enter to add.</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="la-followup-panel la-followup-panel--detail">' +
                            '<h4>Follow-up & Due Dates</h4>' +
                            '<div class="la-followup-block">' +
                                '<label>Follow-up Date</label>' +
                                '<input type="datetime-local" name="followup_at" value="' + (lead.followup_at ? lead.followup_at.replace(" ", "T") : '') + '">' +
                                '<select class="la-time-select la-followup-select" data-target="followup_at">' +
                                    '<option value="">Follow-up defaults</option>' +
                                    '<option value="5">In 5 min</option>' +
                                    '<option value="15">In 15 min</option>' +
                                    '<option value="60">In 1 hr</option>' +
                                    '<option value="1440">In 1 day</option>' +
                                    '<option value="10080">In 1 week</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="la-followup-block">' +
                                '<label>Due Date</label>' +
                                '<input type="datetime-local" name="due_at" value="' + (lead.due_at ? lead.due_at.replace(" ", "T") : '') + '">' +
                                '<select class="la-time-select la-due-select" data-target="due_at">' +
                                    '<option value="">Due date defaults</option>' +
                                    '<option value="1440">In 1 day</option>' +
                                    '<option value="4320">In 3 days</option>' +
                                    '<option value="10080">In 1 week</option>' +
                                    '<option value="20160">In 2 weeks</option>' +
                                    '<option value="43200">In 1 month</option>' +
                                '</select>' +
                            '</div>' +
                            '<label class="la-followup-optout"><input type="checkbox" name="skip_reminders" value="1"' + (lead.skip_reminders ? ' checked' : '') + '> Skip reminder emails for this lead</label>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                (readOnly ? '<div class="la-form-actions"><button type="button" class="la-btn la-btn--ghost la-cancel-edit">Close</button></div>' :
                    '<div class="la-form-actions">' +
                    '<button type="submit" class="la-btn">' + icons.edit + 'Update Lead</button>' +
                    '<button type="button" class="la-btn la-btn--ghost la-cancel-edit">Cancel</button>' +
                    '</div>') +
                '<div class="la-message"></div>' +
                '</form>' +
                '</div>';

            html += '<div class="la-notes la-notes-full">';
            html += '<h4>Notes</h4>';
            if (!notes.length) {
                html += '<p class="la-muted">No notes yet.</p>';
            } else {
                html += '<div class="la-notes-list">';
                notes.forEach(function (note) {
                    html += '<div class="la-note-card" data-note-id="' + note.id + '">' +
                        '<div class="la-note-body"><div class="la-note-text">' + note.note + '</div><div class="la-note-meta">' + formatNoteDate(note.created_at) + '</div></div>' +
                        (readOnly ? '' : '<div class="la-note-actions">' +
                        '<button type="button" class="la-btn la-btn--ghost la-note-edit" title="Edit" data-note-id="' + note.id + '">' + icons.edit + '</button>' +
                        '<button type="button" class="la-btn la-btn--ghost la-note-delete" data-note-id="' + note.id + '">' + icons.trash + '</button>' +
                        '</div>') +
                        '</div>';
                });
                html += '</div>';
            }
            if (!readOnly) {
                html += '<form class="la-form la-note-form">' +
                    '<textarea name="note" rows="3" placeholder="Add a note"></textarea>' +
                    '<button type="submit" class="la-btn">' + icons.plus + 'Add Note</button>' +
                    '</form></div>';
            } else {
                html += '</div>';
            }

            $el.html(html);


            function renderTagList(filter) {
                var selectedHtml = '';
                selectedTags.forEach(function (tagId) {
                    var tag = tags.find(function (t) { return t.id === tagId; });
                    if (!tag) {
                        return;
                    }
                    selectedHtml += '<span class="la-tag-selected-chip" data-tag-id="' + tag.id + '">' +
                        tag.name + '<button type="button" class="la-tag-remove">' + icons.x + '</button></span>';
                });
                if (!selectedHtml) {
                    selectedHtml = '<span class="la-muted">No tags selected.</span>';
                }
                $el.find('.la-tag-selected').html(selectedHtml);

                var listHtml = '';
                var lowerFilter = (filter || '').toLowerCase();
                tags.forEach(function (tag) {
                    if (lowerFilter && tag.name.toLowerCase().indexOf(lowerFilter) === -1) {
                        return;
                    }
                    var isSelected = selectedTags.indexOf(tag.id) !== -1;
                    listHtml += '<button type="button" class="la-tag-chip' + (isSelected ? ' is-selected' : '') + '" data-tag-id="' + tag.id + '">' +
                        '<span class="la-tag-chip-label">' + tag.name + '</span>' +
                        '<span class="la-tag-chip-remove" aria-hidden="true">×</span>' +
                        '</button>';
                });
                if (!listHtml) {
                    listHtml = '<span class="la-muted">No tags found.</span>';
                }
                $el.find('.la-tag-list').html(listHtml);
                updateAddButton(filter || '');
            }

            renderTagList('');

            function saveTags() {
                console.log('[Lead Aggregator] Saving tags', {
                    leadId: leadId,
                    selectedTags: selectedTags
                });
                apiRequest('PUT', 'leads/' + leadId, { tags: selectedTags }).done(function (response) {
                    console.log('[Lead Aggregator] Tags saved', response);
                    $el.find('.la-message').text('Tags updated.');
                }).fail(function (xhr) {
                    console.error('[Lead Aggregator] Tag save failed', xhr);
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update tags.';
                    $el.find('.la-message').text(message);
                });
            }

            $el.find('.la-tag-list').on('click', '.la-tag-chip', function () {
                var tagId = parseInt($(this).data('tag-id'), 10);
                if (isNaN(tagId)) {
                    return;
                }
                console.log('[Lead Aggregator] Tag chip clicked', {
                    leadId: leadId,
                    tagId: tagId,
                    before: selectedTags.slice()
                });
                if (selectedTags.indexOf(tagId) === -1) {
                    selectedTags.push(tagId);
                } else {
                    selectedTags = selectedTags.filter(function (id) {
                        return id !== tagId;
                    });
                }
                console.log('[Lead Aggregator] Tag chip updated', {
                    leadId: leadId,
                    tagId: tagId,
                    after: selectedTags.slice()
                });
                renderTagList($el.find('.la-tag-search').val());
                saveTags();
            });

            $el.find('.la-tag-list').on('click', '.la-tag-chip-remove', function (event) {
                event.stopPropagation();
                var tagId = parseInt($(this).closest('.la-tag-chip').data('tag-id'), 10);
                if (isNaN(tagId)) {
                    return;
                }
                console.log('[Lead Aggregator] Tag remove clicked', {
                    leadId: leadId,
                    tagId: tagId,
                    before: selectedTags.slice()
                });
                selectedTags = selectedTags.filter(function (id) {
                    return id !== tagId;
                });
                console.log('[Lead Aggregator] Tag removed', {
                    leadId: leadId,
                    tagId: tagId,
                    after: selectedTags.slice()
                });
                renderTagList($el.find('.la-tag-search').val());
                saveTags();
            });

            $el.find('.la-tag-selected').on('click', '.la-tag-remove', function () {
                var tagId = parseInt($(this).closest('.la-tag-selected-chip').data('tag-id'), 10);
                if (isNaN(tagId)) {
                    return;
                }
                selectedTags = selectedTags.filter(function (id) {
                    return id !== tagId;
                });
                renderTagList($el.find('.la-tag-search').val());
                saveTags();
            });

            function updateAddButton(value) {
                var name = (value || '').trim();
                var button = $el.find('.la-tag-create');
                button.attr('data-tag-name', name);
                if (!name) {
                    button.prop('disabled', false).html(icons.plus + 'Add tag');
                    return;
                }
                var existing = tags.find(function (tag) {
                    return tag.name.toLowerCase() === name.toLowerCase();
                });
                if (existing) {
                    button.prop('disabled', false).html(icons.plus + 'Assign "' + existing.name + '"');
                } else {
                    button.prop('disabled', false).html(icons.plus + 'Create "' + name + '"');
                }
            }

            function handleAddTag(name) {
                var trimmed = (name || '').trim();
                if (!trimmed) {
                    return;
                }
                var existing = tags.find(function (tag) {
                    return tag.name.toLowerCase() === trimmed.toLowerCase();
                });
                if (existing) {
                    if (selectedTags.indexOf(existing.id) === -1) {
                        selectedTags.push(existing.id);
                        renderTagList('');
                        saveTags();
                    }
                    $el.find('.la-tag-search').val('');
                    updateAddButton('');
                    return;
                }
                apiRequest('POST', 'tags', { name: trimmed }).done(function (response) {
                    if (response && response.tag_id) {
                        tags.push({ id: response.tag_id, name: trimmed });
                        selectedTags.push(response.tag_id);
                        $el.find('.la-tag-search').val('');
                        renderTagList('');
                        saveTags();
                    }
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to add tag.';
                    $el.find('.la-message').text(message);
                });
            }

            $el.find('.la-tag-search').on('input', function () {
                var value = $(this).val();
                renderTagList(value);
            });

            $el.find('.la-followup-quick-btn').on('click', function () {
                var minutes = parseInt($(this).data('minutes'), 10);
                if (isNaN(minutes)) {
                    return;
                }
                var date = new Date();
                date.setMinutes(date.getMinutes() + minutes);
                $el.find('input[name="followup_at"]').val(formatDateTimeLocal(date));
            });

            $el.find('.la-tag-search').on('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    handleAddTag($(this).val());
                }
            });

            updateAddButton($el.find('.la-tag-search').val());

            $el.find('.la-time-select').on('change', function () {
                var minutes = parseInt($(this).val(), 10);
                if (isNaN(minutes)) {
                    return;
                }
                var target = $(this).data('target');
                var input = $el.find('input[name="' + target + '"]');
                if (!input.length) {
                    return;
                }
                var next = new Date();
                next.setMinutes(next.getMinutes() + minutes);
                input.val(formatDateTimeLocal(next));
            });

            $el.find('.la-update-form').on('submit', function (e) {
                e.preventDefault();
                var data = {};
                $(this).serializeArray().forEach(function (item) {
                    data[item.name] = item.value;
                });
                data.skip_reminders = $el.find('input[name="skip_reminders"]').is(':checked') ? 1 : 0;
                data.tags = selectedTags;
                apiRequest('PUT', 'leads/' + leadId, data).done(function () {
                    $el.find('.la-message').text('Lead updated.');
                    $(document).trigger('leadAggregator:refresh');
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to update lead.';
                    $el.find('.la-message').text(message);
                });
            });

            $el.find('.la-cancel-edit').on('click', function () {
                $(document).trigger('leadAggregator:refresh');
            });

            $el.find('.la-delete-lead').on('click', function () {
                if (!confirm('Delete this lead? This cannot be undone.')) {
                    return;
                }
                apiRequest('DELETE', 'leads/' + leadId).done(function () {
                    $el.find('.la-message').text('Lead deleted.');
                    if (history.replaceState) {
                        history.replaceState(null, '', window.location.pathname);
                    }
                    $(document).trigger('leadAggregator:refresh');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to delete lead.');
                });
            });

            if (readOnly) {
                $el.find('.la-update-form input, .la-update-form select, .la-update-form textarea').prop('disabled', true);
                $el.find('.la-tag-chip, .la-tag-remove, .la-tag-search').prop('disabled', true);
            }

            $el.find('.la-note-form').on('submit', function (e) {
                e.preventDefault();
                var note = $(this).find('textarea[name="note"]').val();
                if (!note) {
                    return;
                }
                apiRequest('POST', 'leads/' + leadId + '/notes', { note: note }).done(function (response) {
                    var noteId = response && response.note_id ? response.note_id : null;
                    var meta = formatNoteDate(new Date().toISOString());
                    var list = $el.find('.la-notes-list');
                    if (!list.length) {
                        $el.find('.la-notes .la-muted').remove();
                        list = $('<div class="la-notes-list"></div>');
                        $el.find('.la-note-form').before(list);
                    }
                    var card = $('<div class="la-note-card" data-note-id="' + (noteId || '') + '"><div class="la-note-body"><div class="la-note-text">' + note + '</div><div class="la-note-meta">' + meta + '</div></div><div class="la-note-actions"><button type="button" class="la-btn la-btn--ghost la-note-edit" title="Edit" data-note-id="' + (noteId || '') + '">' + icons.edit + '</button><button type="button" class="la-btn la-btn--ghost la-note-delete" data-note-id="' + (noteId || '') + '">' + icons.trash + '</button></div></div>');
                    list.prepend(card);
                    $el.find('.la-note-form textarea[name="note"]').val('');
                });
            });

            $el.off('click', '.la-note-edit').on('click', '.la-note-edit', function () {
                var card = $(this).closest('.la-note-card');
                if (card.hasClass('is-editing')) {
                    return;
                }
                var text = card.find('.la-note-text').text();
                card.addClass('is-editing');
                card.find('.la-note-text').replaceWith('<textarea class="la-note-edit-input" rows="3"></textarea>');
                card.find('.la-note-edit-input').val(text);
                card.find('.la-note-actions').prepend('<button type="button" class="la-btn la-btn--ghost la-note-save" title="Save">' + icons.check + '</button>' +
                    '<button type="button" class="la-btn la-btn--ghost la-note-cancel" title="Cancel">' + icons.x + '</button>');
            });

            $el.off('click', '.la-note-cancel').on('click', '.la-note-cancel', function () {
                var card = $(this).closest('.la-note-card');
                var original = card.find('.la-note-edit-input').val();
                card.find('.la-note-edit-input').replaceWith('<div class="la-note-text">' + original + '</div>');
                card.find('.la-note-save, .la-note-cancel').remove();
                card.removeClass('is-editing');
            });

            $el.off('click', '.la-note-save').on('click', '.la-note-save', function () {
                var card = $(this).closest('.la-note-card');
                var noteId = card.data('note-id');
                var nextValue = card.find('.la-note-edit-input').val();
                if (!nextValue) {
                    return;
                }
                apiRequest('PUT', 'leads/' + leadId + '/notes/' + noteId, { note: nextValue }).done(function () {
                    card.find('.la-note-edit-input').replaceWith('<div class="la-note-text">' + nextValue + '</div>');
                    card.find('.la-note-save, .la-note-cancel').remove();
                    card.removeClass('is-editing');
                });
            });

            $el.off('click', '.la-note-delete').on('click', '.la-note-delete', function () {
                var noteId = $(this).data('note-id');
                if (!confirm('Delete this note? This cannot be undone.')) {
                    return;
                }
                apiRequest('DELETE', 'leads/' + leadId + '/notes/' + noteId).done(function () {
                    $(this).closest('.la-note-card').remove();
                }.bind(this));
            });
        }).fail(function () {
            $el.html('<p>Unable to load lead.</p>');
        });
    }

    function renderTags($el) {
        $el.html('<p>Loading tags...</p>');
        apiRequest('GET', 'tags').done(function (tags) {
            var html = '<div class="la-section-header"><h3>Tags</h3><p class="la-muted">Manage your reusable tags here.</p></div>';
            html += '<form class="la-form la-tag-form"><input type="text" name="name" placeholder="Tag name" required><button type="submit" class="la-btn">' + icons.plus + 'Add Tag</button></form>';
            html += '<ul class="la-list">';
            tags.forEach(function (tag) {
                var count = tag.lead_count ? parseInt(tag.lead_count, 10) : 0;
                html += '<li class="la-tag-row"><span>' + tag.name + ' <span class="la-tag-count">(' + count + ')</span> <span class="la-tag-id">ID ' + tag.id + '</span></span>' +
                    '<div class="la-tag-actions">' +
                    '<button type="button" data-id="' + tag.id + '" data-name="' + tag.name + '" class="la-btn la-btn--ghost la-tag-view">View Leads</button>' +
                    '<button type="button" data-id="' + tag.id + '" data-name="' + tag.name + '" class="la-btn la-btn--ghost la-tag-edit" title="Rename">' + icons.edit + '</button>' +
                    '<button type="button" data-id="' + tag.id + '" class="la-btn la-btn--ghost la-delete" title="Delete">' + icons.trash + '</button>' +
                    '</div>' +
                    '</li>' +
                    '';
            });
            html += '</ul>';
            $el.html(html);

            $el.find('.la-tag-form').on('submit', function (e) {
                e.preventDefault();
                var name = $(this).find('input[name="name"]').val();
                apiRequest('POST', 'tags', { name: name }).done(function () {
                    renderTags($el);
                });
            });

            $el.find('.la-delete').on('click', function () {
                apiRequest('DELETE', 'tags/' + $(this).data('id')).done(function () {
                    renderTags($el);
                });
            });

            $el.find('.la-tag-edit').on('click', function () {
                var tagId = $(this).data('id');
                var currentName = $(this).data('name') || '';
                var nextName = window.prompt('Rename tag', currentName);
                if (!nextName || nextName === currentName) {
                    return;
                }
                apiRequest('PUT', 'tags/' + tagId, { name: nextName }).done(function () {
                    renderTags($el);
                });
            });

            $el.find('.la-tag-view').on('click', function () {
                var tagId = $(this).data('id');
                var tagName = $(this).data('name') || 'Tag';
                if (window.leadAggregatorOpenLeadTag) {
                    window.leadAggregatorOpenLeadTag(tagId, tagName);
                }
            });
        });
    }

    function renderCalendar($el) {
        $el.html('<p>Loading followups...</p>');
        $.when(apiRequest('GET', 'leads'), apiRequest('GET', 'me'), apiRequest('GET', 'custom-fields')).done(function (leadsRes, meRes, customFieldsRes) {
            var leads = leadsRes[0] || [];
            var me = meRes && meRes[0] ? meRes[0] : {};
            var readOnly = (me.access_level || 'full') === 'read';
            var customFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].fields) ? customFieldsRes[0].fields : {};
            var followupFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].followup) ? customFieldsRes[0].followup : {};
            var view = $el.data('view-mode') || 'calendar';
            console.log('[Lead Aggregator] Followups view', view);
            var calendarUrl = leadAggregator.restUrl + 'calendar?_wpnonce=' + encodeURIComponent(leadAggregator.nonce);
            var html = '<div class="la-section-header"><h3>Follow-ups</h3>' +
                '<div class="la-section-actions la-view-toggle">' +
                '<button type="button" class="la-btn la-btn--ghost la-view-btn' + (view === 'list' ? ' is-active' : '') + '" data-view="list">List</button>' +
                '<button type="button" class="la-btn la-btn--ghost la-view-btn' + (view === 'calendar' ? ' is-active' : '') + '" data-view="calendar">Calendar</button>' +
                '<a class="la-btn la-btn--ghost la-calendar-subscribe" href="' + calendarUrl + '" target="_blank">Subscribe</a>' +
                '</div></div>';

            html += '<div class="la-followup-views">';
            html += '<div class="la-followup-list"' + (view === 'list' ? '' : ' style="display:none;"') + '>';
            html += '<table class="la-table"><thead><tr><th>Lead</th><th>Followup Date</th><th>Followup Due</th><th>Pipeline Stage</th><th>Follow-up Status</th>' + (readOnly ? '' : '<th class="la-actions-cell">Actions</th>') + '</tr></thead><tbody>';
            var hasRows = false;
            leads.forEach(function (lead) {
                if (lead.followup_at || lead.due_at) {
                    hasRows = true;
                    var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                    var followupStatus = lead.followup_status || 'scheduled';
                    var leadStatus = lead.status || 'open';
                    html += '<tr>';
                    html += '<td>' + (name.trim() || 'Lead #' + lead.id) + '</td>';
                    html += '<td>' + formatDisplayDate(lead.followup_at) + '</td>';
                    html += '<td>' + formatDisplayDate(lead.due_at) + '</td>';
                    html += '<td>' + leadStatus + '</td>';
                    html += '<td>' + followupStatus + '</td>';
                    if (!readOnly) {
                        html += '<td class="la-actions">' +
                            '<button type="button" class="la-btn la-btn--ghost la-followup-manage" data-lead-id="' + lead.id + '">' + icons.edit + 'Manage</button>' +
                            '<button type="button" class="la-btn la-btn--ghost la-followup-complete" data-lead-id="' + lead.id + '">' + icons.check + 'Complete</button>' +
                            '<button type="button" class="la-btn la-btn--ghost la-followup-cancel" data-lead-id="' + lead.id + '">' + icons.trash + 'Cancel</button>' +
                            '</td>';
                    }
                    html += '</tr>';
                    html += '<tr class="la-followup-row" data-lead-id="' + lead.id + '" style="display:none;"><td colspan="' + (readOnly ? 5 : 6) + '"></td></tr>';
                }
            });
            if (!hasRows) {
                html += '<tr><td class="la-empty" colspan="6">No follow-ups scheduled.</td></tr>';
            }
            html += '</tbody></table>';
            html += '</div>';
            html += '<div class="la-followup-calendar"' + (view === 'calendar' ? '' : ' style="display:none;"') + '>';
            html += '<div class="la-calendar-header">' +
                '<button type="button" class="la-btn la-btn--ghost la-calendar-prev">Prev</button>' +
                '<div class="la-calendar-title"></div>' +
                '<button type="button" class="la-btn la-btn--ghost la-calendar-next">Next</button>' +
                '</div>' +
                '<div class="la-calendar-grid"></div>' +
                '</div>';
            html += '</div>';

            $el.html(html);

            function parseLeadDate(value) {
                if (!value) {
                    return null;
                }
                var normalized = String(value).replace(' ', 'T');
                var date = new Date(normalized);
                return isNaN(date.getTime()) ? null : date;
            }

            function renderMonth(date) {
                var year = date.getFullYear();
                var month = date.getMonth();
                var start = new Date(year, month, 1);
                var end = new Date(year, month + 1, 0);
                var startDay = start.getDay();
                var totalDays = end.getDate();
                var today = new Date();
                var todayIso = today.toISOString().slice(0, 10);

                var title = start.toLocaleString([], { month: 'long', year: 'numeric' });
                $el.find('.la-calendar-title').text(title);

                var grid = '<div class="la-calendar-week">';
                ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(function (day) {
                    grid += '<div class="la-calendar-day la-calendar-day--header">' + day + '</div>';
                });
                grid += '</div><div class="la-calendar-week">';

                var dayCount = 0;
                for (var i = 0; i < startDay; i += 1) {
                    grid += '<div class="la-calendar-day la-calendar-day--empty"></div>';
                    dayCount += 1;
                }

                for (var d = 1; d <= totalDays; d += 1) {
                    var current = new Date(year, month, d);
                    var iso = current.toISOString().slice(0, 10);
                    var isToday = iso === todayIso;
                    var items = [];
                    leads.forEach(function (lead) {
                        var followup = parseLeadDate(lead.followup_at);
                        var due = parseLeadDate(lead.due_at);
                        if (followup && followup.toISOString().slice(0, 10) === iso) {
                            items.push({ lead: lead, type: 'followup' });
                        }
                        if (due && due.toISOString().slice(0, 10) === iso) {
                            items.push({ lead: lead, type: 'due' });
                        }
                    });

                    grid += '<div class="la-calendar-day' + (isToday ? ' is-today' : '') + '">' +
                        '<div class="la-calendar-date">' + d + '</div>';
                    if (items.length) {
                        items.slice(0, 4).forEach(function (item) {
                            var lead = item.lead;
                            var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                            var itemClass = 'la-calendar-item' + (item.type === 'due' ? ' is-due' : ' is-followup');
                            grid += '<div class="' + itemClass + '" data-lead-id="' + lead.id + '">' +
                                (name.trim() || 'Lead #' + lead.id) +
                                '</div>';
                        });
                        if (items.length > 4) {
                            grid += '<div class="la-calendar-more">+' + (items.length - 4) + ' more</div>';
                        }
                    }
                    grid += '</div>';

                    dayCount += 1;
                    if (dayCount % 7 === 0 && d !== totalDays) {
                        grid += '</div><div class="la-calendar-week">';
                    }
                }

                while (dayCount % 7 !== 0) {
                    grid += '<div class="la-calendar-day la-calendar-day--empty"></div>';
                    dayCount += 1;
                }
                grid += '</div>';

                $el.find('.la-calendar-grid').html(grid);
            }

            var currentMonth = $el.data('calendar-month');
            if (!currentMonth) {
                currentMonth = new Date();
                $el.data('calendar-month', currentMonth);
            }

            renderMonth(new Date(currentMonth));

            $el.find('.la-followup-manage').on('click', function () {
                var leadId = $(this).data('lead-id');
                var row = $el.find('.la-followup-row[data-lead-id="' + leadId + '"]');
                if (row.is(':visible')) {
                    row.hide().find('td').empty();
                    return;
                }
                row.show().find('td').html('<div class="la-followup-panel">Loading details...</div>');
                apiRequest('GET', 'leads/' + leadId).done(function (lead) {
                    apiRequest('GET', 'leads/' + leadId + '/notes').done(function (notes) {
                        var statusSelect = buildSelect('status', lead.status || 'open', statusOptions);
                        var followupSelect = buildSelect('followup_status', lead.followup_status || 'scheduled', followupOptions);
                        var panel = '<div class="la-followup-panel">' +
                            '<div class="la-followup-grid">' +
                                '<div>' +
                                    '<label>Pipeline Stage ' + statusSelect + '</label>' +
                                '</div>' +
                                '<div>' +
                                    '<label>Follow-up Status ' + followupSelect + '</label>' +
                                '</div>' +
                                '<div>' +
                                    '<label>Followup Date <input type="datetime-local" name="followup_at" value="' + (lead.followup_at ? lead.followup_at.replace(" ", "T") : '') + '"></label>' +
                                '</div>' +
                                '<div>' +
                                    '<label>Due Date <input type="datetime-local" name="due_at" value="' + (lead.due_at ? lead.due_at.replace(" ", "T") : '') + '"></label>' +
                                '</div>' +
                            '</div>' +
                            '<label class="la-followup-optout"><input type="checkbox" name="skip_reminders" value="1"' + (lead.skip_reminders ? ' checked' : '') + '> Skip reminder emails for this lead</label>' +
                            (readOnly ? '' : '<div class="la-followup-actions">' +
                                '<button type="button" class="la-btn la-followup-save">' + icons.edit + 'Update</button>' +
                            '</div>') +
                            '<div class="la-followup-notes">' +
                                '<h4>Notes</h4>' +
                                '<div class="la-notes-list">' +
                                    (notes.length ? notes.map(function (note) {
                                        return '<div class="la-note-card" data-note-id="' + note.id + '"><div class="la-note-body"><div class="la-note-text">' + note.note + '</div><div class="la-note-meta">' + note.created_at + '</div></div>' +
                                            (readOnly ? '' : '<div class="la-note-actions"><button type="button" class="la-btn la-btn--ghost la-note-edit" title="Edit" data-note-id="' + note.id + '">' + icons.edit + '</button><button type="button" class="la-btn la-btn--ghost la-note-delete" data-note-id="' + note.id + '">' + icons.trash + '</button></div>') +
                                            '</div>';
                                    }).join('') : '<p class="la-muted">No notes yet.</p>') +
                                '</div>' +
                                (readOnly ? '' : '<textarea class="la-followup-note-input" rows="3" placeholder="Add a note"></textarea>' +
                                '<button type="button" class="la-btn la-followup-note-save">' + icons.plus + 'Add Note</button>') +
                            '</div>' +
                        '</div>';
                        row.find('td').html(panel);

                        row.find('.la-followup-save').on('click', function () {
                            var data = {
                                status: row.find('select[name="status"]').val(),
                                followup_status: row.find('select[name="followup_status"]').val(),
                                followup_at: row.find('input[name="followup_at"]').val(),
                                due_at: row.find('input[name="due_at"]').val(),
                                skip_reminders: row.find('input[name="skip_reminders"]').is(':checked') ? 1 : 0
                            };
                            apiRequest('PUT', 'leads/' + leadId, data).done(function () {
                                renderCalendar($el);
                            });
                        });

                        row.find('.la-followup-note-save').on('click', function () {
                            var noteValue = row.find('.la-followup-note-input').val();
                            if (!noteValue) {
                                return;
                            }
                            apiRequest('POST', 'leads/' + leadId + '/notes', { note: noteValue }).done(function (response) {
                                var noteId = response && response.note_id ? response.note_id : null;
                                var meta = formatNoteDate(new Date().toISOString());
                                var list = row.find('.la-notes-list');
                                if (!list.length) {
                                    row.find('.la-followup-notes .la-muted').remove();
                                    list = $('<div class="la-notes-list"></div>');
                                    row.find('.la-followup-notes h4').after(list);
                                }
                                var card = $('<div class="la-note-card" data-note-id="' + (noteId || '') + '"><div class="la-note-body"><div class="la-note-text">' + noteValue + '</div><div class="la-note-meta">' + meta + '</div></div><div class="la-note-actions"><button type="button" class="la-btn la-btn--ghost la-note-edit" title="Edit" data-note-id="' + (noteId || '') + '">' + icons.edit + '</button><button type="button" class="la-btn la-btn--ghost la-note-delete" data-note-id="' + (noteId || '') + '">' + icons.trash + '</button></div></div>');
                                list.prepend(card);
                                row.find('.la-followup-note-input').val('');
                            });
                        });

                        row.off('click', '.la-note-edit').on('click', '.la-note-edit', function () {
                            var card = $(this).closest('.la-note-card');
                            if (card.hasClass('is-editing')) {
                                return;
                            }
                            var text = card.find('.la-note-text').text();
                            card.addClass('is-editing');
                            card.find('.la-note-text').replaceWith('<textarea class="la-note-edit-input" rows="3"></textarea>');
                            card.find('.la-note-edit-input').val(text);
                            card.find('.la-note-actions').prepend('<button type="button" class="la-btn la-btn--ghost la-note-save" title="Save">' + icons.check + '</button>' +
                                '<button type="button" class="la-btn la-btn--ghost la-note-cancel" title="Cancel">' + icons.x + '</button>');
                        });

                        row.off('click', '.la-note-cancel').on('click', '.la-note-cancel', function () {
                            var card = $(this).closest('.la-note-card');
                            var original = card.find('.la-note-edit-input').val();
                            card.find('.la-note-edit-input').replaceWith('<div class="la-note-text">' + original + '</div>');
                            card.find('.la-note-save, .la-note-cancel').remove();
                            card.removeClass('is-editing');
                        });

                        row.off('click', '.la-note-save').on('click', '.la-note-save', function () {
                            var card = $(this).closest('.la-note-card');
                            var noteId = card.data('note-id');
                            var nextValue = card.find('.la-note-edit-input').val();
                            if (!nextValue) {
                                return;
                            }
                            apiRequest('PUT', 'leads/' + leadId + '/notes/' + noteId, { note: nextValue }).done(function () {
                                card.find('.la-note-edit-input').replaceWith('<div class="la-note-text">' + nextValue + '</div>');
                                card.find('.la-note-save, .la-note-cancel').remove();
                                card.removeClass('is-editing');
                            });
                        });

                        row.off('click', '.la-note-delete').on('click', '.la-note-delete', function () {
                            var noteId = $(this).data('note-id');
                            if (!confirm('Delete this note? This cannot be undone.')) {
                                return;
                            }
                            apiRequest('DELETE', 'leads/' + leadId + '/notes/' + noteId).done(function () {
                                $(this).closest('.la-note-card').remove();
                            }.bind(this));
                        });
                    });
                });
            });

            $el.find('.la-followup-complete').on('click', function () {
                var leadId = $(this).data('lead-id');
                apiRequest('PUT', 'leads/' + leadId, { followup_status: 'completed' }).done(function () {
                    renderCalendar($el);
                });
            });

            $el.find('.la-followup-cancel').on('click', function () {
                var leadId = $(this).data('lead-id');
                if (!confirm('Cancel this follow-up?')) {
                    return;
                }
                apiRequest('PUT', 'leads/' + leadId, { followup_status: 'canceled', followup_at: null, due_at: null }).done(function () {
                    renderCalendar($el);
                });
            });

            $el.find('.la-view-btn').on('click', function () {
                var nextView = $(this).data('view');
                $el.data('view-mode', nextView);
                $el.find('.la-followup-list').toggle(nextView === 'list');
                $el.find('.la-followup-calendar').toggle(nextView === 'calendar');
                $el.find('.la-view-btn').removeClass('is-active');
                $(this).addClass('is-active');
            });

            $el.find('.la-calendar-prev').on('click', function () {
                var current = $el.data('calendar-month') || new Date();
                var next = new Date(current.getFullYear(), current.getMonth() - 1, 1);
                $el.data('calendar-month', next);
                renderMonth(next);
            });

            $el.find('.la-calendar-next').on('click', function () {
                var current = $el.data('calendar-month') || new Date();
                var next = new Date(current.getFullYear(), current.getMonth() + 1, 1);
                $el.data('calendar-month', next);
                renderMonth(next);
            });

            $el.on('click', '.la-calendar-item', function () {
                var leadId = $(this).data('lead-id');
                if (leadId && window.leadAggregatorOpenLeadDetail) {
                    window.leadAggregatorOpenLeadDetail(leadId);
                }
            });
        }).fail(function () {
            $el.html('<p>Unable to load calendar.</p>');
        });
    }

    function renderCalendarOnly($el) {
        $el.html('<p>Loading calendar...</p>');
        apiRequest('GET', 'leads').done(function (leads) {
            var calendarUrl = leadAggregator.restUrl + 'calendar?_wpnonce=' + encodeURIComponent(leadAggregator.nonce);
            var html = '<div class="la-section-header"><h3>Calendar</h3>' +
                '<div class="la-section-actions la-view-toggle">' +
                '<a class="la-btn la-btn--ghost la-calendar-subscribe" href="' + calendarUrl + '" target="_blank">Subscribe</a>' +
                '</div></div>';

            html += '<div class="la-followup-calendar">' +
                '<div class="la-calendar-header">' +
                '<button type="button" class="la-btn la-btn--ghost la-calendar-prev">Prev</button>' +
                '<div class="la-calendar-title"></div>' +
                '<button type="button" class="la-btn la-btn--ghost la-calendar-next">Next</button>' +
                '</div>' +
                '<div class="la-calendar-grid"></div>' +
                '</div>';

            $el.html(html);

            function parseLeadDate(value) {
                if (!value) {
                    return null;
                }
                var normalized = String(value).replace(' ', 'T');
                var date = new Date(normalized);
                return isNaN(date.getTime()) ? null : date;
            }

            function renderMonth(date) {
                var year = date.getFullYear();
                var month = date.getMonth();
                var start = new Date(year, month, 1);
                var end = new Date(year, month + 1, 0);
                var startDay = start.getDay();
                var totalDays = end.getDate();
                var today = new Date();
                var todayIso = today.toISOString().slice(0, 10);

                var title = start.toLocaleString([], { month: 'long', year: 'numeric' });
                $el.find('.la-calendar-title').text(title);

                var grid = '<div class="la-calendar-week">';
                ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(function (day) {
                    grid += '<div class="la-calendar-day la-calendar-day--header">' + day + '</div>';
                });
                grid += '</div><div class="la-calendar-week">';

                var dayCount = 0;
                for (var i = 0; i < startDay; i += 1) {
                    grid += '<div class="la-calendar-day la-calendar-day--empty"></div>';
                    dayCount += 1;
                }

                for (var d = 1; d <= totalDays; d += 1) {
                    var dateValue = new Date(year, month, d);
                    var iso = dateValue.toISOString().slice(0, 10);
                    var isToday = iso === todayIso;
                    var items = [];
                    (leads || []).forEach(function (lead) {
                        var followup = parseLeadDate(lead.followup_at);
                        var due = parseLeadDate(lead.due_at);
                        if (followup && followup.toISOString().slice(0, 10) === iso) {
                            items.push({ lead: lead, type: 'followup' });
                        }
                        if (due && due.toISOString().slice(0, 10) === iso) {
                            items.push({ lead: lead, type: 'due' });
                        }
                    });

                    grid += '<div class="la-calendar-day' + (isToday ? ' is-today' : '') + '">' +
                        '<div class="la-calendar-date">' + d + '</div>';
                    if (items.length) {
                        items.slice(0, 4).forEach(function (item) {
                            var lead = item.lead;
                            var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                            var itemClass = 'la-calendar-item' + (item.type === 'due' ? ' is-due' : ' is-followup');
                            grid += '<div class="' + itemClass + '" data-lead-id="' + lead.id + '">' +
                                (name.trim() || 'Lead #' + lead.id) +
                                '</div>';
                        });
                        if (items.length > 4) {
                            grid += '<div class="la-calendar-more">+' + (items.length - 4) + ' more</div>';
                        }
                    }
                    grid += '</div>';
                    dayCount += 1;
                    if (dayCount % 7 === 0 && d !== totalDays) {
                        grid += '</div><div class="la-calendar-week">';
                    }
                }

                while (dayCount % 7 !== 0) {
                    grid += '<div class="la-calendar-day la-calendar-day--empty"></div>';
                    dayCount += 1;
                }
                grid += '</div>';

                $el.find('.la-calendar-grid').html(grid);
            }

            var currentMonth = $el.data('calendar-month');
            if (!currentMonth) {
                currentMonth = new Date();
                $el.data('calendar-month', currentMonth);
            }

            renderMonth(new Date(currentMonth));

            $el.find('.la-calendar-prev').on('click', function () {
                var current = $el.data('calendar-month') || new Date();
                var next = new Date(current.getFullYear(), current.getMonth() - 1, 1);
                $el.data('calendar-month', next);
                renderMonth(next);
            });

            $el.find('.la-calendar-next').on('click', function () {
                var current = $el.data('calendar-month') || new Date();
                var next = new Date(current.getFullYear(), current.getMonth() + 1, 1);
                $el.data('calendar-month', next);
                renderMonth(next);
            });

            $el.on('click', '.la-calendar-item', function () {
                var leadId = $(this).data('lead-id');
                if (leadId && window.leadAggregatorOpenLeadDetail) {
                    window.leadAggregatorOpenLeadDetail(leadId);
                }
            });
        }).fail(function () {
            $el.html('<p>Unable to load calendar.</p>');
        });
    }

    function renderFollowupsList($el) {
        $el.html('<p>Loading followups...</p>');
        $.when(apiRequest('GET', 'leads'), apiRequest('GET', 'me'), apiRequest('GET', 'custom-fields')).done(function (leadsRes, meRes, customFieldsRes) {
            var leads = leadsRes[0] || [];
            var me = meRes && meRes[0] ? meRes[0] : {};
            var readOnly = (me.access_level || 'full') === 'read';
            var customFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].fields) ? customFieldsRes[0].fields : {};
            var followupFields = (customFieldsRes && customFieldsRes[0] && customFieldsRes[0].followup) ? customFieldsRes[0].followup : {};
            var filterValue = $el.data('followup-filter') || 'all';
            var now = new Date();
            var activeColumns = [];

            function isOverdue(lead) {
                if (!lead.due_at) {
                    return false;
                }
                var status = String(lead.followup_status || '').toLowerCase();
                if (status === 'completed' || status === 'canceled') {
                    return false;
                }
                var due = new Date(String(lead.due_at).replace(' ', 'T'));
                if (isNaN(due.getTime())) {
                    return false;
                }
                return due < now;
            }

            var columnDefs = [
                { key: 'lead', label: 'Lead' },
                { key: 'followup_at', label: 'Followup Date' },
                { key: 'due_at', label: 'Followup Due' },
                { key: 'status', label: 'Pipeline Stage' },
                { key: 'followup_status', label: 'Follow-up Status' },
                { key: 'last_actioned', label: 'Last Actioned' },
                { key: 'last_contacted', label: 'Last Contacted' }
            ];
            for (var i = 1; i <= 10; i += 1) {
                var key = 'custom_' + i;
                columnDefs.push({
                    key: key,
                    label: customFields[key] || ('Custom Field ' + i)
                });
            }
            var defaultColumns = ['lead', 'followup_at', 'due_at', 'status', 'followup_status'];
            var storedColumns = [];
            try {
                storedColumns = JSON.parse(window.localStorage.getItem('leadAggregatorFollowupColumns') || '[]');
            } catch (err) {
                storedColumns = [];
            }
            var activeColumns = (storedColumns && storedColumns.length) ? storedColumns : defaultColumns.slice();

            function getColumnLabel(key) {
                var found = columnDefs.find(function (col) { return col.key === key; });
                return found ? found.label : key;
            }

            function renderCell(lead, key) {
                if (key === 'lead') {
                    var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                    return name.trim() || ('Lead #' + lead.id);
                }
                if (key === 'followup_at') {
                    return formatDisplayDate(lead.followup_at);
                }
                if (key === 'due_at') {
                    var overdueBadge = isOverdue(lead) ? ' <span class="la-badge la-badge--overdue">Overdue</span>' : '';
                    return formatDisplayDate(lead.due_at) + overdueBadge;
                }
                if (key === 'status') {
                    var stageKey = normalizePipelineStageValue(lead.status);
                    return '<span class="la-pill la-pill--' + stageKey + '">' + pipelineStageLabel(lead.status) + '</span>';
                }
                if (key === 'followup_status') {
                    return followupStatusLabel(lead.followup_status);
                }
                if (key === 'last_actioned') {
                    return formatDisplayDate(lead.last_actioned);
                }
                if (key === 'last_contacted') {
                    return formatDisplayDate(lead.last_contacted);
                }
                return lead[key] ? lead[key] : '';
            }

            var rows = leads.filter(function (lead) {
                return lead.followup_at || lead.due_at;
            });
            if (filterValue === 'overdue') {
                rows = rows.filter(function (lead) {
                    return isOverdue(lead);
                });
            }
            var countLabel = 'Showing ' + rows.length + ' lead' + (rows.length === 1 ? '' : 's');

            var html = '<div class="la-section-header"><h3>Follow-ups</h3>' +
                '<div class="la-section-actions la-followup-actions">' +
                '<select class="la-followup-filter">' +
                '<option value="all"' + (filterValue === 'all' ? ' selected' : '') + '>All</option>' +
                '<option value="overdue"' + (filterValue === 'overdue' ? ' selected' : '') + '>Overdue</option>' +
                '</select>' +
                '<div class="la-column-toggle-wrap">' +
                '<button type="button" class="la-btn la-btn--ghost la-followup-columns">' + icons.columns + 'Columns</button>' +
                '<div class="la-column-menu">';
            columnDefs.forEach(function (col) {
                var checked = activeColumns.indexOf(col.key) !== -1 ? ' checked' : '';
                html += '<label><input type="checkbox" data-key="' + col.key + '"' + checked + '> ' + col.label + '</label>';
            });
            html += '</div></div>' +
                '</div></div>';
            html += '<div class="la-filter-count la-followup-count">' + countLabel + '</div>';
            html += '<table class="la-table"><thead><tr>';
            activeColumns.forEach(function (key) {
                html += '<th>' + getColumnLabel(key) + '</th>';
            });
            html += '<th class="la-actions-cell">Actions</th></tr></thead><tbody>';

            if (!rows.length) {
                html += '<tr><td class="la-empty" colspan="' + (activeColumns.length + 1) + '">No followups scheduled.</td></tr>';
            } else {
                rows.forEach(function (lead) {
                    html += '<tr>';
                    activeColumns.forEach(function (key) {
                        html += '<td>' + renderCell(lead, key) + '</td>';
                    });
                    if (readOnly) {
                        html += '<td class="la-actions-cell"><button type="button" class="la-btn la-btn--ghost la-followup-open" data-id="' + lead.id + '">' + icons.external + 'Open</button></td>';
                    } else {
                        html += '<td class="la-actions la-actions-cell">' +
                            '<button type="button" class="la-btn la-btn--ghost la-followup-manage" data-id="' + lead.id + '">' + icons.action + 'Action</button>' +
                            '<button type="button" class="la-btn la-btn--ghost la-followup-open" data-id="' + lead.id + '">' + icons.external + 'Open</button>' +
                            '<button type="button" class="la-btn la-btn--ghost la-followup-remove" data-id="' + lead.id + '">' + icons.trash + 'Remove</button>' +
                            '</td>';
                    }
                    html += '</tr>';
                });
            }
            html += '</tbody></table>';
            html += '<div class="la-filter-count la-followup-count">' + countLabel + '</div>';
            $el.html(html);

            $el.find('.la-followup-open').on('click', function () {
                var leadId = $(this).data('id');
                if (leadId && window.leadAggregatorOpenLeadDetail) {
                    window.leadAggregatorOpenLeadDetail(leadId);
                }
            });

            $el.find('.la-followup-manage').on('click', function () {
                var leadId = $(this).data('id');
                var lead = leads.find(function (item) { return String(item.id) === String(leadId); });
                if (lead) {
                    openFollowupManageModal(lead, { labels: customFields, followup: followupFields });
                }
            });

            $el.find('.la-followup-remove').on('click', function () {
                var leadId = $(this).data('id');
                if (!leadId) {
                    return;
                }
                if (!confirm('Remove this lead from follow-ups?')) {
                    return;
                }
                apiRequest('PUT', 'leads/' + leadId, { followup_at: null, due_at: null }).done(function () {
                    renderFollowupsList($el);
                });
            });

            $el.find('.la-followup-filter').on('change', function () {
                $el.data('followup-filter', $(this).val());
                renderFollowupsList($el);
            });

            $el.find('.la-followup-columns').on('click', function (event) {
                event.stopPropagation();
                $el.find('.la-column-menu').toggleClass('is-open');
            });

            $el.find('.la-column-menu input[type="checkbox"]').on('change', function () {
                var key = $(this).data('key');
                if (this.checked) {
                    if (activeColumns.indexOf(key) === -1) {
                        activeColumns.push(key);
                    }
                } else {
                    activeColumns = activeColumns.filter(function (item) { return item !== key; });
                }
                window.localStorage.setItem('leadAggregatorFollowupColumns', JSON.stringify(activeColumns));
                renderFollowupsList($el);
            });
        }).fail(function () {
            $el.html('<p>Unable to load followups.</p>');
        });
    }

    function renderExport($el) {
        var url = leadAggregator.restUrl + 'export?_wpnonce=' + encodeURIComponent(leadAggregator.nonce);
        $el.html('<div class="la-section-header"><h3>Export</h3></div><a class="la-btn" href="' + url + '">' + icons.download + 'Download CSV</a>');
    }

    function renderBusinessProfile($el) {
        $el.html('<p>Loading profile...</p>');
        apiRequest('GET', 'business-profile').done(function (profile) {
            profile = profile || {};
            var html = '<div class="la-section-header"><h3>Business Profile</h3></div>' +
                '<form class="la-form la-profile-form">' +
                '<div><label>Business Name <input type="text" name="business_name" value="' + (profile.business_name || '') + '"></label></div>' +
                '<div><label>Website URL <input type="url" name="website_url" value="' + (profile.website_url || '') + '" placeholder="https://"></label></div>' +
                '<div><label>Business Type <input type="text" name="business_type" value="' + (profile.business_type || '') + '" placeholder="Online lessons, services, local business"></label></div>' +
                '<div><label>Industry <input type="text" name="industry" value="' + (profile.industry || '') + '"></label></div>' +
                '<div><label>Primary Offer <input type="text" name="primary_offer" value="' + (profile.primary_offer || '') + '" placeholder="What you sell or offer"></label></div>' +
                '<div><label>Target Audience <input type="text" name="target_audience" value="' + (profile.target_audience || '') + '" placeholder="Who you want to reach"></label></div>' +
                '<div><label>Ideal Customer <input type="text" name="ideal_customer" value="' + (profile.ideal_customer || '') + '" placeholder="Best-fit customer"></label></div>' +
                '<div><label>Service Area <input type="text" name="service_area" value="' + (profile.service_area || '') + '" placeholder="Local, national, global"></label></div>' +
                '<div><label>Marketing Channels <input type="text" name="marketing_channels" value="' + (profile.marketing_channels || '') + '" placeholder="Email, ads, social, referrals"></label></div>' +
                '<div><label>Monthly Lead Goal <input type="text" name="monthly_leads_goal" value="' + (profile.monthly_leads_goal || '') + '" placeholder="e.g. 50"></label></div>' +
                '<div><label>Average Order Value <input type="text" name="average_order_value" value="' + (profile.average_order_value || '') + '" placeholder="$ or amount"></label></div>' +
                '<div><label>Goals <textarea name="goals">' + (profile.goals || '') + '</textarea></label></div>' +
                '<div><label>Challenges <textarea name="challenges">' + (profile.challenges || '') + '</textarea></label></div>' +
                '<div><label>Notes <textarea name="notes">' + (profile.notes || '') + '</textarea></label></div>' +
                '<div class="la-form-actions">' +
                    '<button type="button" class="la-btn la-btn--ghost la-profile-scrape">' + icons.download + 'Auto-fill from website</button>' +
                    '<button type="submit" class="la-btn">Save</button>' +
                '</div>' +
                '<div class="la-message"></div>' +
                '</form>';
            $el.html(html);

            $el.find('form').on('submit', function (e) {
                e.preventDefault();
                var data = {};
                $(this).serializeArray().forEach(function (item) {
                    data[item.name] = item.value;
                });
                apiRequest('POST', 'business-profile', data).done(function () {
                    $el.find('.la-message').text('Saved.');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to save.');
                });
            });

            $el.find('.la-profile-scrape').on('click', function () {
                var url = $el.find('input[name="website_url"]').val();
                if (!url) {
                    $el.find('.la-message').text('Enter a website URL first.');
                    return;
                }
                $el.find('.la-message').text('Fetching website details...');
                apiRequest('POST', 'business-profile/scrape', { website_url: url }).done(function (response) {
                    var data = response && response.data ? response.data : {};
                    Object.keys(data).forEach(function (key) {
                        var field = $el.find('[name="' + key + '"]');
                        if (field.length) {
                            field.val(data[key]);
                        }
                    });
                    $el.find('.la-message').text('Auto-fill complete. Review and save.');
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to fetch website details.';
                    $el.find('.la-message').text(message);
                });
            });
        });
    }

    function renderNotificationSettings($el) {
        $el.html('<p>Loading notifications...</p>');
        apiRequest('GET', 'notifications/settings').done(function (settings) {
            settings = settings || {};
            var time = settings.time || '09:00';
            var timezone = settings.timezone || (Intl && Intl.DateTimeFormat ? Intl.DateTimeFormat().resolvedOptions().timeZone : 'America/New_York');
            var timezoneOptions = [
                { value: 'America/New_York', label: 'Eastern (America/New_York)' },
                { value: 'America/Chicago', label: 'Central (America/Chicago)' },
                { value: 'America/Denver', label: 'Mountain (America/Denver)' },
                { value: 'America/Phoenix', label: 'Arizona (America/Phoenix)' },
                { value: 'America/Los_Angeles', label: 'Pacific (America/Los_Angeles)' },
                { value: 'America/Anchorage', label: 'Alaska (America/Anchorage)' },
                { value: 'Pacific/Honolulu', label: 'Hawaii (Pacific/Honolulu)' }
            ];
            var timezoneSelect = '<select name="timezone">';
            timezoneOptions.forEach(function (option) {
                var selected = option.value === timezone ? ' selected' : '';
                timezoneSelect += '<option value="' + option.value + '"' + selected + '>' + option.label + '</option>';
            });
            timezoneSelect += '</select>';
            var html = '<div class="la-section-header"><h3>Daily Digest</h3>' +
                '<p class="la-muted">Send one daily email with due follow-ups.</p></div>' +
                '<form class="la-form la-notification-form">' +
                '<div><label>Time of day <input type="time" name="time" value="' + time + '" required></label></div>' +
                '<div><label>Timezone ' + timezoneSelect + '</label>' +
                '<p class="la-muted">Select your local timezone to schedule the digest.</p></div>' +
                '<button type="submit" class="la-btn">Save</button>' +
                '<div class="la-message"></div>' +
                '</form>';
            $el.html(html);

            $el.find('form').on('submit', function (e) {
                e.preventDefault();
                var data = {};
                $(this).serializeArray().forEach(function (item) {
                    data[item.name] = item.value;
                });
                apiRequest('POST', 'notifications/settings', data).done(function () {
                    $el.find('.la-message').text('Saved.');
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to save.';
                    $el.find('.la-message').text(message);
                });
            });
        }).fail(function () {
            $el.html('<p>Unable to load notifications.</p>');
        });
    }

    function renderCustomFields($el) {
        $el.html('<p>Loading custom fields...</p>');
        apiRequest('GET', 'custom-fields').done(function (response) {
            var fields = response && response.fields ? response.fields : {};
            var followup = response && response.followup ? response.followup : {};
            var html = '<div class="la-section-header"><h3>Custom Fields</h3>' +
                '<p class="la-muted">Rename custom fields and choose which appear in follow-up.</p></div>' +
                '<form class="la-form la-custom-fields-form">';
            for (var i = 1; i <= 10; i += 1) {
                var key = 'custom_' + i;
                var label = fields[key] || ('Custom Field ' + i);
                var checked = followup[key] ? ' checked' : '';
                html += '<div class="la-custom-field-row">' +
                    '<label class="la-custom-field-label">Field ' + i + ' <input type="text" name="' + key + '" value="' + label + '"></label>' +
                    '<label class="la-switch la-custom-field-toggle">' +
                    '<span class="la-switch-label">Show in follow-up</span>' +
                    '<span class="la-switch-control">' +
                    '<input type="checkbox" class="la-custom-followup-toggle" data-key="' + key + '" role="switch" aria-checked="' + (followup[key] ? 'true' : 'false') + '"' + checked + '>' +
                    '<span class="la-switch-track"><span class="la-switch-thumb"></span></span>' +
                    '</span>' +
                    '</label>' +
                    '</div>';
            }
            html += '<button type="submit" class="la-btn">Save</button>' +
                '<div class="la-message"></div>' +
                '</form>';
            $el.html(html);

            $el.find('.la-custom-fields-form').on('submit', function (e) {
                e.preventDefault();
                var payload = { fields: {}, followup: {} };
                $(this).serializeArray().forEach(function (item) {
                    payload.fields[item.name] = item.value;
                });
                $(this).find('.la-custom-followup-toggle').each(function () {
                    var key = $(this).data('key');
                    payload.followup[key] = $(this).is(':checked') ? 1 : 0;
                });
                apiRequest('POST', 'custom-fields', payload).done(function () {
                    $el.find('.la-message').text('Saved.');
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to save.';
                    $el.find('.la-message').text(message);
                });
            });
            $el.find('.la-custom-followup-toggle').on('change', function () {
                $(this).attr('aria-checked', $(this).is(':checked') ? 'true' : 'false');
            });
        }).fail(function () {
            $el.html('<p>Unable to load custom fields.</p>');
        });
    }

    function renderTeam($el) {
        $el.html('<p>Loading team...</p>');
        apiRequest('GET', 'team').done(function (response) {
            var users = response && response.users ? response.users : [];
            var html = '<div class="la-section-header"><h3>Team</h3>' +
                '<p class="la-muted">Create sub-accounts that share the same leads.</p></div>' +
                '<form class="la-form la-team-form">' +
                '<div><label>Name <input type="text" name="name" placeholder="Jane Smith"></label></div>' +
                '<div><label>Email <input type="email" name="email" placeholder="jane@example.com" required></label></div>' +
                '<div><label>Access <select name="access_level"><option value="full">Full access</option><option value="read">Read-only</option></select></label></div>' +
                '<button type="submit" class="la-btn">' + icons.plus + 'Add Team Member</button>' +
                '<div class="la-message"></div>' +
                '</form>';

            html += '<div class="la-team-list">';
            if (!users.length) {
                html += '<p class="la-muted">No team members yet.</p>';
            } else {
                html += '<table class="la-table"><thead><tr><th>Name</th><th>Email</th><th>Access</th><th>Active</th><th>Actions</th></tr></thead><tbody>';
                users.forEach(function (user) {
                    var access = user.access_level === 'read' ? 'read' : 'full';
                    var activeChecked = user.access_enabled ? ' checked' : '';
                    html += '<tr data-id="' + user.id + '">' +
                        '<td>' + (user.name || '') + '</td>' +
                        '<td>' + (user.email || '') + '</td>' +
                        '<td><select class="la-team-access"><option value="full"' + (access === 'full' ? ' selected' : '') + '>Full</option><option value="read"' + (access === 'read' ? ' selected' : '') + '>Read-only</option></select></td>' +
                        '<td><label class="la-toggle"><input type="checkbox" class="la-team-active"' + activeChecked + '><span>Active</span></label></td>' +
                        '<td><button type="button" class="la-btn la-btn--ghost la-team-reset" title="Reset password">' +
                        '<svg class="la-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />' +
                        '</svg>Reset Password</button></td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
            }
            html += '</div>';
            $el.html(html);

            $el.find('.la-team-form').on('submit', function (e) {
                e.preventDefault();
                var data = {};
                $(this).serializeArray().forEach(function (item) {
                    data[item.name] = item.value;
                });
                apiRequest('POST', 'team', data).done(function (response) {
                    if (response && response.password) {
                        $el.find('.la-message').text('Created. Temp password: ' + response.password);
                    } else {
                        $el.find('.la-message').text('Created.');
                    }
                    renderTeam($el);
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to create user.';
                    $el.find('.la-message').text(message);
                });
            });

            $el.find('.la-team-access').on('change', function () {
                var row = $(this).closest('tr');
                var id = row.data('id');
                var level = $(this).val();
                apiRequest('PUT', 'team/' + id, { access_level: level });
            });

            $el.find('.la-team-active').on('change', function () {
                var row = $(this).closest('tr');
                var id = row.data('id');
                var enabled = $(this).is(':checked') ? 1 : 0;
                apiRequest('PUT', 'team/' + id, { access_enabled: enabled });
            });

            $el.find('.la-team-reset').on('click', function () {
                var row = $(this).closest('tr');
                var id = row.data('id');
                apiRequest('PUT', 'team/' + id, { reset_password: 1 }).done(function (response) {
                    if (response && response.password) {
                        alert('Temporary password: ' + response.password);
                    }
                });
            });
        }).fail(function () {
            $el.html('<p>Unable to load team.</p>');
        });
    }

    function renderAIHelp($el) {
        $el.html('<p>Loading AI tools...</p>');
        $.when(
            apiRequest('GET', 'leads'),
            apiRequest('GET', 'business-profile')
        ).done(function (leadsRes, profileRes) {
            var leads = leadsRes[0] || [];
            var profile = profileRes[0] || {};

            var now = new Date();
            var dueCount = 0;
            var followedCount = 0;
            var sourceCounts = {};
            var sourceWon = {};
            var stageCounts = {};
            var scoredLeads = [];
            var stalledLeads = [];

            function parseDate(value) {
                if (!value) {
                    return null;
                }
                var date = new Date(String(value).replace(' ', 'T'));
                return isNaN(date.getTime()) ? null : date;
            }

            function leadDisplayName(lead) {
                var name = ((lead.first_name || '') + ' ' + (lead.last_name || '')).trim();
                return name || lead.email || ('Lead #' + lead.id);
            }

            function leadScore(lead) {
                var score = 0;
                var status = (lead.status || '').toLowerCase();
                if (status === 'won') {
                    score += 100;
                } else if (status === 'proposal' || status === 'qualified') {
                    score += 70;
                } else if (status === 'contacted') {
                    score += 50;
                } else if (status === 'lost') {
                    score += 10;
                } else {
                    score += 40;
                }

                var updated = parseDate(lead.updated_at) || parseDate(lead.created_at);
                if (updated) {
                    var days = Math.floor((now - updated) / 86400000);
                    if (days <= 3) {
                        score += 20;
                    } else if (days <= 7) {
                        score += 10;
                    } else if (days > 14) {
                        score -= 10;
                    }
                }

                var followup = parseDate(lead.followup_at);
                var due = parseDate(lead.due_at);
                if (followup && followup <= now) {
                    score += 15;
                }
                if (due && due <= now) {
                    score += 10;
                }

                return score;
            }

            leads.forEach(function (lead) {
                if (lead.followup_at) {
                    var followupDate = parseDate(lead.followup_at);
                    if (followupDate && followupDate <= now) {
                        followedCount += 1;
                    }
                }
                if (lead.due_at) {
                    var dueDate = parseDate(lead.due_at);
                    if (dueDate && dueDate <= now) {
                        dueCount += 1;
                    }
                }

                var sourceKey = lead.source || 'manual';
                sourceCounts[sourceKey] = (sourceCounts[sourceKey] || 0) + 1;
                if ((lead.status || '').toLowerCase() === 'won') {
                    sourceWon[sourceKey] = (sourceWon[sourceKey] || 0) + 1;
                }

                var stageLabel = pipelineStageLabel(lead.status);
                stageCounts[stageLabel] = (stageCounts[stageLabel] || 0) + 1;

                scoredLeads.push({
                    id: lead.id,
                    name: leadDisplayName(lead),
                    score: leadScore(lead)
                });

                var updated = parseDate(lead.updated_at) || parseDate(lead.created_at);
                if (updated) {
                    var daysSince = Math.floor((now - updated) / 86400000);
                    var status = (lead.status || '').toLowerCase();
                    if (daysSince >= 14 && status !== 'won' && status !== 'lost') {
                        stalledLeads.push({
                            id: lead.id,
                            name: leadDisplayName(lead),
                            days: daysSince
                        });
                    }
                }
            });

            function renderCountList(counts, emptyLabel) {
                var keys = Object.keys(counts);
                if (!keys.length) {
                    return '<p class="la-muted">' + emptyLabel + '</p>';
                }
                keys.sort(function (a, b) {
                    return counts[b] - counts[a];
                });
                var html = '<ul class="la-ai-list">';
                keys.forEach(function (key) {
                    html += '<li><span>' + key + '</span><strong>' + counts[key] + '</strong></li>';
                });
                html += '</ul>';
                return html;
            }

            function renderScoredLeads(list) {
                if (!list.length) {
                    return '<p class="la-muted">No leads scored yet.</p>';
                }
                list.sort(function (a, b) {
                    return b.score - a.score;
                });
                var html = '<ul class="la-ai-list">';
                list.slice(0, 5).forEach(function (item) {
                    html += '<li><a href="/dashboard/?lead_id=' + item.id + '" class="la-link la-ai-lead-link">' + item.name + '</a><strong>' + item.score + '</strong></li>';
                });
                html += '</ul>';
                return html;
            }

            function renderStalledLeads(list) {
                if (!list.length) {
                    return '<p class="la-muted">No stalled leads found.</p>';
                }
                list.sort(function (a, b) {
                    return b.days - a.days;
                });
                var html = '<ul class="la-ai-list">';
                list.slice(0, 5).forEach(function (item) {
                    html += '<li><span>' + item.name + '</span><strong>' + item.days + 'd</strong></li>';
                });
                html += '</ul>';
                return html;
            }

            function renderSourceConversion() {
                var keys = Object.keys(sourceCounts);
                if (!keys.length) {
                    return '<p class="la-muted">No lead sources yet.</p>';
                }
                keys.sort(function (a, b) {
                    var rateA = (sourceWon[a] || 0) / sourceCounts[a];
                    var rateB = (sourceWon[b] || 0) / sourceCounts[b];
                    return rateB - rateA;
                });
                var html = '<ul class="la-ai-list">';
                keys.slice(0, 5).forEach(function (key) {
                    var total = sourceCounts[key] || 0;
                    var won = sourceWon[key] || 0;
                    var rate = total ? Math.round((won / total) * 100) : 0;
                    html += '<li><span>' + key + '</span><strong>' + rate + '%</strong></li>';
                });
                html += '</ul>';
                return html;
            }

            var businessName = profile.business_name || 'your business';
            var goal = profile.monthly_leads_goal ? 'Your monthly lead goal: ' + profile.monthly_leads_goal + '.' : '';

            var html = '<div class="la-ai-grid">';
            html += '<section class="la-ai-card">' +
                '<h3>Lead Insights</h3>' +
                '<p class="la-muted">Snapshot of what needs attention right now.</p>' +
                '<div class="la-ai-stat"><strong>' + leads.length + '</strong> total leads</div>' +
                '<div class="la-ai-stat"><strong>' + followedCount + '</strong> followed-up</div>' +
                '<div class="la-ai-stat"><strong>' + dueCount + '</strong> follow-ups due</div>' +
                (goal ? '<div class="la-ai-stat">' + goal + '</div>' : '') +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>Top Sources</h3>' +
                renderCountList(sourceCounts, 'No lead sources yet.') +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>Pipeline Breakdown</h3>' +
                renderCountList(stageCounts, 'No stages yet.') +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>Hot Leads</h3>' +
                '<p class="la-muted">Top leads based on status, recency, and follow-ups.</p>' +
                renderScoredLeads(scoredLeads) +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>Stalled Leads</h3>' +
                '<p class="la-muted">No activity in 14+ days (not won/lost).</p>' +
                renderStalledLeads(stalledLeads) +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>Source Conversion</h3>' +
                '<p class="la-muted">Won rate by source (top 5).</p>' +
                renderSourceConversion() +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>AI Follow-up Draft</h3>' +
                '<p class="la-muted">Generate a draft for a selected lead. (AI coming next.)</p>' +
                '<div class="la-ai-draft">' +
                    '<select class="la-ai-lead-select"><option value="">Select a lead</option>' +
                    leads.map(function (lead) {
                        return '<option value="' + lead.id + '">' + leadDisplayName(lead) + '</option>';
                    }).join('') +
                    '</select>' +
                    '<div class="la-ai-draft-actions">' +
                        '<button type="button" class="la-btn la-ai-generate">Generate Draft</button>' +
                        '<button type="button" class="la-btn la-btn--ghost la-ai-copy">Copy Draft</button>' +
                    '</div>' +
                    '<textarea class="la-ai-output" rows="6" placeholder="Draft will appear here..."></textarea>' +
                '</div>' +
                '</section>';

            html += '<section class="la-ai-card">' +
                '<h3>Growth Ideas</h3>' +
                '<p class="la-muted">Based on ' + businessName + ', here are starter ideas you can try today.</p>' +
                '<ul class="la-ai-bullets">' +
                    '<li>Create a simple lead magnet and promote it in your top channel.</li>' +
                    '<li>Add a follow-up sequence for leads that go quiet after 7 days.</li>' +
                    '<li>Ask recent leads for referrals or testimonials.</li>' +
                '</ul>' +
                '</section>';
            html += '</div>';

            $el.html(html);

            $el.find('.la-ai-generate').on('click', function () {
                var leadId = $el.find('.la-ai-lead-select').val();
                if (!leadId) {
                    $el.find('.la-ai-output').val('Select a lead to generate a draft.');
                    return;
                }
                var lead = leads.find(function (item) { return String(item.id) === String(leadId); });
                var name = lead ? (((lead.first_name || '') + ' ' + (lead.last_name || '')).trim() || lead.email || 'there') : 'there';
                var output = 'Hi ' + name + ',\n\n' +
                    'Thanks for reaching out to ' + businessName + '! I wanted to follow up and see how we can help. ' +
                    'If you have any questions or want to move forward, just reply here and I can help.\n\n' +
                    'Best,\n' +
                    (profile.business_name || 'Lead Aggregator');
                $el.find('.la-ai-output').val(output);
            });

            $el.find('.la-ai-copy').on('click', function () {
                var text = $el.find('.la-ai-output').val();
                if (!text) {
                    return;
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text);
                } else {
                    $el.find('.la-ai-output').select();
                    document.execCommand('copy');
                }
            });
        }).fail(function () {
            $el.html('<p>Unable to load AI tools.</p>');
        });
    }

    function formatPlanLimit(limit) {
        if (!limit || limit <= 0) {
            return 'Unlimited contacts';
        }
        return 'Up to ' + limit + ' contacts';
    }

    function buildPlanGrid(plans) {
        var html = '<div class="la-plan-grid">';
        plans.forEach(function (plan) {
            html += '<div class="la-plan-card" data-plan="' + plan.key + '">' +
                '<h4>' + plan.label + '</h4>' +
                '<p class="la-muted">' + formatPlanLimit(plan.limit) + '</p>' +
                '<div class="la-plan-actions">' +
                    '<button type="button" class="la-btn la-plan-select" data-interval="monthly" data-plan="' + plan.key + '">Monthly</button>' +
                    '<button type="button" class="la-btn la-btn--ghost la-plan-select" data-interval="annual" data-plan="' + plan.key + '">Annual</button>' +
                '</div>' +
            '</div>';
        });
        html += '</div>';
        return html;
    }

    function attachCheckoutHandlers($el, getPayload) {
        $el.find('.la-plan-select').on('click', function () {
            var planKey = $(this).data('plan');
            var interval = $(this).data('interval');
            var payload = getPayload ? getPayload(planKey, interval) : { plan_key: planKey, interval: interval };
            if (!payload) {
                return;
            }
            $el.find('.la-message').remove();
            $el.append('<div class="la-message">Redirecting to checkout...</div>');
            apiRequest('POST', 'billing/checkout', payload).done(function (response) {
                if (response && response.checkout_url) {
                    window.location.href = response.checkout_url;
                } else {
                    $el.find('.la-message').text('Unable to start checkout.');
                }
            }).fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to start checkout.';
                $el.find('.la-message').text(message);
            });
        });
    }

    function renderBilling($el) {
        $el.html('<p>Loading billing...</p>');
        apiRequest('GET', 'billing/status').done(function (status) {
            var html = '<div class="la-section-header"><h3>Billing</h3></div>';
            var planLabel = status.plan_label || 'Plan';
            var limitLabel = formatPlanLimit(status.plan_limit);
            if (status.status === 'active') {
                html += '<div class="la-billing-summary">' +
                    '<strong>Active:</strong> ' + planLabel + ' · ' + limitLabel +
                    '</div>' +
                    '<div class="la-billing-meta">Leads used: ' + status.lead_count + '</div>' +
                    '<button type="button" class="la-btn la-billing-portal">Manage subscription</button>';
                $el.html(html);
                $el.find('.la-billing-portal').on('click', function () {
                    apiRequest('POST', 'billing/portal').done(function (response) {
                        if (response && response.portal_url) {
                            window.location.href = response.portal_url;
                        }
                    });
                });
            } else {
                html += '<p class="la-muted">Subscription inactive. Choose a plan to activate your account.</p>';
                html += buildPlanGrid(status.plans || []);
                $el.html(html);
                attachCheckoutHandlers($el);
            }
        }).fail(function () {
            $el.html('<p>Unable to load billing.</p>');
        });
    }

    function renderPricing($el) {
        $el.html('<p>Loading plans...</p>');
        apiRequest('GET', 'billing/status').done(function () {
            renderBilling($el);
        }).fail(function () {
            apiRequest('GET', 'billing/plans').done(function (response) {
                var plans = response && response.plans ? response.plans : [];
                var html = '<div class="la-section-header"><h3>Choose Your Plan</h3>' +
                    '<p class="la-muted">Pay first to activate your account.</p></div>' +
                    '<form class="la-form la-billing-signup">' +
                        '<div><label>Email <input type="email" name="email" required></label></div>' +
                        '<div><label>Password <input type="password" name="password" required></label></div>' +
                        '<div><label>Username (optional) <input type="text" name="username"></label></div>' +
                    '</form>' +
                    buildPlanGrid(plans);
                $el.html(html);
                attachCheckoutHandlers($el, function (planKey, interval) {
                    var email = $el.find('input[name="email"]').val();
                    var password = $el.find('input[name="password"]').val();
                    var username = $el.find('input[name="username"]').val();
                    if (!email || !password) {
                        $el.find('.la-message').remove();
                        $el.append('<div class="la-message">Email and password are required.</div>');
                        return null;
                    }
                    return {
                        plan_key: planKey,
                        interval: interval,
                        email: email,
                        password: password,
                        username: username
                    };
                });
            }).fail(function () {
                $el.html('<p>Unable to load plans.</p>');
            });
        });
    }

    function renderWebhooks($el) {
        $el.html('<p>Loading webhooks...</p>');
        apiRequest('GET', 'webhook-sources').done(function (sources) {
            var html = '<div class="la-section-header"><h3>Webhooks</h3><p class="la-muted">Use this endpoint to send leads from external tools.</p></div>';
            if (!sources || !sources.length) {
                html += '<form class="la-form la-webhook-form">' +
                    '<p class="la-muted">A shared secret is required to post leads. We will generate one for you.</p>' +
                    '<button type="submit" class="la-btn">' + icons.plus + 'Create Webhook</button>' +
                    '</form>';
            }
            html += '<div class="la-webhook-help">' +
                '<h4>Webhook Payload</h4>' +
                '<p class="la-muted">Send JSON. Include a source to label where the lead came from. If omitted, it defaults to "webhook". You must send the shared secret in the <strong>X-Lead-Aggregator-Secret</strong> header.</p>' +
                '<pre class="la-code-block">{' +
                '\n  "first_name": "Jamie",' +
                '\n  "last_name": "Smith",' +
                '\n  "email": "jamie@example.com",' +
                '\n  "phone": "555-123-4567",' +
                '\n  "company": "Acme Co.",' +
                '\n  "address_street": "123 Main St",' +
                '\n  "address_city": "Denver",' +
                '\n  "address_state": "CO",' +
                '\n  "address_zip": "80202",' +
                '\n  "address_country": "US",' +
                '\n  "followup_at": "2026-01-24 16:00:00",' +
                '\n  "due_at": "2026-01-31 09:30:00",' +
                '\n  "source": "shopify",' +
                '\n  "tags": [12, 15],' +
                '\n  "custom_fields": {' +
                '\n    "custom_1": "Value",' +
                '\n    "custom_2": "Value"' +
                '\n  }' +
                '\n}</pre>' +
                '</div>';

            if (sources && sources.length) {
                html += '<div class="la-webhook-list">';
                sources.forEach(function (source) {
                    var endpoint = leadAggregator.restUrl + 'webhooks/user/' + source.webhook_token;
                    html += '<div class="la-webhook-item">' +
                        '<div class="la-webhook-endpoint-block"><strong>Webhook</strong><div class="la-muted">Endpoint</div><code class="la-webhook-endpoint">' + endpoint + '</code></div>' +
                        '<div class="la-webhook-secret"><label>Shared Secret <input type="text" class="la-webhook-secret-input" data-id="' + source.id + '" value="' + (source.shared_secret || '') + '" readonly></label></div>' +
                        '<div class="la-webhook-actions">' +
                        '<button type="button" class="la-btn la-btn--ghost la-webhook-copy" data-endpoint="' + endpoint + '">Copy</button>' +
                        '<button type="button" class="la-btn la-btn--ghost la-webhook-refresh" data-id="' + source.id + '" title="Refresh secret">' + icons.refresh + '</button>' +
                        '<button type="button" class="la-btn la-btn--ghost la-webhook-delete" data-id="' + source.id + '" title="Delete">' + icons.trash + '</button>' +
                        '</div>' +
                        '</div>';
                });
                html += '</div>';
            } else {
                html += '<p class="la-muted">No webhooks created yet.</p>';
            }

            $el.html(html);

            $el.find('.la-webhook-form').on('submit', function (e) {
                e.preventDefault();
                var data = {};
                $(this).serializeArray().forEach(function (item) {
                    data[item.name] = item.value;
                });
                apiRequest('POST', 'webhook-sources', data).done(function () {
                    renderWebhooks($el);
                }).fail(function (xhr) {
                    var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to add webhook.';
                    $el.find('.la-webhook-form .la-message').remove();
                    $el.find('.la-webhook-form').append('<div class="la-message">' + message + '</div>');
                });
            });

            $el.find('.la-webhook-delete').on('click', function () {
                var id = $(this).data('id');
                apiRequest('DELETE', 'webhook-sources/' + id).done(function () {
                    renderWebhooks($el);
                });
            });

            $el.find('.la-webhook-refresh').on('click', function () {
                var id = $(this).data('id');
                apiRequest('PUT', 'webhook-sources/' + id, { regenerate: 1 }).done(function () {
                    renderWebhooks($el);
                });
            });

            $el.find('.la-webhook-save').on('click', function () {
                var id = $(this).data('id');
                apiRequest('PUT', 'webhook-sources/' + id, { regenerate: 1 }).done(function () {
                    renderWebhooks($el);
                });
            });

            $el.find('.la-webhook-copy').on('click', function () {
                var endpoint = $(this).data('endpoint');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(endpoint);
                }
            });
        });
    }

    function renderActivity($el) {
        if (!$el || !$el.length) {
            return;
        }
        var state = {
            view: 'reporting',
            range: 'last_30',
            start: '',
            end: '',
            actor: '',
            action: '',
            entity_type: '',
            search: '',
            page: 1,
            per_page: 25
        };
        var auditRows = [];
        var teamUsers = [];
        var actionLabels = {
            lead_created: 'Lead created',
            lead_updated: 'Lead updated',
            lead_deleted: 'Lead deleted',
            lead_bulk_deleted: 'Leads deleted',
            pipeline_stage_changed: 'Pipeline stage changed',
            followup_status_changed: 'Follow-up status changed',
            followup_rescheduled: 'Follow-up rescheduled',
            note_added: 'Note added',
            note_updated: 'Note updated',
            note_deleted: 'Note deleted',
            tag_created: 'Tag created',
            tag_updated: 'Tag updated',
            tag_deleted: 'Tag deleted',
            tag_added_to_lead: 'Tag added to lead',
            tag_removed_from_lead: 'Tag removed from lead',
            webhook_created: 'Webhook created',
            webhook_updated: 'Webhook updated',
            webhook_deleted: 'Webhook deleted',
            webhook_fired: 'Webhook fired',
            webhook_failed: 'Webhook failed',
            team_member_added: 'Team member added',
            team_role_changed: 'Team role changed',
            team_access_toggled: 'Team access toggled',
            team_password_reset: 'Team password reset',
            settings_changed: 'Settings changed',
            export_completed: 'Export completed'
        };

        var html = '<div class="la-section-header la-activity-header">' +
            '<div><h3>Activity</h3><p class="la-muted">Team audit log and reporting.</p></div>' +
            '</div>' +
            '<div class="la-activity-tabs">' +
            '<button type="button" class="la-activity-tab is-active" data-view="reporting">Reporting</button>' +
            '<button type="button" class="la-activity-tab" data-view="log">Audit Log</button>' +
            '</div>' +
            '<div class="la-activity-view la-activity-view--log"></div>' +
            '<div class="la-activity-view la-activity-view--reporting"></div>' +
            '<div class="la-activity-drawer" aria-hidden="true">' +
            '<div class="la-activity-drawer__panel">' +
            '<div class="la-activity-drawer__header"><h4>Details</h4><button type="button" class="la-activity-drawer__close">×</button></div>' +
            '<div class="la-activity-drawer__body"></div>' +
            '</div></div>';
        $el.html(html);

        function renderFilters(container) {
            var filters = '<div class="la-activity-filters">' +
                '<select class="la-activity-range">' +
                '<option value="last_7">Last 7 days</option>' +
                '<option value="last_30">Last 30 days</option>' +
                '<option value="last_90">Last 90 days</option>' +
                '<option value="custom">Custom</option>' +
                '</select>' +
                '<input type="date" class="la-activity-start" style="display:none;">' +
                '<input type="date" class="la-activity-end" style="display:none;">' +
                '<select class="la-activity-actor"><option value="">All team members</option></select>' +
                '<select class="la-activity-action"><option value="">All actions</option></select>' +
                '<select class="la-activity-entity"><option value="">All entities</option></select>' +
                '<input type="text" class="la-activity-search" placeholder="Search activity">' +
                '<button type="button" class="la-btn la-activity-export">Export CSV</button>' +
                '</div>';
            container.append(filters);

            var $actor = container.find('.la-activity-actor');
            teamUsers.forEach(function (user) {
                $actor.append('<option value="' + user.id + '">' + user.name + '</option>');
            });
            var $action = container.find('.la-activity-action');
            Object.keys(actionLabels).forEach(function (key) {
                $action.append('<option value="' + key + '">' + actionLabels[key] + '</option>');
            });
            var $entity = container.find('.la-activity-entity');
            ['lead', 'followup', 'note', 'tag', 'webhook', 'team_member', 'settings', 'export'].forEach(function (type) {
                $entity.append('<option value="' + type + '">' + type.replace('_', ' ') + '</option>');
            });
        }

        function loadTeamUsers() {
            return apiRequest('GET', 'team').done(function (response) {
                teamUsers = response && response.users ? response.users : [];
            }).fail(function () {
                teamUsers = [];
            });
        }

        function toggleCustomDates() {
            var show = state.range === 'custom';
            $el.find('.la-activity-start, .la-activity-end').toggle(show);
        }

        function renderAuditLog() {
            var $view = $el.find('.la-activity-view--log');
            $view.html('<div class="la-loading">Loading activity...</div>');
            var params = {
                range: state.range,
                start: state.start,
                end: state.end,
                actor: state.actor,
                action: state.action,
                entity_type: state.entity_type,
                search: state.search,
                page: state.page,
                per_page: state.per_page
            };
            apiRequest('GET', 'activity/log', params).done(function (data) {
                auditRows = data.rows || [];
                var html = '<div class="la-activity-panel">';
                html += '<div class="la-activity-panel__header"><h4>Audit Log</h4><span class="la-muted">Showing ' + (data.total || 0) + ' events</span></div>';
                html += '<div class="la-activity-panel__filters"></div>';
                html += '<div class="la-activity-table-wrap"><table class="la-table la-activity-table"><thead><tr><th>Time</th><th>Team member</th><th>Action</th><th>Item</th><th>Details</th></tr></thead><tbody>';
                if (!auditRows.length) {
                    html += '<tr><td colspan="5" class="la-empty">No activity found.</td></tr>';
                } else {
                    auditRows.forEach(function (row, idx) {
                        var member = row.actor_name || row.actor_email || 'Unknown';
                        var actionLabel = actionLabels[row.action] || row.action;
                        var item = row.entity_label || row.entity_type;
                        html += '<tr>' +
                            '<td>' + (row.created_at || '') + '</td>' +
                            '<td>' + member + '</td>' +
                            '<td>' + actionLabel + '</td>' +
                            '<td>' + item + '</td>' +
                            '<td><button type="button" class="la-btn la-btn--ghost la-activity-details" data-index="' + idx + '">View</button></td>' +
                            '</tr>';
                    });
                }
                html += '</tbody></table></div>';
                html += '<div class="la-activity-pagination">' +
                    '<div class="la-muted">Page ' + data.page + ' of ' + data.pages + '</div>' +
                    '<div class="la-activity-pagination__controls">' +
                    '<button type="button" class="la-btn la-btn--ghost la-activity-prev"' + (data.page <= 1 ? ' disabled' : '') + '>Previous</button>' +
                    '<button type="button" class="la-btn la-btn--ghost la-activity-next"' + (data.page >= data.pages ? ' disabled' : '') + '>Next</button>' +
                    '<select class="la-activity-per-page">' +
                    '<option value="25">25</option><option value="50">50</option><option value="100">100</option>' +
                    '</select>' +
                    '</div></div>';
                html += '</div>';
                $view.html(html);
                renderFilters($view.find('.la-activity-panel__filters'));
                $view.find('.la-activity-range').val(state.range);
                $view.find('.la-activity-start').val(state.start);
                $view.find('.la-activity-end').val(state.end);
                $view.find('.la-activity-actor').val(state.actor);
                $view.find('.la-activity-action').val(state.action);
                $view.find('.la-activity-entity').val(state.entity_type);
                $view.find('.la-activity-search').val(state.search);
                $view.find('.la-activity-per-page').val(String(state.per_page));
                toggleCustomDates();
            }).fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to load activity.';
                $view.html('<div class="la-empty">' + message + '</div>');
            });
        }

        function renderReporting() {
            var $view = $el.find('.la-activity-view--reporting');
            $view.html('<div class="la-loading">Loading reporting...</div>');
            var params = { range: state.range, start: state.start, end: state.end };
            apiRequest('GET', 'activity/reporting', params).done(function (data) {
                var summary = data.summary || {};
                var charts = data.charts || {};
                var html = '<div class="la-activity-panel">' +
                    '<div class="la-activity-panel__header"><h4>Reporting</h4></div>' +
                    '<div class="la-activity-panel__filters"></div>' +
                    '<div class="la-activity-summary">' +
                    '<div class="la-card"><h5>Leads created</h5><div class="la-stat-value">' + (summary.leads_created || 0) + '</div></div>' +
                    '<div class="la-card"><h5>Leads contacted</h5><div class="la-stat-value">' + (summary.leads_contacted || 0) + '</div></div>' +
                    '<div class="la-card"><h5>Follow-ups completed</h5><div class="la-stat-value">' + (summary.followups_completed || 0) + '</div></div>' +
                    '<div class="la-card"><h5>Avg time to first follow-up</h5><div class="la-stat-value">' + (summary.avg_time_to_first_followup || 0) + ' min</div></div>' +
                    '<div class="la-card"><h5>Win rate</h5><div class="la-stat-value">' + (summary.win_rate || 0) + '%</div></div>' +
                    '<div class="la-card"><h5>Active team members</h5><div class="la-stat-value">' + (summary.active_team_members || 0) + '</div></div>' +
                    '</div>' +
                    '<div class="la-activity-charts">' +
                    '<div class="la-card"><h5>Activity over time</h5><canvas id="la-activity-chart-events"></canvas></div>' +
                    '<div class="la-card"><h5>Activity by team member</h5><canvas id="la-activity-chart-members"></canvas></div>' +
                    '<div class="la-card"><h5>Lead stage changes</h5><canvas id="la-activity-chart-stages"></canvas></div>' +
                    '<div class="la-card"><h5>Follow-up completion rate</h5><canvas id="la-activity-chart-followups"></canvas></div>' +
                    '<div class="la-card"><h5>Top actions</h5><canvas id="la-activity-chart-actions"></canvas></div>' +
                    '</div>' +
                    '</div>';
                $view.html(html);
                renderFilters($view.find('.la-activity-panel__filters'));
                $view.find('.la-activity-range').val(state.range);
                $view.find('.la-activity-start').val(state.start);
                $view.find('.la-activity-end').val(state.end);
                toggleCustomDates();
                renderCharts(charts);
            }).fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to load reporting.';
                $view.html('<div class="la-empty">' + message + '</div>');
            });
        }

        function renderCharts(charts) {
            if (typeof Chart === 'undefined') {
                return;
            }
            var events = charts.events_over_time || [];
            var days = events.map(function (row) { return row.day; });
            var totals = events.map(function (row) { return row.total; });
            new Chart(document.getElementById('la-activity-chart-events'), {
                type: 'line',
                data: { labels: days, datasets: [{ label: 'Events', data: totals, borderColor: '#0257ab', backgroundColor: 'rgba(2, 87, 171, 0.2)', tension: 0.3 }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            var members = charts.by_member || [];
            new Chart(document.getElementById('la-activity-chart-members'), {
                type: 'bar',
                data: { labels: members.map(function (row) { return row.actor_name || row.actor_user_id; }), datasets: [{ label: 'Events', data: members.map(function (row) { return row.total; }), backgroundColor: '#13b1c4' }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            var stageData = charts.stage_changes || [];
            new Chart(document.getElementById('la-activity-chart-stages'), {
                type: 'bar',
                data: { labels: stageData.map(function (row) { return row.day; }), datasets: [{ label: 'Stage changes', data: stageData.map(function (row) { return Object.values(row.values || {}).reduce(function (sum, val) { return sum + val; }, 0); }), backgroundColor: '#0257ab' }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            var followupData = charts.followup_completion || [];
            new Chart(document.getElementById('la-activity-chart-followups'), {
                type: 'line',
                data: { labels: followupData.map(function (row) { return row.day; }), datasets: [{ label: 'Completion rate %', data: followupData.map(function (row) { return row.rate; }), borderColor: '#13b1c4', backgroundColor: 'rgba(19, 177, 196, 0.2)', tension: 0.3 }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            var topActions = charts.top_actions || [];
            new Chart(document.getElementById('la-activity-chart-actions'), {
                type: 'bar',
                data: { labels: topActions.map(function (row) { return actionLabels[row.action] || row.action; }), datasets: [{ label: 'Count', data: topActions.map(function (row) { return row.total; }), backgroundColor: '#0257ab' }] },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
            });
        }

        function refreshView() {
            if (state.view === 'log') {
                renderAuditLog();
            } else {
                renderReporting();
            }
        }

        $el.on('click', '.la-activity-tab', function () {
            var view = $(this).data('view');
            state.view = view;
            $el.find('.la-activity-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $el.find('.la-activity-view').hide();
            $el.find('.la-activity-view--' + view).show();
            refreshView();
        });

        $el.on('change', '.la-activity-range', function () {
            state.range = $(this).val();
            state.page = 1;
            toggleCustomDates();
            refreshView();
        });

        $el.on('change', '.la-activity-start', function () {
            state.start = $(this).val();
        });

        $el.on('change', '.la-activity-end', function () {
            state.end = $(this).val();
        });

        $el.on('change', '.la-activity-actor', function () {
            state.actor = $(this).val();
            state.page = 1;
            refreshView();
        });

        $el.on('change', '.la-activity-action', function () {
            state.action = $(this).val();
            state.page = 1;
            refreshView();
        });

        $el.on('change', '.la-activity-entity', function () {
            state.entity_type = $(this).val();
            state.page = 1;
            refreshView();
        });

        $el.on('input', '.la-activity-search', function () {
            state.search = $(this).val();
        });

        $el.on('keydown', '.la-activity-search', function (e) {
            if (e.key === 'Enter') {
                state.page = 1;
                refreshView();
            }
        });

        $el.on('click', '.la-activity-export', function () {
            var query = $.param({
                range: state.range,
                start: state.start,
                end: state.end,
                actor: state.actor,
                action: state.action,
                entity_type: state.entity_type,
                search: state.search
            });
            window.open(leadAggregator.restUrl + 'activity/export?' + query, '_blank');
        });

        $el.on('click', '.la-activity-prev', function () {
            if (state.page > 1) {
                state.page -= 1;
                renderAuditLog();
            }
        });

        $el.on('click', '.la-activity-next', function () {
            state.page += 1;
            renderAuditLog();
        });

        $el.on('change', '.la-activity-per-page', function () {
            state.per_page = parseInt($(this).val(), 10);
            state.page = 1;
            renderAuditLog();
        });

        $el.on('click', '.la-activity-details', function () {
            var idx = parseInt($(this).data('index'), 10);
            var row = auditRows[idx];
            if (!row) {
                return;
            }
            var $drawer = $el.find('.la-activity-drawer');
            var $body = $drawer.find('.la-activity-drawer__body');
            var meta = row.metadata ? JSON.stringify(row.metadata, null, 2) : 'No additional details.';
            $body.html('<pre>' + meta + '</pre>');
            $drawer.addClass('is-open').attr('aria-hidden', 'false');
        });

        $el.on('click', '.la-activity-drawer__close', function () {
            $el.find('.la-activity-drawer').removeClass('is-open').attr('aria-hidden', 'true');
        });

        loadTeamUsers().always(function () {
            refreshView();
        });
    }

    function renderGetStarted($el) {
        var html = '<div class="la-section-header"><h3>Get Started</h3><p class="la-muted">Quick start guide for your lead manager.</p></div>' +
            '<div class="la-card">' +
            '<h4>Core workflow</h4>' +
            '<ul class="la-ai-list">' +
            '<li><span><strong>Add your first lead</strong> using the Add Lead button in Overview.</span></li>' +
            '<li><span><strong>Import leads via webhooks</strong> in Settings → Webhooks.</span></li>' +
            '<li><span><strong>Manage follow-ups</strong> in the Follow-ups tab (Action button opens the follow-up modal).</span></li>' +
            '<li><span><strong>Use pipeline stages</strong> to keep deals moving (New → Won/Lost).</span></li>' +
            '<li><span><strong>Tags</strong> help you group leads and filter quickly.</span></li>' +
            '<li><span><strong>Export</strong> your data anytime from Settings → Export.</span></li>' +
            '</ul>' +
            '</div>' +
            '<div class="la-card">' +
            '<h4>Most important areas</h4>' +
            '<ul class="la-ai-list">' +
            '<li><span><strong>Overview</strong> — quick search, filters, and follow-up button.</span></li>' +
            '<li><span><strong>Leads</strong> — full lead detail and edits.</span></li>' +
            '<li><span><strong>Follow-ups</strong> — list of leads needing action + calendar view.</span></li>' +
            '<li><span><strong>AI Tools</strong> — insights, hot leads, and summaries.</span></li>' +
            '<li><span><strong>Settings</strong> — Webhooks, Notifications, Tags, Team, Custom Fields.</span></li>' +
            '</ul>' +
            '</div>' +
            '<div class="la-card">' +
            '<h4>Hide this tab</h4>' +
            '<p class="la-muted">Go to Settings → Get Started and toggle off “Show Get Started tab.” You can turn it back on anytime.</p>' +
            '</div>';
        $el.html(html);
    }

    function renderGetStartedSettings($el, onToggle) {
        $el.html('<p>Loading settings...</p>');
        apiRequest('GET', 'get-started/settings').done(function (settings) {
            settings = settings || {};
            var enabled = settings.enabled !== 0;
            var html = '<div class="la-section-header"><h3>Get Started</h3><p class="la-muted">Show or hide the Get Started tab.</p></div>' +
                '<form class="la-form la-get-started-settings">' +
                '<label class="la-switch">' +
                '<span class="la-switch-label">Show Get Started tab</span>' +
                '<span class="la-switch-control">' +
                '<input type="checkbox" name="enabled" role="switch" aria-checked="' + (enabled ? 'true' : 'false') + '"' + (enabled ? ' checked' : '') + '>' +
                '<span class="la-switch-track"><span class="la-switch-thumb"></span></span>' +
                '</span>' +
                '</label>' +
                '<div class="la-message"></div>' +
                '</form>';
            $el.html(html);
            if (onToggle) {
                onToggle(enabled);
            }
            $el.find('input[name="enabled"]').on('change', function () {
                var checked = $(this).is(':checked');
                $(this).attr('aria-checked', checked ? 'true' : 'false');
                apiRequest('POST', 'get-started/settings', { enabled: checked ? 1 : 0 }).done(function (response) {
                    var nextEnabled = response && typeof response.enabled !== 'undefined' ? !!response.enabled : checked;
                    if (onToggle) {
                        onToggle(nextEnabled);
                    }
                    $el.find('.la-message').text('Settings saved.');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to save settings.');
                });
            });
        }).fail(function () {
            $el.html('<p>Unable to load settings.</p>');
        });
    }

    function renderAppearanceSettings($el) {
        $el.html('<p>Loading appearance...</p>');
        apiRequest('GET', 'appearance/settings').done(function (settings) {
            settings = settings || {};
            var enabled = settings.dark_mode === 1;
            var html = '<div class="la-section-header"><h3>Appearance</h3><p class="la-muted">Toggle dark mode for the dashboard.</p></div>' +
                '<form class="la-form la-appearance-settings">' +
                '<label class="la-switch">' +
                '<span class="la-switch-label">Dark mode</span>' +
                '<span class="la-switch-control">' +
                '<input type="checkbox" name="dark_mode" role="switch" aria-checked="' + (enabled ? 'true' : 'false') + '"' + (enabled ? ' checked' : '') + '>' +
                '<span class="la-switch-track"><span class="la-switch-thumb"></span></span>' +
                '</span>' +
                '</label>' +
                '<div class="la-message"></div>' +
                '</form>';
            $el.html(html);
            $el.find('input[name="dark_mode"]').on('change', function () {
                var isOn = $(this).is(':checked');
                $(this).attr('aria-checked', isOn ? 'true' : 'false');
                $('body').toggleClass('dark-mode', isOn);
                apiRequest('POST', 'appearance/settings', { dark_mode: isOn ? 1 : 0 }).done(function () {
                    $el.find('.la-message').text('Appearance saved.');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to save appearance.');
                });
            });
        }).fail(function () {
            $el.html('<p>Unable to load appearance settings.</p>');
        });
    }

    function initSettingsAccordion($panel) {
        if (!$panel || !$panel.length) {
            return;
        }
        var $grid = $panel.find('.la-settings-grid');
        if (!$grid.length || $grid.data('accordion-ready')) {
            return;
        }
        $grid.addClass('is-accordion');
        var wrappedCount = 0;
        $grid.find('.la-settings-card').each(function (idx) {
            var $card = $(this);
            if ($card.find('> .la-accordion-toggle').length) {
                return;
            }
            var $header = $card.find('> .la-section-header').first();
            if (!$header.length) {
                return;
            }
            var titleText = $.trim($header.find('h3').first().text() || $header.text() || 'Settings');
            var subtitleText = $.trim($header.find('p').first().text());
            var $body = $('<div class="la-accordion-body"></div>');
            if (subtitleText) {
                $body.append('<p class="la-accordion-subtitle">' + subtitleText + '</p>');
            }
            $body.append($card.children().not($header));
            var $toggle = $('<button type="button" class="la-accordion-toggle" aria-expanded="false"></button>');
            $toggle.append('<span class="la-accordion-title">' + titleText + '</span>');
            $toggle.append('<span class="la-accordion-icon">+</span>');
            $card.empty().append($toggle).append($body);
            if (idx === 0) {
                $card.addClass('is-open');
                $toggle.attr('aria-expanded', 'true');
            }
            wrappedCount += 1;
        });
        if (wrappedCount > 0) {
            $grid.data('accordion-ready', true);
        } else if (!$grid.data('accordion-observer') && typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function () {
                if ($grid.find('> .la-settings-card > .la-section-header').length) {
                    observer.disconnect();
                    $grid.data('accordion-observer', null);
                    $grid.data('accordion-ready', false);
                    initSettingsAccordion($panel);
                }
            });
            observer.observe($grid[0], { childList: true, subtree: true });
            $grid.data('accordion-observer', observer);
        }

        $grid.off('click', '.la-accordion-toggle').on('click', '.la-accordion-toggle', function () {
            var $card = $(this).closest('.la-settings-card');
            var isOpen = $card.hasClass('is-open');
            $grid.find('.la-settings-card.is-open')
                .removeClass('is-open')
                .find('.la-accordion-toggle')
                .attr('aria-expanded', 'false');
            if (!isOpen) {
                $card.addClass('is-open');
                $(this).attr('aria-expanded', 'true');
            }
        });
    }

    function initSettingsLayout($panel) {
        if (!$panel || !$panel.length) {
            return;
        }
        var $grid = $panel.find('.la-settings-grid');
        if (!$grid.length || $panel.find('.mml-settings').length) {
            return;
        }

        var $cards = $grid.find('.la-settings-card');
        if (!$cards.length) {
            return;
        }

        var labelMap = {
            'la-settings-get-started': 'Get Started',
            'la-settings-appearance': 'Appearance',
            'la-settings-tags': 'Tags',
            'la-settings-team': 'Team',
            'la-settings-notifications': 'Notifications',
            'la-settings-quick-action': 'Quick Action',
            'la-settings-webhooks': 'Webhooks',
            'la-settings-billing': 'Billing',
            'la-settings-business': 'Business Profile',
            'la-settings-custom-fields': 'Custom Fields',
            'la-settings-export': 'Export'
        };

        var groups = [
            { label: 'Getting Started', items: ['la-settings-get-started'] },
            { label: 'Workspace', items: ['la-settings-business', 'la-settings-appearance', 'la-settings-team'] },
            { label: 'CRM Defaults', items: ['la-settings-quick-action', 'la-settings-tags', 'la-settings-custom-fields'] },
            { label: 'Automation', items: ['la-settings-notifications', 'la-settings-webhooks'] },
            { label: 'Billing', items: ['la-settings-billing', 'la-settings-export'] }
        ];

        var $wrapper = $('<div class="mml-settings"></div>');
        var $header = $('<header class="mml-settings__header"><div><h3>Settings</h3><p>Manage your workspace settings and defaults.</p></div></header>');
        var $body = $('<div class="mml-settings__body"></div>');
        var $nav = $('<aside class="mml-settings__nav" aria-label="Settings"></aside>');
        var $content = $('<main class="mml-settings__content"></main>');
        var $mobileSelect = $('<select class="mml-settings__select" aria-label="Select settings section"></select>');
        var $navList = $('<div class="mml-settings__nav-list" role="tablist"></div>');

        var panels = [];
        $cards.each(function (idx) {
            var $card = $(this);
            var cardId = $card.attr('id') || '';
            var title = labelMap[cardId] || '';
            var key = cardId ? cardId.replace('la-settings-', '') : '';
            if (!title) {
                var $headerEl = $card.find('> .la-section-header h3').first();
                title = $.trim($headerEl.text() || 'Settings');
            }
            if (!key) {
                key = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
            if (!key) {
                key = 'settings-' + idx;
            }
            $card.attr('data-settings-key', key);
            var $panelWrap = $('<section class="mml-settings__panel" data-settings-panel="' + key + '" role="tabpanel"></section>');
            $panelWrap.append($card);
            $content.append($panelWrap);
            panels.push({ key: key, id: cardId, title: title });
            $mobileSelect.append('<option value="' + key + '">' + title + '</option>');
        });

        groups.forEach(function (group) {
            var groupItems = group.items.filter(function (id) {
                return panels.some(function (panel) { return panel.id === id; });
            });
            if (!groupItems.length) {
                return;
            }
            $navList.append('<div class="mml-settings__nav-title">' + group.label + '</div>');
            groupItems.forEach(function (id) {
                var panel = panels.find(function (p) { return p.id === id; });
                if (!panel) {
                    return;
                }
                var $btn = $('<button type="button" class="mml-settings__nav-item" role="tab" aria-selected="false"></button>');
                $btn.attr('data-target', panel.key).text(panel.title);
                $navList.append($btn);
            });
        });

        $nav.append($mobileSelect).append($navList);
        $body.append($nav).append($content);
        $wrapper.append($header).append($body);
        $grid.before($wrapper);
        $grid.remove();

        function showSection(key) {
            $content.find('.mml-settings__panel').removeClass('is-active');
            $navList.find('.mml-settings__nav-item').removeClass('is-active').attr('aria-current', 'false').attr('aria-selected', 'false');
            var $targetPanel = $content.find('[data-settings-panel="' + key + '"]');
            if (!$targetPanel.length) {
                $targetPanel = $content.find('.mml-settings__panel').first();
                key = $targetPanel.data('settings-panel');
            }
            $targetPanel.addClass('is-active');
            $navList.find('.mml-settings__nav-item[data-target="' + key + '"]').addClass('is-active').attr('aria-current', 'page').attr('aria-selected', 'true');
            $mobileSelect.val(key);
            try {
                window.localStorage.setItem('leadAggregatorSettingsSection', key);
            } catch (err) {}
        }

        $navList.on('click', '.mml-settings__nav-item', function () {
            showSection($(this).data('target'));
        });

        $mobileSelect.on('change', function () {
            showSection($(this).val());
        });

        var stored = '';
        var fromQuery = '';
        try {
            var searchParams = new URLSearchParams(window.location.search);
            fromQuery = searchParams.get('settings') || '';
            stored = window.localStorage.getItem('leadAggregatorSettingsSection') || '';
        } catch (err) {}
        showSection(fromQuery || stored);
    }

    function renderQuickActionSettings($el) {
        $el.html('<p>Loading quick action...</p>');
        apiRequest('GET', 'quick-action/settings').done(function (settings) {
            settings = settings || {};
            var status = settings.status || 'contacted';
            var followupStatus = settings.followup_status || 'scheduled';
            var statusSelect = buildSelect('status', status, statusOptions);
            var followupSelect = buildSelect('followup_status', followupStatus, followupOptions);
            var html = '<div class="la-section-header"><h3>Quick Action</h3><p class="la-muted">Set the default status updates for the Apply Quick Action button.</p></div>' +
                '<form class="la-form la-quick-action-settings">' +
                '<div><label>Pipeline Stage ' + statusSelect + '</label></div>' +
                '<div><label>Follow-up Status ' + followupSelect + '</label></div>' +
                '<button type="submit" class="la-btn">Save</button>' +
                '<div class="la-message"></div>' +
                '</form>';
            $el.html(html);
            $el.find('form').on('submit', function (e) {
                e.preventDefault();
                var payload = {
                    status: $(this).find('select[name="status"]').val(),
                    followup_status: $(this).find('select[name="followup_status"]').val()
                };
                apiRequest('POST', 'quick-action/settings', payload).done(function () {
                    $el.find('.la-message').text('Quick action saved.');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to save quick action.');
                });
            });
        }).fail(function () {
            $el.html('<p>Unable to load quick action settings.</p>');
        });
    }

    function renderDashboard($el) {
        document.documentElement.setAttribute('data-lead-aggregator-render', 'dashboard');
        if ($el.attr('data-la-rendered') === 'dashboard') {
            return;
        }
        $el.attr('data-la-rendered', 'dashboard');

        var leadId = new URLSearchParams(window.location.search).get('lead_id');
        var getStartedEnabled = String($el.data('get-started') || '1') !== '0';
        function updateGetStartedVisibility(enabled) {
            $el.attr('data-get-started', enabled ? '1' : '0');
            var $tab = $el.find('.la-tab[data-tab="get-started"]');
            var $panel = $el.find('#la-panel-get-started');
            if (enabled) {
                $tab.show();
                $panel.show();
                renderGetStarted($panel);
            } else {
                $tab.hide();
                $panel.hide().removeClass('is-active');
                if ($el.find('.la-tab.is-active').data('tab') === 'get-started') {
                    $el.find('.la-tab').removeClass('is-active');
                    $el.find('.la-tab-panel').removeClass('is-active');
                    $el.find('.la-tab[data-tab="overview"]').addClass('is-active');
                    $el.find('.la-tab-panel[data-tab="overview"]').addClass('is-active');
                }
            }
        }

        var statsEl = $el.find('.la-dashboard-stats');
        if (getStartedEnabled) {
            renderGetStarted($el.find('#la-panel-get-started'));
        }
        renderInbox($el.find('#la-panel-inbox'), { showFollowupAction: true, statsEl: statsEl });
        renderInbox($el.find('#la-panel-leads'));
        renderFollowupsList($el.find('#la-panel-followups'));
        renderCalendarOnly($el.find('#la-panel-calendar'));
        renderAIHelp($el.find('#la-panel-ai-tools'));
        renderActivity($el.find('#la-panel-activity'));
        renderTags($el.find('#la-settings-tags'));
        renderTeam($el.find('#la-settings-team'));
        renderWebhooks($el.find('#la-settings-webhooks'));
        renderBusinessProfile($el.find('#la-settings-business'));
        renderCustomFields($el.find('#la-settings-custom-fields'));
        renderExport($el.find('#la-settings-export'));
        renderBilling($el.find('#la-settings-billing'));
        renderNotificationSettings($el.find('#la-settings-notifications'));
        renderQuickActionSettings($el.find('#la-settings-quick-action'));
        renderGetStartedSettings($el.find('#la-settings-get-started'), updateGetStartedVisibility);
        renderAppearanceSettings($el.find('#la-settings-appearance'));
        initSettingsLayout($el.find('#la-panel-settings'));

        $el.find('#la-panel-notes-tags').html('<div class="la-section-header"><h3>Notes</h3><p class="la-muted">Notes are managed per lead in the Lead Detail tab.</p></div>');

        function refreshStats() {
            apiRequest('GET', 'leads').done(function (leads) {
                var total = leads.length;
                var followup = 0;
                var overdue = 0;
                var followed = 0;
                var now = new Date();

                leads.forEach(function (lead) {
                    if (lead.due_at) {
                        var dueDate = parseLeadDate(lead.due_at);
                        var followupStatus = String(lead.followup_status || '').toLowerCase();
                        if (dueDate) {
                            if (dueDate <= now && followupStatus !== 'completed' && followupStatus !== 'canceled') {
                                followup += 1;
                            }
                            if (dueDate < now && followupStatus !== 'completed' && followupStatus !== 'canceled') {
                                overdue += 1;
                            }
                        }
                    }
                    if (lead.last_actioned || lead.last_contacted || (normalizeFollowupStatusValue(lead.followup_status) === 'completed' && lead.followup_at)) {
                        var raw = lead.last_actioned || lead.last_contacted || lead.followup_at;
                        var actionDate = parseLeadDate(raw);
                        if (actionDate) {
                            if (actionDate.getFullYear() === now.getFullYear() &&
                                actionDate.getMonth() === now.getMonth() &&
                                actionDate.getDate() === now.getDate()) {
                                followed += 1;
                            }
                        }
                    }
                });

                $el.find('[data-stat="total"]').text(total);
                $el.find('[data-stat="followup"]').text(followup);
                $el.find('[data-stat="followed"]').text(followed);
                $el.find('[data-stat="overdue"]').text(overdue);
            });
        }

        var leadPanel = $el.find('#la-panel-leads');

        function openLeadDetail(selectedId) {
            renderLeadDetail(leadPanel, selectedId);
            $el.find('.la-tab').removeClass('is-active');
            $el.find('.la-tab-panel').removeClass('is-active');
            $el.find('.la-tab[data-tab="leads"]').addClass('is-active');
            $el.find('.la-tab-panel[data-tab="leads"]').addClass('is-active');
        }

        window.leadAggregatorOpenLeadDetail = function (selectedId, focusNote) {
            openLeadDetail(selectedId);
            if (focusNote) {
                setTimeout(function () {
                    $el.find('#la-panel-leads textarea[name="note"]').focus();
                }, 200);
            }
        };

        window.leadAggregatorOpenLeadTag = function (tagId, tagName) {
            renderInbox($el.find('#la-panel-leads'), { tagId: tagId, tagName: tagName });
            $el.find('.la-tab').removeClass('is-active');
            $el.find('.la-tab-panel').removeClass('is-active');
            $el.find('.la-tab[data-tab="leads"]').addClass('is-active');
            $el.find('.la-tab-panel[data-tab="leads"]').addClass('is-active');
        };

        if (leadId) {
            openLeadDetail(leadId);
        }

        refreshStats();

        statsEl.on('click', '.la-stat-button', function () {
            var filter = $(this).data('filter');
            var filters = { search: '', status: '', followup: '' };
            if (filter === 'followup') {
                filters.followup = 'due';
            } else if (filter === 'followed') {
                filters.followup = 'followed';
            } else if (filter === 'overdue') {
                filters.followup = 'overdue';
            }
            renderInbox($el.find('#la-panel-inbox'), { showFollowupAction: true, statsEl: statsEl, filters: filters });
        });

        $(document).off('leadAggregator:refresh').on('leadAggregator:refresh', function () {
            renderInbox($el.find('#la-panel-inbox'), { showFollowupAction: true, statsEl: statsEl });
            renderInbox($el.find('#la-panel-leads'));
            renderFollowupsList($el.find('#la-panel-followups'));
            renderCalendarOnly($el.find('#la-panel-calendar'));
            refreshStats();
        });

        $el.on('click', '.la-tab', function () {
            var tab = $(this).data('tab');
            $el.find('.la-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $el.find('.la-tab-panel').removeClass('is-active');
            $el.find('.la-tab-panel[data-tab="' + tab + '"]').addClass('is-active');
            clearLeadIdFromUrl();

            if (tab === 'leads') {
                renderInbox($el.find('#la-panel-leads'));
            }
            if (tab === 'ai-tools') {
                renderAIHelp($el.find('#la-panel-ai-tools'));
            }
            if (tab === 'calendar') {
                renderCalendarOnly($el.find('#la-panel-calendar'));
            }
            if (tab === 'activity') {
                renderActivity($el.find('#la-panel-activity'));
            }
            if (tab === 'get-started') {
                renderGetStarted($el.find('#la-panel-get-started'));
            }
        });

        window.leadAggregatorOpenTab = function (tab) {
            clearLeadIdFromUrl();
            $el.find('.la-tab[data-tab="' + tab + '"]').trigger('click');
        };

        $el.on('click', '.la-lead-link', function () {
            var selectedId = $(this).data('lead-id');
            openLeadDetail(selectedId);
            if (history.replaceState) {
                history.replaceState(null, '', '?lead_id=' + selectedId);
            }
        });
    }

    function openNoteModal(leadId) {
        var modal = $(
            '<div class="la-modal-overlay">' +
                '<div class="la-modal">' +
                    '<div class="la-modal-header">' +
                        '<h4>Add Follow-up Note</h4>' +
                        '<button type="button" class="la-modal-close">×</button>' +
                    '</div>' +
                    '<div class="la-modal-body">' +
                        '<textarea rows="4" placeholder="Add a note"></textarea>' +
                        '<div class="la-modal-actions">' +
                            '<button type="button" class="la-btn la-modal-cancel">Cancel</button>' +
                            '<button type="button" class="la-btn">Save Note</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append(modal);

        function close() {
            modal.remove();
        }

        modal.find('.la-modal-close, .la-modal-cancel').on('click', close);

        modal.on('click', function (event) {
            if ($(event.target).is('.la-modal-overlay')) {
                close();
            }
        });

        modal.find('.la-btn').last().on('click', function () {
            var note = modal.find('textarea').val();
            if (!note) {
                return;
            }
            apiRequest('POST', 'leads/' + leadId + '/notes', { note: note }).done(function () {
                close();
            });
        });
    }

    function initViews() {
        $('.lead-aggregator-view').each(function () {
            var $el = $(this);
            var view = $el.data('view');
            if (view === 'inbox') {
                renderInbox($el);
            } else if (view === 'lead-form') {
                renderLeadForm($el);
            } else if (view === 'lead-detail') {
                renderLeadDetail($el, $el.data('lead-id'));
            } else if (view === 'calendar') {
                renderCalendar($el);
            } else if (view === 'tags') {
                renderTags($el);
            } else if (view === 'export') {
                renderExport($el);
            } else if (view === 'business-profile') {
                renderBusinessProfile($el);
            } else if (view === 'dashboard') {
                renderDashboard($el);
            } else if (view === 'billing') {
                renderBilling($el);
            } else if (view === 'pricing') {
                renderPricing($el);
            } else if (view === 'notifications') {
                renderNotificationSettings($el);
            }
        });
    }

    function clearLeadIdFromUrl() {
        if (!history.replaceState) {
            return;
        }
        var url = new URL(window.location.href);
        if (!url.searchParams.has('lead_id')) {
            return;
        }
        url.searchParams.delete('lead_id');
        var next = url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '') + url.hash;
        history.replaceState(null, '', next);
    }

    initViews();

    // Poll for async builders replacing content.
    var attempts = 0;
    var interval = setInterval(function () {
        attempts += 1;
        var $dash = $('.lead-aggregator-view[data-view="dashboard"]');
        if ($dash.length) {
            renderDashboard($dash);
        }
        if (attempts > 8) {
            clearInterval(interval);
        }
    }, 400);
});
