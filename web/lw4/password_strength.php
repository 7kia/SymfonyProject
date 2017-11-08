<?php
header('Content-Type:text/html; charset=utf-8');
const ARG_COUNT = 1;

function getParamFromGetRequest($arg_name)
{
    if(isset($_GET[$arg_name])) {
        return $_GET[$arg_name];
    }
    
    throw new InvalidArgumentException($arg_name);
}

function getEvaluationData($text)
{
	$symbols = preg_split(
		'//u',
		$text,
		-1,
		PREG_SPLIT_NO_EMPTY
	);
    $countValues = array_count_values($symbols);
	$evaluationData = array( 
		"numbers" => 0,
		"lowerCaseSymbols" => 0,
		"upperCaseSymbols" => 0,
		"repeat" => 0,
		"repeatSymbolAmounts" => 0,
	);
	foreach ($countValues as $count) {
		if($count > 1) {
			$evaluationData["repeat"] += $count;
			$evaluationData["repeatSymbolAmounts"] += ($count - 1);
		}
	}
		
	foreach (array_values($symbols) as $symbol) {
		if(is_numeric($symbol)) {
			$evaluationData["numbers"] += 1;
		} else if(ctype_upper((string)$symbol)) {
			$evaluationData["upperCaseSymbols"] += 1;
		} else if(ctype_lower((string)$symbol)) {
			$evaluationData["lowerCaseSymbols"] += 1;
		}  

	}
	print_r($evaluationData);
	return $evaluationData;
}

function getPasswordStrength($password)
{
	if(gettype($password) != string) {
		throw new Exception('В getPasswordStrength передана не строка');
	}
	
	$symbolsAmounts = getEvaluationData($password);

	$strength = 0;
	
	$evaluationValues = array(
		strlen($password) * 4,
		$symbolsAmounts["numbers"] * 4,
		(strlen($password) - $symbolsAmounts["lowerCaseSymbols"]) * 2,
		(strlen($password) - $symbolsAmounts["upperCaseSymbols"]) * 2,
		-$symbolsAmounts["repeat"] * $symbolsAmounts["repeatSymbolAmounts"]
	);
	
	print_r($evaluationValues);
	
	$letterAmount = $symbolsAmounts["lowerCaseSymbols"] + $symbolsAmounts["upperCaseSymbols"];
	if((strlen($password) == $letterAmount)
		|| (strlen($password) == $symbolsAmounts["numbers"])
	) {
		$strength -= strlen($password);
	}
	
	foreach($evaluationValues as $value) {
		$strength += $value;
	}

	return $strength;
}

try
{
    if(count($_GET) != ARG_COUNT) {
		header('HTTP/1.0 400');
        throw new Exception('В качестве аргумента укажите: password=<Строка>');
    }

	$strength = getPasswordStrength(getParamFromGetRequest('password'));
    echo "Strength =" . $strength;
	echo json_encode(array("Strength" => $strength));
}
catch (InvalidArgumentException $e)
{
    header('HTTP/1.0 400');
    echo 'Ошибка, не передан аргумент ' . $e->getMessage() . '.';
}
catch (Exception $e)
{
    echo $e->getMessage();
}
