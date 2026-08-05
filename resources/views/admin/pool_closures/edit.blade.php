@extends('layouts.app')

@section('content')
<div class="container py-4" dir="rtl">

    <div class="mb-4">
        <h3 class="mb-1">تعديل عطل المسبح</h3>
        <p class="text-muted mb-0">
            في حالة تعديل تاريخ أو وقت العطل أو النشاط، يرجى إعادة مراجعة الحجوزات المتأثرة.
        </p>
    </div>

    @include('admin.pool_closures.partials.alerts')

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form action="{{ route('admin.pool-closures.update', $poolClosure) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label fw-bold">المركب / النشاط</label>

                        <select name="complex_activity_id" class="form-select" required>
                            <option value="">-- اختر المركب والنشاط --</option>

                            @foreach($complexActivities as $complexActivity)
                                <option value="{{ $complexActivity->id }}"
                                    @selected(old('complex_activity_id', $poolClosure->complex_activity_id) == $complexActivity->id)>
                                    {{ $complexActivity->complex->nom ?? 'مركب غير معروف' }}
                                    -
                                    {{ $complexActivity->activity->title ?? 'نشاط غير معروف' }}
                                </option>
                            @endforeach
                        </select>

                        @error('complex_activity_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">بداية العطل</label>

                        <input type="datetime-local"
                               name="start_date"
                               value="{{ old('start_date', optional($poolClosure->start_date)->format('Y-m-d\\TH:i')) }}"
                               class="form-control"
                               required>

                        @error('start_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">نهاية العطل</label>

                        <input type="datetime-local"
                               name="end_date"
                               value="{{ old('end_date', optional($poolClosure->end_date)->format('Y-m-d\\TH:i')) }}"
                               class="form-control"
                               required>

                        @error('end_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">سبب العطل</label>

                        <input type="text"
                               name="reason"
                               value="{{ old('reason', $poolClosure->reason) }}"
                               class="form-control"
                               placeholder="مثال: عطل تقني في المسبح">

                        @error('reason')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">الحالة</label>

                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status', $poolClosure->status) === 'active')>
                                نشط
                            </option>

                            <option value="cancelled" @selected(old('status', $poolClosure->status) === 'cancelled')>
                                ملغى
                            </option>
                        </select>

                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
                    <a href="{{ route('admin.pool-closures.index') }}" class="btn btn-secondary">
                        رجوع
                    </a>

                    <button type="submit" class="btn btn-warning">
                        حفظ التعديلات
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
