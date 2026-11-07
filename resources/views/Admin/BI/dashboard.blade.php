@extends('Admin.Keuangan.layout')

@section('title', 'Dashboard Analisis Penjualan')

@section('content')

    @include('Admin.BI.dashboard-content', [
        'dashboardAction' => route('admin.bi.dashboard')
    ])

@endsection
