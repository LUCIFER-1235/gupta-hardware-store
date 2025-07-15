// === SEARCH FUNCTIONALITY ===
function searchProducts() {
    const query = document.getElementById('searchBox').value.toLowerCase();
    const products = document.querySelectorAll('.product-card');

    products.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        card.style.display = name.includes(query) ? 'block' : 'none';
    });
}

// === ADD TO CART ===
function addToCart(productId) {
    fetch('../server/addToCart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + encodeURIComponent(productId)
    })
    .then(res => res.json())
    .then(data => alert(data.message));
}

// === BUY NOW (Redirect) ===
function buyNow(productId) {
    window.location.href = 'cart.php?buy=' + encodeURIComponent(productId);
}

// === TRENDING SLIDER SCROLL (if arrows used) ===
function scrollTrending(direction) {
    const container = document.querySelector('.trending-bar');
    const scrollAmount = 200;
    container.scrollLeft += (direction === 'left' ? -scrollAmount : scrollAmount);
}
