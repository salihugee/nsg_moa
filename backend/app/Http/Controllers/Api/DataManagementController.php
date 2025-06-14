<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Farm;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;

class DataManagementController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/data/import",
     *     summary="Import data from CSV files",
     *     tags={"Data Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="file",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     enum={"farmers", "farms", "projects"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data imported successfully"
     *     )
     * )
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'type' => 'required|in:farmers,farms,projects'
        ]);

        $file = $request->file('file');
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        DB::beginTransaction();
        try {
            $records = $csv->getRecords();
            
            switch($request->type) {
                case 'farmers':
                    foreach ($records as $record) {
                        Farmer::create([
                            'registration_number' => $record['registration_number'],
                            'full_name' => $record['full_name'],
                            'phone_number' => $record['phone_number'],
                            'location' => DB::raw("ST_SetSRID(ST_MakePoint({$record['longitude']}, {$record['latitude']}), 4326)"),
                            'farm_size' => $record['farm_size'],
                            'status' => $record['status']
                        ]);
                    }
                    break;

                case 'farms':
                    foreach ($records as $record) {
                        Farm::create([
                            'farmer_id' => $record['farmer_id'],
                            'name' => $record['name'],
                            'boundaries' => $record['boundaries'], // Expects GeoJSON
                            'soil_type' => $record['soil_type'],
                            'water_source' => $record['water_source']
                        ]);
                    }
                    break;

                case 'projects':
                    foreach ($records as $record) {
                        Project::create([
                            'name' => $record['name'],
                            'description' => $record['description'],
                            'start_date' => $record['start_date'],
                            'end_date' => $record['end_date'],
                            'budget' => $record['budget'],
                            'status' => $record['status'],
                            'coverage_area' => $record['coverage_area'] // Expects GeoJSON
                        ]);
                    }
                    break;
            }

            DB::commit();
            return response()->json(['message' => 'Data imported successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/data/export/{type}",
     *     summary="Export data to CSV",
     *     tags={"Data Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"farmers", "farms", "projects"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CSV file download"
     *     )
     * )
     */
    public function export($type)
    {
        $csv = Writer::createFromString();

        switch($type) {
            case 'farmers':
                $farmers = Farmer::all();
                $csv->insertOne(['registration_number', 'full_name', 'phone_number', 'latitude', 'longitude', 'farm_size', 'status']);
                
                foreach ($farmers as $farmer) {
                    $location = DB::select("SELECT ST_X(location) as lng, ST_Y(location) as lat FROM farmers WHERE id = ?", [$farmer->id])[0];
                    $csv->insertOne([
                        $farmer->registration_number,
                        $farmer->full_name,
                        $farmer->phone_number,
                        $location->lat,
                        $location->lng,
                        $farmer->farm_size,
                        $farmer->status
                    ]);
                }
                break;

            case 'farms':
                $farms = Farm::all();
                $csv->insertOne(['farmer_id', 'name', 'boundaries', 'soil_type', 'water_source']);
                
                foreach ($farms as $farm) {
                    $boundaries = $farm->boundaries_geojson;
                    $csv->insertOne([
                        $farm->farmer_id,
                        $farm->name,
                        json_encode($boundaries),
                        $farm->soil_type,
                        $farm->water_source
                    ]);
                }
                break;

            case 'projects':
                $projects = Project::all();
                $csv->insertOne(['name', 'description', 'start_date', 'end_date', 'budget', 'status', 'coverage_area']);
                
                foreach ($projects as $project) {
                    $coverage = $project->coverage_area_geojson;
                    $csv->insertOne([
                        $project->name,
                        $project->description,
                        $project->start_date,
                        $project->end_date,
                        $project->budget,
                        $project->status,
                        json_encode($coverage)
                    ]);
                }
                break;

            default:
                return response()->json(['message' => 'Invalid export type'], 400);
        }

        $filename = $type . '_' . now()->format('Y-m-d_His') . '.csv';
        Storage::put('exports/' . $filename, $csv->getContent());

        return response()->download(storage_path('app/exports/' . $filename))->deleteFileAfterSend();
    }
}
