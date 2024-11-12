<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SimData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SimDataController extends Controller
{

    public function uploadProviderExcel(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv',
    ]);

    $filePath = $request->file('file')->store('uploads');
    $spreadsheet = IOFactory::load(Storage::path($filePath));
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();

    $newEntries = [];
    $updatedEntries = [];
    
    // First, collect all data from Excel
    for ($row = 2; $row <= $highestRow; $row++) {
        $simNumber = $worksheet->getCell('C' . $row)->getValue();
        $rowData = $this->getRowData($worksheet, $row);
        
        // Check if SIM exists
        $existingSim = SimData::where('sim_number', $simNumber)->first();
        
        if ($existingSim) {
            $updatedEntries[] = [
                'sim' => $existingSim,
                'data' => $rowData
            ];
        } else {
            $newEntries[] = $rowData;
        }
    }

    // Process updates and new entries
    $this->processSimEntries($newEntries, $updatedEntries);

    return response()->json([
        'message' => 'File processed successfully',
        'new_entries' => count($newEntries),
        'updated_entries' => count($updatedEntries)
    ]);
}

private function getRowData($worksheet, $row)
{
    return [
        'network' => $worksheet->getCell('A' . $row)->getValue(),
        'product' => $worksheet->getCell('B' . $row)->getValue(),
        'sim_number' => $worksheet->getCell('C' . $row)->getValue(),
        'mobile_number' => $worksheet->getCell('D' . $row)->getValue(),
        'customer_name' => $worksheet->getCell('E' . $row)->getValue(),
        'sage_customer_number' => $worksheet->getCell('F' . $row)->getValue(),
        'date_of_sale' => $worksheet->getCell('G' . $row)->getValue(),
        'customer_buy_price' => $worksheet->getCell('H' . $row)->getValue(),
        'customer_commission_due' => $worksheet->getCell('I' . $row)->getValue(),
        'topup2_bonus' => $worksheet->getCell('J' . $row)->getValue(),
        'topup3_bonus' => $worksheet->getCell('K' . $row)->getValue(),
        'topup4_bonus' => $worksheet->getCell('L' . $row)->getValue(),
        'topup5_bonus' => $worksheet->getCell('M' . $row)->getValue(),
        'topup6_bonus' => $worksheet->getCell('N' . $row)->getValue(),
        'topup7_bonus' => $worksheet->getCell('O' . $row)->getValue(),
        'topup8_bonus' => $worksheet->getCell('P' . $row)->getValue(),
        'topup9_bonus' => $worksheet->getCell('Q' . $row)->getValue(),
        'topup10_bonus' => $worksheet->getCell('R' . $row)->getValue(),
        'topup11_bonus' => $worksheet->getCell('S' . $row)->getValue(),
        'topup12_bonus' => $worksheet->getCell('T' . $row)->getValue(),
        'end_user_first_name' => $worksheet->getCell('U' . $row)->getValue(),
        'end_user_last_name' => $worksheet->getCell('V' . $row)->getValue(),
        'end_user_address' => $worksheet->getCell('W' . $row)->getValue(),
        'end_user_postcode' => $worksheet->getCell('X' . $row)->getValue(),
        'invoiced' => $worksheet->getCell('Y' . $row)->getValue() === '1',
        'master_carton' => $worksheet->getCell('Z' . $row)->getValue(),
        'revenue_share_month1_percent' => $worksheet->getCell('AA' . $row)->getValue(),
        'revenue_share_month2_percent' => $worksheet->getCell('AB' . $row)->getValue(),
        'revenue_share_month3_percent' => $worksheet->getCell('AC' . $row)->getValue(),
        'revenue_share_month4_percent' => $worksheet->getCell('AD' . $row)->getValue(),
        'revenue_share_month5_percent' => $worksheet->getCell('AE' . $row)->getValue(),
        'revenue_share_month6_percent' => $worksheet->getCell('AF' . $row)->getValue(),
        'revenue_share_month7_percent' => $worksheet->getCell('AG' . $row)->getValue(),
        'revenue_share_month8_percent' => $worksheet->getCell('AH' . $row)->getValue(),
        'revenue_share_month9_percent' => $worksheet->getCell('AI' . $row)->getValue(),
        'revenue_share_month10_percent' => $worksheet->getCell('AJ' . $row)->getValue(),
        'revenue_share_month11_percent' => $worksheet->getCell('AK' . $row)->getValue(),
        'revenue_share_month12_percent' => $worksheet->getCell('AL' . $row)->getValue(),
    ];
}

private function processSimEntries($newEntries, $updatedEntries)
{
    // Process new entries
    foreach ($newEntries as $entry) {
        SimData::create($entry);
    }

    // Process updates
    foreach ($updatedEntries as $entry) {
        $entry['sim']->update($entry['data']);
    }
}


    public function showexcel()
    {
        $data = SimData::all();
        return response()->json($data);
    }
}
