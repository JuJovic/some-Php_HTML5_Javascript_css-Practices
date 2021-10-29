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

// to do: change name 'PageTemplate' throughout this file
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
class Fahrer extends Page
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
        $orders = array();

        $first_sql ="SELECT distinct ordering.address, ordering.id
        FROM `ordering` ordering, `ordered_articles` ordart
        WHERE  ordering.id = ordart.f_order_id";

        $recordset1 = $this->_database->query($first_sql);
        if(!$recordset1) throw new Exception("Abfrage fehlgeschlagen".$this->_database->error);

        while($record = $recordset1->fetch_assoc()){
            $address = $record["address"];
            $k_id = $record["id"];

            $getPizzas = "SELECT article.name, article.price, ordart.status
            FROM `article` article , `ordered_articles` ordart
            WHERE ordart.f_article_id = article.id AND ordart.f_order_id = $k_id AND ordart.status BETWEEN 0 AND 3";

            $recordset2 = $this->_database->query($getPizzas);
            if(!$recordset2) throw new Exception("Abfrage fehlgeschlagen".$this->_database->error);

            if($recordset2->num_rows == 0){
                continue;
            }

            $custOrd = array('address' => $address, 'cust_id' => $k_id);
            while($row = $recordset2->fetch_assoc()){
                $p_name = $row["name"];
                $p_price = $row["price"];
                $status = $row["status"];
                $pizzas = array('name' => $p_name, 'price' => $p_price, 'status' => $status);
                array_push($custOrd, $pizzas);
            }
            array_push($orders, $custOrd);
        }

        $recordset1->free();
        $recordset2->free();
        return $orders;
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

        $orders = $this->Database();
        $this->generatePageHeader('Fahrer');


        echo "<h1> Auslieferbare Bestellungen </h1>
        <form id='pizzenStatus' action='Fahrer.php' method='post' accept-charset='UTF-8'>";

        $count = 0;
        $isOrderReady = true;
        $amountReadyOrders = 0;

        foreach($orders as $row){
            $address = htmlspecialchars($row['address']);
            $k_id = htmlspecialchars($row['cust_id']);

            $fahrerstatus = array(3);
            $pizzaNamen = array();
            $totalPrice = 0;
            $customerOrder = $orders[$count]; 
        
            for($i = 0; $i < count($customerOrder) - 2; $i++){
                $pizza = $customerOrder[$i];
                $name = $pizza['name'];
                $totalPrice =  $totalPrice + $pizza['price'];
                array_push($pizzaNamen, $name);
                $status = $pizza['status'];

                if($status == 0 || $status == 1){
                    $isOrderReady = false;
                    continue;
                }
                elseif ($status == 2){
                    $fahrerstatus[0] = 'checked';
                    $fahrerstatus[1] = '';
                    $fahrerstatus[2] = '';
                }
                elseif ($status == 3) {
                    $fahrerstatus[0] = '';
                    $fahrerstatus[1] = 'checked';
                    $fahrerstatus[2] = '';
                }
            } 
            if( $isOrderReady == false){
                $count++;
                $isOrderReady = true;
                continue;
            } else {
                echo <<<BESTELLUNG
                <fieldset class="fahrer"> <h2> Adresse: $address</h2>
                Bestellung: <br>
                <ul>
BESTELLUNG;

                for($j = 0; $j < count($pizzaNamen); $j++){
                    echo " <li> $pizzaNamen[$j] </li>";
                }
                
                $buttonIDs = array('fertig'. $amountReadyOrders, 'unterwegs'. $amountReadyOrders, 'geliefert'. $amountReadyOrders);
                
                echo <<<PIZZEN
                </ul>
                <input type='hidden' name='orderID$amountReadyOrders' value= '$k_id' >
                <p>
                <label for = $buttonIDs[0]> auslieferbereit  </label>
                <input $fahrerstatus[0] type = 'radio' name =  'pizza$amountReadyOrders' value = '2' id = $buttonIDs[0] 
                onclick="document.forms['pizzenStatus'].submit();" tabindex = '1' /> </p>
                <p>
                <label for = $buttonIDs[1]> unterwegs </label>
                <input $fahrerstatus[1] type = 'radio' name = 'pizza$amountReadyOrders' value = '3' id = $buttonIDs[1] 
                onclick="document.forms['pizzenStatus'].submit();" tabindex = '2'/> </p>
                <p>
                <label for = $buttonIDs[2]> geliefert </label>
                <input $fahrerstatus[2] type = 'radio' name = 'pizza$amountReadyOrders' value = '4' id = $buttonIDs[2] 
                onclick="document.forms['pizzenStatus'].submit();" tabindex = '3' /> </p>
                <div class="gesamtPreis">Gesamtpreis: $totalPrice €</div> 
                </fieldset>
                <br>
PIZZEN;

                $amountReadyOrders++;
                $count++;
            }
        }

        $anstehendeLieferungen = $count - $amountReadyOrders;
        echo <<<SUBMIT
            <p id="anstehendeLieferung"> Anstehende Lieferungen: $anstehendeLieferungen </p>
            <input type='hidden' name='amountOrders' value= $amountReadyOrders>
            </form>
SUBMIT;
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
       
        $amountOrders = 0;

        // amount of orders that can change status
        if (!empty($_POST)) {
            header('Location: Fahrer.php');
            if (isset($_POST["amountOrders"])){
                $amountOrders = (Int) $this->_database->real_escape_string($_POST["amountOrders"]);
            }   
        }
        // get current status and orderid
        for ($i = 0; $i < $amountOrders; $i++){
            $status = (Int) $this->_database->real_escape_string($_POST['pizza'. $i]);
            $custID = (Int) $this->_database->real_escape_string($_POST['orderID' . $i]);

            // update Database
            $sql_update = "UPDATE `ordered_articles` SET `status` = $status WHERE `f_order_id` = $custID";
            try{
               $query = $this->_database->query($sql_update);
            }
            catch(Exception $e){
                echo 'Update des Fahrers fehlgeschlagen' ,$e->getMessage(),"\n";
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
            $page = new Fahrer();
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
Fahrer::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >