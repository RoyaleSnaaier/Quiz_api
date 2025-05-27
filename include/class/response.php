<?php


// ik vind het nuttig om een response class te maken.
// zo kan ik snel een bericht terugsturen naar de client.
// ook kan ik aangeven of ik data wil terugsturen of niet.
// en dat ik een status code wil terugsturen.

class Response
{
    public $Message;
    public $Data;

    // constructor van de class
    // status code is optioneel, maar default is 200
    // dit is de status code die je krijgt als alles goed is gegaan.
    function __construct($message, $data, $status = 200)
    {
        // in de constructor geef ik de message en data mee
        $this->Message = $message;
        $this->Data = $data;

        // de status code geef ik mee aan de header. Dan is het mogelijk om in een C# app (of andere app) 
        // de status code uit te lezen. Zo heeft .Net een .IsSuccessStatusCode() functie.
        http_response_code($status);
        $this->Send();
    }

    function Send()
    {
        // ik stuur de header mee dat het een json response is.
        // dit is belangrijk omdat ik in de client een json response verwacht.
        header('Content-Type: application/json');
        echo json_encode($this);
        exit;
    }
}