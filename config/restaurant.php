<?php

/*
|--------------------------------------------------------------------------
| Restaurant / Cafe Settings
|--------------------------------------------------------------------------
|
| Central place for business rules used when calculating order totals.
| Used by App\Models\Order::recalculateTotals() and the OrderSeeder,
| so the controller, seeder, and views always agree on the numbers.
|
| Access with: config('restaurant.tax_rate')
|
*/

return [

    'name' => env('RESTAURANT_NAME', 'Kafe Nusantara'),

    // PB1 (Pajak Restoran) in Indonesia is generally 10%.
    'tax_rate' => (float) env('RESTAURANT_TAX_RATE', 0.10),

    // Service charge, only applied to dine-in orders.
    'service_rate' => (float) env('RESTAURANT_SERVICE_RATE', 0.05),

    'currency_prefix' => 'Rp ',

];
