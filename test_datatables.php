<?php
// Quick test of DataTables endpoint
require_once 'bootstrap/app.php';

$app = app();
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Test the controller directly
try {
    $controller = new \App\Http\Controllers\BandwidthBuy\BandwidthReportController();
    
    // Create a mock request
    $request = \Illuminate\Http\Request::create('/bandwidth-buy/report/datatables', 'GET', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'search' => ['value' => ''],
    ]);
    
    // Call the method
    $response = $controller->datatables($request);
    
    echo "✓ DataTables endpoint working!\n";
    echo "Response type: " . get_class($response) . "\n";
    
    // Decode and show sample of response
    $data = json_decode($response->getContent(), true);
    echo "Draw: " . ($data['draw'] ?? 'N/A') . "\n";
    echo "Total Records: " . ($data['recordsTotal'] ?? 'N/A') . "\n";
    echo "Filtered Records: " . ($data['recordsFiltered'] ?? 'N/A') . "\n";
    echo "Data rows: " . count($data['data'] ?? []) . "\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
