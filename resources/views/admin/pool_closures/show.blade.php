@extends('layouts.app')

@section('content')
<div class="container py-4" dir="rtl">

    {{-- العنوان --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">الحجوزات المتأثرة بالعطل</h3>
            <p class="text-muted mb-0">
                تعرض هذه الصفحة الحجوزات المدفوعة والمؤكدة التي تتقاطع مع فترة العطل، ويتم احتساب التعويض حسب الأيام المطابقة للحصص.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.pool-closures.edit', $poolClosure) }}" class="btn btn-warning">
                تعديل العطل
            </a>

            <a href="{{ route('admin.pool-closures.index') }}" class="btn btn-secondary">
                رجوع
            </a>
        </div>
    </div>

    {{-- التنبيهات --}}
    @include('admin.pool_closures.partials.alerts')

    {{-- معلومات العطل --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <strong>معلومات العطل</strong>

            @if($poolClosure->status === 'active')
                <span class="badge bg-light text-success">نشط</span>
            @else
                <span class="badge bg-light text-secondary">ملغى</span>
            @endif
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <strong>المركب:</strong>
                        <div class="mt-1">
                            {{ $poolClosure->complexActivity->complex->nom ?? '—' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <strong>النشاط:</strong>
                        <div class="mt-1">
                            {{ $poolClosure->complexActivity->activity->title ?? '—' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <strong>فترة العطل:</strong>
                        <div class="mt-1">
                            @if($poolClosure->start_date && $poolClosure->end_date)
                                {{ \Carbon\Carbon::parse($poolClosure->start_date)->format('d/m/Y H:i') }}
                                إلى
                                {{ \Carbon\Carbon::parse($poolClosure->end_date)->format('d/m/Y H:i') }}
                            @elseif($poolClosure->closure_date)
                                {{ \Carbon\Carbon::parse($poolClosure->closure_date)->format('d/m/Y H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 bg-light">
                        <strong>عدد أيام العطل:</strong>
                        <div class="mt-1">
                            @if($poolClosure->start_date && $poolClosure->end_date)
                                @php
                                    $closureDaysCount = \Carbon\Carbon::parse($poolClosure->start_date)
                                        ->startOfDay()->diffInDays(\Carbon\Carbon::parse($poolClosure->end_date)->startOfDay()) + 1;
                                @endphp

                                <span class="badge bg-info text-dark">
                                    {{ $closureDaysCount }} يوم
                                </span>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="border rounded p-3 bg-light">
                        <strong>سبب العطل:</strong>
                        <div class="mt-1">
                            {{ $poolClosure->reason ?? '—' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ملخص سريع --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">عدد الحجوزات المتأثرة</div>
                    <h4 class="mb-0 text-primary">
                        {{ $impactedReservations->count() }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">مجموع الأيام المتأثرة</div>
                    <h4 class="mb-0 text-warning">
                        {{ $impactedReservations->sum('impacted_days_count') }}
                    </h4>
                </div>
            </div>
        </div>

<div class="col-md-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
            <div class="text-muted mb-1">مجموع التعويضات</div>

            <h4 class="mb-0 text-danger" dir="ltr">
                {{ number_format((float) $impactedReservations->sum('total_credit'), 2, '.', ' ') }} 
            </h4>
  &nbsp;دج
        </div>
    </div>
</div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted mb-1">حالة التوليد</div>

                    @if($alreadyApplied)
                        <span class="badge bg-success fs-6">تم التوليد</span>
                    @elseif($poolClosure->status !== 'active')
                        <span class="badge bg-secondary fs-6">العطل ملغى</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6">في الانتظار</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- جدول الحجوزات المتأثرة --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                <strong>قائمة الحجوزات المتأثرة</strong>
                <span class="badge bg-primary ms-2">
                    {{ $impactedReservations->count() }}
                </span>
            </div>

            <div>
                @if($impactedReservations->count() > 0 && !$alreadyApplied && $poolClosure->status === 'active')
                    <form action="{{ route('admin.pool-closures.apply', $poolClosure) }}"
                          method="POST"
                          onsubmit="return confirm('هل تؤكد توليد الأرصدة التعويضية لجميع الحجوزات المتأثرة؟');">
                        @csrf

                        <button type="submit" class="btn btn-danger">
                           تأكيد تعويض الحصص للحجز التالي 
                        </button>
                    </form>
                @elseif($alreadyApplied)
                    <span class="badge bg-success p-2">
                        تم توليد الأرصدة مسبقاً
                    </span>
                @elseif($poolClosure->status !== 'active')
                    <span class="badge bg-secondary p-2">
                        لا يمكن التوليد لأن العطل ملغى
                    </span>
                @endif
            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                    <tr>
                        <th>رقم الحجز</th>
                        <th>المستخدم</th>
                        <th>المجموعة</th>
                        <th>فترة الحجز</th>
                        <th>سعر الجدول</th>
                        <th>عدد حصص الحجز</th>
                        <th>الأيام المتأثرة</th>
                        <th>عدد الأيام المتأثرة</th>
                        <th>قيمة الحصة</th>
                        <th>مجموع التعويض</th>
                        <th>الحالة</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($impactedReservations as $item)
                        @php
                            $reservation = $item['reservation'];

                            $impactedDates = $item['impacted_dates'] ?? [];
                            $impactedDaysCount = $item['impacted_days_count'] ?? 0;
                            $sessionsCount = $item['sessions_count'] ?? 0;
                            $schedulePrice = $item['schedule_price'] ?? 0;
                            $sessionValue = $item['session_value'] ?? 0;
                            $totalCredit = $item['total_credit'] ?? 0;
                            $alreadyCredited = $item['already_credited'] ?? false;
                            $alreadyCreditedCount = $item['already_credited_count'] ?? 0;
                        @endphp

                        <tr>
                            <td>
                                <strong>#{{ $reservation->id }}</strong>
                            </td>

                            <td>
                                {{ $reservation->user->name ?? '—' }}
                            </td>

                            <td>
                                {{ $reservation->schedule->groupe ?? '—' }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($reservation->start_date)->format('d/m/Y') }}
                                إلى
                                {{ \Carbon\Carbon::parse($reservation->end_date)->format('d/m/Y') }}
                            </td>

                            <td>
                                
                               
                                 <strong class="text-danger">
    <span dir="ltr" style="unicode-bidi: isolate;">
        {{ number_format((float) $schedulePrice, 2, '.', ' ') }}
    </span>
    دج
</strong>
                            </td>

                            <td>
                                <span class="badge bg-dark">
                                    {{ $sessionsCount }}
                                </span>
                            </td>

                            <td style="min-width: 180px;">
                                @forelse($impactedDates as $date)
                                    <span class="badge bg-light text-dark border mb-1">
                                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                    </span>
                                @empty
                                    —
                                @endforelse
                            </td>

                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $impactedDaysCount }}
                                </span>
                            </td>

                            <td>
                                {{ number_format((float) $sessionValue, 2, ',', ' ') }} دج
                            </td>

                            <td>
                                <strong class="text-danger">
                                    {{ number_format((float) $totalCredit, 2, ',', ' ') }} دج
                                </strong>
                            </td>

                            <td>
                                @if($alreadyCredited)
                                    <span class="badge bg-success">
                                        تم التعويض
                                    </span>
                                @elseif($alreadyCreditedCount > 0)
                                    <span class="badge bg-warning text-dark">
                                        تعويض جزئي {{ $alreadyCreditedCount }}/{{ $impactedDaysCount }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        في انتظار التعويض
                                    </span>
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                لا توجد حجوزات متأثرة بهذه الفترة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($impactedReservations->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end">
                                المجموع:
                            </th>

                            <th>
                                {{ $impactedReservations->sum('impacted_days_count') }}
                            </th>

                            <th>
                                —
                            </th>

                            <th class="text-danger">
                                {{ number_format($impactedReservations->sum('total_credit'), 2, ',', ' ') }} دج
                            </th>

                            <th>
                                —
                            </th>
                        </tr>
                    </tfoot>
                @endif

            </table>
        </div>
    </div>

</div>
@endsection