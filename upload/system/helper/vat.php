<?php
function vat_validation($prefix, $number) {
	$iso_code_2_data = array(
		'AT' => 'AT', //Austria
		'BE' => 'BE', //Belgium
		'BG' => 'BG', //Bulgaria
		'DK' => 'DK', //Denmark
		'FI' => 'FI', //Finland
		'FR' => 'FR', //France
		'FX' => 'FR', //France métropolitaine
		'MC' => 'FR', //Monaco
		'DE' => 'DE', //Germany
		'GR' => 'EL', //Greece
		'IE' => 'IE', //Ireland
		'IT' => 'IT', //Italy
		'LU' => 'LU', //Luxembourg
		'NL' => 'NL', //Netherlands
		'PT' => 'PT', //Portugal
		'ES' => 'ES', //Spain
		'SE' => 'SE', //Sweden
		'GB' => 'GB', //United Kingdom
		'IM' => 'GB', //Isle of Man
		'CY' => 'CY', //Cyprus
		'EE' => 'EE', //Estonia
		'HU' => 'HU', //Hungary
		'LV' => 'LV', //Latvia
		'LT' => 'LT', //Lithuania
		'MT' => 'MT', //Malta
		'PL' => 'PL', //Poland
		'RO' => 'RO', //Romania
		'SK' => 'SK', //Slovakia
		'CZ' => 'CZ', //Czech Republic
		'SI' => 'SI'  //Slovenia
	);

	$number = str_replace(' ', '', $number);

	if (array_search(substr($number, 0, 2), $iso_code_2_data)) {
		$number = substr($number, 2);
	}

	if (array_key_exists($prefix, $iso_code_2_data)) {
		$response = file_get_contents('http://ec.europa.eu/taxation_customs/vies/viesquer.do?ms=' . $iso_code_2_data[$prefix] . '&iso=' . $iso_code_2_data[$prefix] . '&vat=' . $number);

		if (preg_match('/\bvalid VAT number\b/i', $response)) {
			return 'valid';
		} else {
			return 'invalid';
		}
	} else {
		return 'unknown';
	}
}
?>