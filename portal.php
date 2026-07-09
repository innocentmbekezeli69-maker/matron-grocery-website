<?php

session_start();

if (
    !isset($_SESSION['username']) ||
    $_SESSION['role'] !== 'Member'
) {
    header("Location: index.php");
    exit();
}

require_once 'db_config.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Resident Order Portal
    </title>

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

        logout.php" class="logout-link">
            Sign Out
        </a>

    </div>

</header>

<div class="main-layout">

    <main class="catalog-section">

        <h3>Available Groceries</h3>

        <div class="products-grid">

            <?php

            $sql =
            "SELECT
                ItemID,
                Description,
                Price,
                Image
             FROM tblgroceryitems
             WHERE Available = 'Y'
             ORDER BY Description";

            $result = $conn->query($sql);

            if (
                $result &&
                $result->num_rows > 0
            ) {

                while (
                    $row =
                    $result->fetch_assoc()
                ) {

                    $imageFile =
                        !empty($row['Image'])
                        ? $row['Image']
                        : 'default.jpg';

                    $imageUrl =
                        "https://matron-grocery-api.onrender.com/images/" .
                        rawurlencode($imageFile);

                    ?>

                    <div class="product-card">

                        <div class="product-image-container">

                            <?php echo $imageUrl; ?>php echo htmlspecialchars($row['Description']); ?>"
                                class="product-img"
                                onclick="openImageModal(this)"
                                style="cursor:pointer;">

                        </div>

                        <div class="product-info">

                            <h4>
                                <?php echo htmlspecialchars($row['Description']); ?>
                            </h4>

                            <p class="item-id">
                                Code:
                                <?php echo htmlspecialchars($row['ItemID']); ?>
                            </p>

                            <p class="price">
                                R <?php echo number_format($row['Price'], 2); ?>
                            </p>

                            <button
                                class="btn-add"
                                onclick="addToCart(
                                    '<?php echo $row['ItemID']; ?>',
                                    '<?php echo htmlspecialchars($row['Description'], ENT_QUOTES); ?>',
                                    <?php echo $row['Price']; ?>
                                )">

                                Add To Order

                            </button>

                        </div>

                    </div>

                    <?php

                }

            } else {

                echo '<p class="empty-msg">No products available.</p>';

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

function addToCart(id, desc, price)
{
    let found =
        cart.find(
            item => item.id === id
        );

    if (found)
    {
        found.qty++;
    }
    else
    {
        cart.push({
            id:id,
            desc:desc,
            price:price,
            qty:1
        });
    }

    renderCart();
}

function renderCart()
{
    const container =
        document.getElementById(
            'cartItemsContainer'
        );

    const totalDisplay =
        document.getElementById(
            'cartGrandTotal'
        );

    if(cart.length === 0)
    {
        container.innerHTML =
            '<p class="empty-msg">No items added to your active list yet.</p>';

        totalDisplay.innerText =
            'R 0.00';

        return;
    }

    container.innerHTML = '';

    let grandTotal = 0;

    cart.forEach(item =>
    {
        let itemTotal =
            item.price * item.qty;

        grandTotal += itemTotal;

        container.innerHTML +=
        `
        <div class="cart-item-row">

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

        </div>
        `;
    });

    totalDisplay.innerText =
        'R ' +
        grandTotal.toFixed(2);
}

function submitFinalOrder()
{
    if(cart.length === 0)
    {
        alert('Your cart is empty.');
        return;
    }

    fetch(
        'submit_order.php',
        {
            method:'POST',

            headers:{
                'Content-Type':
                'application/json'
            },

            body:JSON.stringify({
                cart:cart
            })
        }
    )
    .then(response => response.json())

    .then(data =>
    {
        if(data.success)
        {
            alert(data.message);

            cart = [];

            renderCart();
        }
        else
        {
            alert(data.message);
        }
    })

    .catch(error =>
    {
        console.error(error);

        alert(
            'Server communication error.'
        );
    });
}

function openImageModal(imgElement)
{
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

function closeImageModal()
{
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
