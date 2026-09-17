echo json_encode(DB::connection('poa_prod')->select('DESCRIBE proyectos'));
