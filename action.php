<?php
	session_start();
	require 'config.php';

	// Add products into the cart table
	if (isset($_POST['pid'])) {
		
	  $pid = $_POST['pid'];
	  $pname = $_POST['pname'];
	  $pprice = $_POST['pprice'];
	  $pimage = $_POST['pimage'];
	  $pcode = $_POST['pcode'];
	  $pqty = $_POST['pqty'];
	  $total_price = $pprice * $pqty;
	  /*******************vérification stock ***********************/
	 $stmt_stock = $conn->prepare('SELECT stock FROM product WHERE product_code=?');
    $stmt_stock->bind_param('s', $pcode);
    $stmt_stock->execute();
    $res_stock = $stmt_stock->get_result();
    $r_stock = $res_stock->fetch_assoc();
    $stock = $r_stock['stock'] ?? 0;

if ($stock > 0) {
        if ($pqty <= $stock) {
            // Stock disponible, ajouter le produit au panier
            $stmt = $conn->prepare('SELECT product_code FROM cart WHERE product_code=?');
            $stmt->bind_param('s', $pcode);
            $stmt->execute();
            $res = $stmt->get_result();
            $r = $res->fetch_assoc();
            $code = $r['product_code'] ?? '';

            if (!$code) {
                // Ajouter le produit au panier
                $query = $conn->prepare('INSERT INTO cart (product_name,product_price,product_image,qty,total_price,product_code) VALUES (?,?,?,?,?,?)');
                $query->bind_param('ssssss', $pname, $pprice, $pimage, $pqty, $total_price, $pcode);
                $query->execute();

                echo '<div class="alert alert-success alert-dismissible mt-2">
                          <button type="button" class="close" data-dismiss="alert">&times;</button>
                          <strong>Item added to your cart!</strong>
                        </div>';
            } else {
                // Le produit est déjà dans le panier
                echo '<div class="alert alert-danger alert-dismissible mt-2">
                          <button type="button" class="close" data-dismiss="alert">&times;</button>
                          <strong>Item already added to your cart!</strong>
                        </div>';
            }
        } else {
            // La quantité demandée est supérieure au stock disponible
            echo '<div class="alert alert-danger alert-dismissible mt-2">
                      <button type="button" class="close" data-dismiss="alert">&times;</button>
                      <strong>Stock is not sufficient for the requested quantity!</strong>
                    </div>';
        }
    } else {
        // Le produit n'est pas disponible (stock épuisé)
        echo '<div class="alert alert-danger alert-dismissible mt-2">
                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                  <strong>Product is not available (out of stock)!</strong>
                </div>';
    }
	}

	// Get no.of items available in the cart table
	if (isset($_GET['cartItem']) && isset($_GET['cartItem']) == 'cart_item') {
	  $stmt = $conn->prepare('SELECT * FROM cart');
	  $stmt->execute();
	  $stmt->store_result();
	  $rows = $stmt->num_rows;

	  echo $rows;
	}

	// Remove single items from cart
	if (isset($_GET['remove'])) {
	  $id = $_GET['remove'];

	  $stmt = $conn->prepare('DELETE FROM cart WHERE id=?');
	  $stmt->bind_param('i',$id);
	  $stmt->execute();

	  $_SESSION['showAlert'] = 'block';
	  $_SESSION['message'] = 'Item removed from the cart!';
	  header('location:cart.php');
	}

	// Remove all items at once from cart
	if (isset($_GET['clear'])) {
	  $stmt = $conn->prepare('DELETE FROM cart');
	  $stmt->execute();
	  $_SESSION['showAlert'] = 'block';
	  $_SESSION['message'] = 'All Item removed from the cart!';
	  header('location:cart.php');
	}

	// Set total price of the product in the cart table
	if (isset($_POST['qty'])) {
	  $qty = $_POST['qty'];
	  $pid = $_POST['pid'];
	  $pprice = $_POST['pprice'];

	  $tprice = $qty * $pprice;

	  $stmt = $conn->prepare('UPDATE cart SET qty=?, total_price=? WHERE id=?');
	  $stmt->bind_param('isi',$qty,$tprice,$pid);
	  $stmt->execute();
	  
	}

	// Checkout and save customer info in the orders table
	if (isset($_POST['action']) && isset($_POST['action']) == 'order') {
	  $name = $_POST['name'];
	  $email = $_POST['email'];
	  $phone = $_POST['phone'];
	  $products = $_POST['products'];
	  
/************************************** Convertir $products en array associatif*******************************************/
	   
$productList = explode(', ', $products);

$productData = [];

foreach ($productList as $product) {
    // Séparer le nom du produit et la quantité
    list($productName, $quantity) = explode('(', str_replace(')', '', $product));

    // Ajouter les données au tableau associatif sans guillemets autour du nom du produit
    $productData[trim($productName)] = (int)$quantity;
}


/***************************** Modification de stock ****************************************************/
foreach ($productData as $productName => $quantity) {
    // Supposons que 'product_name' est la colonne qui stocke le nom du produit
    // et 'stock' est la colonne qui stocke la quantité en stock dans la table 'product'
    
    // Vérifier si le stock est suffisant avant la mise à jour
    $checkStockStmt = $conn->prepare('SELECT stock FROM product WHERE product_name = ?');
    $checkStockStmt->bind_param('s', $productName);
    $checkStockStmt->execute();
    $checkStockStmt->bind_result($currentStock);
    $checkStockStmt->fetch();
    $checkStockStmt->close();

    if ($currentStock - $quantity >= 0) {
        // Mettre à jour le stock dans la table 'product'
        $updateStockStmt = $conn->prepare('UPDATE product SET stock = stock - ? WHERE product_name = ?');
        $updateStockStmt->bind_param('is', $quantity, $productName);
        $updateStockStmt->execute();
        $updateStockStmt->close();
    } else {
        // Stock insuffisant, afficher une alerte
        echo '<script>alert("Stock insuffisant pour ' . $productName . '");</script>';
    }
}


	   /*********************************************************************************/
	  $grand_total = $_POST['grand_total'];
	  $address = $_POST['address'];
	  $pmode = $_POST['pmode'];

	  $data = '';

	  $stmt = $conn->prepare('INSERT INTO orders (name,email,phone,address,pmode,products,amount_paid)VALUES(?,?,?,?,?,?,?)');
	  $stmt->bind_param('sssssss',$name,$email,$phone,$address,$pmode,$products,$grand_total);
	  $stmt->execute();
	  $stmt2 = $conn->prepare('DELETE FROM cart');
	  $stmt2->execute();
	  
    
   
	  $data .= '<div class="text-center">
								<h1 class="display-4 mt-2 text-success">Thank You!</h1>
								<h2 class="text-success">Your Order Placed Successfully!</h2>
								<h4 class="bg-info text-light rounded p-2">Items Purchased : ' . $products . '</h4>
								<h4>Your Name : ' . $name . '</h4>
								<h4>Your E-mail : ' . $email . '</h4>
								<h4>Your Phone : ' . $phone . '</h4>
								<h4>Total Amount Paid : ' . number_format($grand_total,2) . '</h4>
								<h4>Payment Mode : ' . $pmode . '</h4>
						  </div>';
	  echo $data;
	}
?>

