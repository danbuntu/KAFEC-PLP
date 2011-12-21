<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


if (isset($_POST['submit'])) {
 $learnerid = $_POST['student_id'];
 $site = $_POST['site'];

 if ($site == 'Maidstone') {
     $sitedb = 'castmaidstone';
 } elseif ($site == 'Medway') {
      $sitedb = 'castmedway';
 }


 echo 'site db is: ' . $sitedb . '<br/>';
 echo 'site is '. $site . '<br/>';
 echo 'learnerid is ' . $learnerid . '<br/>';

$server = '10.0.100.71';

$link = mssql_connect($server, 'sa', 'r3sult5!');

if (!$link) {
    die('something went wrong with the connecting to Correo mssql database');
}

$select = mssql_select_db('NG_Training');

$learnerquery = "SELECT     STUDstudent.STUD_Student_ID AS LearnerID, STYRstudentYR.STYR_Year AS Year, RTRIM(RTRIM(TutorName.PERS_Forename)
                      + ' ' + TutorName.PERS_Forename_2) + ' ' + TutorName.PERS_Surname AS TutorName, PriorInstitution.CMPN_Company_Name AS PriorInstitution,
                      Employer.CMPN_Company_Name AS EmployerName,
                      CASE WHEN STUD_Gender = 'f' THEN 'Female' WHEN STUD_Gender = 'm' THEN 'Male' ELSE 'Unknown' END AS Gender,
                      STUDstudent.STUD_EMail_Address AS EmailAddress, STUDstudent.STUD_Mobile_Telephone AS MobilePhone,
                      STUDstudent.STUD_Home_Telephone_No AS HomePhone, STUDstudent.STUD_Daytime_Telephone AS DaytimePhone,
                      STUDstudent.STUD_DOB AS DOB, [Learner Learning Difficulty or Health Problem].GNCD_Description AS LLDHP,
                      Disability.GNCD_Description AS Disability, EmergencyContact.EmergencyName, EmergencyContact.SCON_Home_Phone AS Emergency_Home_No,
                      EmergencyContact.SCON_Daytime_Phone, EmergencyContact.SCON_Mobile_Phone AS Emergency_Mobile_No,
                      EmergencyContact.SCON_Relation_to_Student AS EMRRelationshiptoLearner, STYRstudentYR.STYR_Add_Supp_Cost AS AdditionalSupportCost,
                      PriorAttainment.GNCD_Description AS PriorAttainmentLevel, Address.GNAM_Address1, Address.GNAM_Address2, Address.GNAM_Address3,
                      Address.GNAM_Address4, Address.GNAM_Address5, Address.GNAM_Country, Address.GNAM_PostCode, GNALAddressLinks.GNAL_AddressType,
                      GNALAddressLinks.GNAL_ToDate, EMALearners.EMAL_EMA_Number, EMALearners.EMAL_ALG_Reference,
                      STYRstudentYR.STYR_Drop_Out_Date AS LeftCollege, DisadvantageUplift.GNCD_Description AS DU,
                      RestrictedUseIndicator.GNCD_Description AS RestUse, AdditionalSupport.GNCD_Description AS AddSupp,
                      LearnerStatus.GNCD_Description AS StudentStatus, CountryOfDomicile.GNCD_Description AS CoD, Ethnicity.GNCD_Description AS Ethnicity,
                      Nationality.GNCD_Description AS Nationality, Difficulty.GNCD_Description AS Difficulty, NINumber.NINumber, ULN.ULN,
                      RTRIM(STUDstudent.STUD_Title) AS Title, RTRIM(STUDstudent.STUD_Forename_1) + ' ' + RTRIM(STUDstudent.STUD_Surname) AS LearnerName,
                      CASE WHEN STUD_Photo_filename = ' ' THEN 'Nophoto.jpg' ELSE Rtrim(STUD_Photo_filename) END AS Photo, University.[University Number]
FROM         (SELECT     GNCD_Description, GNCD_General_Code
                       FROM          GNCDgncodes AS GNCDgncodes_2
                       WHERE      (GNCD_Code_Type = 'SD')) AS [Learner Learning Difficulty or Health Problem] RIGHT OUTER JOIN
                      STUDstudent INNER JOIN
                      STYRstudentYR ON STYRstudentYR.STYR_Student_ID = STUDstudent.STUD_Student_ID INNER JOIN
                      GNAMAddressMain AS Address INNER JOIN
                      GNALAddressLinks ON Address.GNAM_ISN = GNALAddressLinks.GNAL_GNAM_ISN ON
                      STUDstudent.STUD_Student_ID = GNALAddressLinks.GNAL_EntityRef LEFT OUTER JOIN
                          (SELECT     STAN_Student_ID AS LearnerID, RTRIM(STAN_Alternative_ID) AS [University Number], STAN_Alias_Type
                            FROM          STANaltno AS STANaltno_2
                            GROUP BY STAN_Student_ID, RTRIM(STAN_Alternative_ID), STAN_Alias_Type
                            HAVING      (RTRIM(STAN_Alternative_ID) <> ' ') AND (STAN_Alias_Type = 'UC8')) AS University ON
                      STUDstudent.STUD_Student_ID = University.LearnerID LEFT OUTER JOIN
                          (SELECT     STAN_Student_ID AS LearnerID, RTRIM(STAN_Alternative_ID) AS NINumber
                            FROM          STANaltno
                            WHERE      (STAN_Alias_Type = 'NIN')
                            GROUP BY STAN_Student_ID, RTRIM(STAN_Alternative_ID)
                            HAVING      (RTRIM(STAN_Alternative_ID) <> ' ')) AS NINumber ON STUDstudent.STUD_Student_ID = NINumber.LearnerID LEFT OUTER JOIN
                          (SELECT     STAN_Student_ID AS LearnerID, RTRIM(STAN_Alternative_ID) AS ULN
                            FROM          STANaltno AS STANaltno_1
                            WHERE      (STAN_Alias_Type = 'ULN')
                            GROUP BY STAN_Student_ID, RTRIM(STAN_Alternative_ID)
                            HAVING      (RTRIM(STAN_Alternative_ID) <> ' ')) AS ULN ON STUDstudent.STUD_Student_ID = ULN.LearnerID LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_10
                            WHERE      (GNCD_Code_Type = 'LD')) AS Difficulty ON STYRstudentYR.STYR_Learning_Difficulty = Difficulty.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_11
                            WHERE      (GNCD_Code_Type = 'DB')) AS Disability ON STYRstudentYR.STYR_Disability = Disability.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_1
                            WHERE      (GNCD_Code_Type = 'LPA')) AS PriorAttainment ON STYRstudentYR.STYR_Prior_Attain_Lvl = PriorAttainment.GNCD_General_Code ON
                      [Learner Learning Difficulty or Health Problem].GNCD_General_Code = STYRstudentYR.STYR_LDDHP LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_3
                            WHERE      (GNCD_Code_Type = 'NA')) AS Nationality ON STUDstudent.STUD_Nationality = Nationality.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_5
                            WHERE      (GNCD_Code_Type = 'EH')) AS Ethnicity ON STUDstudent.STUD_Ethnicity = Ethnicity.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_6
                            WHERE      (GNCD_Code_Type = 'COD')) AS CountryOfDomicile ON
                      STYRstudentYR.STYR_COD = CountryOfDomicile.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_8
                            WHERE      (GNCD_Code_Type = 'SY')) AS LearnerStatus ON
                      STYRstudentYR.STYR_Student_Status = LearnerStatus.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_9
                            WHERE      (GNCD_Code_Type = 'LAS')) AS AdditionalSupport ON
                      STYRstudentYR.STYR_Adnl_Supp_Asses = AdditionalSupport.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes AS GNCDgncodes_7
                            WHERE      (GNCD_Code_Type = 'LRU')) AS RestrictedUseIndicator ON
                      STUDstudent.STUD_Rest_Use = RestrictedUseIndicator.GNCD_General_Code LEFT OUTER JOIN
                          (SELECT     GNCD_Description, GNCD_General_Code
                            FROM          GNCDgncodes
                            WHERE      (GNCD_Code_Type = 'UP')) AS DisadvantageUplift ON
                      STYRstudentYR.STYR_DU_Category = DisadvantageUplift.GNCD_General_Code LEFT OUTER JOIN
                      EMALearners ON STYRstudentYR.STYR_Year = EMALearners.EMAL_Year AND
                      STYRstudentYR.STYR_Student_ID = EMALearners.EMAL_Student_ID LEFT OUTER JOIN
                      CMPN_Company_main AS PriorInstitution ON STUDstudent.STUD_School_ISN = PriorInstitution.CMPN_ISN LEFT OUTER JOIN
                      CMPN_Company_main AS Employer ON STUDstudent.STUD_Employer_Code = Employer.CMPN_Company_Code LEFT OUTER JOIN
                      PERSstaff AS TutorName ON STYRstudentYR.STYR_Personal_Tutor = TutorName.PERS_Staff_Code LEFT OUTER JOIN
                      PERSstaff AS Staff ON STYRstudentYR.STYR_Senior_Tutor_ISN = Staff.PERS_ISN LEFT OUTER JOIN
                          (SELECT     SCON_Student_ID, LTRIM(RTRIM(SCON_Title) + ' ' + RTRIM(SCON_Forename1) + ' ' + RTRIM(SCON_Surname)) AS EmergencyName,
                                                   SCON_Home_Phone, SCON_Mobile_Phone, SCON_Daytime_Phone, SCON_Relation_to_Student
                            FROM          SCONContacts
                            WHERE      (SCON_Deceased = 0) AND (SCON_Primary_Emergency = 1)) AS EmergencyContact ON
                      STUDstudent.STUD_Student_ID = EmergencyContact.SCON_Student_ID
WHERE    (STUDstudent.STUD_Student_ID = '" . $learnerid . "') AND
                      (GNALAddressLinks.GNAL_AddressType = 'Home') AND (GNALAddressLinks.GNAL_ToDate IS NULL)";


$results = mssql_query($learnerquery);


//echo 'Note currently hardcoded to 07041437';


while ($row = mssql_fetch_assoc($results)) {
    echo 'Learner Name: ' . $row['LearnerName'] . '<br/>';

    list($firstname, $lastname) = split('[ ]', $row['LearnerName']);
    echo 'firstname: ' . $firstname . '<br/>';
    echo 'lastname: ' . $lastname . '<br/>';
    echo 'DOB: ' . $row['DOB'] . '<br/>';
$dob =  $row['DOB'];
    $street1 = $row['GNAM_Address1'];
    $street2 = $row['GNAM_Address2'];
    $town = $row['GNAM_Address4'];
    $county = $row['GNAM_Address5'];
    $postcode = $row['GNAM_PostCode'];
    $hometel = $row['HomePhone'];
    $mobtel = $row['MobilePhone'];
    $email = $row['EmailAddress'];

    echo 'street1: ' . $street1 . '<br/>';
    echo 'street2: ' . $street2 . '<br/>';
    echo 'town: ' . $town . '<br/>';
    echo 'county: ' . $county . '<br/>';
    echo 'postcode: ' . $postcode . '<br/>';
    echo 'hometel: ' . $hometel . '<br/>';
    echo 'modtel: ' . $mobtel . '<br/>';
    echo 'email: ' . $email . '<br/>';
}

$learnerref = '07041437';

mssql_close($link);



//connect to the cast server

$server = '10.0.100.199';

$link = mysql_connect($server, 'root', '88boom!');

if (!$link) {
    die('something went wrong with the connecting to s-web2 mysql database');
}

mysql_select_db($sitedb);

//half working need to change date format to '2010-05-28 08:22:19'

$dob = strtotime($dob);

$newdate = date('Y-m-d', $dob);

echo 'new date: ' . $newdate;

//INSERT INTO students (firstname, lastname, learnerref, dob, created, modified) VALUES ('Emma', 'Gibbon', '07041437', 'Jul 20 1991 12:00AM',CURDATE(), CURDATE())


//Check a record doesn't already exist
$checkexistance = "SELECT * FROM students WHERE learnerref='" .  $learnerref . "'";
$result3 = mysql_query($checkexistance);
$num_of_rows = mysql_num_rows($result3);
if ($num_of_rows >= 1 ) {
    echo '<br/><h2>Students record already exists in cast database</h2>';
    exit;
}




//insert the details from mis into the cast sytem
$castquery = "INSERT INTO students (firstname, lastname, learnerref, dob, created, modified) VALUES ('" . $firstname . "', '" . $lastname . "', '" . $learnerref . "', '" . $newdate . "',CURDATE(), CURDATE())";
echo '<br/>' . $castquery . '<br/>';
mysql_query($castquery);


//Get the new students record from the cast system in order to find it's id
$castquery2 = "SELECT * FROM students WHERE learnerref='" . $learnerref . "'";
$result2 = mysql_query($castquery2);

while ($row2 = mysql_fetch_assoc($result2)) {
    $castid = $row2['id'];
}
echo '<br/>';
//insert records into the residences table
$queryres = "INSERT INTO residences (house_number, street1, street2, town, county, postcode, home_tel, mobile_tel, email, student_id) VALUES ('', '" . $street1 . "', '" .  $street2 . "', '" .  $town . "', '" .  $county . "', '" .  $postcode . "', '" .  $hometel . "', '" .  $mobtel . "', '" .  $email . "', '" .  $castid . "')";
echo $queryres;

mysql_query($queryres);



} ?>

<h2>Import a new user from NG</h2>

<form name="userimport" method="post" action="userimport.php">
    Enter Student ID number
    <input type="text" name="student_id" value="" />
    <select name="site">
        <option>Select Site</option>
        <option>Maidstone</option>
        <option>Medway</option>
    </select>
    <input name="submit" type="submit" value="Submit" />
</form>