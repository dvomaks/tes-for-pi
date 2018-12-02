<?php

namespace App\Http\Controllers;

use App\TransferLog;
use App\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbuseController extends Controller
{
    public function generateReport()
    {

        TransferLog::truncate();

        $faker = Faker::create();

        $users = User::all();

        $month6 = Carbon::now()->subMonth(6)->format('Y-m') . '-1 00:00:00';

        $logArr = [];

        foreach ($users as $u) {
            for ($i = 6; $i > 0; $i--) {
                $startDate = Carbon::now()->subMonth(6 - $i)->format('Y-m') . '-1 00:00:00';
                $endDate = Carbon::now()->subMonth(6 - $i - 1)->format('Y-m') . '-1 00:00:00';
                if ($i === 6) {
                    $endDate = 'now';
                }

                $logArr[] = [
                    'user_id' => $u->id,
                    'transfer_date' => $faker->dateTimeBetween($startDate, $endDate),
                    'resource' => $faker->url,
                    'transferred_bytes' => $faker->numberBetween(100, 1000000000000)
                ];
            }

            $randomLine = mt_rand(50, 500);
            for ($j = 0; $j < $randomLine; $j++) {
                $logArr[] = [
                    'user_id' => $u->id,
                    'transfer_date' => $faker->dateTimeBetween($month6, 'now'),
                    'resource' => $faker->url,
                    'transferred_bytes' => $faker->numberBetween(100, 1000000000000)
                ];
            }
        }

        $logArr500Items = array_chunk($logArr, 500);

        foreach ($logArr500Items as $arr) {
            TransferLog::insert($arr);
        }

        return response()->json(true);

    }

    public function showReport(Request $request)
    {
        $post = $this->validate($request, [
            'year' => 'required|integer',
            'month' => 'required|integer'
        ]);

        $report = TransferLog::whereYear('transfer_date', $post['year'])
            ->whereMonth('transfer_date', $post['month'])
            ->leftJoin('users', 'transfer_logs.user_id', '=', 'users.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->select('companies.name', 'companies.quota_bytes', DB::raw('SUM(transfer_logs.transferred_bytes) as total_bytes'))
            ->groupBy('companies.id')
            ->havingRaw('SUM(transfer_logs.transferred_bytes) > companies.quota_bytes')
            ->get();

        return response()->json($report);
    }
}
