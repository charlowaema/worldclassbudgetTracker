@extends('layouts.app')
@section('title', 'Add Transaction')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
        <h2 class="text-lg font-bold mb-5">Add Transaction</h2>
        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
            @include('transactions._form')
        </form>
    </div>
</div>
@endsection
