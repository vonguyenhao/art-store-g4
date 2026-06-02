import { useEffect, useState } from 'react';

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
    <div>
      <h1>Darwin Art Store</h1>

      {products.map((product) => (
        <div
          key={product.product_no}
          style={{
            border: '1px solid #ccc',
            margin: '10px',
            padding: '10px'
          }}
        >
          <h2>{product.description}</h2>

          <p>
            <strong>Category:</strong> {product.category}
          </p>

          <p>
            <strong>Price:</strong> ${product.price}
          </p>

          <p>
            <strong>Colour:</strong> {product.colour}
          </p>

          <p>
            <strong>Size:</strong> {product.size}
          </p>

          <button
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
      <input
        type="hidden"
        name="product_no"
        value={product.product_no}
      />

      <label>
        Quantity
        <input
          type="number"
          name="quantity"
          defaultValue="1"
          min="1"
          max="20"
        />
      </label>

      <button type="submit">
        Add to Cart
      </button>
    </form>
    
        </div>
      ))}

      
    </div>
  );
}

export default App;