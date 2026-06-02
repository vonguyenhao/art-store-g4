import { useEffect, useState } from 'react';

function App() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

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
        </div>
      ))}
    </div>
  );
}

export default App;