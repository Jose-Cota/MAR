<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mapping = [
    // 1 to 28 are correct
    874 => 3, 884 => 4, 886 => 5, 883 => 6, 888 => 7, 879 => 8, 897 => 9,
    882 => 10, 875 => 11, 881 => 12, 893 => 13, 880 => 14, 876 => 15, 878 => 16,
    896 => 17, 898 => 18, 912 => 19, 904 => 20, 905 => 21, 889 => 22, 890 => 23,
    899 => 24, 894 => 25, 887 => 26, 877 => 27, 891 => 28, 
    
    // 29 to 42 need to be shifted +3 to restore original positions 32 to 45
    892 => 29, 895 => 30, 907 => 31, 873 => 32, 885 => 33, 901 => 34, 902 => 35,
    906 => 36, 900 => 37, 903 => 38, 908 => 39, 909 => 40, 911 => 41, 910 => 42
];

DB::connection('poa_prod')->beginTransaction();
try {
    $count = 0;
    foreach ($mapping as $id => $oldNum) {
        if ($oldNum >= 29) {
            $newNum = $oldNum + 3;
        } else {
            $newNum = $oldNum;
        }
        
        $paddedNum = str_pad($newNum, 2, '0', STR_PAD_LEFT);
        
        DB::connection('poa_prod')->table('proyectos')
            ->where('proyecto_id', $id)
            ->update(['numero' => $paddedNum]);
            
        $count++;
    }
    
    DB::connection('poa_prod')->commit();
    echo "Success! Restored and shifted $count projects to their correct global sequence.\n";
} catch (\Exception $e) {
    DB::connection('poa_prod')->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
