import { useEffect, useState } from 'react';
import './App.css';

function App() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [csrfToken, setCsrfToken] = useState('');


  useEffect(() => {
    fetch('http://localhost:8000/api/products.php')
      .then((response) => response.json())
      .then((data) => {
        setProducts(data);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Error loading products:', error);
        setLoading(false);
      });
  }, []);

   useEffect(() => {
    fetch('http://localhost:8000/api/csrf.php', {
      credentials: 'include',
    })
      .then((res) => res.json())
      .then((data) => setCsrfToken(data.csrf_token));
  }, []);

  if (loading) {
    return <h1>Loading products...</h1>;
  }

  return (
  <main className="app">

    <nav className="navbar">
      <button
      className="navbar-brand"
      onClick={() => window.location.href = '/'}
    >
      Darwin Art Store
    </button>

  <div className="navbar-links">

    <button onClick={() => window.location.href = 'http://localhost:8000/cart.php'}>
      Cart
    </button>

  </div>
</nav>

    <header className="header">
      <h1>New Darwin collection available</h1>
      <p>Our latest artworks are now available for online orders.</p>

    </header>

    <section className="product-grid">
      {products.map((product) => (
        <article className="product-card" key={product.product_no}>
          {product.image_path ? (
            <img
              className="product-image"
              src={`http://localhost:8000/product_image.php?id=${product.product_no}`}
              alt={product.description}
            />
          ) : (
            <div className="art-placeholder">
              Artwork image coming soon
            </div>
          )}

          <h2>{product.description}</h2>
          <p className="meta">{product.category} | {product.colour} | {product.size}</p>
          <p className="price">${product.price}</p>

          <div className="actions">
            <button
              className="details-button"
              onClick={() =>
                window.location.href =
                  `http://localhost:8000/product.php?id=${product.product_no}`
              }
            >
              View Details
            </button>

            <form method="post" action="http://localhost:8000/cart.php">
              <input type="hidden" name="csrf_token" value={csrfToken} />
              <input type="hidden" name="action" value="add" />
              <input type="hidden" name="product_no" value={product.product_no} />

              <label className="quantity-row">
                Quantity
                <input type="number" name="quantity" defaultValue="1" min="1" max="20" />
              </label>

              <button className="add-button" type="submit">
                Add to Cart
              </button>
            </form>
          </div>
        </article>
      ))}
    </section>
  </main>
);

}

export default App;