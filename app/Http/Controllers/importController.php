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
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function import(Request $request)
    {
        return view('import');
    }
    
    public function processImport(Request $request)
    {
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 300);
        if($request->hasFile('excel_file')){
			$path = $request->file('excel_file')->getRealPath();
			$myValueBinder = new MyValueBinder;
			$data = Excel::setValueBinder($myValueBinder)->load($path, "UTF-8")->get();

			if(!empty($data) && $data->count()){
				foreach ($data as $key => $value) {
					if (!empty($value->name)){
						$insert[] = [
							'name' => $value->naam,
							'student_nr' => $value->Studentnummer,
							'class' => $value->klas,
							'preference_1' => $value->voorkeur1,
							'preference_2' => $value->voorkeur2,
							'preference_3' => $value->voorkeur3,
							'preference_4' => $value->voorkeur4,
							'preference_5' => $value->voorkeur5,
						];
					}
				}

				if(!empty($insert)){
					///continue
					$sports[] = [
						"1. Breakdance",
						"2. Fietstocht",
						"3. Fitness",
						"4. Crossfit/bootcamp",
						"5. Kickboksen",
						"6. Spinning",
						"7. Hardlopen",
						"8. Beachvolleybal",
						"9. Stadswandeling",
						"10. Casting",
						"11. Unihockey",
						"12. Roeien",
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
					print_r($insert);exit;







					// ask the service for a Excel5
					$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

					$phpExcelObject->getProperties()->setCreator("kopadmin")
						->setLastModifiedBy("KopAdmin")
						->setTitle("Exported Activity ".$year)
						->setSubject("")
						->setDescription("Exported Activity ".$year)
						->setKeywords("Activity kopadmin")
						->setCategory("Export");
					$phpExcelObject->setActiveSheetIndex(0);

					$alphabet = array(1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e', 6 => 'f', 7 => 'g', 8 => 'h', 9 => 'i', 10 => 'j',11 => 'k', 12 => 'l', 13 => 'm', 14 => 'n', 15 => 'o', 16 => 'p', 17 => 'q', 18 => 'r', 19 => 's', 20 => 't', 21 => 'u', 22 => 'v', 23 => 'w', 24 => 'x', 25 => 'y', 26 => 'z',);

						$phpExcelObject->createSheet();
						$phpExcelObject->setActiveSheetIndex(0);
						$phpExcelObject->getActiveSheet()->setTitle("Rooster");
						$sheet = $phpExcelObject->getActiveSheet(0);

						//////////////////////////////////////////////////////////////////////////////////////////////////// Add headers
						$sheet->setCellValue("A1", "Studentennummer");

						$index_AcT = 2;
						foreach ($users as $user) {
							$sheet->setCellValue(strtoupper($alphabet[$index_AcT]) . "1", ucfirst($user['username']));
							$index_AcT++;
						}
						$sheet->setCellValue(strtoupper($alphabet[$index_AcT]) . "1", 'Totaal per type');

						$index_user = 2;
						foreach ($Activity_templates as $Activity_template) {
							$sheet->setCellValue("A" . $index_user, $Activity_template['name']);
							$index_user++;
						}
						$sheet->setCellValue("A" . $index_user, "Totaal per gebruiker");


					$phpExcelObject->getActiveSheet()->getColumnDimension('A')->setWidth(50);
					$phpExcelObject->getActiveSheet()->getColumnDimension(strtoupper($alphabet[count($users)+2]))->setWidth(15);

					// create the writer
					$phpExcelObject->setActiveSheetIndex(0);
					$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
					// create the response
					$response = $this->get('phpexcel')->createStreamedResponse($writer);
					// adding headers
					$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
					$response->headers->set('Content-Disposition', 'attachment;filename=activity-export-'.$year.'.xls');

					return $response;






				}
			}
		}

		return back();
    }
}
