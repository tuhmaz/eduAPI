$(document).ready(function() {
    // تهيئة Select2 أولاً
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2();
    }

    // المتغيرات الرئيسية
    const selectAllCheckbox = $('#select-all-logs');
    const logCheckboxes = $('.log-checkbox');
    const bulkDeleteBtn = $('#bulk-delete-btn');
    const toggleSelectAllBtn = $('#toggle-select-all-btn');
    const bulkActionsBar = $('.bulk-actions');
    const formFeedback = $('#form-feedback');

    // تهيئة المكونات
    initializeComponents();
    initializeEventListeners();
    initializeFilters();
    initializeExport();

    // تهيئة المكونات الأساسية
    function initializeComponents() {
        // تهيئة التواريخ
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                language: 'ar',
                rtl: true
            });
        }

        // تهيئة القوائم المنسدلة
        if ($.fn.select2) {
            $('.select2').select2({
                dir: 'rtl',
                placeholder: 'اختر...',
                allowClear: true
            });
        }

        // تهيئة الجدول
        if ($.fn.DataTable) {
            $('.security-logs-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json'
                },
                order: [[1, 'desc']], // ترتيب حسب التاريخ تنازلياً
                pageLength: 15
            });
        }
    }

    // تهيئة مستمعي الأحداث
    function initializeEventListeners() {
        // تحديد الكل
        toggleSelectAllBtn.on('click', function() {
            const isChecked = selectAllCheckbox.prop('checked');
            selectAllCheckbox.prop('checked', !isChecked);
            logCheckboxes.prop('checked', !isChecked);
            updateBulkActions(!isChecked);
            updateToggleButtonText(!isChecked);
        });

        // تغيير حالة صندوق التحديد الرئيسي
        selectAllCheckbox.on('change', function() {
            const isChecked = $(this).prop('checked');
            logCheckboxes.prop('checked', isChecked);
            updateBulkActions(isChecked);
        });

        // تغيير حالة صناديق التحديد الفردية
        logCheckboxes.on('change', function() {
            const checkedCount = logCheckboxes.filter(':checked').length;
            const totalCount = logCheckboxes.length;
            
            selectAllCheckbox.prop('checked', checkedCount === totalCount);
            updateBulkActions(checkedCount > 0);
        });

        // حذف السجلات المحددة
        bulkDeleteBtn.on('click', handleBulkDelete);
    }

    // تحديث شريط الإجراءات الجماعية
    function updateBulkActions(show) {
        const checkedCount = logCheckboxes.filter(':checked').length;
        bulkActionsBar.toggleClass('show', show);
        
        if (show) {
            bulkActionsBar.find('.selected-count').text(checkedCount);
        }
        
        bulkDeleteBtn.toggleClass('d-none', !show);
    }

    // تحديث نص زر التحديد
    function updateToggleButtonText(isAllSelected) {
        toggleSelectAllBtn.html(
            isAllSelected
                ? '<i class="ri-checkbox-multiple-blank-line me-1"></i> إلغاء تحديد الكل'
                : '<i class="ri-checkbox-multiple-line me-1"></i> تحديد الكل'
        );
    }

    // معالجة الحذف الجماعي
    function handleBulkDelete() {
        const selectedIds = getSelectedIds();

        if (selectedIds.length === 0) {
            showFeedback('error', 'الرجاء تحديد السجلات التي تريد حذفها');
            return;
        }

        if (!confirm('هل أنت متأكد من حذف السجلات المحددة؟')) {
            return;
        }

        const loadingBtn = createLoadingButton(bulkDeleteBtn);
        
        $.ajax({
            url: $('#bulk-destroy-form').attr('action'),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                ids: selectedIds.join(',')
            },
            beforeSend: () => {
                loadingBtn.start();
                formFeedback.removeClass('d-none success error');
            },
            success: (response) => {
                if (response.success) {
                    showFeedback('success', response.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showFeedback('error', response.message);
                }
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'حدث خطأ أثناء حذف السجلات';
                showFeedback('error', message);
            },
            complete: () => {
                loadingBtn.stop();
            }
        });
    }

    // تهيئة الفلاتر
    function initializeFilters() {
        const filterForm = $('#filter-form');
        
        filterForm.on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            $.ajax({
                url: filterForm.attr('action'),
                method: 'GET',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    $('#logs-table-container').html(response);
                    initializeComponents(); // إعادة تهيئة المكونات بعد تحديث الجدول
                },
                error: (xhr) => {
                    showFeedback('error', 'حدث خطأ أثناء تصفية النتائج');
                }
            });
        });

        // تحديث الفلاتر تلقائياً
        filterForm.find('select, input').on('change', function() {
            filterForm.submit();
        });
    }

    // تهيئة التصدير
    function initializeExport() {
        $('.export-excel').on('click', function(e) {
            e.preventDefault();
            
            const filters = {
                event_type: $('#event-type-filter').val(),
                status: $('#status-filter').val(),
                date_from: $('#date-from').val(),
                date_to: $('#date-to').val()
            };
            
            const queryParams = Object.entries(filters)
                .filter(([_, value]) => value)
                .map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
                .join('&');
            
            const exportUrl = window.exportUrl + (queryParams ? `?${queryParams}` : '');
            window.location.href = exportUrl;
        });
    }

    // وظائف مساعدة
    function getSelectedIds() {
        return Array.from(logCheckboxes.filter(':checked')).map(cb => cb.value);
    }

    function showFeedback(type, message) {
        formFeedback
            .removeClass('d-none success error')
            .addClass(type)
            .html(message)
            .fadeIn();
    }

    function createLoadingButton(btn) {
        const originalHtml = btn.html();
        const loadingHtml = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';
        
        return {
            start: () => {
                btn.prop('disabled', true).html(loadingHtml);
            },
            stop: () => {
                btn.prop('disabled', false).html(originalHtml);
            }
        };
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // تهيئة Select2
    $('.select2').select2();

    // تهيئة Flatpickr
    $('.flatpickr').flatpickr({
        dateFormat: "Y-m-d",
        locale: "ar"
    });

    // متغيرات عامة
    const bulkActionsBar = document.querySelector('.bulk-actions');
    const selectAllCheckbox = document.getElementById('select-all-logs');
    const logCheckboxes = document.querySelectorAll('.log-checkbox');
    const selectedCountSpan = document.querySelector('.selected-count');
    const bulkDestroyForm = document.getElementById('bulk-destroy-form');
    const bulkDestroyIds = document.getElementById('bulk-destroy-ids');
    const formFeedback = document.getElementById('form-feedback');

    // تحديث شريط الإجراءات الجماعية
    function updateBulkActionsBar() {
        const selectedCount = document.querySelectorAll('.log-checkbox:checked').length;
        selectedCountSpan.textContent = selectedCount;
        
        if (selectedCount > 0) {
            bulkActionsBar.classList.remove('d-none');
        } else {
            bulkActionsBar.classList.add('d-none');
            selectAllCheckbox.checked = false;
        }
    }

    // تحديد/إلغاء تحديد كل السجلات
    selectAllCheckbox?.addEventListener('change', function() {
        logCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionsBar();
    });

    // تحديث حالة "تحديد الكل" عند تغيير أي صندوق اختيار
    logCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionsBar();
            
            const allChecked = document.querySelectorAll('.log-checkbox:not(:checked)').length === 0;
            selectAllCheckbox.checked = allChecked;
        });
    });

    // حذف السجلات المحددة
    document.getElementById('bulk-delete-btn')?.addEventListener('click', function() {
        if (confirm('هل أنت متأكد من حذف السجلات المحددة؟')) {
            const selectedIds = Array.from(document.querySelectorAll('.log-checkbox:checked'))
                                   .map(checkbox => checkbox.value);
            
            bulkDestroyIds.value = JSON.stringify(selectedIds);
            bulkDestroyForm.submit();
        }
    });

    // تبديل حالة الحل
    document.querySelectorAll('.toggle-resolution-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const logId = this.dataset.logId;
            
            try {
                const response = await fetch(`/dashboard/security/logs/${logId}/toggle-resolution`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showFeedback('success', data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showFeedback('error', data.message || 'حدث خطأ أثناء تحديث الحالة');
                }
            } catch (error) {
                showFeedback('error', 'حدث خطأ أثناء الاتصال بالخادم');
            }
        });
    });

    // حذف سجل واحد
    document.querySelectorAll('.delete-log-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('هل أنت متأكد من حذف هذا السجل؟')) return;

            const logId = this.dataset.logId;
            
            try {
                const response = await fetch(`/dashboard/security/logs/${logId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    showFeedback('success', data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showFeedback('error', data.message || 'حدث خطأ أثناء حذف السجل');
                }
            } catch (error) {
                showFeedback('error', 'حدث خطأ أثناء الاتصال بالخادم');
            }
        });
    });

    // عرض رسائل التنبيه
    function showFeedback(type, message) {
        formFeedback.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
        formFeedback.textContent = message;
        formFeedback.classList.remove('d-none');

        setTimeout(() => {
            formFeedback.classList.add('d-none');
        }, 3000);
    }
});
