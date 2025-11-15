import { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Image,
  TouchableOpacity,
  ActivityIndicator,
  ScrollView,
  Alert,
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useProductsStore } from '@/store/productsStore';
import { useCartStore } from '@/store/cartStore';

export default function ProductDetailsScreen() {
  const { id } = useLocalSearchParams();
  const router = useRouter();
  const { currentProduct, loading, fetchProduct } = useProductsStore();
  const { addItem } = useCartStore();
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    if (id) {
      fetchProduct(Number(id), 'en');
    }
  }, [id]);

  const handleAddToCart = () => {
    if (!currentProduct) return;

    addItem({
      product_id: currentProduct.id,
      name_en: currentProduct.name_en,
      name_ar: currentProduct.name_ar,
      price: currentProduct.price,
      quantity,
    });

    Alert.alert('Success', 'Product added to cart', [
      { text: 'Continue Shopping', onPress: () => router.back() },
      { text: 'Go to Cart', onPress: () => router.push('/(tabs)/cart') },
    ]);
  };

  if (loading || !currentProduct) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2ecc71" />
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      {currentProduct.images && currentProduct.images[0] && (
        <Image
          source={{ uri: currentProduct.images[0].url }}
          style={styles.image}
        />
      )}

      <View style={styles.content}>
        <Text style={styles.name}>{currentProduct.name_en}</Text>
        <Text style={styles.price}>${currentProduct.price}</Text>

        <Text style={styles.description}>{currentProduct.description_en}</Text>

        <View style={styles.stockInfo}>
          <Text style={styles.stockLabel}>Available Stock:</Text>
          <Text style={styles.stockValue}>{currentProduct.available_stock} units</Text>
        </View>

        <View style={styles.quantitySection}>
          <Text style={styles.quantityLabel}>Quantity:</Text>
          <View style={styles.quantityControl}>
            <TouchableOpacity
              onPress={() => setQuantity(Math.max(1, quantity - 1))}
              style={styles.quantityButton}
            >
              <Text style={styles.quantityButtonText}>-</Text>
            </TouchableOpacity>
            <Text style={styles.quantityValue}>{quantity}</Text>
            <TouchableOpacity
              onPress={() =>
                setQuantity(
                  Math.min(
                    currentProduct.max_order_item,
                    currentProduct.available_stock,
                    quantity + 1
                  )
                )
              }
              style={styles.quantityButton}
            >
              <Text style={styles.quantityButtonText}>+</Text>
            </TouchableOpacity>
          </View>
        </View>

        <TouchableOpacity style={styles.addButton} onPress={handleAddToCart}>
          <Text style={styles.addButtonText}>Add to Cart</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  image: {
    width: '100%',
    height: 300,
  },
  content: {
    padding: 20,
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  price: {
    fontSize: 28,
    color: '#2ecc71',
    fontWeight: 'bold',
    marginBottom: 15,
  },
  description: {
    fontSize: 14,
    color: '#666',
    lineHeight: 20,
    marginBottom: 20,
  },
  stockInfo: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: '#f5f5f5',
    padding: 10,
    borderRadius: 8,
    marginBottom: 20,
  },
  stockLabel: {
    fontWeight: 'bold',
  },
  stockValue: {
    color: '#2ecc71',
    fontWeight: 'bold',
  },
  quantitySection: {
    marginBottom: 20,
  },
  quantityLabel: {
    fontWeight: 'bold',
    marginBottom: 10,
  },
  quantityControl: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  quantityButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#2ecc71',
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantityButtonText: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  quantityValue: {
    marginHorizontal: 20,
    fontSize: 18,
    fontWeight: 'bold',
  },
  addButton: {
    backgroundColor: '#2ecc71',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  addButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

