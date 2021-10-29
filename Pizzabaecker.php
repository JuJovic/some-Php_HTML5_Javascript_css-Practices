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

/**
 * This is a template for top level classes, which represent 
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class. 
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking 
 * during implementation.
 
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de> 
 * @author   Ralf Hahn, <ralf.hahn@h-da.de> 
 */
class Pizzabaecker extends Page
{
    // to do: declare reference variables for members 
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

        $sql="SELECT a.name, o.status, o.id, o.f_order_id 
        FROM  `article` a , `ordered_articles` o
        WHERE a.id = o.f_article_id AND o.status BETWEEN 0 AND 2";

        $recordset = $this->_database->query($sql);
        if(!$recordset) throw new Exception("Abfrage fehlgeschlagen".$this->_database->error);

        $ordered_articles = array();

        while($record = $recordset->fetch_assoc()){
			$line = array('id' => $record['id'], 'name' => $record['name'],'status' => $record['status'], 'k_id' => $record['f_order_id']);
			array_push($ordered_articles,$line);
		}
		
        $recordset->free(); 
        return $ordered_articles;
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

        $ordered_articles = $this->Database();
        $this->generatePageHeader('Pizzabaecker');

        if(empty($ordered_articles)){
            echo <<<NOORDERS
            <h2> Es stehen keine offenen Bestellungen an </h2>
NOORDERS;
        } else{
            echo "<h1> Offene Bestellungen </h1>";
            echo"<form id='pizzabaecker' action='Pizzabaecker.php' method='post' accept-charset='UTF-8'>";
        
            $baeckerstatus = array(3);
            (Int) $i = 0;
            
            foreach($ordered_articles as $order) {
                $id = htmlspecialchars($order['id']);
                $name = htmlspecialchars($order['name']);
                $status = $order['status'];
                $k_id = htmlspecialchars($order['k_id']);
    
                // set correct status that was fetched from the database
                if ($status == 0){
                    $baeckerstatus[0] = 'checked';
                    $baeckerstatus[1] = '';
                    $baeckerstatus[2] = '';
                }
                elseif ($status == 1) {
                    $baeckerstatus[0] = '';
                    $baeckerstatus[1] = 'checked';
                    $baeckerstatus[2] = '';
                }
                elseif ($status == 2) {
                    $baeckerstatus[0] = '';
                    $baeckerstatus[1] = '';
                    $baeckerstatus[2] = 'checked';
                }
                
                $buttonIDs = array('bestellt'. $i, 'imOfen'. $i, 'fertig'. $i);
                   
                // RadioButtons for the baker to change status
                echo <<<BUTTONS
                <input type='hidden' name='orderID$i' value=$id>
                <fieldset class="baecker">
                <h2> $name </h2> 
                <p>
                <label for = $buttonIDs[0]> bestellt </label>
                <input $baeckerstatus[0] type = 'radio' name = 'pizza$i' value = '0' id = '$buttonIDs[0]' 
                onclick="document.forms['pizzabaecker'].submit();"/> 
                </p>
                <p>
                <label for = $buttonIDs[1]> im Ofen </label>
                <input $baeckerstatus[1] type = 'radio' name = 'pizza$i' value = '1' id = '$buttonIDs[1]' 
                onclick="document.forms['pizzabaecker'].submit();" /> 
                </p>
                <p>
                <label for = $buttonIDs[2]> fertig </label>
                <input $baeckerstatus[2] type = 'radio' name = 'pizza$i' value = '2' id = '$buttonIDs[2]' 
                onclick="document.forms['pizzabaecker'].submit();" /> 
                </p>
                <div class="bn">Bestellnummer: $k_id</div> 
                </fieldset>
                <br>
BUTTONS;
                $i++;
            }
            echo <<<SUBMIT
            <input type='hidden' name='amountPizzas' value=$i>
            </form>
SUBMIT;
        }
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

        $amountPizzas = 0;

        // amount of pizzas that can change status
        if (!empty($_POST)) {
            Header('Location: Pizzabaecker.php');
            if (isset($_POST["amountPizzas"])){
                $amountPizzas = (Int) $this->_database->real_escape_string($_POST["amountPizzas"]);
            }   
        }
        // get current status and ordered_articles id
        for ($i = 0; $i < $amountPizzas; $i++){
            $status = (Int) $this->_database->real_escape_string($_POST['pizza'. $i]);
            $orderID = (Int) $this->_database->real_escape_string($_POST['orderID' . $i]);

            // update Database
            $sql_update = "UPDATE `ordered_articles` SET `status` = $status WHERE `id` = $orderID";
            try{
                $this->_database->query($sql_update);
            }
            catch(Exception $e){
                echo 'Update des Baeckers fehlgeschlagen' ,$e->getMessage(),"\n";
            }
        }

        // update the site every 5 seconds
        // changes not submitted will be lost!!
        header("Refresh:5");
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
            $page = new Pizzabaecker();
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
Pizzabaecker::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >