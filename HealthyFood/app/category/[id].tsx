import { useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  Image,
} from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useProductsStore } from '@/store/productsStore';
import { useCartStore } from '@/store/cartStore';

export default function CategoryDetailsScreen() {
  const { id } = useLocalSearchParams();
  const router = useRouter();
  const { products, loading, fetchProductsByCategory } = useProductsStore();
  const { addItem } = useCartStore();

  useEffect(() => {
    if (id) {
      fetchProductsByCategory(Number(id), 'en');
    }
  }, [id]);

  const handleAddToCart = (product: any) => {
    addItem({
      product_id: product.id,
      name_en: product.name_en,
      name_ar: product.name_ar,
      price: product.price,
      quantity: 1,
    });
  };

  const renderProduct = ({ item }: { item: any }) => (
    <TouchableOpacity
      style={styles.productCard}
      onPress={() => router.push(`/product/${item.id}`)}
    >
      {item.images && item.images[0] && (
        <Image
          source={{ uri: item.images[0].url }}
          style={styles.productImage}
        />
      )}
      <Text style={styles.productName}>{item.name_en}</Text>
      <Text style={styles.productPrice}>${item.price}</Text>
      <TouchableOpacity
        style={styles.addButton}
        onPress={() => handleAddToCart(item)}
      >
        <Text style={styles.addButtonText}>Add to Cart</Text>
      </TouchableOpacity>
    </TouchableOpacity>
  );

  if (loading && products.length === 0) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2ecc71" />
      </View>
    );
  }

  if (products.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <Text style={styles.emptyText}>No products in this category</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <FlatList
        data={products}
        renderItem={renderProduct}
        keyExtractor={(item) => item.id.toString()}
        numColumns={2}
        columnWrapperStyle={styles.row}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 10,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 18,
    color: '#999',
  },
  row: {
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  productCard: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 8,
    padding: 10,
    marginHorizontal: 5,
  },
  productImage: {
    width: '100%',
    height: 120,
    borderRadius: 8,
    marginBottom: 10,
  },
  productName: {
    fontSize: 14,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  productPrice: {
    fontSize: 16,
    color: '#2ecc71',
    fontWeight: 'bold',
    marginBottom: 10,
  },
  addButton: {
    backgroundColor: '#2ecc71',
    padding: 8,
    borderRadius: 6,
    alignItems: 'center',
  },
  addButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 12,
  },
});

