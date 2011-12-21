<?php
/*
    "NuSOAP and WSDL"
    http://www.nonplus.net/geek/000752.php

    Copyright 2003, Stepan Riha, All right reserved.

    You're free to make derivative and commercial use of this code
    without attribution.

    Version 2.0 - Oct 29, 2003
    Fixed problems with unquoted string constants.

*/
require_once('nusoap.php');

$NAMESPACE = 'http://moodledev.midkent.ac.uk/coursesxml/books';

$server = new soap_server;

$server->debug_flag=false;
$server->configureWSDL('Books', $NAMESPACE);
$server->wsdl->schemaTargetNamespace = $NAMESPACE;

// ==== WSDL TYPES DECLARATION ==============================================

// ---- Chapter -------------------------------------------------------------

$server->wsdl->addComplexType(
    'Chapter',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'title' => array('name'=>'title','type'=>'xsd:string'),
        'page' => array('name'=>'page','type'=>'xsd:int')
    )
);

// ---- Chapter[] -----------------------------------------------------------

$server->wsdl->addComplexType(
    'ChapterArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
        array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Chapter[]')
    ),
    'tns:Chapter'
);

// ---- Book ----------------------------------------------------------------

$server->wsdl->addComplexType(
    'Book',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'author' => array('name'=>'author','type'=>'xsd:string'),
        'title' => array('name'=>'title','type'=>'xsd:string'),
        'numpages' => array('name'=>'numpages','type'=>'xsd:int'),
        'toc' => array('name'=>'toc','type'=>'tns:ChapterArray')
    )
);

// ---- Book[] --------------------------------------------------------------

$server->wsdl->addComplexType(
    'BookArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
        array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Book[]')
    ),
    'tns:Book'
);

// ==== WSDL METHODS REGISTRATION ===========================================

$server->register(
    'getBook',
    array('title'=>'xsd:string'),
    array('return'=>'tns:Book'),
    $NAMESPACE);

$server->register(
    'getBooks',
    array('author'=>'xsd:string'),
    array('return'=>'tns:BookArray'),
    $NAMESPACE);

// ==== PROCESS REQUEST =====================================================

$HTTP_RAW_POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA'])
                        ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
$server->service($HTTP_RAW_POST_DATA);
exit();


// ==== METHOD IMPLEMENTATION ===============================================

// ---- getBook(title) ------------------------------------------------------

function getBook($title) {
    // Here we'd look up a book based on the title
    // Instead, we'll make a book with 3-5 chapters each 5-25 pages long

    // Create TOC
    $num = intval(rand(3,5));
    $toc = array();
    $page = 1;
    for($i = 1; $i <= $num; $i++) {
        // Create chapter
        $chapter = array(
                        'title' => "Chapter $i",
                        'page' => $page);
        // Add to array
        $toc[] = $chapter;
        // Bump page number
        $page += rand(20,25);
    }

    // Create book (hardcoded author)
    $book = array(
                'author' => "Jack London",
                'title' => $title,
                'numpages' => $page,
                'toc' => $toc);

    return $book;
}

// ---- getBooks(author) ----------------------------------------------------

function getBooks($author) {
    // Here you could query your database for book by an author.
    // Instead, we'll create 3-5 books

    // Initialize books array
    $num = intval(rand(3,5));
    $books = array();
    for($i = 1; $i <= $num; $i++) {
        // Generate a book
        $book = getBook("Title $i");
        // Fixup author
        $book['author'] = $author;
        // Add to book array
        $books[] = $book;
    }

    // Return array
    return $books;
}

?>