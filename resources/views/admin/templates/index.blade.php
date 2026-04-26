@extends('layouts.admin')

@php
    $pageTitle = 'قوالب الاستبيانات';
    $pageSubtitle = 'عرض وإدارة القوالب الجاهزة';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">إنشاء قالب جديد</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>اسم القالب</th>
                    <th>المستوى</th>
                    <th>الكلية</th>
                    <th>القسم</th>
                    <th>الوصف</th>
                    <th>عدد المحاور</th>
                    <th>عدد الأسئلة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td>{{ $template->name }}</td>
                        <td>
                            @if($template->scope_level === 'university')
                                <span class="badge badge-primary">جامعة</span>
                            @elseif($template->scope_level === 'faculty')
                                <span class="badge badge-success">كلية</span>
                            @else
                                <span class="badge badge-warning">قسم</span>
                            @endif
                        </td>
                        <td>{{ $template->faculty?->name_ar ?? '—' }}</td>
                        <td>{{ $template->department?->name_ar ?? '—' }}</td>
                        <td>{{ $template->description ?: '—' }}</td>
                        <td>{{ $template->sections_count }}</td>
                        <td>{{ $template->questions_count }}</td>
                        <td>
                            @if($template->is_active)
                                <span class="badge badge-success">نشط</span>
                            @else
                                <span class="badge badge-warning">غير نشط</span>
                            @endif
                        </td>
                        <td>
                            <div class="page-actions" style="margin:0;">
                                <a href="{{ route('admin.templates.edit', $template->id) }}" class="btn btn-secondary">تعديل</a>

                                <form method="POST"
                                      action="{{ route('admin.templates.destroy', $template->id) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف القالب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">لا توجد قوالب حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection