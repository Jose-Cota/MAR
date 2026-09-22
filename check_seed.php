<?php $seed = json_decode(file_get_contents(__DIR__."/scratch_seed.json"), true); echo implode(", ", array_keys($seed));
