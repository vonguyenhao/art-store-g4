import { useEffect, useState } from 'react';
import './App.css';

function App() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [csrfToken, setCsrfToken] = useState('');



  // Fetches available products from the PHP backend

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

  // =========================
  // CSRF Security Token
  // =========================

  // Retrieves CSRF token from PHP backend
  // Used when submitting Add to Cart

   useEffect(() => {
    fetch('http://localhost:8000/api/csrf.php', {
      credentials: 'include',
    })
      .then((res) => res.json())
      .then((data) => setCsrfToken(data.csrf_token));
  }, []);

  // Display loading message while products are being retrieved
  if (loading) {
    return <h1>Loading products...</h1>;
  }

  return (

  <>
  
    
    <header className="site-header">
  <button
    className="brand nav-brand-button"
    onClick={() => window.location.href = '/'}
  >
    Darwin Art Store
  </button>

  <nav>
    <a href="/">Shop</a>
    <a href="http://localhost:8000/testimonials.php">Testimonials</a>
    <a href="http://localhost:8000/cart.php">Cart</a>
  </nav>
</header>

<main className="container">
    <section className="hero">
      <div className="hero-content">
        <p className="eyebrow">Darwin local art marketplace</p>
        <h1>Discover original artworks from a small Darwin art company</h1>
        <p>
          Browse available artworks, view details, add pieces to your cart,
          and submit a purchase request directly through our online store.
        </p>
      </div>

      <div className="hero-panel">
        <h2>Why shop with us?</h2>
        <ul className="feature-list">
          <li>Original Darwin-inspired artworks</li>
          <li>Clear product details before ordering</li>
          <li>Simple online purchase request process</li>
          <li>Positive customer feedback</li>
        </ul>
      </div>
    </section>

    <section id="available-artworks" className="section-block">
      <div className="section-heading">
        <div>
          <p className="eyebrow">Available artworks</p>
          <h2>Shop current pieces</h2>
        </div>
      </div>

      <section className="grid product-grid" aria-label="Available artworks">
  {products.map((product) => (
    <article className="card product-card" key={product.product_no}>
      <a
        className="artwork-media"
        href={`http://localhost:8000/product.php?id=${product.product_no}`}
        aria-label={`View ${product.description}`}
      >
        {product.image_path ? (
          <img
            src={`http://localhost:8000/product_image.php?id=${product.product_no}`}
            alt={product.description}
          />
        ) : (
          <span className="artwork-placeholder">
            Artwork image coming soon
          </span>
        )}
      </a>

      <div className="product-card-body">
        <p className="badge">{product.category}</p>

        <h3>
          <a href={`http://localhost:8000/product.php?id=${product.product_no}`}>
            {product.description}
          </a>
        </h3>

        <p className="muted product-meta">
          {product.colour && <>Colour: {product.colour}</>}
          {product.colour && product.size && ' | '}
          {product.size && <>Size: {product.size}</>}
        </p>

        <p className="price">${product.price}</p>
      </div>

      <form className="card-action-form" method="post" action="http://localhost:8000/cart.php">
        <input type="hidden" name="csrf_token" value={csrfToken} />
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="product_no" value={product.product_no} />

        <label>
          Quantity
          <input type="number" name="quantity" defaultValue="1" min="1" max="20" required />
        </label>

        <div className="actions">
          <button type="submit">Add to cart</button>

          <a
            className="button secondary"
            href={`http://localhost:8000/product.php?id=${product.product_no}`}
          >
            Details
          </a>
        </div>
      </form>
    </article>
  ))}
</section>
    </section>
  </main>

  </>
);

}

export default App;