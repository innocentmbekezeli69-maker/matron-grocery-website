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
    <div class="nav-brand">
        Grocery Order Portal
    </div>

    <div class="nav-user">
        Active Session:
        <span id="userDisplay">
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </span>
        |
        <a href="logout.php" class="logout-link">
            Sign Out
        </a>
    </div>
</header>

<div class="main-layout">

    <main class="catalog-section">

        <h3>Available Groceries</h3>

        <div class="products-grid">

            <?php

            $sql = "
                SELECT
                    ItemID,
                    Description,
                    Price
                FROM tblgroceryitems
                WHERE Available = 'Y'
            ";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {

                    renderItemCard(
                        $row['ItemID'],
                        $row['Description'],
                        $row['Price']
                    );

                }

            } else {

                $fallbackItems = [
                    ['BR0001', 'White Bread Loaf', 16.94],
                    ['BR0002', 'Brown Bread Loaf', 17.47],
                    ['ML0001', 'Full Cream Milk 1L', 15.88],
                    ['ML0002', 'Low Fat Milk 1L', 15.99],
                    ['RI0001', 'Rice 5kg', 79.48],
                    ['SU0001', 'White Sugar 2kg', 44.50],
                    ['MA0001', 'Maize Meal 5kg', 62.15],
                    ['OI0001', 'Sunflower Oil 2L', 69.99]
                ];

                foreach ($fallbackItems as $item) {

                    renderItemCard(
                        $item[0],
                        $item[1],
                        $item[2]
                    );

                }

            }

            function renderItemCard($id, $desc, $price)
            {
                $imageSrc =
                    "https://matron-grocery-api.onrender.com/images/" .
                    strtolower($id) .
                    ".jpg";

                echo '<div class="product-card">';

               echo '<div class="product-image-container">';

echo '<img src="' .
     $imageSrc .
     '" alt="' .
     htmlspecialchars($desc) .
     '" o '</div>';

                echo '<div class="product-info">';

                echo '<h4>' .
                     htmlspecialchars($desc) .
                     '</h4>';

                echo '<p class="item-id">';

                echo 'Code: ' .
                     htmlspecialchars($id);

                echo '</p>';

                echo '<p class="price">';

                echo 'R ' .
                     number_format($price, 2);

                echo '</p>';

                echo '<button
                        class="btn-add"
                        onclick="addToCart(
                            \'' . htmlspecialchars($id) . '\',
                            \'' . htmlspecialchars($desc) . '\',
                            ' . $price . '
                        )">

                        Add To Order

                      </button>';

                echo '</div>';

                echo '</div>';
            }

            ?>

        </div>

    </main>

    <aside class="cart-section">

        <h3>Your Order Summary</h3>

        <hr>

        <div id="cartItemsContainer">
            <p class="empty-msg">
                No items added to your active list yet.
            </p>
        </div>

        <hr>

        <div class="cart-total-summary">

            <strong>
                Gross Cumulative Total:
            </strong>

            <span id="cartGrandTotal">
                R 0.00
            </span>

        </div>

        <button
            class="btn-checkout"
            onclick="submitFinalOrder()">

            Submit Weekly Order

        </button>

    </aside>

</div>

<div
    id="imagePreviewModal"
    class="image-modal-overlay"
    onclick="closeImageModal()">

    <div
        class="image-modal-content"
        onclick="event.stopPropagation()">

        <span
            class="image-modal-close"
            onclick="closeImageModal()">

            &times;

        </span>

        

    </div>

</div>

<script>

let cart = [];

function addToCart(id, desc, price) {

    const existingItem =
        cart.find(
            item => item.id === id
        );

    if (existingItem) {

        existingItem.qty += 1;

    } else {

        cart.push({
            id: id,
            desc: desc,
            price: price,
            qty: 1
        });

    }

    renderCart();
}

function renderCart() {

    const container =
        document.getElementById(
            'cartItemsContainer'
        );

    const totalDisplay =
        document.getElementById(
            'cartGrandTotal'
        );

    if (cart.length === 0) {

        container.innerHTML =
            '<p class="empty-msg">No items added to your active list yet.</p>';

        totalDisplay.innerText =
            'R 0.00';

        return;
    }

    container.innerHTML = "";

    let grandTotal = 0;

    cart.forEach(item => {

        let itemTotal =
            item.price * item.qty;

        grandTotal += itemTotal;

        let row =
            document.createElement('div');

        row.className =
            'cart-item-row';

        row.innerHTML = `

            <div>

                <strong>${item.desc}</strong><br>

                <small>
                    Qty: ${item.qty}
                    x
                    R ${item.price.toFixed(2)}
                </small>

            </div>

            <div>

                <strong>
                    R ${itemTotal.toFixed(2)}
                </strong>

            </div>

        `;

        container.appendChild(row);

    });

    totalDisplay.innerText =
        `R ${grandTotal.toFixed(2)}`;
}

function submitFinalOrder() {

    if (cart.length === 0) {

        alert("Your cart is empty!");
        return;

    }

    fetch('submit_order.php', {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json'
        },

        body: JSON.stringify({
            cart: cart
        })

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            alert(
                "Order submitted successfully!"
            );

            cart = [];

            renderCart();

        } else {

            alert(
                "Submission Error: " +
                data.message
            );

        }

    })

    .catch(error => {

        console.error(error);

        alert(
            "Server communication error."
        );

    });

}

function openImageModal(imgElement) {

    const modal =
        document.getElementById(
            'imagePreviewModal'
        );

    const modalImg =
        document.getElementById(
            'modalTargetImage'
        );

    modalImg.src =
        imgElement.src;

    modalImg.alt =
        imgElement.alt;

    modal.classList.add(
        'is-visible'
    );
}

function closeImageModal() {

    document
        .getElementById(
            'imagePreviewModal'
        )
        .classList
        .remove('is-visible');
}

</script>

</body>
</html>

<?php
$conn->close();
?>
