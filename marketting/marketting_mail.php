<?php

/*
 * Generate the emails to send the printers based on the MIS systems
 */

//varibles
//$to = "cudos@belmont.co.uk";
$to = "dan.attwood@midkent.ac.uk";
$subject ="AUTOEMAILORDER Prospectus mailing request for Mid Kent College";

$server = '10.0.100.70';

$link = mssql_connect($server, 'sa', 'r3sult5');

if (!$link) {
    die('something went wrong with the connecting to Correo mssql database');
}

//select the database to use
//$select = mssql_select_db('NGReports');
$select = mssql_select_db('NG');

$query = "select GNALAddressLinks.GNAL_AddressType, e.ENQH_Student_ID, i.ENQI_Information_Code, i.ENQI_Date_Sent, s.STUD_Student_id, s.STUD_Surname, s.STUD_Known_As, s.STUD_Title, address.GNAM_Address1, address.GNAM_Address2, address.GNAM_Address3, address.GNAM_Address4, address.GNAM_Address5, address.GNAM_PostCode
from dbo.ENQHEADER e JOIN dbo.ENQInfoReqd i on e.eNQH_ISN=i.ENQI_ENQH_ISN JOIN dbo.STUDStudent s on e.ENQH_STUDENT_ID=s.STUD_Student_ID JOIN GNAMAddressMain AS Address INNER JOIN
                      GNALAddressLinks ON Address.GNAM_ISN = GNALAddressLinks.GNAL_GNAM_ISN on s.STUD_Student_ID = GNALAddressLinks.GNAL_EntityRef
                      where i.ENQI_Date_Sent is null and (i.ENQI_Information_Code='0035' or i.ENQI_Information_Code='0036' or i.ENQI_Information_Code='0037') and GNALAddressLinks.GNAL_AddressType='HOME'";

$results = mssql_query($query);
$num_rows = mssql_num_rows($results);

echo 'total returned rows ' . $num_rows . '<br/>';


while ($row = mssql_fetch_assoc($results)) {
    echo '<br/>';
    echo '^OrderStart^^~<br/>';
    echo '^CancelDupeORef^True^~<br/>';
    echo '^User^MidKent^~<br/>';
    echo '^OrderRef^' .  $row['STUD_Student_id'] .'^~<br/>';
    echo '^DeliveryAddress1^' . $row['STUD_Title'] . ' ' . $row['STUD_Known_As'] . ' ' . $row['STUD_Surname'] . '^~<br/>';
    echo '^DeliveryAddress2^' . $row['GNAM_Address1'] . '^~<br/>';
    echo '^DeliveryAddress3^' . $row['GNAM_Address2'] . '^~<br/>';
    echo '^DeliveryAddress4^' . $row['GNAM_Address3'] . '^~<br/>';
    echo '^DeliveryAddress5^' . $row['GNAM_Address4'] . '^~<br/>';
    echo '^DeliveryAddress6^' . $row['GNAM_Address5'] . '^~<br/>';
    echo '^DeliveryPostalCode^' . $row['GNAM_PostCode'] . '^~<br/>';
    echo '^DeliveryCountry^Country^~<br/>';
    echo '^LineStart^1^~<br/>';
    echo '^ProductCode^' . $row['ENQI_Information_Code'] . '^~<br/>';
    echo '^ProductQuantity^1^~<br/>';
echo '^LineEnd^1^~<br/>';
echo '^OrderEnd^^~<br/>';
     
$body = 
"^OrderStart^^~
^CancelDupeORef^True^~
^User^mkglobal^~
^OrderRef^" . $row['STUD_Student_id'] . "^~
^DeliveryAddress1^" . $row['STUD_Title'] . ' ' . $row['STUD_Known_As'] . ' ' . $row['STUD_Surname'] . "^~
^DeliveryAddress2^" . $row['GNAM_Address1'] . "^~
^DeliveryAddress3^" . $row['GNAM_Address2'] . "^~
^DeliveryAddress4^" . $row['GNAM_Address3'] . "^~
^DeliveryAddress5^" . $row['GNAM_Address4'] . "^~
^DeliveryAddress6^" . $row['GNAM_Address5'] . "^~
^DeliveryPostalCode^" . $row['GNAM_PostCode'] . "^~
^DeliveryCountry^Country^~
^LineStart^1^~
^ProductCode^" . $row['ENQI_Information_Code'] . "^~
^ProductQuantity^1^~
^LineEnd^1^~
^OrderEnd^^~";

mail($to, $subject, $body);


}



mssql_close($link);

?>