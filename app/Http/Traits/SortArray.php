<?php 
namespace App\Http\Traits;

trait SortArray
{
	/***
    Sort Array via column name
    ***/
	public function MysortArray($array, $sortByKey, $sortDirection){
			$sortArray = array();
		    $tempArray = array();

		    foreach ( $array as $key => $value ) {
		        $tempArray[] = strtolower( $value[ $sortByKey ] );
		    }

		    if($sortDirection=='ASC'){ asort($tempArray ); }
		        else{ arsort($tempArray ); }

		    foreach ( $tempArray as $key => $temp ){
		        $sortArray[] = $array[ $key ];
		    }

		    return $sortArray;
	}
}
?>