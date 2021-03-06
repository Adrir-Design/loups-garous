<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Content-Type: application/json');

    if( $_SERVER['REQUEST_METHOD'] != "POST"){
        header("HTTP/1.1 405 Method Not Allowed");
        echo(json_encode( array("error" => "POST Service asked with a ".$_SERVER['REQUEST_METHOD']." method.") ));
        return;
    }

    if( $_SERVER['HTTP_APIKEY'] != APIKEY ){
        header("HTTP/1.1 401 - Unauthorized");
        echo(json_encode( array("error" => "Wrong API Key") ));
        return;
    }

    error_reporting(0);

    $result=array();
    $field_control = true;

    // Validate inputs

    $invoice_id = (isset($_POST['invoice-id']) ? strip_tags($_POST['invoice-id']) : '');
    if(empty($invoice_id)){
        $result=array_merge($result,array("invoice_id " => "Missing and required" ) );
        $field_control = false;
    }

    $invoice_date = (isset($_POST['invoice-date']) ? strip_tags($_POST['invoice-date']) : '');
    if(empty($invoice_date)){
        $result=array_merge($result,array("invoice-date" => "Missing and required" ) );
        $field_control = false;
    }


    if( !$field_control ){
        header("HTTP/1.1 422 - Unprocessable Entity");
        $result=array_merge($result,array("result" => "error" ) );
        echo( json_encode($result) );
        return;
    }

    $mysqli_invoices = new mysqli(DB_URL,DB_USER,DB_PASSWORD,DB_NAME);
    if ($mysqli_invoices->connect_errno) {
        echo(json_encode( array("error" => "DB connexion failed") ));
        printf("Connect failed: %s\n", $mysqli_invoices->connect_error);
        return;
    }

    $query_invoice = "INSERT INTO   `invoices`  (`invoice-id`,`invoice-date`)
                                    VALUE       ('".$invoice_id."','".$invoice_date."')";
    if (!$mysqli_invoices->query($query_invoice)) {
        printf("Error message: %s\n", $mysqli_invoices->error);
        echo(json_encode( array("error" => "DB request failed") ));
        return;
    }

    $mysqli_invoices->close();


    $log_data = $_POST['invoice-id'].' , '.$_POST['invoice-date'];
    db_log( $_SERVER['HTTP_APIKEY'], "CREATE INVOICE", $log_data);

    $result=array_merge($result,array("result" => "success" ) );
    $result=array_merge($result,array("message" => "Invoice has been added" ) );

    echo( json_encode($result) );
    return;
?>
