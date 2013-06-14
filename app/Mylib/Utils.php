<?php

/*
 * утилиты
 */

class Mylib_Utils
{

	public static function getDateDeltaFromMysql($date, DateTime $nowDate)
	{
		$sourceDate = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		return $nowDate->diff($sourceDate);
	}

	public static function secondsToTime($sec)
	{
		$hours = floor($sec / (60 * 60));
		$divisor_for_minutes = fmod($sec, 60*60);
		$minutes = floor($divisor_for_minutes / 60);
		$divisor_for_seconds = fmod($divisor_for_minutes, 60);
		$seconds = ceil($divisor_for_seconds);
		return sprintf('%dh. %dm. %ds.', $hours, $minutes, $seconds);
	}

	/**
	 *
	 */
	public static function fputcsv($handle, $data, $delim=';', $enc='"')
	{
		$callback = function(&$val, $key, $enc){ $val = sprintf('%s%s%s', $enc, $val, $enc); };
		array_walk($data, $callback, $enc);
		fwrite($handle, implode($delim, $data) . "\r\n");
	}




	/*
     * tail functionaly
     */
    public static function tail( $file, $lines )
    {
        //global $fsize;
        if( !file_exists($file) )
            throw new Exception("Filepath {$file} cannot be opened");

        $handle = fopen($file, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = array( );
        while($linecounter > 0)
        {
            $t = " ";
            while($t != "\n")
            {
                if( fseek($handle, $pos, SEEK_END) == -1 ) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if( $beginning ) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgetss($handle);
            if( $beginning )
                break;
        }
        fclose($handle);
        return array_reverse($text);
    }

	/*
	 * генерация рандомного пароля
	 */
	public static function rand_str( $length = 16, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890' )
	{
		// Length of character list
		$chars_length = ( strlen($chars) - 1 );

		// Start our string
		$string = $chars{rand(0, $chars_length)};

		// Generate random string
		for($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};

			// Make sure the same two characters don't appear next to each other
			if( $r != $string{$i - 1} )
				$string .= $r;
		}

		// Return the string
		return $string;
	}

    /*
     * переводит элементы массива в int
     */
    public static function str2intArr( $data, $colums )
    {
        foreach($colums as $col)
        {
            if( isset($data[$col]) ) {
                $data[$col] = (int) $data[$col];
            }
        }
        return $data;
    }

    /*
     * приводит int элементы массива в читабельный вид
     */
    public static function int2formatArr( $data, $colums )
    {
        foreach($colums as $col)
        {
            if( isset($data[$col]) ) {
                $data[$col] = number_format((int) $data[$col], 0, '', '`');
            }
        }
        return $data;
    }

    /*
     * приводит int элементы массива в читабельный вид
     */
    public static function numFormater( &$data )
    {
        foreach($data as $key => $val)
        {
            if( preg_match('/^\d{5,}$/', $val) == true ) {
                $data[$key] = number_format((int) $val, 0, '', '`');
            }
        }
    }

    /*
     * возвращает timestamp границы текущих суток
     * $begin - начало суток
     */
    public static function getTimeBorders( $offset = 0 )
    {
        $begin = strtotime(date('Y-m-d 00:00:00', time() - $offset * 3600 * 24));
        $end = strtotime('+1 day', $begin);
        return array( $begin, $end );
    }


    /*
     * возвращает значение reduce для входящих данных
     * 1-0
     * 2-0
     * 3-0
     * 4-0
     * 5-0
     * 6-0
     * 7-1
     * 8-1
     * 9-1
     * 10-2
     * 11-2
     * 12-2
     */
    public static function getReduce( $data )
    {
        if( count($data) == 0 )
            return 0;

        $sum = array( );
        foreach($data as $row)
        {
            unset($row['date']);
            $sum[] = array_sum($row) / count($row);
        }
        $len = strlen((string) round(array_sum($sum) / count($sum)));
        return ( $len <= 6 ) ? 0 : ceil(( ($len - 6) / 3));
    }

    /*
     * вычисляет дельту изменений
     * работает только для одной серии (первой)
     */
    public static function getDelta( $data )
    {
        $result = array( );

        $oldVal = ( isset($data[0]["ser1"]) ) ? $data[0]["ser1"] : 0;
        foreach($data as $key => $value)
        {
            $result[$value['date']] = intval($value["ser1"]) - intval($oldVal);
            $oldVal = $value["ser1"];
        }

        return $result;
    }

    /*
     * преобразует данные в соответствии с reduce
     * (ССЫЛКА)
     */
    public static function conversionReduce( &$item, $key, $divisor )
    {
        if( $key == 'date' )
            return false;
        $item = round($item / pow(1000, $divisor));
        return true;
    }

	/*
	 * валидация слайдерных полей
	 */
	public static function validateSlide( $varMin, $varMax )
	{
		return (
					(empty($varMin) && empty($varMax))
					||
					(
						filter_var($varMin, FILTER_VALIDATE_INT, array( 'min_range' => 0, 'max_range' => $varMax )) !== false
						&&
						filter_var($varMax, FILTER_VALIDATE_INT, array( 'min_range' => $varMin )) !== false
						&&
						$varMin >= 0
						&&
						$varMin < $varMax
					)
		);
	}

    /*
     * формы слова по числительным
     * день дня дней
     */
    public static function plural( $form1, $form2, $form3, $number )
    {
        $messages = array( $form1, $form2, $form3 );

        $index = $number % 10 == 1 && $number % 100 != 11 ? 0 : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20) ? 1 : 2);

        return $messages[$index];
    }

}