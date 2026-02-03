/* global jQuery, leadAggregator */
window.leadAggregatorBuild = 'dashboard-tabs-1';
document.documentElement.setAttribute('data-lead-aggregator-build', window.leadAggregatorBuild);
console.log('Lead Aggregator frontend loaded', window.leadAggregatorBuild);
jQuery(function ($) {
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

    function renderInbox($el) {
        $el.html('<p>Loading leads...</p>');
        $.when(apiRequest('GET', 'leads'), apiRequest('GET', 'stages')).done(function (leadsRes, stagesRes) {
            var leads = leadsRes[0] || [];
            var stages = stagesRes[0] || [];
            var stageMap = {};
            stages.forEach(function (stage) {
                stageMap[stage.id] = stage.name;
            });

            var html = '<div class="la-section-header"><h3>Inbox</h3></div>';
            html += '<div class="la-toolbar"><input type="text" class="la-search" placeholder="Search leads"></div>';
            html += '<table class="la-table"><thead><tr><th>Name</th><th>Email</th><th>Stage</th><th>Status</th><th>Followup</th><th>Due</th></tr></thead><tbody>';

            if (leads.length === 0) {
                html += '<tr><td class="la-empty" colspan="6">No leads yet.</td></tr>';
            } else {
                leads.forEach(function (lead) {
                    var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                    html += '<tr>';
                    html += '<td><a href="?lead_id=' + lead.id + '">' + (name.trim() || 'Lead #' + lead.id) + '</a></td>';
                    html += '<td>' + (lead.email || '') + '</td>';
                    html += '<td>' + (stageMap[lead.stage_id] || '') + '</td>';
                    html += '<td>' + (lead.status || '') + '</td>';
                    html += '<td>' + (lead.followup_at || '') + '</td>';
                    html += '<td>' + (lead.due_at || '') + '</td>';
                    html += '</tr>';
                });
            }

            html += '</tbody></table>';
            $el.html(html);
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
            '<button type="submit" class="la-btn">Add Lead</button>' +
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

    function renderLeadDetail($el, leadId) {
        if (!leadId) {
            $el.html('<p>No lead selected.</p>');
            return;
        }

        $el.html('<p>Loading lead...</p>');
        $.when(apiRequest('GET', 'leads/' + leadId), apiRequest('GET', 'leads/' + leadId + '/notes')).done(function (leadRes, notesRes) {
            var lead = leadRes[0];
            var notes = notesRes[0] || [];
            var html = '<div class="la-section-header"><h3>Lead Details</h3></div>';
            html += '<div class="la-card"><p><strong>' + (lead.first_name || '') + ' ' + (lead.last_name || '') + '</strong></p>';
            html += '<p>Email: ' + (lead.email || '') + '</p>';
            html += '<p>Phone: ' + (lead.phone || '') + '</p></div>';

            html += '<form class="la-form la-update-form">' +
                '<div><label>Status <input type="text" name="status" value="' + (lead.status || '') + '"></label></div>' +
                '<div><label>Followup Date <input type="datetime-local" name="followup_at" value="' + (lead.followup_at ? lead.followup_at.replace(" ", "T") : '') + '"></label></div>' +
                '<div><label>Due Date <input type="datetime-local" name="due_at" value="' + (lead.due_at ? lead.due_at.replace(" ", "T") : '') + '"></label></div>' +
                '<button type="submit" class="la-btn">Update Lead</button>' +
                '<div class="la-message"></div>' +
                '</form>';

            html += '<div class="la-notes"><h4>Notes</h4><ul>';
            notes.forEach(function (note) {
                html += '<li>' + note.note + '</li>';
            });
            html += '</ul>';
            html += '<form class="la-form la-note-form">' +
                '<textarea name="note" rows="3" placeholder="Add a note"></textarea>' +
                '<button type="submit" class="la-btn">Add Note</button>' +
                '</form></div>';

            $el.html(html);

            $el.find('.la-update-form').on('submit', function (e) {
                e.preventDefault();
                var data = {};
                $(this).serializeArray().forEach(function (item) {
                    data[item.name] = item.value;
                });
                apiRequest('PUT', 'leads/' + leadId, data).done(function () {
                    $el.find('.la-message').text('Lead updated.');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to update lead.');
                });
            });

            $el.find('.la-note-form').on('submit', function (e) {
                e.preventDefault();
                var note = $(this).find('textarea[name="note"]').val();
                apiRequest('POST', 'leads/' + leadId + '/notes', { note: note }).done(function () {
                    renderLeadDetail($el, leadId);
                });
            });
        }).fail(function () {
            $el.html('<p>Unable to load lead.</p>');
        });
    }

    function renderStages($el) {
        $el.html('<p>Loading stages...</p>');
        apiRequest('GET', 'stages').done(function (stages) {
            var html = '<div class="la-section-header"><h3>Stages</h3></div>';
            html += '<form class="la-form la-stage-form"><input type="text" name="name" placeholder="Stage name" required><button type="submit" class="la-btn">Add Stage</button></form>';
            html += '<ul class="la-list">';
            stages.forEach(function (stage) {
                html += '<li><span>' + stage.name + '</span> <button type="button" data-id="' + stage.id + '" class="la-btn la-btn--ghost la-delete">Delete</button></li>';
            });
            html += '</ul>';
            $el.html(html);

            $el.find('.la-stage-form').on('submit', function (e) {
                e.preventDefault();
                var name = $(this).find('input[name="name"]').val();
                apiRequest('POST', 'stages', { name: name }).done(function () {
                    renderStages($el);
                });
            });

            $el.find('.la-delete').on('click', function () {
                apiRequest('DELETE', 'stages/' + $(this).data('id')).done(function () {
                    renderStages($el);
                });
            });
        });
    }

    function renderTags($el) {
        $el.html('<p>Loading tags...</p>');
        apiRequest('GET', 'tags').done(function (tags) {
            var html = '<div class="la-section-header"><h3>Tags</h3></div>';
            html += '<form class="la-form la-tag-form"><input type="text" name="name" placeholder="Tag name" required><button type="submit" class="la-btn">Add Tag</button></form>';
            html += '<ul class="la-list">';
            tags.forEach(function (tag) {
                html += '<li><span>' + tag.name + '</span> <button type="button" data-id="' + tag.id + '" class="la-btn la-btn--ghost la-delete">Delete</button></li>';
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
        });
    }

    function renderCalendar($el) {
        $el.html('<p>Loading followups...</p>');
        apiRequest('GET', 'leads').done(function (leads) {
            var html = '<div class="la-section-header"><h3>Calendar</h3></div>';
            html += '<table class="la-table"><thead><tr><th>Lead</th><th>Followup</th><th>Due</th></tr></thead><tbody>';
            leads.forEach(function (lead) {
                if (lead.followup_at || lead.due_at) {
                    var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
                    html += '<tr><td>' + (name.trim() || 'Lead #' + lead.id) + '</td><td>' + (lead.followup_at || '') + '</td><td>' + (lead.due_at || '') + '</td></tr>';
                }
            });
            html += '</tbody></table>';
            $el.html(html);
        }).fail(function () {
            $el.html('<p>Unable to load calendar.</p>');
        });
    }

    function renderExport($el) {
        var url = leadAggregator.restUrl + 'export?_wpnonce=' + encodeURIComponent(leadAggregator.nonce);
        $el.html('<div class="la-section-header"><h3>Export</h3></div><a class="la-btn" href="' + url + '">Download CSV</a>');
    }

    function renderBusinessProfile($el) {
        $el.html('<p>Loading profile...</p>');
        apiRequest('GET', 'business-profile').done(function (profile) {
            profile = profile || {};
            var html = '<div class="la-section-header"><h3>Business Profile</h3></div>' +
                '<form class="la-form la-profile-form">' +
                '<div><label>Business Name <input type="text" name="business_name" value="' + (profile.business_name || '') + '"></label></div>' +
                '<div><label>Industry <input type="text" name="industry" value="' + (profile.industry || '') + '"></label></div>' +
                '<div><label>Goals <textarea name="goals">' + (profile.goals || '') + '</textarea></label></div>' +
                '<div><label>Challenges <textarea name="challenges">' + (profile.challenges || '') + '</textarea></label></div>' +
                '<div><label>Notes <textarea name="notes">' + (profile.notes || '') + '</textarea></label></div>' +
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
                apiRequest('POST', 'business-profile', data).done(function () {
                    $el.find('.la-message').text('Saved.');
                }).fail(function () {
                    $el.find('.la-message').text('Unable to save.');
                });
            });
        });
    }

    function renderDashboard($el) {
        document.documentElement.setAttribute('data-lead-aggregator-render', 'dashboard');
        var html = '<div class="la-dashboard">' +
            '<div class="la-dashboard-header">' +
            '<div><h2>Lead Dashboard</h2><p>Manage leads, follow-ups, and pipeline stages.</p></div>' +
            '</div>' +
            '<div class="la-tabs">' +
            '<button type="button" class="la-tab" data-tab="overview">Overview</button>' +
            '<button type="button" class="la-tab" data-tab="leads">Leads</button>' +
            '<button type="button" class="la-tab" data-tab="followups">Follow-ups</button>' +
            '<button type="button" class="la-tab" data-tab="pipeline">Pipeline</button>' +
            '<button type="button" class="la-tab" data-tab="notes-tags">Notes & Tags</button>' +
            '<button type="button" class="la-tab" data-tab="business">Business Profile</button>' +
            '<button type="button" class="la-tab" data-tab="export">Export</button>' +
            '</div>' +
            '<div class="la-panel la-tab-panel" data-tab="overview">' +
            '<div class="la-dashboard-stats">' +
            '<div class="la-stat"><span class="la-stat-label">Total Leads</span><span class="la-stat-value" data-stat="total">0</span></div>' +
            '<div class="la-stat"><span class="la-stat-label">Followups Due</span><span class="la-stat-value" data-stat="followup">0</span></div>' +
            '<div class="la-stat"><span class="la-stat-label">Overdue</span><span class="la-stat-value" data-stat="overdue">0</span></div>' +
            '</div>' +
            '<div class="la-dashboard-overview">' +
            '<section class="la-panel la-panel--inbox"></section>' +
            '<section class="la-panel la-panel--form"></section>' +
            '</div>' +
            '</div>' +
            '<div class="la-panel la-tab-panel" data-tab="leads"></div>' +
            '<div class="la-panel la-tab-panel" data-tab="followups"></div>' +
            '<div class="la-panel la-tab-panel" data-tab="pipeline"></div>' +
            '<div class="la-panel la-tab-panel" data-tab="notes-tags"></div>' +
            '<div class="la-panel la-tab-panel" data-tab="business"></div>' +
            '<div class="la-panel la-tab-panel" data-tab="export"></div>' +
            '</div>';

        $el.html(html);

        var leadId = new URLSearchParams(window.location.search).get('lead_id');

        renderInbox($el.find('.la-panel--inbox'));
        renderLeadForm($el.find('.la-panel--form'));
        renderInbox($el.find('[data-tab="leads"]'));
        renderCalendar($el.find('[data-tab="followups"]'));
        renderStages($el.find('[data-tab="pipeline"]'));
        renderTags($el.find('[data-tab="notes-tags"]'));
        renderBusinessProfile($el.find('[data-tab="business"]'));
        renderExport($el.find('[data-tab="export"]'));

        $el.find('[data-tab="notes-tags"]').prepend('<div class="la-section-header"><h3>Notes & Tags</h3><p class="la-muted">Manage tags and organize lead notes. Select a lead to see notes.</p></div>');

        function refreshStats() {
            apiRequest('GET', 'leads').done(function (leads) {
                var total = leads.length;
                var followup = 0;
                var overdue = 0;
                var now = new Date();

                leads.forEach(function (lead) {
                    if (lead.followup_at) {
                        var followupDate = new Date(String(lead.followup_at).replace(' ', 'T'));
                        if (!isNaN(followupDate.getTime()) && followupDate <= now) {
                            followup += 1;
                        }
                    }
                    if (lead.due_at) {
                        var dueDate = new Date(String(lead.due_at).replace(' ', 'T'));
                        if (!isNaN(dueDate.getTime()) && dueDate < now) {
                            overdue += 1;
                        }
                    }
                });

                $el.find('[data-stat="total"]').text(total);
                $el.find('[data-stat="followup"]').text(followup);
                $el.find('[data-stat="overdue"]').text(overdue);
            });
        }

        if (leadId) {
            var detailPanel = $('<div class="la-panel la-tab-panel" data-tab="lead-detail"></div>');
            detailPanel.prepend('<div class="la-section-header"><h3>Lead Detail</h3></div>');
            $el.find('.la-dashboard').append(detailPanel);
            renderLeadDetail(detailPanel, leadId);
            $el.find('.la-tabs').append('<button type="button" class="la-tab" data-tab="lead-detail">Lead Detail</button>');
        }

        refreshStats();

        $(document).off('leadAggregator:refresh').on('leadAggregator:refresh', function () {
            renderInbox($el.find('.la-panel--inbox'));
            renderInbox($el.find('[data-tab="leads"]'));
            renderCalendar($el.find('[data-tab="followups"]'));
            refreshStats();
        });

        $el.find('.la-tab').on('click', function () {
            var tab = $(this).data('tab');
            $el.find('.la-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $el.find('.la-tab-panel').removeClass('is-active');
            $el.find('.la-tab-panel[data-tab="' + tab + '"]').addClass('is-active');
        });

        $el.find('.la-tab').first().trigger('click');
    }

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
        } else if (view === 'stages') {
            renderStages($el);
        } else if (view === 'tags') {
            renderTags($el);
        } else if (view === 'export') {
            renderExport($el);
        } else if (view === 'business-profile') {
            renderBusinessProfile($el);
        } else if (view === 'dashboard') {
            renderDashboard($el);
        }
    });
});
