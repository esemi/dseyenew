<?php

/* 
 * моделька dshelp.info
 * для доступа к адресу картинки с графиком РА игрока
 * для доступа и парсинга страниц с РА игроков по мирам
 * @TODO переделать на Zend_Dom_Query
 */
class App_Model_Dshelp
{
    protected $_cache = null;
    protected $_nameW = '';
    protected $_errorData = array();
    protected $_userAgent = null;
    protected $_dataImg = null;
    protected $_prefixImg = 'dshelp_img_addr';


    public function __construct( $nameW )
    {
        $this->_nameW = $nameW;
        $this->_setCache();
        $this->_userAgent = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('botname');
    }


    protected function _setCache()
    {
         $this->_cache = Zend_Controller_Front::getInstance()
                                ->getParam('bootstrap')
                                ->getResource('cachemanager')
                                ->getCache('long');
    }

    public function getErrors()
    {
        return $this->_errorData;
    }

    /*
     * получить страницу со списком игроков
     */
    public function getPageSource( $num )
    {
        $prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
        $url = $this->_prepareLinkPlayers( $num );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $prop['dshelpPage']['conn']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $prop['dshelpPage']['wait']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        $response = iconv("Windows-1251", "UTF-8", curl_exec($ch));
        $err = curl_error($ch);
        curl_close($ch);
        
        if( $err != '' )
        {
            $this->_errorData[] = "{$url} - {$err}<br>";
            return false;
        }else{
            return $response;
        }

    }

    
    /*
     * парсинг страницы игроков строки с параметрами игроков
     */
    public function parsePlayersPage( $source )
    {
        //ищем наш кусочек таблички
        $pos = strpos( $source, '<table summary="" border="1" cellpadding="1" cellspacing="0">');
        $offset = strpos( $source, '</table>', $pos) - $pos;
        return array_slice( explode('</tr>', substr($source, $pos, $offset)), 2, -2);
    }

    /*
     * парсинг строки на ник и РА
     */
    public function parsePlayersStr( $str )
    {
        $result = new stdClass();
        $result->success = false;
        $result->error = array();
        $result->data = array();
        
        $values = explode('</td>',  str_replace(' ', '', $str));
        
        //не битая ли строка
        if( count($values) != 9 )
        {
            $result->error[] = "строка битая";
        }else{            
            //выдираем ник
            preg_match('/index.html>[\wА-Яа-яёЁ\s.-]{3,50}<\/a>/ui', $values[1], $matches);
            if( !isset($matches[0]) )
            {            
                $result->error[] = "ник не найден {$values[1]}";
            }else{
                $result->data['nik'] = substr($matches[0], 11, -4);
                if( !preg_match('/^[\wА-Яа-яёЁ\s.-]{3,50}$/ui',$result->data['nik']) )                
                    $result->error[] =  "стрёмный ник {$result->data['nik']}";                
            }

            //выдираем РА
            $result->data['ra'] = substr($values[5],13);
            if( !preg_match( '/^[\d]{1,3}(|[.][\d]{1,2})$/', $result->data['ra']) )
                $result->error[] = "стрёный РА {$ra}";
        }
        
        if( count($result->error) === 0 )
            $result->success = true;
        
        return $result;
    }


    /*
     * получить урл на картинку с графиком
     */
    public function getUrl( $nik, $idP )
    {
        if ( !$url = $this->_getImgFromCache( $idP ) )
            $url = $this->_getImgFromHttp( $nik, $idP );
        
        return $url;
    }
        


    /*
     * получаем данные с сайта либо false
     */
    protected function _getImgFromHttp( $nameP, $idP )
    {
        $prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('curl');
        $url = $this->_prepareLinkImg( $nameP );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $prop['dshelpImg']['conn']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $prop['dshelpImg']['wait']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if( $err != '' )
        {
            $this->_errorData .= "{$url} - {$err}<br>";
            return false;
        }
        
        $this->_dataImg = $this->_parseImg( $response );

        if( $this->_dataImg != false )
            $this->_saveImgToCache( $idP );
        
        return $this->_dataImg;
    }


    /*
     * получаем ссылку из кеша либо false
     */
    protected function _getImgFromCache( $idP )
    {
        return ( is_null($this->_cache) ) ?
                false 
                :
                $this->_cache->load("{$this->_prefixImg}_{$idP}");
    }


    /*
     * сохраняем данные в мемкеш
     */
    protected function _saveImgToCache( $idP )
    {
        $this->_cache->save($this->_dataImg, "{$this->_prefixImg}_{$idP}");
    }

    /*
     * составляем ссылку для запроса картинки
     */
    protected function _prepareLinkImg( $nameP )
    {
        return "http://dshelp.info/{$this->_nameW}/player/{$nameP}/index.html";
    }

    /*
     * составляем ссылку для запроса страницы с игроками
     */
    protected function _prepareLinkPlayers( $num )
    {
        return "http://dshelp.info/{$this->_nameW}/players/{$num}/index.html";
    }

    /*
     * парсим ответ и отдаём ссылку
     */
    protected function _parseImg( $data )
    {
        preg_match( "/http:\/\/dshelp\.info\/{$this->_nameW}\/[\d]+\/g20.png/", $data, $matches);
        return (count($matches)) ? array_shift($matches) : false;
    }

}
?>
