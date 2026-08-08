@extends('layouts.app')
@section('title', 'Edit Transaction')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
        <h2 class="text-lg font-bold mb-5">Edit Transaction</h2>
        <form method="POST" action="{{ route('transactions.update', $transaction) }}">
            @csrf
            @method('PUT')
            @include('transactions._form', ['transaction' => $transaction])
        </form>
    </div>
</div>
@endsection
