@extends('layouts.app')

@section('content')
<div class="container py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-1">إدارة أعطال المسبح</h3>
            <p class="text-muted mb-0">
                من خلال هذه الصفحة يمكن تسجيل فترات توقف المسبح بالتاريخ والوقت، ثم عرض الحجوزات المتأثرة وتوليد الأرصدة التعويضية.
            </p>
        </div>

        <a href="{{ route('admin.pool-closures.create') }}" class="btn btn-danger">
            <i class="fa fa-plus"></i>
            إضافة عطل جديد
        </a>
    </div>

    @include('admin.pool_closures.partials.alerts')

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>قائمة الأعطال المسجلة</strong>

            @if(isset($poolClosures))
                <span class="badge bg-primary">
                    {{ $poolClosures->count() }} عطل
                </span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المركب</th>
                        <th>النشاط</th>
                        <th>بداية العطل</th>
                        <th>نهاية العطل</th>
                        <th>المدة</th>
                        <th>سبب العطل</th>
                        <th>الحالة</th>
                        <th style="width: 260px;">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($poolClosures as $closure)
                        @php
                            $startDate = $closure->start_date
                                ? \Carbon\Carbon::parse($closure->start_date)
                                : ($closure->closure_date ? \Carbon\Carbon::parse($closure->closure_date) : null);

                            $endDate = $closure->end_date
                                ? \Carbon\Carbon::parse($closure->end_date)
                                : $startDate;

                            $daysCount = ($startDate && $endDate)
                                ? $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1
                                : 0;

                            $hoursCount = ($startDate && $endDate)
                                ? round($startDate->diffInMinutes($endDate) / 60, 2)
                                : 0;
                        @endphp

                        <tr>
                            <td>{{ $closure->id }}</td>

                            <td>{{ $closure->complexActivity->complex->nom ?? '—' }}</td>

                            <td>{{ $closure->complexActivity->activity->title ?? '—' }}</td>

                            <td>
                                @if($startDate)
                                    {{ $startDate->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                @if($endDate)
                                    {{ $endDate->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                @if($daysCount > 0)
                                    <span class="badge bg-info text-dark d-block mb-1">
                                        {{ $daysCount }} يوم
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        {{ $hoursCount }} ساعة
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td>{{ $closure->reason ?? '—' }}</td>

                            <td>
                                @if($closure->status === 'active')
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">ملغى</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex justify-content-center gap-1 flex-wrap">

                                    <a href="{{ route('admin.pool-closures.show', $closure) }}"
                                       class="btn btn-sm btn-primary">
                                        الحجوزات المتأثرة
                                    </a>

                                    <a href="{{ route('admin.pool-closures.edit', $closure) }}"
                                       class="btn btn-sm btn-warning">
                                        تعديل
                                    </a>

                                    <form action="{{ route('admin.pool-closures.destroy', $closure) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا العطل؟');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            حذف
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                لا توجد أعطال مسجلة حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($poolClosures, 'links'))
            <div class="card-footer bg-white">
                {{ $poolClosures->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
