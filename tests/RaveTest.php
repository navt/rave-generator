<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__)."/Rave.php";

class RaveTest extends TestCase {
    
    protected $rave;
    
    public function testConstruct() {
        $this->rave = new \Navt\Rave("Один.");
        $this->assertEquals("Один.", $this->rave->getVar("input"));
    }
    
    public function testBuildMap() {
        $this->rave = new \Navt\Rave("Однажды, @в студёную Зимнюю пору# я ~ из лесу вышел!");
        $this->rave->clean();
        $this->rave->buildMap();
        $this->assertEquals(
        [
            "однажды,"=>[0=>"в"], "в"=>[0=>"студёную"], 
            "студёную"=>[0=>"Зимнюю"], "Зимнюю"=>[0=>"пору"],
            "пору"=>[0=>"я"], "я"=>[0=>"из"],
            "из"=>[0=>"лесу"], "лесу"=>[0=>"вышел!"],
            "вышел!"=>[0=>"однажды,"]
        ], 
            $this->rave->getVar("map"));
    }
    
    public function testGetGistogram() {
        $this->rave = new \Navt\Rave("Один. Два! Три?");
        $this->assertEquals([1=>3], $this->rave->getGistogram());
        $this->rave = null;
        $this->rave = new \Navt\Rave("Один. Два два! Три три три? Один!");
        $this->assertEquals([1=>2, 2=>1, 3=>1], $this->rave->getGistogram());
    }
    /*
    public function testRemoveShort() {
        $this->rave = new \Navt\Rave("Один. Два! Три?");
        $this->assertEquals([], $this->rave->removeShort(["так", "сяк", "там", "сям"], 4));
        $this->assertEquals([], $this->rave->removeShort([], 4));
    }
     */
    public function testGenerate() {
        $this->rave = new \Navt\Rave("Кошка кошка кошка");
        $this->rave->buildMap();
        $this->rave->minQW = 1;
        $this->rave->maxQW = 1;
        $this->rave->qSentences = 1;
        $this->rave->generate();
        $this->assertEquals("Кошка.", $this->rave->output);
        $this->rave = null;
        
        $this->rave = new \Navt\Rave("Кошка");
        $this->rave->buildMap();
        //var_dump($this->rave->getVar("map"));
        $this->rave->minQW = 2;
        $this->rave->maxQW = 2;
        $this->rave->qSentences = 1;
        $this->rave->generate();
        $this->assertEquals("Кошка кошка.", $this->rave->output);
    }
}
