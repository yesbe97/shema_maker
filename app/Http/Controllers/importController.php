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
	//set global variables
	private $insert;
	private $sports;
	private $capacity;
	private $applicant_a_round;
	private $alphabet;

	public function __construct() {
		//set global variables
		$insert = "";
		$sports = "";
		$capacity = "";
		$applicant_a_round = "";
		$alphabet = "";
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
		global $sports;
		global $capacity;
		global $alphabet;

		//Some config for time and memory use
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 300);


		//Check if the file is a excel file
		if ($request->hasFile('excel_file')) {
			//catch the data from the file
			$path = $request->file('excel_file')->getRealPath();
			$myValueBinder = new MyValueBinder;
			$data = Excel::setValueBinder($myValueBinder)->load($path, "UTF-8")->get();

			//Check if there is data recieved
			if (!empty($data) && $data->count()) {
				foreach ($data as $key => $value) {
					//Put the data in a clean and in a better readable array
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

				//Dubble check if the data is stored
				if (!empty($insert)) {
					//Make a array with all the types of sports.
					//todo *idea* make a import for the sport types
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
					];

					//Make a array with all the capacity of all the sport types
					//"" means there is no capacity, there will only be one round for the corresponding sport type
					//todo *idea* make a import for the sport capacity
					$capacity[] = ["", "", 15, 20, 20, 11, "", 24, 15, 10, 24, 8, 16, 5, 15, 20, 20, 20, 20, 5, 5, 15, 15, 15, 16, 16, 15, 5, 15, 15, 14, 10,];

					//Make a array with all the letters of the alphabet. This will be used later where I will need to set data to a specific row and column combo.
					$alphabet = array(
						1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e', 6 => 'f', 7 => 'g', 8 => 'h', 9 => 'i', 10 => 'j',
						11 => 'k', 12 => 'l', 13 => 'm', 14 => 'n', 15 => 'o', 16 => 'p', 17 => 'q', 18 => 'r', 19 => 's', 20 => 't',
						21 => 'u', 22 => 'v', 23 => 'w', 24 => 'x', 25 => 'y', 26 => 'z',
					);


					$rooster_array = [];
					$round_array = [];
					$rooster_array = array_fill_keys($sports[0], "");

					foreach ($sports[0] as $sport) {
						$round_array= array("Round 1", "Round 2", "Round 3", "Round 4", "Round 5");
						$rooster_array[$sport] = array_fill_keys($round_array, "");
					}


					$index = 2;
					//Foreach loop to loop through all the sport types
					foreach ($sports[0] as $sport){

						//Some new vars to be defined
						$applicant_a_round = "";
						$round_count = 0;
						$column = 2;

						//Start reading the applicants there data
						foreach ($insert as $set_result) {

							//Check if the round capacity has been reached
							$cap_for_round = $capacity[0][($index-2)];
							if ($round_count >= $cap_for_round && $cap_for_round != ""){
								$applicant_a_round = "";
								$round_count = "";
								$column++;
							}

							//Check if the applicants preference is the same as the sport
							if (isset($set_result["preference_1"]) && $set_result["preference_1"] == $sport) {
//							if (isset($set_result["preference_".($column-1)]) && $set_result["preference_".($column-1)] == $sport) {
								//Create a name(number), list for in the round
								$applicant_a_round .= ucfirst($set_result["name"]) . "(" . $set_result["student_nr"] . "), ";
								$rooster_array[$sport]["Round ".($column-1)] = $applicant_a_round;
								$round_count++;
							}
						}
						//Add 1 to this var to go to the next sport type
						$index++;
					}
var_dump($rooster_array);exit;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




					//Start the creation of the excel file that contains the rooster
					Excel::create('Rooster life style day', function ($excel) {
						//Set some properties
						$excel->setTitle("Rooster life style day");
						$excel->setCreator("N. van Driel");
						$excel->setDescription("Rooster life style day");

						//Create a tab
						$excel->sheet("rooster", function ($sheet) {
							global $insert;
							global $sports;
							global $capacity;
							global $alphabet;
							global $applicant_a_round;

							//Set the headers
							$sheet->row(1, array('Sport', 'Ronde 1', "Ronde 2", "Ronde 3", "Ronde 4", "Ronde 5"));

							//Some new vars to be defined
							$index = 2;

							//Foreach loop to loop through all the sport types
							foreach ($sports[0] as $sport){

								//Set sport name in on the most left row
								$sheet->row($index, array($sport));

								//Some new vars to be defined
								$applicant_a_round = "";
								$round_count = 0;
								$column = 2;

								//Start reading the applicants there data
								foreach ($insert as $set_result) {

									//Check if the round capacity has been reached
									$cap_for_round = $capacity[0][($index-2)];
									if ($round_count >= $cap_for_round && $cap_for_round != ""){
										$applicant_a_round = "";
										$round_count = "";
										$column++;
									}

									//Check if the applicants preference is the same as the sport
									if (isset($set_result["preference_".($column-1)]) && $set_result["preference_".($column-1)] == $sport) {

										//Create a name(number), list for in the round
										$applicant_a_round .= ucfirst($set_result["name"]) . "(" . $set_result["student_nr"] . "), ";






										//Add the list of names to the corresponding column/row combo
										//Here the alphabet array is used here a example of the function call:
										//$sheet->cell(B3, function($cell) {
//										$sheet->cell($alphabet[$column].$index, function($cell) {
//											global $applicant_a_round;
//											$cell->setValue($applicant_a_round);
//											// todo: Now every insert result the system will write the $applicant_a_round value to the new excel file. There needs to be a change so it will only write the data when there are no more insert results or if the next round starts.
//										});
										//Add 1 to this var to go to the next round
										$round_count++;
									}
								}
								//Add 1 to this var to go to the next sport type
								$index++;
							}
						});
					//Return the excel file. It downloads automatically
					})->export('xls');
				}
			}
		}
	}
}
