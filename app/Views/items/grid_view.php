<?php
$checkout_button = "hide";
$has_banner_image = (!isset($login_user->id) && get_setting("banner_image_on_public_store")) ? true : false;
if ($cart_items_count) {
    $checkout_button = "";
}
?>

<?php if ($has_banner_image) { ?>
    <div class="clearfix mb30">
        <img class="w100p" src="<?php echo get_file_from_setting("banner_image_on_public_store", false, get_setting("timeline_file_path")); ?>" alt="..." />
    </div>
<?php } ?>

<style>
    .sidebar-off{
        left: -250px !important;
    }
    .navbar-custom{
        left: 0 !important;
    }
    .page-container {
        margin-left: 0 !important;
    }

/* Header */
.header {
    background: linear-gradient(90deg, #0044cc, #0088ff);
    padding: 10px 0;
    color: white;
}

/* Contenedor principal */
.container {
    width:90%;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Logo */
.logo-container {
    display: flex;
    align-items: center;
}

.logo-img {
    width: 300px;
    margin-right: 10px;
}

.logo {
    font-size: 24px;
    font-weight: bold;
}

/* Barra de búsqueda */
.search-box {
    display: flex;
    background: white;
    border-radius: 25px;
    padding: 5px;
    width: 300px;
}

.search-box input {
    border: none;
    outline: none;
    padding: 8px;
    width: 100%;
    border-radius: 25px 0 0 25px;
}

.search-box button {
    background: #0066cc;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 0 25px 25px 0;
    cursor: pointer;
}

/* Menú de categorías */
.categories ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
}

.categories li {
    margin-left: 20px;
    position: relative;
}

.categories a {
    text-decoration: none;
    color: white;
    font-size: 16px;
    padding: 8px 12px;
    display: block;
}

/* Estilo del menú desplegable */
.dropdown-menu {
    display: none;
    position: absolute;
    background: white;
    top: 30px;
    left: 0;
    min-width: 150px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}

.dropdown-menu li {
    margin: 0;
}

.dropdown-menu a {
    color: #333;
    padding: 10px;
    display: block;
}

.dropdown:hover .dropdown-menu {
    display: block;
}
.btn-out{
    background-color: #fff !important;
    border-radius: 30px;
    color: #0044cc !important;
}

/* Tarjeta flotante en formato horizontal */
.credit-card {
    position: fixed;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    width: 380px;
    height: 250px;
    background: linear-gradient(135deg, #0044cc, #0088ff);
    color: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    font-size: 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-start;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    z-index: 100 !important;
}

/* Efecto al pasar el mouse */
.credit-card:hover {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
}

/* Encabezado de la tarjeta */
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 15px;
}

.bank-name {
    font-size: 16px;
    font-weight: bold;
    text-align: right;
}

/* Chip estilo tarjeta */
.chip {
    width: 50px;
    height: 35px;
    background: linear-gradient(135deg, #d4af37, #b8860b, #ffd700);
    border-radius: 8px;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: inset 2px 2px 3px rgba(255, 255, 255, 0.3), 
                inset -2px -2px 3px rgba(0, 0, 0, 0.3);
}

/* Líneas dentro del chip para simular los circuitos */
.chip::before, .chip::after {
    content: "";
    position: absolute;
    width: 70%;
    height: 2px;
    background: rgba(0, 0, 0, 0.5);
    top: 25%;
    left: 15%;
}

.chip::after {
    top: 65%;
}

/* Número de tarjeta */
.card-number {
    font-size: 25px;

    font-weight: bold;
    margin: 0;
    text-align: center;
    margin-bottom: 10px;
    color:#08149c;
}

/* Contenedor para los detalles de la tarjeta y el saldo disponible */
.card-details {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-bottom: 10px;
}

.detail {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.detail span {
    font-size: 12px;
    opacity: 0.8;
}

/* Saldo disponible */
.card-balance {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    text-align: right;
    font-size: 16px;
    position: relative;
    margin-top: 5px;
}

.card-balance span{
    font-size: 25px;
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
}

/* Logo de Visa */
.visa-logo {
    
    width: 70px;
    position: absolute;
    right: 0;
    bottom: 10px; /* Ajusta la posición según lo que prefieras */
    margin-top: 15px !important;

}

</style>


    <header class="header">
        <div class="container">
            <!-- Logo con nombre de la tienda -->
            <div class="logo-container">
                <img src="<?php echo base_url('files/system/logo_blanco.png'); ?>" alt="Logo de MiTienda" class="logo-img">
            </div>

            <!-- Barra de búsqueda -->
            <div class="search-box">
                <input type="text" placeholder="Buscar productos...">
                <button>🔍</button>
            </div>

            <!-- Menú de categorías -->
            <nav class="categories">
                <ul>
                    <li><a href="#">Ecommerce Rubymed</a></li>
                    <li class="dropdown">
                        <a href="#">Categoria▼</a>
                        <ul class="dropdown-menu d-none">
                            <li><a href="#">Electrónica</a></li>
                            <li><a href="#">Ropa</a></li>
                            <li><a href="#">Hogar</a></li>
                            <li><a href="#">Deportes</a></li>
                        </ul>
                    </li>

                    <li><a href="./" class="btn-out" href="#">Salir <i class="fas fa-sign-out-alt"></i></a></li>
                </ul>
            </nav>
        </div>
    </header>

  <!-- Tarjeta de crédito horizontal flotante -->
  <div class="credit-card">
    <div class="card-header">
        <div class="d-flex">
            <div class="chip"></div>
            <img style="width: 40px;transform: rotate(90deg);" src="<?php echo base_url('files/system/signal.png'); ?>" alt="">
        </div>
        <h2 class="bank-name">BUSINESS UNLIMITED</h2>
    </div>
    <div class="card-number">
        <p>9090 5678 9012 3456</p>
    </div>
    <div class="card-details">
        <div class="detail">
            <span>Titular:</span>
            <p>Juan Pérez</p>
        </div>
        <div class="detail">
            <span>Expira:</span>
            <p>12/28</p>
        </div>
        <div class="detail">
            <span>CVV:</span>
            <p>123</p>
        </div>
    </div>
    <div class="card-balance">
        <span>$1,250,000</span>

        <!-- Agregar el logo de Visa al lado derecho -->
        <img src="<?php echo base_url('files/system/signature.png'); ?>" alt="Visa" class="visa-logo pt-2">
    </div>
</div>




<div id="page-content" class="page-wrapper clearfix <?php echo $has_banner_image ? "pt0" : ""; ?>">
    <div class="<?php echo isset($login_user->id) ? "" : "container store-page"; ?>">
        <div class="clearfix mb15">
            <h4 class="float-start"><?php echo app_lang('store'); ?></h4>

            <div class="float-end">
                <?php echo anchor(get_uri("store/process_order"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang("checkout"), array("id" => "item-checkout-button", "class" => "btn btn-success $checkout_button ml15")); ?>
            </div>

            <div class="float-end item-search-box-section">
                <?php
                echo form_input(array(
                    "id" => "item-search-box",
                    "class" => "form-control custom-filter-search item-search-box",
                    "placeholder" => app_lang('search'),
                ));
                ?>
            </div>
            <div class="float-end custom-toolbar item-categories-filter-section">
                <?php
                echo form_input(array(
                    "id" => "item-categories-filter",
                    "name" => "item-categories-filter",
                    "class" => "select2 w200 mr15"
                ));
                ?>
            </div>
        </div>

        <div class="row" id="items-container">
            <?php echo view("items/items_grid_data"); ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var $searchBox = $("#item-search-box");

        $searchBox.on("keyup", function (e) {
            if (!(e.which >= 37 && e.which <= 40)) {
                //witi 200 ms to request ajax cll
                clearTimeout($.data(this, 'timer'));
                var wait = setTimeout(getItemSuggestions, 200);
                $(this).data('timer', wait);
            }
        });

        var $itemCategoriesFilter = $("#item-categories-filter");
        $itemCategoriesFilter.select2({
            data: <?php echo $categories_dropdown; ?>
        }).on("change", function () {
            getItemSuggestions();
        });

        function getItemSuggestions() {
            appLoader.show();

            $.ajax({
                url: "<?php echo get_uri('store/index/'); ?>",
                data: {search: $searchBox.val(), item_search: true, category_id: $itemCategoriesFilter.val()},
                cache: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    appLoader.hide();

                    if (response.success) {
                        $("#items-container").html(response.data);
                    }
                }
            });
        }

        $("body").on("click", ".item-add-to-cart-btn", function () {
            var itemId = $(this).attr("data-item_id"),
                    $instance = $(this);
            appLoader.show();

            //add item to the order items table and show count on cart box
            $.ajax({
                url: "<?php echo get_uri('store/add_item_to_cart'); ?>" + "/" + itemId,
                data: {id: itemId},
                cache: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    appLoader.hide();

                    if (response.success) {
                        //reload cart value
                        window.countCartItems();

                        //change the selector when it's added from view modal
                        if ($("#ajaxModal").hasClass('in')) {
                            $instance = $("#items-container").find("[data-item_id='" + itemId + "']");
                            $("#ajaxModal").modal('hide');
                        }

                        //change button text
                        $instance.text("<?php echo app_lang("added_to_cart"); ?>");
                        $instance.removeClass("item-add-to-cart-btn");
                        $instance.attr("disabled", "disabled");
                    }
                }
            });
        });

        window.refreshAfterUpdate = true;
    });
</script>