$(function() {
  const selectAllCheckbox = $('#select-all-logs');
  const logCheckboxes = $('.log-checkbox');
  const bulkDeleteBtn = $('#bulk-delete-btn');
  const toggleSelectAllBtn = $('#toggle-select-all-btn');

  let allSelected = false;

  console.log("Loaded: selectAllCheckbox:", selectAllCheckbox.length);
  console.log("Loaded: logCheckboxes:", logCheckboxes.length);

  // تحديد الكل وإلغاء التحديد
  toggleSelectAllBtn.on('click', function() {
      allSelected = !allSelected;
      console.log("Toggle Select All:", allSelected);

      logCheckboxes.prop('checked', allSelected);
      selectAllCheckbox.prop('checked', allSelected);
      updateBulkDeleteButton();
      updateToggleButtonText();
  });

  selectAllCheckbox.on('change', function() {
      console.log("Select All Checkbox Changed:", $(this).is(':checked'));

      logCheckboxes.prop('checked', $(this).is(':checked'));
      updateBulkDeleteButton();
  });

  logCheckboxes.on('change', function() {
      const allChecked = logCheckboxes.length === logCheckboxes.filter(':checked').length;
      console.log("Individual Checkbox Changed, All Checked:", allChecked);

      selectAllCheckbox.prop('checked', allChecked);
      updateBulkDeleteButton();
  });

  function updateBulkDeleteButton() {
      const anyChecked = logCheckboxes.filter(':checked').length > 0;
      console.log("Bulk Delete Button Visibility:", anyChecked);

      bulkDeleteBtn.toggleClass('d-none', !anyChecked);
  }

  function updateToggleButtonText() {
      toggleSelectAllBtn.html(allSelected ?
          '<i class="ri-checkbox-multiple-blank-line me-1"></i> إلغاء تحديد الكل' :
          '<i class="ri-checkbox-multiple-line me-1"></i> تحديد الكل'
      );
  }

  function updateBulkDeleteButton() {
      const checkedBoxes = $('input[name="selected_logs[]"]:checked');
      $('#bulk-delete-btn').toggleClass('d-none', checkedBoxes.length === 0);
  }

  function updateToggleButtonText(isAllSelected) {
      $('#toggle-select-all-btn').html(
          isAllSelected
              ? '<i class="ri-checkbox-multiple-blank-line me-1"></i> إلغاء تحديد الكل'
              : '<i class="ri-checkbox-multiple-line me-1"></i> تحديد الكل'
      );
  }

  $('#toggle-select-all-btn').on('click', function() {
      const checkboxes = $('input[name="selected_logs[]"]');
      const selectAllCheckbox = $('#select-all-logs');
      const currentState = selectAllCheckbox.prop('checked');
      
      checkboxes.prop('checked', !currentState);
      selectAllCheckbox.prop('checked', !currentState);
      
      updateBulkDeleteButton();
      updateToggleButtonText(!currentState);
  });

  $(document).on('change', 'input[name="selected_logs[]"]', function() {
      updateBulkDeleteButton();
  });

  $('#bulk-delete-btn').on('click', function() {
      const selectedIds = [];
      $('input[name="selected_logs[]"]:checked').each(function() {
          selectedIds.push($(this).val());
      });

      if (selectedIds.length === 0) {
          alert('الرجاء تحديد السجلات التي تريد حذفها');
          return;
      }

      if (!confirm('هل أنت متأكد من حذف السجلات المحددة؟')) {
          return;
      }

      const feedback = $('#form-feedback');
      feedback.removeClass('d-none alert-danger alert-success');

      $('#bulk-destroy-ids').val(selectedIds.join(','));
      
      $.ajax({
          url: $('#bulk-destroy-form').attr('action'),
          method: 'POST',
          data: {
              _token: $('meta[name="csrf-token"]').attr('content'),
              ids: selectedIds.join(',')
          },
          beforeSend: function() {
              $('#bulk-delete-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحذف...');
          },
          success: function(response) {
              if (response.success) {
                  feedback.addClass('alert alert-success').html(response.message).removeClass('d-none');
                  // Refresh the page after successful deletion
                  setTimeout(() => {
                      window.location.reload();
                  }, 1500);
              } else {
                  feedback.addClass('alert alert-danger').html(response.message).removeClass('d-none');
              }
          },
          error: function(xhr) {
              const message = xhr.responseJSON ? xhr.responseJSON.message : 'حدث خطأ أثناء حذف السجلات';
              feedback.addClass('alert alert-danger').html(message).removeClass('d-none');
          },
          complete: function() {
              $('#bulk-delete-btn').prop('disabled', false).html('<i class="fas fa-trash"></i> حذف المحدد');
          }
      });
  });

  function submitBulkDelete() {
    const selectedIds = [];
    document.querySelectorAll('input[name="selected_logs[]"]:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });

    console.log('Selected IDs:', selectedIds);

    if (selectedIds.length === 0) {
        alert('الرجاء تحديد السجلات التي تريد حذفها');
        return false;
    }

    if (!confirm('هل أنت متأكد من حذف السجلات المحددة؟')) {
        return false;
    }

    const idsString = selectedIds.join(',');
    console.log('IDs string to be sent:', idsString);
    
    document.getElementById('bulk-destroy-ids').value = idsString;
    console.log('Form data before submit:', new FormData(document.getElementById('bulk-destroy-form')));
    
    document.getElementById('bulk-destroy-form').submit();
    return true;
  }

  document.getElementById('toggle-select-all-btn').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[name="selected_logs[]"]');
    const selectAllCheckbox = document.getElementById('select-all-logs');
    const isChecked = selectAllCheckbox.checked;
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !isChecked;
    });
    selectAllCheckbox.checked = !isChecked;
    
    // Show/hide bulk delete button
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
    bulkDeleteBtn.classList.toggle('d-none', !hasChecked);
  });

  document.addEventListener('change', function(e) {
    if (e.target.matches('input[name="selected_logs[]"]')) {
        const checkboxes = document.querySelectorAll('input[name="selected_logs[]"]');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
        bulkDeleteBtn.classList.toggle('d-none', !hasChecked);
    }
  });

  window.submitBulkDelete = submitBulkDelete;

  // Handle Excel export with filters
  $('.export-excel').on('click', function(e) {
      e.preventDefault();
      
      // Get current filter values
      const filters = {
          event_type: $('#event-type-filter').val(),
          status: $('#status-filter').val(),
          date_from: $('#date-from').val(),
          date_to: $('#date-to').val()
      };
      
      // Build query string only if filters have values
      const queryParams = [];
      Object.entries(filters).forEach(([key, value]) => {
          if (value) {
              queryParams.push(`${key}=${encodeURIComponent(value)}`);
          }
      });
      
      // Add query string only if there are filters
      const finalUrl = queryParams.length > 0 
          ? `${window.exportUrl}?${queryParams.join('&')}` 
          : window.exportUrl;
      
      // Redirect to export URL
      window.location.href = finalUrl;
  });

  // تحديث حالة السجل
  $('.resolve-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      
      $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: form.serialize(),
          success: function(response) {
              // تحديث واجهة المستخدم
              const statusCell = form.closest('td');
              statusCell.html('<span class="badge bg-label-success">تم الحل</span>');
              
              // إظهار رسالة نجاح
              if (response.message) {
                  // يمكنك إضافة كود لعرض رسالة النجاح هنا
              }
          },
          error: function(xhr) {
              console.error('Error:', xhr);
              alert('حدث خطأ أثناء تحديث حالة السجل');
          }
      });
  });
});
