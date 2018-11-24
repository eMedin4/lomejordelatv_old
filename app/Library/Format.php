<?php
namespace App\Library;

use Carbon\Carbon;

class Format
{

    // EXTRAE EL ID DE /es/film422703.html
	public function faId($value)
	{
		$value = substr($value, 8); //elimina 8 primeros carácteres
		$value = substr($value, 0, -5); //elimina 5 últimos carácteres
		$value = $this->Integer($value);
		return $value;
    }
    
    //ELIMINA STRING SOLO AL FINAL DE UNA CADENA DE TEXTO VALUE
	public function removeString($value, $string)
	{
		$string = preg_quote($string); //escapa los carácteres especiales para que preg_replace los reconozca
		return trim(preg_replace('/' . $string . '$/', '', $value));
    }
    
    // DEVUELVE EL TEXTO SI EXISTE LA CLASE CSS O EL DEFAULT SI NO
	public function getElementIfExist($element, $class, $default) 
	{
		if ($element->filter($class)->count()) {
			return $element->filter($class)->text(); 
		} else {
			return $default;
		}	
    }

    //SI EXISTE UNA KEY DEVOLVEMOS EL VALUE	
	public function getValueIfExist($array, $key)
	{
		if (array_key_exists($key, $array) AND !empty($array[$key])) {
			return $array[$key];
		} else {
			return NULL;
		}
	}
    
    //QUITA PUNTOS Y OTROS CARÁCTERES Y DEVUELVE EL ENTERO
	public function Integer($value)
	{
		return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

	public function float($value)
	{
		return (float) str_replace(',', '.', $value);
	}
	
	public function score($value) {
    	if ($value == '--') {
    		return null;
    	}
    	return $this->float($value);
	}
	
	//LIMPIAMOS EL STRING DE ESPACIOS, SALTOS DE LINEA,...
	public function cleanData($value)
	{
		$value = preg_replace('/\xA0/u', ' ', $value); //Elimina %C2%A0 del principio y resto de espacios
		$value = trim(str_replace(array("\r", "\n"), '', $value)); //elimina saltos de linea al principio y final
		return $value;
	}

	public function removeMarks($string)
	{
		return str_replace([':', '.', ','], '',$string);
	}

	public function checkYears($year1, $year2, $tolerance)
	{
		if (!$year1 || !$year2) return ['response' => false];
		if ($year1 == $year2) return ['response' => true, 'diff' => 0];
		if ($year1 > $year2) $diff = $year1 - $year2;
		if ($year2 > $year1) $diff = $year2 - $year1;
		if ($diff <= $tolerance) return ['response' => true, 'diff' => $diff];
		return ['response' => false];
	}

	public function setMinutesFromHours($duration)
	{
		$duration = str_replace('m', '', $duration);
		if (strpos($duration, 'h') !== false) { //si existen horas
			$duration = explode('h', $duration);
			return ($duration[0] * 60) + $duration[1];
		}
		return $duration; //si no existen horas
	}

	//decodifica carácteres como &rsquo; a sus carácteres normales
	public function decode($string)
	{
		return html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
	}
    
}
