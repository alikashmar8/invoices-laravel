<?php

namespace App\Exports;

use App\Enums\DiscountType;
use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

use App\Models\User;

class InvoicesExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id)
    {
        $this->id = $id;
    }
    public function headings(): array
    {
        return [

            'ID', 'Reference_number', 'Title', 'Total', 'GST', 'Discount', 'Discount_type', 
            'Paid' ,  'Due_date' , 'Payment_date' , 'Notes' , 'Created_by' ,   'Created_at'
        ];
    }
    public function collection()
    {
        $invoices = Invoice::select('id', 'reference_number', 'title', 'total', 'gst', 'discount', 'discount_type',
        'is_paid' ,  'due_date' , 'payment_date' , 'notes' , 'created_by'  , 'created_at')
        ->where('business_id' , $this->id)->get();
        foreach($invoices as $inv){
            if($inv->is_paid == 1) {$inv->is_paid = 'Yes';} else {$inv->is_paid = 'No';}
            if($inv->discount_type == DiscountType::PERCENTAGE) {$inv->discount_type = 'Percentage';} else {$inv->discount_type = 'Amount';}
            //TODO: I think it can be done this way: $inv->created_by = $inv->created_by->name
            // to be tested
            $inv->created_by = User::findOrFail($inv->created_by)->name;
        }
        return $invoices;// Invoice::where('business_id' , $id);
    }
}
