<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="author" content="Sahil Kumar">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Shopping Cart System</title>
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css' />
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css' />
  <link rel="stylesheet" href="./style.css">
  
</head>

<body>
  <!-- Navbar start -->
  <nav class="navbar navbar-expand-md bg-dark navbar-dark">
    <a class="navbar-brand" href="index.php"><i class="fas fa-mobile-alt"></i>&nbsp;&nbsp;Mobile Store</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapsibleNavbar">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link active" href="index.php"><i class="fas fa-mobile-alt mr-2"></i>Products</a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="checkout.php"><i class="fas fa-money-check-alt mr-2"></i>Checkout</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> <span id="cart-item" class="badge badge-danger"></span></a>
        </li>
      </ul>
    </div>
  </nav>
  <!-- Navbar end -->
  <!-- ******************carousel********************-->
  
    <div id="Carousel-slider">
  <section>
    <div class="Carousel-slider">
      <!-- Background Images div -->
      <div class="slider-item superHero1" data-href="#" ></div>
      <div class="slider-item superHero2" data-href="#" ></div>
      <div class="slider-item superHero3" data-href="#" ></div>
      <div class="slider-item superHero4" data-href="#" ></div>
      <div class="slider-item superHero5" data-href="#" ></div>
      <div class="slider-item superHero6" data-href="#" ></div>
      <div class="slider-item superHero7" data-href="#" ></div>
      <div class="slider-item superHero8" data-href="#" ></div>
      <!-- Background Images div End -->
    </div>
  </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/micro-slider@1.0.9/dist/micro-slider.min.js"></script>
<script  src="./script.js"></script>
  <!-- **************************************-->

  <!-- Affichage des produits début -->
  <div class="container">
    <div id="message"></div>
    <div class="row mt-2 pb-3">
      <?php
  			include 'config.php';
  			$stmt = $conn->prepare('SELECT * FROM product');
  			$stmt->execute();
  			$result = $stmt->get_result();
  	while ($row = $result->fetch_assoc()):
  		?>
      <div class="col-sm-6 col-md-4 col-lg-3 mb-2">
        <div class="card-deck">
          <div class="card p-2 border-secondary mb-2">
            <img src="<?= $row['product_image'] ?>" class="card-img-top" height="250">
            <div class="card-body p-1">
              <h4 class="card-title text-center text-info"><?= $row['product_name'] ?></h4>
              <h5 class="card-text text-center text-danger">  &nbsp;&nbsp;<?= number_format($row['product_price'],2) ?> DA</h5>
              <h5 class=" text-center text-info">stock:&nbsp;<?= $row['stock'] ?> </h5>

            </div>
            <div class="card-footer p-1">
              <form action="" class="form-submit">
                <div class="row p-2">
                  <div class="col-md-6 py-1 pl-4">
                    <b>Quantity : </b>
                  </div>
                  <div class="col-md-6">
                    <input type="number" class="form-control pqty" value="<?= $row['product_qty'] ?>">
                  </div>
                </div>
                <input type="hidden" class="pid" value="<?= $row['id'] ?>">
                <input type="hidden" class="pname" value="<?= $row['product_name'] ?>">
                <input type="hidden" class="pprice" value="<?= $row['product_price'] ?>">
                <input type="hidden" class="pimage" value="<?= $row['product_image'] ?>">

                <input type="hidden" class="pcode" value="<?= $row['product_code'] ?>">
                <button class="btn btn-info btn-block addItemBtn"><i class="fas fa-cart-plus"></i>&nbsp;&nbsp;Add to
                  cart</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <!-- Affichage des produits  End -->

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js'></script>

  <script type="text/javascript">
    $(document).ready(function() {
        // "Envoyer les détails du produit au serveur."
        $(".addItemBtn").click(function(e) {
            e.preventDefault();
            var $form = $(this).closest(".form-submit");
            var pid = $form.find(".pid").val();
            var pname = $form.find(".pname").val();
            var pprice = $form.find(".pprice").val();
            var pimage = $form.find(".pimage").val();
            var pcode = $form.find(".pcode").val();
            var pqty = $form.find(".pqty").val();

            // Construire l'objet représentant le produit
            var productDetails = {
                'pid': pid,
                'pname': pname,
                'pprice': pprice,
                'pimage': pimage,
                'pcode': pcode,
                'pqty': pqty
            };

            // Récupérer tous les produits actuels du panier depuis le stockage local
            var cartProducts = JSON.parse(localStorage.getItem('cartProducts')) || [];

            // Ajouter le nouveau produit au tableau
            cartProducts.push(productDetails);

            // Mettre à jour le stockage local avec le tableau mis à jour
            localStorage.setItem('cartProducts', JSON.stringify(cartProducts));

            // Envoi des détails du produit au serveur
            $.ajax({
                url: 'action.php',
                method: 'post',
                data: {
                    pid: pid,
                    pname: pname,
                    pprice: pprice,
                    pqty: pqty,
                    pimage: pimage,
                    pcode: pcode
                },
                success: function(response) {
                    $("#message").html(response);
                    window.scrollTo(0, 0);
                    load_cart_item_number();
                }
            });
        });

        // Charger le nombre total d'articles ajoutés dans la carte et l'afficher dans la navbar."
        load_cart_item_number();

        function load_cart_item_number() {
            $.ajax({
                url: 'action.php',
                method: 'get',
                data: {
                    cartItem: "cart_item"
                },
                success: function(response) {
                    $("#cart-item").html(response);
                }
            });
        }
    });
</script>

  </script>
</body>

</html>