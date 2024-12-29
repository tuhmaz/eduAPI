@extends('layouts.layoutMaster')

@section('title', 'سجلات الأمان')

@section('page-style')
    @vite(['resources/assets/js/security-logs.js'])

@endsection

@section('content')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />

<div class="container-fluid">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">الأمان /</span> سجلات الأمان
    </h4>

    <!-- فلتر البحث -->
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="ri-filter-line me-2"></i>تصفية النتائج</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('security.logs.filter') }}" method="GET" class="mb-4">
                <x-filters.security-logs />
            </form>
        </div>
    </div>

    <!-- جدول السجلات -->
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="ri-table-line me-2"></i>سجلات الأمان</h5>

            <!-- الأزرار الخاصة بتحديد الكل وحذف السجلات -->
            <div class="d-flex">
                <button type="button" id="bulk-delete-btn" class="btn btn-danger d-none">
                    <i class="ri-delete-bin-line me-1"></i> حذف السجلات المحددة
                </button>
                <button type="button" id="toggle-select-all-btn" class="btn btn-secondary">
                    <i class="ri-checkbox-multiple-line me-1"></i> تحديد الكل
                </button>
                <a href="{{ route('security.logs.export') }}" class="btn btn-success mb-3">
                    <i class="ri-download-2-line me-1"></i>تصدير السجلات
                </a>
                <a href="{{ route('security.logs.export') }}" class="btn btn-primary export-excel me-2">
                    <i class="ri-file-excel-line me-2"></i>تصدير إلى Excel
                </a>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <form id="bulk-destroy-form" action="{{ route('security.logs.bulk-destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="ids" id="bulk-destroy-ids">
                <div id="form-feedback" class="alert d-none"></div>
                <table class="table border-top">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all-logs">
                                </div>
                            </th>
                            <th>التاريخ</th>
                            <th>عنوان IP</th>
                            <th>نوع الحدث</th>
                            <th>الوصف</th>
                            <th>المستخدم</th>
                            <th>درجة الخطورة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" name="selected_logs[]" value="{{ $log->id }}" class="form-check-input log-checkbox">
                                    </div>
                                </td>
                                <td>{{ $log->created_at }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ $log->event_type }}</td>
                                <td>{{ $log->description }}</td>
                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->severity }}</td>
                                <td>{{ $log->status }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('security.logs.show', $log) }}" class="btn btn-sm btn-primary">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <form action="{{ route('security.logs.destroy', $log) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">لا توجد سجلات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $logs->links('components.pagination.custom') }}
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function submitBulkDelete() {
    const selectedIds = [];
    document.querySelectorAll('input[name="selected_logs[]"]:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });

    if (selectedIds.length === 0) {
        alert('الرجاء تحديد سجل واحد على الأقل للحذف');
        return;
    }

    document.getElementById('bulk-destroy-ids').value = selectedIds.join(',');
    document.getElementById('bulk-destroy-form').submit();
}

document.getElementById('toggle-select-all-btn').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('input[name="selected_logs[]"]');
    const isAllChecked = [...checkboxes].every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !isAllChecked;
    });

    document.getElementById('bulk-delete-btn').classList.toggle('d-none', isAllChecked);
});

document.querySelectorAll('input[name="selected_logs[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const anyChecked = document.querySelectorAll('input[name="selected_logs[]"]:checked').length > 0;
        document.getElementById('bulk-delete-btn').classList.toggle('d-none', !anyChecked);
    });
});
</script>
@endsection

@section('page-script')
    <script>
        window.exportUrl = "{{ route('security.logs.export') }}";
    </script>
    @vite(['resources/assets/js/security-logs.js'])
@endsection
