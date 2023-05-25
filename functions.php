<?php

	//Establecer la conexión con la base de datos
	require("dbconnection.php");

	session_start();
	

	//Consulta 1 - Devolver Filas Resultantes
	function getNumRowsQuery($query) {
		global $sqlconnection;
		if ($result = $sqlconnection->query($query))
			return $result->num_rows;
		else
			echo "Error en la consulta!";
	}

	//Consulta 2 - Devuelve un arreglo asociativo con los resultado
	function getFetchAssocQuery($query) {
		global $sqlconnection;
		if ($result = $sqlconnection->query($query)) {
			
			while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        		echo "\n", $row["itemID"], $row["menuID"], $row["menuItemName"], $row["price"];
    		}

			return ($result);
		}
		else
			echo "Error en la consulta!";
			echo $sqlconnection->error;
	}

	//Consulta 3 - Obtiene el último ID tabla
	function getLastID($id,$table) {
		global $sqlconnection;

		$query = "SELECT MAX({$id}) AS {$id} from {$table} ";

		if ($result = $sqlconnection->query($query)) {
			
			$res = $result->fetch_array();

			//Si no se encuentra ID en la tabla
			if ($res[$id] == NULL)
				return 0;

			return $res[$id];
		}
		else {
			echo $sqlconnection->error;
			return null;
		}
	}

	//Consulta 4 -  Obtiene el número de filas en una tabla que id es igual a idnum
	function getCountID($idnum,$id,$table) {
		global $sqlconnection;

		$query = "SELECT COUNT({$id}) AS {$id} from {$table} WHERE {$id}={$idnum}";

		if ($result = $sqlconnection->query($query)) {
			
			$res = $result->fetch_array();

			//Si no se encuentra ID en la tabla
			if ($res[$id] == NULL)
				return 0;

			return $res[$id];
		}
		else {
			echo $sqlconnection->error;
			return null;
		}
	}

	//Consulta 5 - Obtiene el total de ventas de un pedido
	function getSalesTotal($orderID) {
		global $sqlconnection;
		$total = null;

		$query = "SELECT total FROM tbl_order WHERE orderID = ".$orderID;

		if ($result = $sqlconnection->query($query)) {
		
			if ($res = $result->fetch_array()) {
				$total = $res[0];
				return $total;
			}

			return $total;
		}

		else {

			echo $sqlconnection->error;
			return null;

		}
	}

	//Consulta 6 - Obtiene el total de ventas acumulado para un período de tiempo especificado. Puede ser "ALLTIME" para todas las ventas o "DAY", "MONTH" o "WEEK" para ventas en el último día, mes o semana
	function getSalesGrandTotal($duration) {
		global $sqlconnection;
		$total = 0;

		if ($duration == "ALLTIME") {
			$query = "
					SELECT SUM(total) as grandtotal
					FROM tbl_order
					";
		}

		else if ($duration == ("DAY" || "MONTH" || "WEEK")) {

			$query = "
					SELECT SUM(total) as grandtotal
					FROM tbl_order

					WHERE order_date > DATE_SUB(NOW(), INTERVAL 1 ".$duration.")
					";
		}

		else 
			return null;

		if ($result = $sqlconnection->query($query)) {
		
			while ($res = $result->fetch_array(MYSQLI_ASSOC)) {
				$total+=$res['grandtotal'];
			}

			return $total;
		}

		else {

			echo $sqlconnection->error;
			return null;

		}
	}

	//Consulta 7 - La consulta utiliza una subconsulta que calcula el valor total multiplicando la cantidad de cada elemento en el pedido (almacenada en la tabla "tbl_orderdetail") por el precio de ese elemento (almacenado en la tabla "tbl_menuitem"). La subconsulta se une a la tabla principal "tbl_order" mediante las cláusulas JOIN para obtener la orden específica
	function updateTotal($orderID) {
		global $sqlconnection;

		$query = "
			UPDATE tbl_order o
			INNER JOIN (
			    SELECT SUM(OD.quantity*mi.price) AS total
			        FROM tbl_order O
			        LEFT JOIN tbl_orderdetail OD
			        ON O.orderID = OD.orderID
			        LEFT JOIN tbl_menuitem MI
			        ON OD.itemID = MI.itemID
			        LEFT JOIN tbl_menu M
			        ON MI.menuID = M.menuID
			        
			        WHERE o.orderID = ".$orderID."
			) x
			SET o.total = x.total
			WHERE o.orderID = ".$orderID."
		";

		if ($sqlconnection->query($query) === TRUE) {
				echo "Actualizado";
			} 

		else {

				echo "Algo anda mal";
				echo $sqlconnection->error;

		}

	}

?>