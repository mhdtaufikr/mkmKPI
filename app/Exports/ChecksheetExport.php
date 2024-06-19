<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;
use Illuminate\Support\Carbon;

class ChecksheetExport implements FromView, ShouldAutoSize
{
    protected $month;

    public function __construct($month)
    {
        $this->month = Carbon::parse($month)->format('m');
    }

    public function view(): View
    {
        $checksheetData = DB::table('checksheet_headers as ch')
            ->leftJoin('mst_checksheet_sections as ms', 'ch.section_id', '=', 'ms.id')
            ->leftJoin('checksheet_details as cd', 'ch.id', '=', 'cd.header_id')
            ->leftJoin('mst_shops as ms_shop', 'cd.shop_id', '=', 'ms_shop.id')
            ->leftJoin('checksheet_productions as cp', 'cd.id', '=', 'cp.detail_id')
            ->leftJoin('mst_models as mm', 'cp.model_id', '=', 'mm.id')
            ->leftJoin('checksheet_not_goods as cng', 'cp.id', '=', 'cng.production_id')
            ->leftJoin('mst_models as mm_ng', 'cng.model_id', '=', 'mm_ng.id')
            ->leftJoin('checksheet_downtimes as cdw', 'cp.id', '=', 'cdw.production_id')
            ->leftJoin('mst_downtime_causes as mdc', 'cdw.cause_id', '=', 'mdc.id')
            ->whereMonth('ch.date', $this->month)
            ->orderBy('ch.id')
            ->select(
                'ch.date',
                'ch.shift',
                'ms.section_name',
                'ch.sub_section',
                'ms_shop.shop_name',
                'mm.model_name as production_model_name',
                'cd.pic as detail_pic',
                'cd.planning_manpower',
                'cd.actual_manpower',
                'cp.planning_production',
                'cp.actual_production',
                'cp.balance',
                'mdc.category as downtime_cause_category',
                'cdw.problem',
                'cdw.action',
                'cdw.time_from',
                'cdw.time_to',
                'mm_ng.model_name as not_good_model_name',
                'cng.quantity',
                'cng.repair',
                'cng.reject',
                'cng.total',
                'cng.remark as not_good_remark',
                'ch.created_at',
                'ch.updated_at'
            )
            ->get();
        return view('exports.checksheet', [
            'checksheetData' => $checksheetData
        ]);
    }
}

