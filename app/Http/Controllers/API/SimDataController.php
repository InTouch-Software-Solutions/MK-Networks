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

    // public function uploadProviderExcel(Request $request)
    // {

    //     // Validate the request
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv|max:2048',
    //     ]);

    //     // Store the file
    //     $filePath = $request->file('file')->store('uploads');


    //     // Load the file
    //     $spreadsheet = IOFactory::load(Storage::path($filePath));
    //     $worksheet = $spreadsheet->getActiveSheet();

    //     // Get all rows as an array
    //     $highestRow = $worksheet->getHighestRow();
    //     for ($row = 2; $row <= $highestRow; $row++) { // Start from 2 to skip header row
    //         $simData = new SimData();
    //         $simData->network = $worksheet->getCell('A' . $row)->getValue();
    //         $simData->product = $worksheet->getCell('B' . $row)->getValue();
    //         $simData->sim_number = $worksheet->getCell('C' . $row)->getValue();
    //         $simData->mobile_number = $worksheet->getCell('D' . $row)->getValue();
    //         $simData->customer_name = $worksheet->getCell('E' . $row)->getValue();
    //         $simData->sage_customer_number = $worksheet->getCell('F' . $row)->getValue();
    //         $simData->date_of_sale = $worksheet->getCell('G' . $row)->getValue();
    //         $simData->customer_buy_price = $worksheet->getCell('H' . $row)->getValue();
    //         $simData->customer_commission_due = $worksheet->getCell('I' . $row)->getValue();
    //         $simData->topup2_bonus = $worksheet->getCell('J' . $row)->getValue();
    //         $simData->topup3_bonus = $worksheet->getCell('K' . $row)->getValue();
    //         $simData->topup4_bonus = $worksheet->getCell('L' . $row)->getValue();
    //         $simData->topup5_bonus = $worksheet->getCell('M' . $row)->getValue();
    //         $simData->topup6_bonus = $worksheet->getCell('N' . $row)->getValue();
    //         $simData->topup7_bonus = $worksheet->getCell('O' . $row)->getValue();
    //         $simData->topup8_bonus = $worksheet->getCell('P' . $row)->getValue();
    //         $simData->topup9_bonus = $worksheet->getCell('Q' . $row)->getValue();
    //         $simData->topup10_bonus = $worksheet->getCell('R' . $row)->getValue();
    //         $simData->topup11_bonus = $worksheet->getCell('S' . $row)->getValue();
    //         $simData->topup12_bonus = $worksheet->getCell('T' . $row)->getValue();
    //         $simData->end_user_first_name = $worksheet->getCell('U' . $row)->getValue();
    //         $simData->end_user_last_name = $worksheet->getCell('V' . $row)->getValue();
    //         $simData->end_user_address = $worksheet->getCell('W' . $row)->getValue();
    //         $simData->end_user_postcode = $worksheet->getCell('X' . $row)->getValue();
    //         $simData->invoiced = $worksheet->getCell('Y' . $row)->getValue() === '1'; // Adjust based on actual data
    //         $simData->master_carton = $worksheet->getCell('Z' . $row)->getValue();
    //         $simData->revenue_share_month1_percent = $worksheet->getCell('AA' . $row)->getValue();
    //         $simData->revenue_share_month2_percent = $worksheet->getCell('AB' . $row)->getValue();
    //         $simData->revenue_share_month3_percent = $worksheet->getCell('AC' . $row)->getValue();
    //         $simData->revenue_share_month4_percent = $worksheet->getCell('AD' . $row)->getValue();
    //         $simData->revenue_share_month5_percent = $worksheet->getCell('AE' . $row)->getValue();
    //         $simData->revenue_share_month6_percent = $worksheet->getCell('AF' . $row)->getValue();
    //         $simData->revenue_share_month7_percent = $worksheet->getCell('AG' . $row)->getValue();
    //         $simData->revenue_share_month8_percent = $worksheet->getCell('AH' . $row)->getValue();
    //         $simData->revenue_share_month9_percent = $worksheet->getCell('AI' . $row)->getValue();
    //         $simData->revenue_share_month10_percent = $worksheet->getCell('AJ' . $row)->getValue();
    //         $simData->revenue_share_month11_percent = $worksheet->getCell('AK' . $row)->getValue();
    //         $simData->revenue_share_month12_percent = $worksheet->getCell('AL' . $row)->getValue();
    //         $simData->save();
    //     }

    //     return response()->json(['message' => 'File data uploaded successfully']);
    // }
    
     public function uploadProviderExcel(Request $request)
    {
        // Validate the request
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);
        
        $filePath = $request->file('file')->store('uploads');

        // Load the file
        $spreadsheet = IOFactory::load(Storage::path($filePath));
        $worksheet = $spreadsheet->getActiveSheet();

        // Get all rows as an array
        $highestRow = $worksheet->getHighestRow();
        $duplicateRows = [];

        DB::beginTransaction();
        for ($row = 2; $row <= $highestRow; $row++) { // Start from 2 to skip header row
            $sim_number = $worksheet->getCell('C' . $row)->getValue();

            // Check for duplicates based on sim_number
            if (SimData::where('sim_number', $sim_number)->exists()) {
                $duplicateRows[] = $row;
                continue;
            }

            // Save to database
            $simData = new SimData();
            $simData->network = $worksheet->getCell('A' . $row)->getValue();
            $simData->product = $worksheet->getCell('B' . $row)->getValue();
            $simData->sim_number = $sim_number;
            $simData->mobile_number = $worksheet->getCell('D' . $row)->getValue();
            $simData->customer_name = $worksheet->getCell('E' . $row)->getValue();
            $simData->sage_customer_number = $worksheet->getCell('F' . $row)->getValue();
            $simData->date_of_sale = $worksheet->getCell('G' . $row)->getValue();
            $simData->customer_buy_price = $worksheet->getCell('H' . $row)->getValue();
            $simData->customer_commission_due = $worksheet->getCell('I' . $row)->getValue();
            $simData->topup2_bonus = $worksheet->getCell('J' . $row)->getValue();
            $simData->topup3_bonus = $worksheet->getCell('K' . $row)->getValue();
            $simData->topup4_bonus = $worksheet->getCell('L' . $row)->getValue();
            $simData->topup5_bonus = $worksheet->getCell('M' . $row)->getValue();
            $simData->topup6_bonus = $worksheet->getCell('N' . $row)->getValue();
            $simData->topup7_bonus = $worksheet->getCell('O' . $row)->getValue();
            $simData->topup8_bonus = $worksheet->getCell('P' . $row)->getValue();
            $simData->topup9_bonus = $worksheet->getCell('Q' . $row)->getValue();
            $simData->topup10_bonus = $worksheet->getCell('R' . $row)->getValue();
            $simData->topup11_bonus = $worksheet->getCell('S' . $row)->getValue();
            $simData->topup12_bonus = $worksheet->getCell('T' . $row)->getValue();
            $simData->end_user_first_name = $worksheet->getCell('U' . $row)->getValue();
            $simData->end_user_last_name = $worksheet->getCell('V' . $row)->getValue();
            $simData->end_user_address = $worksheet->getCell('W' . $row)->getValue();
            $simData->end_user_postcode = $worksheet->getCell('X' . $row)->getValue();
            $simData->invoiced = $worksheet->getCell('Y' . $row)->getValue() === '1'; // Adjust based on actual data
            $simData->master_carton = $worksheet->getCell('Z' . $row)->getValue();
            $simData->revenue_share_month1_percent = $worksheet->getCell('AA' . $row)->getValue();
            $simData->revenue_share_month2_percent = $worksheet->getCell('AB' . $row)->getValue();
            $simData->revenue_share_month3_percent = $worksheet->getCell('AC' . $row)->getValue();
            $simData->revenue_share_month4_percent = $worksheet->getCell('AD' . $row)->getValue();
            $simData->revenue_share_month5_percent = $worksheet->getCell('AE' . $row)->getValue();
            $simData->revenue_share_month6_percent = $worksheet->getCell('AF' . $row)->getValue();
            $simData->revenue_share_month7_percent = $worksheet->getCell('AG' . $row)->getValue();
            $simData->revenue_share_month8_percent = $worksheet->getCell('AH' . $row)->getValue();
            $simData->revenue_share_month9_percent = $worksheet->getCell('AI' . $row)->getValue();
            $simData->revenue_share_month10_percent = $worksheet->getCell('AJ' . $row)->getValue();
            $simData->revenue_share_month11_percent = $worksheet->getCell('AK' . $row)->getValue();
            $simData->revenue_share_month12_percent = $worksheet->getCell('AL' . $row)->getValue();
            $simData->save();
        }

        DB::commit();

        if (!empty($duplicateRows)) {
            return response()->json(['message' => 'Some rows were not saved due to duplicate SIM numbers',], 400);
        }

        return response()->json(['message' => 'File data uploaded successfully']);
    }


    public function showexcel()
    {
        $data = SimData::all();
        return response()->json($data);
    }
}
