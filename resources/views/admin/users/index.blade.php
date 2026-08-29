@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المستخدمين';
    $pageSubtitle = 'إنشاء وتعديل المستخدمين داخل النظام';
@endphp

@section('content')
    @php
        $roleLabels = [
            'university_admin' => 'أدمن جامعة',
            'presidency_admin' => 'المكتب الفني لرئيس الجامعة',
            'faculty_admin' => 'أدمن كلية',
            'department_admin' => 'أدمن قسم',
            'results_viewer' => 'عرض نتائج فقط',
        ];
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">إضافة مستخدم</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">لوحة المؤشرات</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>النوع</th>
                    <th>الكلية</th>
                    <th>القسم</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $roleLabels[$user->role] ?? $user->role }}</td>
                        <td>{{ $user->faculty?->name_ar ?? '—' }}</td>
                        <td>{{ $user->department?->name_ar ?? '—' }}</td>
                        <td>
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-secondary">تعديل</a>

                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary">Reset Password</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">حذف</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">لا يوجد مستخدمون حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection