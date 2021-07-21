<?php
$servername = "localhost";
$username = "root";
$password = "";
$databasename = "";

$non_fillable = array("id","ID");

$custome_table_fillable = array(
  "user"=>array("customenonfillablecolumn"=>array('id','device_id')),
  "backup"=>array("customenonfillablecolumn"=>array('id','isDelete')),
);

// $custome_table_fillable = array(
//   "user"=>array("customenonfillablecolumn"=>array('id','device_id'),"customefillablecolumn"=>array('name','email','mobile')),
//   "backup"=>array("customenonfillablecolumn"=>array('id','isDelete'),"customefillablecolumn"=>array('file_path','type','isSaveFrom')),
// );

$conn = mysqli_connect($servername, $username, $password , $databasename);

if (!file_exists('./models')) {
    mkdir('./models', 0777, true);
}

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$get_database_table_query  = "SELECT table_name FROM information_schema.tables WHERE table_schema = '".$databasename."'";
$result = $conn->query($get_database_table_query);

$files_generate;
$table_class_name;
$fillable_string = "";
$nonfillable_string = "";
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {

    if(array_key_exists($row['table_name'],$custome_table_fillable)){
        
        $custome_column_list  = $custome_table_fillable[$row['table_name']]['customenonfillablecolumn'];
     
        // $custome_column_list  = array_merge($custome_table_fillable[$row['table_name']]['customenonfillablecolumn'],$custome_table_fillable[$row['table_name']]['customefillablecolumn']);

        $get_database_table_column_query  = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$databasename."' AND `TABLE_NAME`='".$row['table_name']."'";

        $result_column = $conn->query($get_database_table_column_query);
        
        if ($result_column->num_rows > 0) {
          while($row_column = $result_column->fetch_assoc()) {
            if(in_array($row_column['COLUMN_NAME'],$custome_column_list)){
                if(in_array($row_column['COLUMN_NAME'],$custome_table_fillable[$row['table_name']]['customenonfillablecolumn'])){
                  $nonfillable_string .= "\n\t\t'".$row_column['COLUMN_NAME']."',";
                }
                // else if(in_array($row_column['COLUMN_NAME'],$custome_table_fillable[$row['table_name']]['customefillablecolumn'])){
                //   $fillable_string .= "\n\t\t'".$row_column['COLUMN_NAME']."',";
                // }
            }
            else{
              $fillable_string .= "\n\t\t'".$row_column['COLUMN_NAME']."',";
            }
          }
        }

        $table_class_name = str_replace(" ","",ucwords(str_replace(["-","_"]," ",$row['table_name'])," "));
        $files_generate = fopen("models/".$table_class_name.".php", "w") or die("Unable to open file!");     
        fwrite($files_generate, str_replace(['{{CLASSNAMEDYNAMIC}}','{{TABLENAMEDYNAMIC}}','{{FILLABLEDYNAMIC}}','{{NONFILLABLE}}'],[$table_class_name,$row['table_name'],$fillable_string,$nonfillable_string],file_get_contents('./model_template.txt')));
        fclose($files_generate);

        $fillable_string = "";
        $nonfillable_string="";

    }
    else
    {
      $get_database_table_column_query  = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$databasename."' AND `TABLE_NAME`='".$row['table_name']."'";

      $result_column = $conn->query($get_database_table_column_query);
      
      if ($result_column->num_rows > 0) {
        while($row_column = $result_column->fetch_assoc()) {
          if(!in_array($row_column['COLUMN_NAME'], $non_fillable)){
            $fillable_string .= "\n\t\t'".$row_column['COLUMN_NAME']."',";
          }
          else{
            $nonfillable_string .= "\n\t\t'".$row_column['COLUMN_NAME']."',";
          }
        }
      }

      $table_class_name = str_replace(" ","",ucwords(str_replace(["-","_"]," ",$row['table_name'])," "));
      $files_generate = fopen("models/".$table_class_name.".php", "w") or die("Unable to open file!");     
      fwrite($files_generate, str_replace(['{{CLASSNAMEDYNAMIC}}','{{TABLENAMEDYNAMIC}}','{{FILLABLEDYNAMIC}}','{{NONFILLABLE}}'],[$table_class_name,$row['table_name'],$fillable_string,$nonfillable_string],file_get_contents('./model_template.txt')));
      fclose($files_generate);

      $fillable_string = "";
      $nonfillable_string="";
    }
  }
  echo "Operation Successfully Done !";
}else {
    echo "Something is Unexpected " . mysqli_error($conn);
}
mysqli_close($conn);

?>
