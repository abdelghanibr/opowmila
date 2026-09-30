@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right;">

    {{-- العنوان + زر الإضافة --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">👥 رياضيو النادي</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('club.cards') }}"
               class="btn btn-outline-success btn-sm">
                🪪 طباعة كل البطاقات
            </a>

            <a href="{{ route('club.persons.create') }}"
               class="btn btn-success btn-sm">
                ➕ إضافة عضو
            </a>
        </div>
    </div>

    {{-- رسالة نجاح --}}
    @if(session('success'))
        <div class="alert alert-success text-center fw-bold">
            {{ session('success') }}
        </div>
    @endif

    {{-- الجدول --}}
    <div class="table-responsive">
        <table id="personsTable"
               class="table table-striped table-bordered align-middle text-center w-100">

            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>الاسم</th>
                    <th>اللقب</th>
                    <th>العمر</th>
                    <th>الجنس</th>
                    <th>صفة اللاعب</th>
                    <th>التصنيف</th>
                    <th>رقم الإجازة</th>
                    <th>الاستمارة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>

            <tbody>
            @foreach($persons as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    {{-- 🖼️ الصورة --}}
                    <td>
                        @if($p->photo)
                            <img src="{{ asset($p->photo) }}"
                                 alt="photo"
                                 class="person-avatar"
                                 data-bs-toggle="modal"
                                 data-bs-target="#photoModal{{ $p->id }}">
                        @else
                            <img src="{{ asset('images/avatar-default.png') }}"
                                 class="person-avatar">
                        @endif
                    </td>

                    <td>{{ $p->firstname }}</td>
                    <td>{{ $p->lastname }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->birth_date)->age }} سنة</td>
                    <td>{{ $p->gender }}</td>
                    <td>{{ $p->education }}</td>
                    <td>{{ $p->ageCategory?->name ?? '—' }}</td>
  <td>{{ $p->license_number }}</td>
                    {{-- الاستمارة --}}
                    <td>
                        @if(!empty($p->birth_certificate))
                            <a href="{{ $p->birth_certificate }}" target="_blank"
                               class="btn btn-sm btn-outline-success rounded-pill"
                               title="عرض الإستمارة">
                                📄
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    {{-- الإجراءات --}}
                    <td>
                        <a href="{{ route('club.card', $p->id) }}"
                           class="btn btn-sm btn-info" title="بطاقة اللاعب">
                            🪪 بطاقة
                        </a>

                        <a href="{{ route('club.persons.edit', $p->id) }}"
                           class="btn btn-sm btn-warning">
                            ✏ تعديل
                        </a>

                      

                       <form action="{{ route('club.persons.delete', $p->id) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('⚠ هل أنت متأكد من الحذف؟');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">🗑 حذف</button>
                        </form>
                    </td>
                </tr>

                {{-- 🔍 Modal معاينة الصورة --}}
                @if($p->photo)
                <div class="modal fade" id="photoModal{{ $p->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                                <img src="{{ asset($p->photo) }}"
                                     class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection
@push('css')
<style>
.person-avatar {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    transition: transform .2s ease;
}

.person-avatar:hover {
    transform: scale(1.08);
}
</style>
@endpush
@push('js')
@include('admin.partials.datatable-script', ['tableId' => '#personsTable'])
@endpush
