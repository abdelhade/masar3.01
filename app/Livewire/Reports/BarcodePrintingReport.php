<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\OperationItems;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Modules\Settings\Helpers\HelperSettings;

class BarcodePrintingReport extends Component
{
    public $operationId;
    public $items = [];
    public $barcodeCounts = [];
    public $barcodes = [];
    public $isLoading = false;
    public $settings = [];

    public function mount($operationId)
    {
        $this->operationId = $operationId;
        $this->settings = HelperSettings::getBarcodeSettings(); // جلب الإعدادات

        // dd($this->settings);
        $this->loadItems();
    }

    public function loadItems()
    {
        try {
            $this->items = OperationItems::with(['item', 'unit'])
                ->where('pro_id', $this->operationId)
                ->where('isdeleted', 0)
                ->get()
                ->map(function ($item) {
                    $defaultCount = max(1, intval($item->qty_in));
                    $this->barcodeCounts[$item->id] = $defaultCount;
                    return $item;
                });
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء تحميل البيانات: ');
            $this->items = collect();
        }
    }

    public function updatedBarcodeCounts($value, $key)
    {
        if ($value < 0) {
            $this->barcodeCounts[$key] = 0;
            session()->flash('error', 'عدد الباركودات يجب أن يكون غير سالب');
        }

        if ($value > 1000) {
            $this->barcodeCounts[$key] = 1000;
            session()->flash('error', 'الحد الأقصى لعدد الباركودات هو 1000 لكل صنف');
        }
    }

    public function generateBarcodes()
    {
        $this->isLoading = true;

        try {
            $generator = new BarcodeGeneratorPNG();
            $this->barcodes = [];
            $totalBarcodes = 0;

            foreach ($this->barcodeCounts as $count) {
                $totalBarcodes += intval($count);
            }

            if ($totalBarcodes > 5000) {
                session()->flash('error', 'العدد الإجمالي للباركودات كبير جدًا. الحد الأقصى 5000 باركود');
                $this->isLoading = false;
                return;
            }

            if ($totalBarcodes === 0) {
                session()->flash('error', 'لم يتم تحديد أي باركودات للطباعة');
                $this->isLoading = false;
                return;
            }

            foreach ($this->items as $item) {
                $count = intval($this->barcodeCounts[$item->id] ?? 0);

                if ($count > 0) {
                    $barcodeData = $this->generateBarcodeData($item);

                    try {


                        $barcodeImage = $generator->getBarcode(
                            $barcodeData,
                            $generator::TYPE_CODE_128,
                            $this->settings['barcode_width'] / 25.4 * 96, // تحويل مم إلى بكسل (96 DPI)
                            $this->settings['barcode_height'] // ارتفاع الباركود
                        );

                        // بهذا الكود المُحسن:
                        $barcodeImage = $generator->getBarcode(
                            $barcodeData,
                            $generator::TYPE_CODE_128,
                            3, // عرض الباركود (نسبة)
                            60  // ارتفاع الباركود بالبكسل
                        );
                        $barcodeImageBase64 = base64_encode($barcodeImage);

                        // 2. إضافة تحسينات للبيانات المرسلة:
                        for ($i = 0; $i < $count; $i++) {
                            $this->barcodes[] = [
                                'item_id' => $item->item_id,
                                'item_name' => $this->truncateText($item->item->name ?? 'غير محدد', 25),
                                'item_code' => $barcodeData,
                                'barcode_image' => $barcodeImageBase64,
                                'copy_number' => $i + 1,
                                'total_copies' => $count,
                                'company_name' => $this->settings['company_name'] ?? 'اسم الشركة',
                                'show_company_name' => $this->settings['show_company_name'] ?? true,
                                'show_item_name' => $this->settings['show_item_name'] ?? true,
                                'show_item_code' => $this->settings['show_item_code'] ?? true,
                                'show_barcode_image' => $this->settings['show_barcode_image'] ?? true,
                                'show_price_before_discount' => $this->settings['show_price_before_discount'] ?? false,
                                'show_price_after_discount' => $this->settings['show_price_after_discount'] ?? false,
                                'paper_width' => $this->settings['paper_width'] ?? 50,
                                'paper_height' => $this->settings['paper_height'] ?? 30,
                                'margin_top' => $this->settings['margin_top'] ?? 2,
                                'margin_bottom' => $this->settings['margin_bottom'] ?? 2,
                                'margin_left' => $this->settings['margin_left'] ?? 2,
                                'margin_right' => $this->settings['margin_right'] ?? 2,
                                'font_size_company' => $this->settings['font_size_company'] ?? 10,
                                'font_size_item' => $this->settings['font_size_item'] ?? 8,
                                'font_size_price' => $this->settings['font_size_price'] ?? 8,
                                'barcode_width' => $this->settings['barcode_width'] ?? 40,
                                'barcode_height' => $this->settings['barcode_height'] ?? 15,
                                'text_align' => $this->settings['text_align'] ?? 'center',
                                'invert_colors' => $this->settings['invert_colors'] ?? false,
                                'price_before_discount' => $item->price ?? 0,
                                'price_after_discount' => $item->discounted_price ?? $item->price ?? 0,
                            ];
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            if (empty($this->barcodes)) {
                session()->flash('error', 'فشل في توليد الباركودات. تحقق من صحة البيانات');
            } else {
                session()->flash('message', "تم توليد {$totalBarcodes} باركود بنجاح");
                $this->dispatch('barcodesGenerated');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء توليد الباركودات');
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearBarcodes()
    {
        $this->barcodes = [];
        session()->flash('message', 'تم مسح الباركودات');
    }

    public function resetCounts()
    {
        foreach ($this->items as $item) {
            $this->barcodeCounts[$item->id] = max(1, intval($item->qty_in));
        }
        session()->flash('message', 'تم إعادة تعيين العدادات للقيم الافتراضية');
    }

    public function setAllCounts($count = 1)
    {
        $count = max(0, intval($count));
        foreach ($this->items as $item) {
            $this->barcodeCounts[$item->id] = $count;
        }
        session()->flash('message', "تم تعيين جميع العدادات إلى {$count}");
    }

    private function generateBarcodeData($item)
    {
        if (!empty($item->item->code)) {
            return $item->item->code;
        }

        if (!empty($item->item->barcode)) {
            return $item->item->barcode;
        }

        return 'ITEM-' . str_pad($item->item_id, 6, '0', STR_PAD_LEFT);
    }

    private function truncateText($text, $maxLength = 30)
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength - 3) . '...';
    }

    public function getTotalBarcodesProperty()
    {
        return array_sum($this->barcodeCounts);
    }

    public function getItemsWithBarcodesProperty()
    {
        return collect($this->barcodeCounts)->filter(function ($count) {
            return $count > 0;
        })->count();
    }

    #[On('refresh-items')]
    public function refreshItems()
    {
        $this->loadItems();
    }

    public function render()
    {
        return view('livewire.reports.barcode-printing-report', [
            'items' => $this->items,
            'barcodes' => $this->barcodes,
            'totalBarcodes' => $this->totalBarcodes,
            'itemsWithBarcodes' => $this->itemsWithBarcodes,
        ]);
    }
}
