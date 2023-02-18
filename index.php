<?php 

    $servername = "localhost:3306";
    $username = "root";
    $password = "";
    $db = "bdco"; //Connected to this DB 
    $DbName = "newDbBackup";  //Assign new DB name

    // Create connection
    $conn = new mysqli($servername, $username, $password,$db);

    // Check connection
    if ($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
    }
    echo "Connected successfully";
     
    $TN_PK = [];    //contains Table Name and Primary Key
    $limit = 2;     //How many recent records

    //functions
    function createDB($DbName, $conn){
        $dropDb = "DROP DATABASE IF EXISTS $DbName";
        $queryDB = "Create database $DbName";

        if(mysqli_query($conn,$dropDb) && mysqli_query($conn,$queryDB)){
            echo "<br><br>Created $DbName database successfully";
        }else{
            echo("'CreatedNewDb' Error description: " . mysqli_error($conn));
        }
    }

    function createTable($db, $DbName, $tbName, $pk, $limit, $conn){
        $dropTableIfExists = "DROP TABLE IF EXISTS $tbName"."backup";
        $createTableSchema = "CREATE TABLE $DbName.$tbName LIKE $db.$tbName";
        $insertRecords = "INSERT INTO $DbName.$tbName select * from $db.$tbName order by $pk desc limit $limit";
        
        if(mysqli_query($conn,$dropTableIfExists) && mysqli_query($conn,$createTableSchema) &&  mysqli_query($conn,$insertRecords)){
            echo "<br><br> Created new table $tbName with $limit records";
        }else{
            echo("<br><br> 'CreatedNewTableWithSchema&Records' Error description: " . mysqli_error($conn));
        }
    }

    //START ==== (Query to get all table names from DB)
    $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='bdco'";

    $result = mysqli_query($conn,$sql);
    if($result){
        // echo mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)){
            $tableName = $row['TABLE_NAME'];

            $sqlPrimaryKey = "show index from $tableName where Key_name = 'PRIMARY'";
            $result1 = mysqli_query($conn,$sqlPrimaryKey);
            if($row1 = mysqli_fetch_assoc($result1)){
                array_push($TN_PK,$tableName.",".$row1['Column_name']);
            }
        }
    }else{
        echo("<br><br> 'GetTableNames' Error description: " . mysqli_error($conn));
    }

    for($i = 0; $i< count($TN_PK); $i++){
        $TN_PK_str = explode(",", $TN_PK[$i]);
        $i == 0? createDB($DbName, $conn): '';
        createTable($db,$DbName, $TN_PK_str[0], $TN_PK_str[1], $limit, $conn);
    }
?>