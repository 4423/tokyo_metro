<?php

/*

API読み込みと同時に日本語化するようにしたい

*/

class metro{
	private $token 	= '5dd14250f3a9800f224dff10be83fe71a4d4f8d803e340f7e4422775978a97b5';
	private $baseUrl  = 'https://api.tokyometroapp.jp/api/v2/';

	public function datapoints( $prms ){
		$apiName = "datapoints";
		$apiUrl  = $this->baseUrl.$apiName;

		/*パラメータ一覧(必須)
		rdf:type(必須)
		acl:consumerKey(必須)
		PREDICATE
		*/

		$result = self::get( $apiUrl,$prms );

		switch ( $prms['rdf:type'] ) {
			case 'odpt:StationTimetable':
				return self::StationTimetable( $result );
				break;
			case 'odpt:StationFacility':
				break;
			default:
				# code...
				break;
		}

		return $result;
	}

	public function places( $prms ){
		$apiName = "places";
		$apiUrl  = $this->baseUrl.$apiName;	

		/*パラメータ一覧(必須)
		rdf:type(必須)
		lat(必須)
		lon(必須)
		radius(必須)
		acl:consumerKey(必須)
		PREDICATE
		*/
		return self::get( $apiUrl,$prms );
	}

	public function prms( $datas ){
		$temp = "";
		foreach( $datas as $key=>$data ){
			$temp .= ( !$temp )?"?":"&";
			$temp .= $key."=".urlencode($data);
		}
		return $temp;
	}

	public function get( $apiUrl,$prms ){
		$prms['acl:consumerKey'] = $this->token;
		$prms = self::prms( $prms );
		$json = file_get_contents( $apiUrl.$prms );
		$data = json_decode( $json );
		return $data;
	}

//==========================================================================================================
//==========================================================================================================
//==========================================================================================================


	// 日本語の列車所有会社取得
	public function train_owner_jp($english) {
		$json = file_get_contents("./tokyo_metro_json/metro_train_ownerDict.json");
		$data = json_decode($json);

		foreach ($data as $key=>$value) {
			if( $key === $english ){
				return $value;
			}
		}
		return $english;
	}

	// 日本の列車種別取得
	public function train_type_jp($english) {
		$json = file_get_contents("./tokyo_metro_json/metro_train_typeDict.json");
		$data = json_decode($json);
		foreach ($data as $key=>$value) {
			if( $key === $english ){
				return $value;
			}
		}
		return $english;
	}

	// 日本語の路線名取得
	public function railway_jp($english){
  		$json = file_get_contents("./tokyo_metro_json/metro_railwayDict.json");
  		$data = json_decode($json);
		foreach ($data as $key=>$value) {
			if( $key === $english ){
				return $value;
			}
		}
		return $english;
 	}

 	//方向
 	public function rail_direction_jp($english) {
 		$json = file_get_contents("./tokyo_metro_json/metro_rail_directionDict.json");
 		$data = json_decode($json);
		foreach ($data as $key=>$value) {
			if( $key === $english ){
				return $value;
			}
		}
		return $english;
 	}

 	// 日本語の駅名取得
	public function station_jp($english) {
		$json = file_get_contents("./tokyo_metro_json/metro_stationDict.json");
		$data = json_decode($json);
		foreach ($data as $key=>$value) {
			if( $key === $english ){
				return $value;
			}
		}
		$json = file_get_contents("./tokyo_metro_json/other_stationDict.json");
		$data = json_decode($json);
		foreach ($data as $key=>$value) {
			if( $key === $english ){
				return $value;
			}
		}
		return $english;
	}

//==========================================================================================================
//==========================================================================================================
//==========================================================================================================

	//時刻表
	public function StationTimetable( $result ){

		foreach ($result as $key => $value) {	
			//駅名
			if( $value->{'odpt:station'} ){
				$data = self::cut_word( $value->{'odpt:station'} );
				$result[$key]->{'odpt:station'} = self::station_jp( $data[2] )."駅";
			}
			//路線
			if( $value->{'odpt:railway'} ){
				$data = self::cut_word( $value->{'odpt:railway'} );
				$result[$key]->{'odpt:railway'} = self::railway_jp( $data[1] );
			}
			//社名
			if( $value->{'odpt:operator'} ){
				$data = self::cut_word( $value->{'odpt:operator'} );
				$result[$key]->{'odpt:operator'} = self::train_owner_jp( $data[0] );
			}
			//方面
			if( $value->{'odpt:railDirection'} ){
				$data = self::cut_word( $value->{'odpt:railDirection'} );
				$result[$key]->{'odpt:railDirection'} = self::rail_direction_jp( $data[1] );
			}

			foreach (array('weekdays', 'saturdays','holidays' ) as $day_type) {
				if( $day_type_value = $value->{'odpt:'.$day_type} ){
					foreach ($day_type_value as $key2 => $value2) {
						//社名.路線.方向
						if( $value2->{'odpt:destinationStation'} ){
							$data = self::cut_word( $value2->{'odpt:destinationStation'} );
							$result[$key]->{'odpt:'.$day_type}[$key2]->{'odpt:destinationStation'} = self::train_owner_jp( $data[0] ).'.'.self::railway_jp( $data[1] ).'.'.self::rail_direction_jp( $data[2] );
						}
						//社名.車両タイプ
						if( $value2->{'odpt:trainType'} ){
							$data = self::cut_word( $value2->{'odpt:trainType'} );
							$result[$key]->{'odpt:'.$day_type}[$key2]->{'odpt:trainType'} = self::train_owner_jp( $data[0] ).'.'.self::train_type_jp( $data[1] );
						}
					}
				}
			}

		}

		return $result;
	}

	//運行情報
	public function TrainInformation( $result ){
		return $result;
	}

	//列車ロケーション情報
	public function Train( $result ){
		return $result;
	}

	//駅情報
	public function Station( $result ){

		return $result;
	}

	//駅施設情報 
	public function StationFacility( $result ){
		return $result;
	}

	//駅乗降人員数 
	public function PassengerSurvey( $result ){
		return $result;
	}

	//鉄道路線情報 
	public function Railway( $result ){
		return $result;
	}

	//運賃  
	public function RailwayFare( $result ){
		return $result;
	}

//==========================================================================================================
//==========================================================================================================
//==========================================================================================================

	public function cut_word( $string ){
		preg_match('/.*:(.*)/', $string, $data);
		return explode(".", $data[1]);
	}

}