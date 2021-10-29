<?php	// UTF-8 marker äöüÄÖÜß€
/**
 * Class Page for the exercises of the EWA lecture
 * Demonstrates use of PHP including class and OO.
 * Implements Zend coding standards.
 * Generate documentation with Doxygen or phpdoc
 * 
 * PHP Version 7
 *
 * @file     Page.php
 * @package  Page Templates
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de> 
 * @author   Ralf Hahn, <ralf.hahn@h-da.de> 
 * @version  2.0 
 */
 
/**
 * This abstract class is a common base class for all 
 * HTML-pages to be created. 
 * It manages access to the database and provides operations 
 * for outputting header and footer of a page.
 * Specific pages have to inherit from that class.
 * Each inherited class can use these operations for accessing the db
 * and for creating the generic parts of a HTML-page.
 *
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de> 
 * @author   Ralf Hahn, <ralf.hahn@h-da.de> 
 */ 
abstract class Page
{
    // --- ATTRIBUTES ---

    /**
     * Reference to the MySQLi-Database that is
     * accessed by all operations of the class.
     */
    protected $_database = null;
    
    // --- OPERATIONS ---
    
    /**
     * Connects to DB and stores 
     * the connection in member $_database.  
     * Needs name of DB, user, password.
     *
     * @return none
     */
    protected function __construct()

    {
        error_reporting (E_ALL);
        $host="localhost";

        /********************************************/
        // This code switches from the the local installation (XAMPP) to the docker installation
        if (gethostbyname('mariadb')!="mariadb"){ // mariadb is known?

            $host="mariadb";

        }
        /********************************************/

        $this->_database = new MySQLi($host, "username", "password", "yourdatabase");

        if (mysqli_connect_errno())

            throw new Exception("Connect failed: " . mysqli_connect_error());

        // set charset to UTF8!!

        if (!$this->_database->set_charset("utf8"))

          throw new Exception($this->_database->error);

    }
    
    /**
     * Closes the DB connection and cleans up
     *
     * @return none
     */
    public function __destruct()    
    {
		$this->_database->close();
    }
    
    /**
     * Generates the header section of the page.
     * i.e. starting from the content type up to the body-tag.
     * Takes care that all strings passed from outside
     * are converted to safe HTML by htmlspecialchars.
     *
     * @param $headline $headline is the text to be used as title of the page
     *
     * @return none
     */
    protected function generatePageHeader($headline = "") 
    {
        $headline = htmlspecialchars($headline);
        header("Content-type: text/html; charset=UTF-8");
        echo <<<EOT
        <!DOCTYPE html>
        <html lang="de">
        <head>
        <meta charset="UTF-8"/>
        <link rel="stylesheet" href="StyleSheet.css"/>
        <script src ="Warenkorb.js"> </script>
        <script src="StatusUpdate.js"></script>
        <title>$headline</title>
        </head>
EOT;
        if($headline == "Kunde") {
            echo "<body onload=\"startPolling();\">";
        } else {
            echo "<body>";
        }
    }

    /**
     * Outputs the end of the HTML-file i.e. /body etc.
     *
     * @return none
     */
    protected function generatePageFooter() 
    {
        echo <<<END
        </body> 
        </html>
END;
    }

    /**
     * Processes the data that comes via GET or POST i.e. CGI.
     * If every page is supposed to do something with submitted
     * data do it here. E.g. checking the settings of PHP that
     * influence passing the parameters (e.g. magic_quotes).
     *
     * @return none
     */
    protected function processGatheredData() 
    {

    }
} // end of class

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >