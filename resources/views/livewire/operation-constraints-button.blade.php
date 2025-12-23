<div>
    <flux:button 
        variant="ghost" 
        size="sm"
        wire:click="openModal"
        wire:loading.attr="disabled"
        class="!p-1 !h-6 !w-6"
        title="عرض القيود المحاسبية"
    >
        <flux:icon.book-open-text variant="micro" />
    </flux:button>

    <flux:modal name="operation-constraints-{{ $operheadId }}" focusable class="max-w-4xl">
        <div class="space-y-4">
                <div>
                    <flux:heading size="lg">القيود المحاسبية المرتبطة بالعملية</flux:heading>
                </div>

                @if(count($journalHeads) > 0)
                    <div class="space-y-6">
                        @foreach($journalHeads as $head)
                            <div class="border rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <flux:text class="font-semibold">تاريخ القيد: {{ $head->date }}</flux:text>
                                        <flux:text class="text-sm text-gray-600">الإجمالي: {{ number_format($head->total, 2) }}</flux:text>
                                    </div>
                                    @if($head->details)
                                        <flux:text class="text-sm">رقم القيد: {{ $head->id }}</flux:text>
                                    @endif
                                </div>

                                @if($head->details && count($head->details) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">الحساب</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">مدين</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">دائن</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">ملاحظات</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($head->details as $detail)
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm">{{ $detail->account_name ?? 'حساب #' . $detail->account_id }}</td>
                                                        <td class="px-4 py-2 text-sm">{{ $detail->debit > 0 ? number_format($detail->debit, 2) : '-' }}</td>
                                                        <td class="px-4 py-2 text-sm">{{ $detail->credit > 0 ? number_format($detail->credit, 2) : '-' }}</td>
                                                        <td class="px-4 py-2 text-sm">{{ $detail->info ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:callout variant="info">
                        لا توجد قيود محاسبية مرتبطة بهذه العملية
                    </flux:callout>
                @endif

                <div class="flex justify-end">
                    <flux:button variant="filled" wire:click="closeModal">إغلاق</flux:button>
                </div>
            </div>
        </flux:modal>
</div>
