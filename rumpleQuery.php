<?php
/* ----------------------------------------------------------
    Alright reserved by Sangol Ramses Galanga
    @(Glg) 2020
    Code updated by Ramses Galanga, date : 2022
------------------------------------------------------------*/
    class RumpleQuery{
        private $dbname = "leika_db";
        private $hostname = "localhost";
        private $username = "root";
        private $password = "";
        private $port;
        private $conn;

        // Set Server Information (host,database,username,password,port); port is optional parameter
        public function __construct(){
           $this->connexionDB();

        }   
        private function connexionDB(){
            try{
                    $this->conn = new PDO("mysql:host=$this->hostname;dbname=$this->dbname","$this->username","$this->password");
                }catch(Exception $e){
                    $this->errorMessage("Echec de connexion à la base de données. <br> Erreur : ".$e->getMessage());
                }
                
        }
        public function errorMessage($message){
            echo ("
                        <center>
                        <nav style='padding: 10px; background-color: red; color: white; text-align: center;'>
                            <h2> Erreur: ".$message."</h2>
                        </nav>
                        </center>");
        }
        public function successMessage($message){
            echo ("
                        <center>
                        <nav style='padding: 20px; background-color: green; color: white; text-align: center;'>
                            <h1>Success:  </h1>
                            <h2>".$message."</h2>
                        </nav>
                        </center>");
        }
        // ----------------------------------------------------------------
        private function parseValue(array $row){
            $valueMap = "";
            for($i = 0; $i < count($row); $i++){
                if($i+1 == count($row)) $valueMap.="'".$row[$i]."'";
                else  $valueMap.="'".$row[$i]."', ";
            } 
            return $valueMap;
        }
        private function parseRow(array $row){
            $rowMap = "";
            for($i = 0; $i < count($row); $i++){
                if($i+1 == count($row)) $rowMap.=$row[$i];
                else  $rowMap.=$row[$i].", ";
            } 
            return $rowMap;
        }

        private function mapValueForUpdate(array $columns, array $value){
            $mapped = "";
            for($i = 0; $i < count($columns);$i++){

                if($i+1 == count($columns)){
                    $mapped .= $columns[$i]." ='".$value[$i]."'";
                }else $mapped .= $columns[$i]." ='".$value[$i]."', ";
            }
            return $mapped;
        }
        // --------------------------------------------------------------------------------
        //To insert a new data you need the table name, column name and values
        //insert(table name, column name, value to insert into)
        //Ex: insert("student",["name","age","gender"],["Patrick","19","male"]);
        public function insert($table,array $row,array $value){
                $rowMap = $this->parseRow($row);
                $valueMap = $this->parseValue($value);
                $succes = "";

                try{
                    $query = "INSERT INTO $table ($rowMap) VALUES ($valueMap)";
                    $req = $this->conn->prepare($query);
                    $req->execute();
                    $succes = "Operation successful";
                    $this->successMessage($succes);
                   
                }catch(PDOException $e){

                    $succes = "Operation failed".$this->errorMessage($e.getMessage());;
                }
            return $succes;
        }
        //----------------------------------------------------------------
        //To select data from table you need the table name and the row name
        // or just * to select all rows from the table
        //Ex1: select("student",["name","gender"]); that, select the name and the gender of the student
        //Ex2: select("student",["*"]); that, select all columns from the table student
        public function select($table, array $rows,$column= null,$value = null){
            $rowMap = $this->parseRow($rows);
            $query ="";
            if($column != null && $value!==null){
                $query = "SELECT * FROM $table WHERE $column = '$value'";
            }else{
                if($rowMap == "*")  $query = "SELECT * FROM $table ";
                else $query = "SELECT ($rowMap) FROM $table";
            }
                
           try{
                $req = $this->conn->prepare($query);
                $req->execute();
                $res = $req->fetchAll();
              
                return $res;
           }catch(PDOException $e){
            $this->errorMessage($e.getMessage());
        }
    }
    // -----------------------JSON QUERY SELECT -----------------------------------------
        public function jsonSelect($table){
            $data = array();
			try {
				$req = $this->conn->query("SELECT donnees FROM $table");
                while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                    $data[] = $row;
                    return $data;
                }
			} catch (\Throwable $th) {
				echo "ERREUR".$th->getMessage();
			}
        }
    // ----------------------------------------------------------------
    //To delete a row from the table 
    //you need to set the table name where you whant to delete element
    // and the value of one of it's columns
    // Ex: delete("Student","id","4"); this delete the student where his id is 4.
    public function delete($table,$row,$value){
        $query = "DELETE FROM $table WHERE $row = $value";
        $succes = "";
        try{
            $req = $this->conn->prepare($query);
            $req->execute();
            $succes = "Operation successful";
            $this->successMessage($succes);
        }catch(PDOException $e){
            $succes = "Operation failed";
            $this->errorMessage($succes.'  '.$e.getMessage());
        }
        return $succes;
    }
    // --------------------------------------------------------------------
    public function update($table,array $columns,array $value,$columnRef,$ref){
        $set = $this->mapValueForUpdate($columns,$value);
        $query = "UPDATE $table SET $set WHERE $columnRef = '$ref' ";
        $succes = "";
        try{
            $req = $this->conn->prepare($query);
            $req->execute();
            $succes = "Operation successful";
            $this->successMessage($succes);
        }catch(PDOException $e){
            $succes = "Operation failed";
            $this->errorMessage($succes);
        }
        return $succes;
    }
    
    // --------------------------------------------------------------------
    //User Name Authentication method
    //This Method is partial
    //May be get an error with a different columns name like the following 'nom' or 'mdp'
    public function auth($table,$name,$mdp){
			try{
				$req = $this->conn->prepare("SELECT * FROM $table WHERE nom=? and mdp=? ");
				$req->execute(array($name,$mdp));
                $exist = $req->rowCount();
				if($exist == 1){
                    return true;
                }
                else{
                    return false;
                }
			}catch(PDOException $e){
                $this->errorMessage("ERREUR".$e->getMessage());
            }
				
		}

    //Email Authentication method
    //This Method is partial
    //May be get an error with a different columns name like the following 'nom' or 'mdp'
    public function emailAuth($table,$email,$mdp){
        try{
            $req = $this->conn->prepare("SELECT * FROM $table WHERE email=? and mdp=? ");
            $req->execute(array($email,$mdp));
            $exist = $req->rowCount();
            if($exist == 1){
                return true;
            }
            else{
                return false;
            }
        }catch(PDOException $e){
            $this->errorMessage("ERREUR".$e->getMessage());
        }
    }
      
}

/*---JSON TEST COTE CLIENT 
$rQuery = new RumpleQuery();
    $req = $rQuery->jsonSelect('commandes');
    $commandes = json_decode($req[0]['donnees']);
    foreach ($commandes as $command){
        echo $command->stock;
    }
    /*echo $commandes[0]->stock;
    //var_dump($req);
    //$a = json_encode($commandes);

    $data =["donnees"=>'[{"nom" : "Ali", "commandes" :"AAA"},{"nom" : "Pierre", "commandes" :"bbb"}]'];

    $dataE = json_encode($data);
    //$dataD = json_decode($data);
    //var_dump($dataD);
    $dataBodyE=$data['donnees'];
    echo $dataBodyE;
    $dataBodyD = json_decode($dataBodyE);
    echo $dataBodyD[0]->nom;
    //echo $dataD[1]->nom;
   //print($commandes);*/
?>