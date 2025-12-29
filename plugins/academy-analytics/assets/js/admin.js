/**
 * Academy Analytics Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Select all checkbox
        $('#cb-select-all').on('change', function() {
            $('input[name="event_ids[]"]').prop('checked', $(this).prop('checked'));
        });
        
        // Individual checkbox change
        $('input[name="event_ids[]"]').on('change', function() {
            var total = $('input[name="event_ids[]"]').length;
            var checked = $('input[name="event_ids[]"]:checked').length;
            $('#cb-select-all').prop('checked', total === checked);
        });
        
        // Bulk action - no confirmation needed
        
        // Copy webhook URL to clipboard
        $('.copy-webhook-url').on('click', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var $input = $('<input>').val(url).appendTo('body').select();
            document.execCommand('copy');
            $input.remove();
            
            var $button = $(this);
            var originalText = $button.text();
            $button.text('Copied!').addClass('button-primary');
            setTimeout(function() {
                $button.text(originalText).removeClass('button-primary');
            }, 2000);
        });
        
        // Initialize charts if Chart.js is loaded and data exists
        // Wait a bit to ensure Chart.js is fully loaded
        if (typeof academyAnalyticsChartData !== 'undefined') {
            // Wait for Chart.js to be available
            var chartInitAttempts = 0;
            var chartInitInterval = setInterval(function() {
                chartInitAttempts++;
                if (typeof Chart !== 'undefined') {
                    clearInterval(chartInitInterval);
                    initCharts();
                } else if (chartInitAttempts > 20) {
                    // Give up after 2 seconds
                    clearInterval(chartInitInterval);
                    console.error('Academy Analytics: Chart.js failed to load');
                }
            }, 100);
        }
        
    });
    
    /**
     * Initialize all charts
     */
    function initCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Academy Analytics: Chart.js is not loaded');
            return;
        }
        
        if (typeof academyAnalyticsChartData === 'undefined') {
            console.error('Academy Analytics: Chart data is not available');
            return;
        }
        
        var data = academyAnalyticsChartData;
        
        // Events Over Time Chart
        var timeSeriesCtx = document.getElementById('events-over-time-chart');
        if (timeSeriesCtx) {
            var timeLabels = [];
            var timeData = [];
            
            if (data.time_series && data.time_series.length > 0) {
                data.time_series.forEach(function(item) {
                    timeLabels.push(item.date);
                    timeData.push(parseInt(item.count));
                });
            } else {
                // Show empty state
                timeLabels = ['No data'];
                timeData = [0];
            }
            
            try {
                new Chart(timeSeriesCtx, {
                type: 'line',
                data: {
                    labels: timeLabels,
                    datasets: [{
                        label: 'Events',
                        data: timeData,
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
                });
            } catch (e) {
                console.error('Academy Analytics: Error creating time series chart', e);
            }
        }
        
        // Events by Type Chart
        var byTypeCtx = document.getElementById('events-by-type-chart');
        if (byTypeCtx && data.by_type) {
            var typeLabels = [];
            var typeData = [];
            var typeColors = [
                '#2271b1',
                '#00a32a',
                '#d63638',
                '#f0b849',
                '#826eb4',
                '#00ba37',
                '#d54e21',
                '#8f8f8f'
            ];
            
            var colorIndex = 0;
            for (var type in data.by_type) {
                if (data.by_type.hasOwnProperty(type)) {
                    typeLabels.push(type.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }));
                    typeData.push(parseInt(data.by_type[type].count));
                    colorIndex++;
                }
            }
            
            try {
                new Chart(byTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        data: typeData,
                        backgroundColor: typeColors.slice(0, typeLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
                });
            } catch (e) {
                console.error('Academy Analytics: Error creating events by type chart', e);
            }
        }
        
        // Top Forms Chart
        var topFormsCtx = document.getElementById('top-forms-chart');
        if (topFormsCtx && data.top_forms && data.top_forms.length > 0) {
            var formLabels = [];
            var formData = [];
            
            data.top_forms.forEach(function(form) {
                formLabels.push(form.form_name);
                formData.push(parseInt(form.count));
            });
            
            try {
                new Chart(topFormsCtx, {
                type: 'bar',
                data: {
                    labels: formLabels,
                    datasets: [{
                        label: 'Submissions',
                        data: formData,
                        backgroundColor: '#2271b1',
                        borderColor: '#135e96',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
                });
            } catch (e) {
                console.error('Academy Analytics: Error creating top forms chart', e);
            }
        }
        
        // Top Pages Chart
        var topPagesCtx = document.getElementById('top-pages-chart');
        if (topPagesCtx && data.top_pages && data.top_pages.length > 0) {
            var pageLabels = [];
            var pageData = [];
            
            data.top_pages.forEach(function(page) {
                var label = page.page_title || page.page_url;
                if (label.length > 30) {
                    label = label.substring(0, 30) + '...';
                }
                pageLabels.push(label);
                pageData.push(parseInt(page.count));
            });
            
            try {
                new Chart(topPagesCtx, {
                type: 'bar',
                data: {
                    labels: pageLabels,
                    datasets: [{
                        label: 'Visits',
                        data: pageData,
                        backgroundColor: '#00a32a',
                        borderColor: '#007a20',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
                });
            } catch (e) {
                console.error('Academy Analytics: Error creating top pages chart', e);
            }
        }
        
        // Initialize conversion rate chart if on dashboard page
        if (typeof conversionChartData !== 'undefined') {
            initConversionChart();
        }
    });
    
    /**
     * Initialize conversion rate chart
     */
    function initConversionChart() {
        if (typeof Chart === 'undefined' || typeof conversionChartData === 'undefined') {
            return;
        }
        
        var ctx = document.getElementById('conversion-rate-chart');
        if (!ctx) {
            return;
        }
        
        try {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: conversionChartData.labels,
                    datasets: [
                        {
                            label: 'Starting Events',
                            data: conversionChartData.starting,
                            borderColor: '#2271b1',
                            backgroundColor: 'rgba(34, 113, 177, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Conversions',
                            data: conversionChartData.conversions,
                            borderColor: '#00a32a',
                            backgroundColor: 'rgba(0, 163, 42, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Conversion Rate %',
                            data: conversionChartData.rates,
                            borderColor: '#d63638',
                            backgroundColor: 'rgba(214, 54, 56, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            yAxisID: 'y1',
                            borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Academy Analytics: Error creating conversion chart', e);
        }
    }
    
})(jQuery);

