<?php
/**
 * Markov chain text generator
 */
namespace Navt;

class Rave
{
    private $input;
    private $map = [];      // карта переходов от слова из исходного текста к
                           //следующему слову
    private $mapSize = 0;
    private $mapKeys = [];
    private $ends = ["!",".","?"];
    
    public $minQW = 5;      // min количество слов в генерируемом предложении
    public $maxQW = 12;     // max количество слов в генерируемом предложении
    public $qSentences = 8; // количество генерируемых предложений
    
    public $output;
    
    // источником может быть строка или файл
    public function __construct($src="")
    {
        mb_internal_encoding("UTF-8");
        if ($src !== "") {
            $this->load($src);
        } else {
            throw new RaveException(__METHOD__."Так создать объект нельзя.");
        }
    }
    
    private function load($src)
    {
        if (is_readable($src)) {
            $this->input = file_get_contents($src);
            return;
        }
        $this->input = $src;
    }
    // очистка стоки от "лишних" символов
    public function clean()
    {
        // удаляем переносы строк
        $this->input = str_replace(["\n", "\r", "\t"], " ", $this->input);
        // оставляем только допустимые символы
        $this->input = preg_replace('~[^a-zёа-я0-9\s-!\?\.\,\:]~ui', "", $this->input);
        // дубли точек и многоточия
        $this->input = preg_replace('~(\.{2,})~u', '.', $this->input);
        // удаляем последовательности из 2-х и более пробелов
        $this->input = preg_replace("~(\s{2,})~u", " ", $this->input);
        //
        $this->input = trim($this->input);
        return $this;
    }
    // постоение карты
    public function buildMap()
    {
        $words = explode(" ", $this->input);
        $qWords = count($words);
        $words[-1] = "Старт!";
        for ($i = 0; $i < $qWords; $i++) {
            $early = $words[$i-1];
            $word = $words[$i];
            $words[$i] = $this->isOwn($early, $word);
        }
        for ($i = 0; $i < $qWords; $i++) {
            $word = $words[$i];
            if ($i == $qWords - 1) {
                $nextWord = $words[0];
            } else {
                $nextWord = $words[$i+1];
            }
            $this->map[$word][] = $nextWord;
        }
        $this->mapSize = count($this->map);
        $this->mapKeys = array_keys($this->map);
    }
    // определение имен собственных
    private function isOwn($early, $word)
    {
        // Слово начинается с заглавной буквы?
        if (preg_match("~[A-ZА-ЯЁ]~", $word)) {
            // Если в предыдущем слове закончилось предложение исходного текста
            if ($this->isInString($early, $this->ends)) {
                $word = mb_strtolower($word);
            }
        }
        return $word;
    }
    // имеется ли в строке подстрока из массива $units
    private function isInString($str, $units=[])
    {
        foreach ($units as $unit) {
            if (mb_strpos($str, $unit) !== false) {
                return true;
            }
        }
        return false;
    }
    
    // getGistogram() возвращает массив: ключи - количесво слов в предложении,
    // значения - количество таких предложений в тексте
    public function getGistogram()
    {
        $str = preg_replace(["~\.~", "~\!~", "~\?~"], "~", $this->input);
        $sentences = explode("~", $str);
        $gistogram = [];
        for ($i = 0; $i < count($sentences)-1; $i++) {
            $words = explode(" ", trim($sentences[$i]));
            $qW = count($words);
            if (array_key_exists($qW, $gistogram)) {
                $gistogram[$qW] = $gistogram[$qW] + 1;
            } else {
                $gistogram[$qW] = 1;
            }
        }
        ksort($gistogram);
         
        return $gistogram;
    }
    
    public function generate()
    {
        if (empty($this->map)) {
            throw new RaveException(__METHOD__."Без карты генерация не работает.");
        }
        $sentences = [];
        // набираем нужное количество предложений
        for ($i = 0; $i < $this->qSentences; $i++) {
            
            // собираем отдельное предложение
            $sentence = [];
            $qw = mt_rand($this->minQW, $this->maxQW);
            $early = $this->findWord("");
            $sentence[0] = $this->mb_ucfirst($early);
            for ($ii = 1; $ii < $qw; $ii++) {
                $word = $this->findWord($early);
                if ($this->isInString($word, $this->ends) && $ii < $qw-1) {
                    $sentence[$ii] = str_replace($this->ends, "", $word);
                } else {
                    $sentence[$ii] = $word;
                }
                $early = $word;
            }
            // убираем в конце предложения предлоги, союзы ...
            $sentence = $this->removeShort($sentence, 3);
            $last = count($sentence)-1;
            if ($this->isInString($sentence[$last], [",", ":"])) {
                $sentence[$last] = str_replace([",", ":"], "", $sentence[$last]);
            }
            if ($this->isInString($sentence[$last], $this->ends)) {
                $sentences[$i] = implode(" ", $sentence);
            } else {
                $sentences[$i] = implode(" ", $sentence).".";
            }
        }
        $this->output = implode(" ", $sentences);
    }
    // убираем в конце предложения слова корче $limit символов
    private function removeShort($sentence=[], $limit=3)
    {
        if (is_array($sentence) && count($sentence) > 0) {
            while (mb_strlen($sentence[count($sentence)-1]) < $limit) {
                unset($sentence[count($sentence)-1]);
                if ($sentence == []) {
                    return $sentence;
                }
            }
            return $sentence;
        } else {
            return [];
        }
    }
    // поиск слова для предложения
    private function findWord($early)
    {
        // если это первое слово в предложении или новая генерация случайного слова
        if ($early === "") {
            $i = mt_rand(0, $this->mapSize-1);
            $word = $this->mapKeys[$i];
            if ($this->isInString($word, [".", "!", "?", ":"])) {
                return $this->findWord("");
            }
            return $word;
        }
        // если слово в предложении НЕ первое
        if (!isset($this->map[$early])) {
            return $this->findWord("");
        } else {
            if (is_array($this->map[$early])) {
                $a = $this->map[$early];
                $i = mt_rand(0, count($a)-1);
                return $a[$i];
            } else {
                throw new RaveException(__METHOD__." Для ключа $early в карте нет массива ");
            }
        }
    }
    
    private function mb_ucfirst($str)
    {
        $fc = mb_strtoupper(mb_substr($str, 0, 1));
        return $fc.mb_substr($str, 1);
    }
    
        
    public function getVar($name)
    {
        return $this->$name;
    }
    
    public function printString($str="", $limit=10)
    {
        $words = explode(" ", $str);
        $qw = count($words);
        $i = 0;
        $j = 0;
        while ($i < $qw) {
            $line[] = $words[$i];
            ++$i;
            ++$j;
            if ($j === $limit || $i === $qw) {
                $j = 0;
                $l = implode(" ", $line)."\r\n";
                echo $l;
                $line = [];
            }
        }
    }
}
class RaveException extends \Exception
{
}
