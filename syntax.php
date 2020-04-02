<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Go Namhyeon <gnh1201@gmail.com>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_aladin extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 160;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{aladin.*?>*?}}',$mode,'plugin_aladin');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        $data = array();
        
        list($params, $keyword) = explode('>', trim($match,'{}'), 2);

        $queryfields = array(
            "ttbkey" => "ttbgnh12011935001",
            "Query" => $keyword,
            "QueryType" => "Title",
            "MaxResults" => 10,
            "start" => 1,
            "SearchTarget" => "Book",
            "output" => "xml",
            "Version" => "20070901"
        );
        
        $url = "http://www.aladin.co.kr/ttb/api/ItemSearch.aspx?" . http_build_query($queryfields, '', '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($response);
        foreach($xml->item as $item) {
            $data[] = array(
                "link" => (string)$item->link,
                "title" => (string)$item->title,
                "author" => (string)$item->author,
                "publisher" => (string)$item->publisher
            );
        }

        return $data;
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $R, $data) {
        $doc = "<ul>";

        foreach($data as $item) {
            $doc .= sprintf('<li><a href="%s">%s (%s, %s)</a></li>', $item['link'], $item['title'], $item['author'], $item['publisher']);
        }

        $doc .= "</ul>";
        
        $R->doc .= $doc;
        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
