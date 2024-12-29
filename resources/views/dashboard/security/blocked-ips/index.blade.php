@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'عناوين IP المحظورة')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
<style>
    .table thead th {
        color: #000 !important;
        font-weight: 600;
    }
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-dark">الأمان /</span> <span class="text-dark fw-medium">عناوين IP المحظورة</span>
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-dark">قائمة عناوين IP المحظورة</h5>
        <button type="button" id="bulk-delete" class="btn btn-danger" disabled>
            <i class="ti ti-trash me-1"></i>
            إزالة الحظر عن المحدد
        </button>
    </div>
    <div class="card-body">
        @if($blockedIps->isEmpty())
            <div class="alert alert-primary" role="alert">
                <div class="alert-body d-flex align-items-center">
                    <i class="ti ti-info-circle me-2"></i>
                    <span>لا توجد عناوين IP محظورة حالياً</span>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped dt-complex-header table-blocked-ips">
                    <thead>
                        <tr>
                            <th style="width: 20px" class="text-dark">
                                <input type="checkbox" class="form-check-input select-all">
                            </th>
                            <th class="text-dark">عنوان IP</th>
                            <th class="text-dark">سبب الحظر</th>
                            <th class="text-dark">تم الحظر في</th>
                            <th class="text-dark">تم الحظر بواسطة</th>
                            <th class="text-dark">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blockedIps as $ip)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input select-item" value="{{ $ip->id }}">
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $ip->ip_address }}</span>
                                </td>
                                <td>{{ $ip->reason }}</td>
                                <td>
                                    <span class="text-nowrap">{{ $ip->blocked_at->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td>{{ $ip->blockedBy->name }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <form action="{{ route('security.blocked-ips.destroy', $ip) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon delete-record">
                                                <i class="ti ti-trash text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="إزالة الحظر"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $blockedIps->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = $('.table-blocked-ips').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/ar.json'
        },
        pageLength: 10,
        order: [[3, 'desc']]
    });

    const selectAll = document.querySelector('.select-all');
    const selectItems = document.querySelectorAll('.select-item');
    const bulkDeleteBtn = document.getElementById('bulk-delete');

    // تحديد/إلغاء تحديد الكل
    selectAll?.addEventListener('change', function() {
        selectItems.forEach(item => {
            item.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // تحديث زر الحذف المتعدد
    selectItems.forEach(item => {
        item.addEventListener('change', function() {
            updateBulkDeleteButton();
            selectAll.checked = [...selectItems].every(item => item.checked);
        });
    });

    function updateBulkDeleteButton() {
        const selectedCount = document.querySelectorAll('.select-item:checked').length;
        bulkDeleteBtn.disabled = selectedCount === 0;
    }

    // حذف سجل واحد
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "هل تريد إزالة هذا IP من قائمة الحظر؟",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، قم بإزالة الحظر',
                cancelButtonText: 'إلغاء',
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // حذف متعدد
    bulkDeleteBtn?.addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.select-item:checked')).map(el => el.value);
        
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "هل تريد إزالة عناوين IP المحددة من قائمة الحظر؟",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، قم بإزالة الحظر',
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-danger me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("security.blocked-ips.bulk-destroy") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم بنجاح!',
                            text: data.message,
                            customClass: {
                                confirmButton: 'btn btn-success'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ!',
                        text: 'حدث خطأ أثناء إزالة الحظر',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                });
            }
        });
    });
});
</script>
@endsection
