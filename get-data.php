<?php 
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
  include_once "config.php";
  $id = $_SESSION['unique-id'];
  $json = file_get_contents("php://input");
  $type = json_decode($json)->type;
  
  if($type == "patient"){
    $sql = "SELECT * FROM $type WHERE 
    PID = $id";
    $result = $link -> query($sql);
    $row = mysqli_fetch_assoc($result);
    $fname = $row['PFirstName'];
    $mname = $row['PMiddleName'];
    $lname = $row['PLastName'];
    $address = $row['PAddress'];
    $phone = $row["PPhone"];
    
    $response = array(
      "fname" => $fname,
      "mname" => $mname,
      "lname" => $lname,
      "address" => $address,
      "phone" => $phone
    );
  } else if($type == "doctor"){
    $sql = "SELECT * FROM $type WHERE 
    DID = $id";
    $result = $link -> query($sql);
    $row = mysqli_fetch_assoc($result);
    $fname = $row['DFirstName'];
    $lname = $row['DLastName'];
    $experience = $row['Experience'];

    $response = array(
      "fname" => $fname,
      "lname" => $lname,
      "experience" => $experience
    );
  } else if($type == "equip"){
    $sql = "SELECT * FROM medical_equipments WHERE PID = $id";
    $result = $link -> query($sql);
    $response = mysqli_fetch_all($result,MYSQLI_ASSOC);

  } else if($type == "hospital-patient-list"){
    // 1 select those who are covid positive
    $sql = 'SELECT PFIRSTNAME,PLASTNAME FROM patient,medical_tests WHERE patient.PID = 
    medical_tests.PID  AND medical_tests.Diagnosis="COVID-19 Positive"';
    $result = $link -> query($sql);
    $response1 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // 2 select those who are from new delhi
    $sql = "SELECT PFIRSTNAME,PLASTNAME FROM patient
    WHERE patient.PADDRESS='New Delhi'";
    $result = $link -> query($sql);
    $response2 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // 3 select those who need ct scan
    $sql = 'SELECT PFIRSTNAME,PLASTNAME FROM patient, medical_tests
    WHERE patient.PID = medical_tests.PID
    AND medical_tests.DIAGNOSIS = "CT-SCAN NEEDED"';
    $result = $link -> query($sql);
    $response3 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $response = array(
      "19-positive" => $response1,
      "new-delhi" => $response2,
      "ct-scan" => $response3
    );
  } else if($type == "hospital-doctor-list"){
    // 1 Select those with > 10 experience
    $sql = "SELECT DFIRSTNAME,DLASTNAME,EXPERIENCE 
    FROM doctor  
    WHERE EXPERIENCE>10 ORDER BY EXPERIENCE DESC";
    $result = $link -> query($sql);
    $response1 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // 2 Those who did ct scan
    $sql = "SELECT DFIRSTNAME,DLASTNAME 
    FROM doctor,patient,medical_tests WHERE patient.PID = medical_tests.PID AND doctor.did=medical_tests.DID 
    AND medical_tests.TEST_NAME=\"CT-SCAN\"";
    $result = $link -> query($sql);
    $response2 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // 3 doctors who performed RT PCR and belong to New Delhi
    $sql = "select DFIRSTNAME,DLASTNAME from 
    doctor,patient,medical_tests where 
    patient.pid = medical_tests.pid 
    and doctor.did=medical_tests.did and 
    medical_tests.test_name=\"RT-PCR\"  
    and paddress = \"New Delhi\"";
    $result = $link -> query($sql);
    $response3 = mysqli_fetch_all($result, MYSQLI_ASSOC);


    // 4 Doctors who performed CT Scan and belong to New Delhi
    $sql = "select DFIRSTNAME, DLASTNAME 
    from doctor, patient, medical_tests where 
    patient.pid = medical_tests.pid and doctor.did = medical_tests.did and medical_tests.test_name=\"CT-SCAN\"
    and paddress = \"New Delhi\"";
    $result = $link -> query($sql);
    $response4 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $response = array(
      "10-greater" => $response1,
      "ct-scan" => $response2,
      "rt-pcr" => $response3,
      "ct-scan-delhi" => $response4
    );
  } else if($type == "medical-equipment-list"){
    // 1 Select all equipment with names of patients
    $sql = "SELECT EQCATEGORY,PFIRSTNAME,PLASTNAME 
    FROM medical_equipments,patient
    WHERE patient.PID = medical_equipments.PID";
    $result = $link -> query($sql);
    $response1 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    //2 Equipment category with their count
    $sql = "select 
    EQCATEGORY,count(medical_equipments.eqid) as EQUIP_COUNT from 
    medical_equipments,supplier_supplies,supplier where 
    medical_equipments.eqid = supplier_supplies.eqid 
    and supplier.sid = supplier_supplies.sid group by(eqcategory)";
    $result = $link -> query($sql, MYSQLI_ASSOC);
    $response2 = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $response = array(
      "medical-cat-pname" => $response1,
      "medical-cat-count" => $response2
    );
  }
  echo json_encode($response);
?>