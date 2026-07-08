<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Member') {
    header("Location: index.php");
    exit();
}

require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resident Order Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header class="navbar">
    <div class="nav-brand">Grocery Order Portal</div>
    <div class="nav-user">
        Active Session: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> | 
        <a href="logout.php" class="logout-link">Sign Out</a>
    </div>
</header>

<div class="main-layout">
    <main class="catalog-section">
        <h3>Available Groceries</h3>
        <div class="products-grid">
            <?php
            // Double check table casing if running on case-sensitive hosting systems
            $sql = "SELECT ItemID, Description, Price, Image FROM tblGroceryItems WHERE Available='Y' ORDER BY Description";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image = !empty($row['Image']) ? $row['Image'] : 'default.jpg';
                    $imageUrl = "https://matron-grocery-api.onrender.com/images/" . rawurlencode($image);
                    ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($row['Description']); ?>"
                                 class="product-img"
                                 onclick="openImageModal(this)"
                                 style="cursor:pointer;">
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($row['Description']); ?></h4>
                            <p class="item-id">Code: <?php echo htmlspecialchars($row['ItemID']); ?></p>
                            <p class="price">R <?php echo number_format($row['Price'], 2); ?></p>
                            
                            <button class="btn-add" 
                                    onclick="addToCart(
                                        '<?php echo addslashes($row['ItemID']); ?>',
                                        '<?php echo addslashes($row['Description']); ?>',
                                        <?php echo floatval($row['Price']); ?>
                                    )">
                                Add To Order
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No products available.</p>';
            }
            ?>
        </div>
    </main>

    <aside class="cart-section">
        <h3>Your Order Summary</h3>
        <hr>
        <div id="cartItemsContainer">
            <p>No items added.</p>
        </div>
        <hr>
        <div class="cart-total-summary">
            <strong>Total:</strong>
            <span id="cartGrandTotal">R 0.00</span>
        </div>
        <button class="btn-checkout" onclick="submitFinalOrder()">Submit Weekly Order</button>
    </aside>
</div>

<div id="imagePreviewModal" class="image-modal-overlay" style="display:none;" onclick="closeImageModal()">
    <div class="image-modal-content" onclick="event.stopPropagation()">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img id="modalTargetImage" src="" alt="Preview" style="width:100%; max-height:75vh; object-fit:contain;">
    </div>
</div>

<script>
let cart = [];

function addToCart(id, desc, price) {
    let existing = cart.find(item => item.id === id);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ id: id, desc: desc, price: price, qty: 1 });
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItemsContainer');
    const totalDisplay = document.getElementById('cartGrandTotal');
    let total = 0;
    container.innerHTML = '';

    cart.forEach(item => {
        let rowTotal = item.price * item.qty;
        total += rowTotal;
        container.innerHTML += `
        <div class="cart-item-row">
            <div>
                <strong>${item.desc}</strong><br>
                Qty: ${item.qty}
            </div>
            <div>R ${rowTotal.toFixed(2)}</div>
        </div>`;
    });

    if (cart.length === 0) {
        container.innerHTML = '<p>No items added.</p>';
    }
    totalDisplay.innerText = 'R ' + total.toFixed(2);
}

function submitFinalOrder() {
    if (cart.length === 0) {
        alert('Your cart is empty.');
        return;
    }

    fetch('submit_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart: cart })
    })
    // Safely parse JSON text from background endpoint
    .then(response => response.text().then(text => {
        try {
            return JSON.parse(text);
        } catch (err) {
            // Throw fallback message containing server stack trace
            throw new Error(text);
        }
    }))
    .then(data => {
        if (data.success) {
            alert(data.message);
            cart = [];
            renderCart();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error("Submission Error Details:", error);
        alert('Server communication error. Check your API backend connectivity endpoints.');
    });
}

function openImageModal(img) {
    const target = document.getElementById('modalTargetImage');
    if(target) {
        target.src = img.src;
        document.getElementById('imagePreviewModal').style.display = 'block';
    }
}

function closeImageModal() {
    document.getElementById('imagePreviewModal').style.display = 'none';
}
</script>

</body>
</html>
<?php $conn->close(); ?>
