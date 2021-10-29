<?php	// UTF-8 marker äöüÄÖÜß€
/**
 * Class PageTemplate for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 * 
 * PHP Version 7
 *
 * @file     PageTemplate.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de> 
 * @author   Ralf Hahn, <ralf.hahn@h-da.de> 
 * @version  2.0 
 */

require_once './Page.php';

class Bestellung extends Page
{
    // representing substructures/blocks
    
    /**
     * Instantiates members (to be defined above).   
     * Calls the constructor of the parent i.e. page class.
     * So the database connection is established.
     *
     * @return none
     */
    protected function __construct() 
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }
    
    /**
     * Cleans up what ever is needed.   
     * Calls the destructor of the parent i.e. page class.
     * So the database connection is closed.
     *
     * @return none
     */
    public function __destruct() 
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is stored in an easily accessible way e.g. as associative array.
     *
     * @return none
     */
    protected function Database()
    {
        error_reporting (E_ALL);

	    $sql= "SELECT name , picture , price  FROM `article`";
        $recordset= $this->_database->query($sql);
        if(!$recordset)
         throw new Exception("Abfrage fehlgeschlagen".$this->_database->error);

      $lines =array();
      $record= $recordset->fetch_assoc();
      while($record){
        $line=array("name" => $record["name"],
             "price" => $record["price"],"picture" => $record["picture"]);
        array_push($lines,$line);
        $record= $recordset->fetch_assoc();
      }
      $recordset->free();
      return $lines;
    
    }
    
    /**
     * First the necessary data is fetched and then the HTML is 
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if avaialable- the content of 
     * all views contained is generated.
     * Finally the footer is added.
     *
     * @return none
     */
    protected function viewDatabase() 
    {
        error_reporting (E_ALL);

        $lines = $this->Database();
        $this->generatePageHeader('Bestellung');
        // to do: call viewDatabase() for all members
        
echo <<<SPEISEKARTE1
        <div class="content">
        <section class="menue">
        <h1> Speisekarte </h1>
SPEISEKARTE1;
    
		foreach($lines as $line) 
		{
		$name= htmlspecialchars($line["name"]);
        $price= htmlspecialchars($line["price"]);
        $picture= htmlspecialchars($line["picture"]); 
       
        $formPrice = number_format($price, 2);

echo <<<SPEISEKARTE
        <section class="picture">
        <form action="Bestellung.php" method="post">
        <p>
        <img src= $picture alt="" width="200" height="150" onclick="addPizzaToCart('$name', '$price');"> <br />
        </p>
        <h2>$name: $formPrice € </h2>
        </form>
        </section>
SPEISEKARTE;
		
       
        }
        
echo <<<WARENKORB
        </section>
        <form action="Bestellung.php" id="warenkorb1" method="post" accept-charset="UTF-8"> 
		<section class="cart">
        <h2> Warenkorb </h2>
        <select  id="warenkorb" tabindex="0" name="warenkorb[]" size="5" multiple="" onchange="checkInput();"> 
        </select> 
        <p id="price"> </p>
        <input class="button" type = "button" value = "Auswahl löschen" onclick="deleteSelected();"/> 
		<input class="button" type = "button" value = "Alles löschen" onclick="deleteAll();" /> 
		</section>
WARENKORB;

echo <<<KUNDENINFO
		<section class="info">
        <h2>
            Kundeninformation
        </h2>
        	<p>
            <label for = "adresse"> Ihre Adresse </label> 
            <br/>
            <input type = "text" name = "adresse" value = "" id = "adresse" placeholder = "Müller, Kasinostraße. 8" required 
            onchange="checkInput();"/> 
			</p>
				   
			<input class="button" type = "submit" id="bestellen" value = "Bestellen" disabled="" onclick="sendOrder();"/>
		</section>
		</form>
		</div>
KUNDENINFO;
        $this->generatePageFooter();
    }
    
    /**
     * Processes the data that comes via GET or POST i.e. CGI.
     * If this page is supposed to do something with submitted
     * data do it here. 
     * If the page contains blocks, delegate processing of the 
	 * respective subsets of data to them.
     *
     * @return none 
     */
    protected function processGatheredData() 
    {
        parent::processGatheredData();
        // to do: call processGatheredData() for all members
       
        session_start();

		if (isset($_POST["adresse"]) && isset($_POST["warenkorb"])){
			$adresse = (String) $this->_database->real_escape_string($_POST["adresse"]);
		    $bpizzen = array();
            $bpizzen = $_POST["warenkorb"];

           //insert addresse in der Tabelle Ordering
			$sql_insert =" INSERT INTO `ordering` (address,timestamp) 
            VALUES ('$adresse',current_timestamp())";

			try{
            $query = $this->_database->query($sql_insert);
            } catch(Exception $e){
				echo ' Insert neuer Bestellung Fehlgeschlagen' ,$e->getMessage(),"\n";
            }
            
            //die Max id von Tabelle Ordering holen und in bestelltId speichern
            $q = "SELECT o.id as bid FROM `ordering` o ORDER BY o.timestamp DESC LIMIT 1";
           
             $rs = $this->_database->query($q);
             if(!$rs)
             throw new Exception("Abfrage fehlgeschlagen".$this->_database->error);
             $r= $rs->fetch_assoc();
             $bestelltId = (int) $r["bid"];
             $rs->free();

             $_SESSION["bestelltid"] = $bestelltId;

            for($i = 0; $i < count($bpizzen); $i++){
                //id von jeden Namen in Warenkorb bestimmen
                $name = (String)$this->_database->real_escape_string($bpizzen[$i]);
		        $sql = "SELECT a.id as id_von_name
                        FROM article a
                        WHERE a.name = '$name' ";
                        
                $recordset = $this->_database->query($sql);
                if(!$recordset)
                    throw new Exception("Abfrage fehlgeschlagen".$this->_database->error);

                $record = $recordset->fetch_assoc();
                if($record != NULL){
                    $ID = (int) $record["id_von_name"];
                    $sql_bestelltePizza="INSERT INTO `ordered_articles`( f_article_id, f_order_id, status)
                    VALUES('$ID','$bestelltId' , 0)";
                    //insert in ordered_articles mit passenden f_article_id und  f_order_id
                    try {
                        $this->_database->query($sql_bestelltePizza);
                    } catch(Exception $e) {
                        echo 'Insert einer neuen bestellten Pizza fehlgeschlagen ' , $e->getMessage(),"\n";
                    }
                } else {
                    echo"
                    <fieldset> <legend accesskey='1'> <em> No Pizza ID found! </em> </legend>
                    Something went wrong with fetching the data from the database!
                    Order could not be saved.
                    </fieldset>";
                }
                $recordset->free();
                Header('Location: Kunde.php');
            }   
        }					
    }

    /**
     * This main-function has the only purpose to create an instance 
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
     *
     * @return none 
     */    
    public static function main() 
    {
        try {
            $page = new Bestellung();
            $page->processGatheredData();
            $page->viewDatabase();
        }
        catch (Exception $e) {
            header("Content-type: text/plain; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Bestellung::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >