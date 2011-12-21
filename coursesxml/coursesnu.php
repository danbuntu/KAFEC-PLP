<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once('/lib/nusoap.php');

include ('corero_conection.php');

$server = new soap_server;

    // wsdl generation
    $server->debug_flag=false;
    $server->configureWSDL('Weather', 'http://weather.org/Weather');
    $server->wsdl->schemaTargetNamespace = 'http://weather.org/Weather';


    // add complex type
    $server->wsdl->addComplexType(
        'StudentData',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'degrees' => array('name'=>'firstname', 'type'=>'xsd:string'),
            'forecast' => array('name'=>'lastname', 'type'=>'xsd:string'))
    );


   // register method
    $server->register('getStudent', array(
        'Student' => 'xsd:string'),
            array('return'=>'tns:StudentData'),
                'http://weather.org/Weather');


  function getStudent($id) {
        $query = "SELECT * FROM studstudent WHERE STUD_Student_ID='" . $id . "'";
        echo $query;

        $result = mssql_query($query);
        $student = array();
        while ($row = mssql_fetch_array($result)) {
            $student[] = $row;
        }
        return $student;
    }

    // simple error checking
if (!$result) {
                return new soap_fault('Server', '', 'Internal server error.');
            }

            // no data avaible for x city
            if (!mysql_num_rows($result)) {
                return new soap_fault('Server', '',
                    'Service contains data only for a few students.');
            }
            mysql_close($link);

            // return data
            return mysql_fetch_array($result, MYSQL_ASSOC);
        }
        // we accept only a string
        else {
          return new soap_fault('Client', '', 'Service requires a string parameter.');
        }
      }

    // pass incoming (posted) data
    $server->service($HTTP_RAW_POST_DATA);






?>
