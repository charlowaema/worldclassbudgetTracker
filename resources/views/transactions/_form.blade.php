@php
    $t = $transaction ?? null;
    $selectedType = old('type', $t->type ?? $type ?? 'expense');
@endphp

<div x-data="{ type: '{{ $selectedType }}', recurring: {{ old('is_recurring', $t->is_recurring ?? false) ? 'true' : 'false' }} }" class="space-y-5">
    {{-- Type toggle --}}
    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-900 rounded-lg">
        <button type="button" @click="type = 'expense'"
                :class="type === 'expense' ? 'bg-white dark:bg-slate-700 shadow-sm text-red-600' : 'text-slate-500'"
                class="py-2 rounded-md text-sm font-semibold transition">Expense</button>
        <button type="button" @click="type = 'income'"
                :class="type === 'income' ? 'bg-white dark:bg-slate-700 shadow-sm text-emerald-600' : 'text-slate-500'"
                class="py-2 rounded-md text-sm font-semibold transition">Income</button>
    </div>
    <input type="hidden" name="type" x-model="type">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1.5">Amount</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">{{ auth()->user()->currencySymbol() }}</span>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $t->amount ?? '') }}" required
                       class="w-full pl-8 pr-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1.5">Date</label>
            <input type="date" name="date" value="{{ old('date', isset($t) ? $t->date->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                   class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1.5">Category</label>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" x-show="type === 'expense'">
            @foreach ($categories->where('type', 'expense') as $c)
                <label class="cursor-pointer">
                    <input type="radio" name="category_id" value="{{ $c->id }}" class="peer sr-only"
                           {{ old('category_id', $t->category_id ?? null) == $c->id ? 'checked' : '' }}>
                    <div class="flex flex-col items-center gap-1 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-500/10 text-center">
                        <i data-lucide="{{ $c->icon }}" class="w-4 h-4" style="color: {{ $c->color }}"></i>
                        <span class="text-xs font-medium truncate w-full">{{ $c->name }}</span>
                    </div>
                </label>
            @endforeach
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" x-show="type === 'income'" x-cloak>
            @foreach ($categories->where('type', 'income') as $c)
                <label class="cursor-pointer">
                    <input type="radio" name="category_id" value="{{ $c->id }}" class="peer sr-only"
                           {{ old('category_id', $t->category_id ?? null) == $c->id ? 'checked' : '' }}>
                    <div class="flex flex-col items-center gap-1 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-500/10 text-center">
                        <i data-lucide="{{ $c->icon }}" class="w-4 h-4" style="color: {{ $c->color }}"></i>
                        <span class="text-xs font-medium truncate w-full">{{ $c->name }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1.5">Description</label>
        <input type="text" name="description" value="{{ old('description', $t->description ?? '') }}" placeholder="e.g. Weekly groceries"
               class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1.5">Payment Method</label>
            <select name="payment_method" class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ old('payment_method', $t->payment_method ?? 'cash') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col justify-end">
            <label class="flex items-center gap-2 text-sm font-medium mb-1.5 h-[42px]">
                <input type="checkbox" name="is_recurring" value="1" x-model="recurring" class="rounded border-slate-300 text-primary-600">
                Recurring transaction
            </label>
        </div>
    </div>

    <div x-show="recurring" x-cloak>
        <label class="block text-sm font-medium mb-1.5">Frequency</label>
        <select name="recurring_frequency" class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
            @foreach (['weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $val => $label)
                <option value="{{ $val }}" {{ old('recurring_frequency', $t->recurring_frequency ?? 'monthly') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1.5">Notes (optional)</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">{{ old('notes', $t->notes ?? '') }}</textarea>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold">
            {{ isset($t) ? 'Update Transaction' : 'Save Transaction' }}
        </button>
        <a href="{{ route('transactions.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-semibold text-center">Cancel</a>
    </div>
</div>
