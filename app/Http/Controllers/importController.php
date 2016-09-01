<?php

namespace App\Http\Controllers;

use App\Company_text;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\CompanyTextRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Company;
use App\Postcode;
use Excel;
use DB;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use PHPExcel_Cell_IValueBinder;
use PHPExcel_Cell_DefaultValueBinder;
$insert = "test";

class MyValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
	/**
	 * @param PHPExcel_Cell $cell
	 * @param null $value
	 * @return bool
	 * @throws \PHPExcel_Exception
     */
	public function bindValue(PHPExcel_Cell $cell, $value = null)
	{
		$cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);

		return true;
	}
}
class ImportController extends Controller
{
	private $insert;

	public function __construct() {
		$insert = "INIT!";
	}
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function import()
    {
        return view('import');
    }
    
    public function processImport(Request $request)
	{
		global $insert;
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 300);
		if ($request->hasFile('excel_file')) {
			$path = $request->file('excel_file')->getRealPath();
			$myValueBinder = new MyValueBinder;
			$data = Excel::setValueBinder($myValueBinder)->load($path, "UTF-8")->get();

			if (!empty($data) && $data->count()) {
				foreach ($data as $key => $value) {
					$insert[] = [
						'name' => $value->naam,
						'student_nr' => $value->studentnummer,
						'class' => $value->klas,
						'preference_1' => $value->voorkeur1,
						'preference_2' => $value->voorkeur2,
						'preference_3' => $value->voorkeur3,
						'preference_4' => $value->voorkeur4,
						'preference_5' => $value->voorkeur5,
					];
				}

				if (!empty($insert)) {
					///continue
					$sports[] = [
						"1. Breakdance",
						"2. Fietstocht (neem je eigen fiets mee)",
						"3. Fitness",
						"4. Crossfit/bootcamp",
						"5. Kickboksen",
						"6. Spinning",
						"7. Hardlopen (beginners en gevorderden)",
						"8. Beachvolleybal",
						"9. Stadswandeling",
						"10. Casting",
						"11. Unihockey",
						"12. 12. Roeien (zorg dat je op de fiets komt of bij iemand achterop kunt; je moet kunnen zwemmen)",
						"13. Bamboebouwen",
						"14. Voetbal",
						"15. In Control",
						"16. Zumba/Core training/Aerobics",
						"17. Bootcamp met Dirk-Jan Bartels",
						"18. Yoga",
						"19. Dans workshop",
						"20. Trampoline",
						"21. Balancebikes",
						"22. Bootcamp met Monica",
						"23. Vechtsport",
						"24. Weerbaarheid",
						"25. Pannakooi",
						"26. Roparun Light",
						"27. Drama workshop",
						"28. DJ Workshop",
						"29. Muziek workshop",
						"30. Design workshop",
						"31. Curves",
						"32. Tai Chi",
						"33. Vlog WeHelpen",
					];

					Excel::create('Filename', function ($excel) {
						$excel->setTitle("Rooster life style day");
						$excel->setCreator("N. van Driel");
						$excel->setDescription("Rooster life style day");

						$excel->sheet("rooster", function ($sheet) {
							global $insert;
							$sheet->row(1, array('Studentnummer', 'Naam', "Klas", "voorkeuren >>>"));
							$index = 2;
							foreach ($insert as $set_result) {
								$sheet->row($index, array($set_result["student_nr"], ucfirst($set_result["name"]), $set_result["class"]));
								$index++;
							}


						});
					})->export('xls');
				}
			}
		}
	}
}
