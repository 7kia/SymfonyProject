<?php
#1.	Разработайте приложение /remove_extra_blanks на языке PHP.                   
#10 В запросе GET передается параметр text.                                     
#    Скрипт выводит в стандартный поток вывода этот же текст без лишних пробелов.
#    Результирующая строка не должна содержать пробелов в начале и в конце строки.
#    Внутри строки не может быть более одного пробела подряд.

header('Content-Type:text/html; charset=utf-8');
const ARG_COUNT = 1;

function getParamFromGetRequest($arg_name)
{
    if(isset($_GET[$arg_name])) {
        return $_GET[$arg_name];
    }
    
    throw new InvalidArgumentException($arg_name);
}

function removeExtraBlanks ($text)
{
    $trimmed = trim($text, " ");
	$result = preg_replace('/\s\s+/', ' ', $trimmed);
	return $result;
}

try
{
    if(count($_GET) != ARG_COUNT) {
		header('HTTP/1.0 400');
        throw new Exception('В качестве аргумента укажите: text=<Строка>');
    }

    echo "text =" . removeExtraBlanks(getParamFromGetRequest('text')) . "|";
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
