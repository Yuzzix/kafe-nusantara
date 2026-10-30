<?php

namespace Database\Seeders;

use App\Models\DiningTable;
use Illuminate\Database\Seeder;

class DiningTableSeeder extends Seeder
{
    public function run(): void
    {
        // [number, capacity, area]
        $tables = [
            ['T01', 2, 'Indoor'], ['T02', 2, 'Indoor'], ['T03', 4, 'Indoor'], ['T04', 4, 'Indoor'],
            ['T05', 4, 'Indoor'], ['T06', 6, 'Indoor'],
            ['T07', 2, 'Outdoor'], ['T08', 4, 'Outdoor'], ['T09', 4, 'Outdoor'], ['T10', 6, 'Outdoor'],
            ['VIP1', 8, 'VIP'], ['VIP2', 10, 'VIP'],
        ];

        foreach ($tables as [$number, $capacity, $area]) {
            DiningTable::create([
                'number' => $number,
                'capacity' => $capacity,
                'area' => $area,
                // VIP2 is booked for tonight. "occupied" is set later by OrderSeeder
                // based on the actual active orders, so the data stays consistent.
                'status' => $number === 'VIP2' ? DiningTable::STATUS_RESERVED : DiningTable::STATUS_AVAILABLE,
            ]);
        }
    }
}
