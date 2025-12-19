import { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  Image,
  TextInput,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useProductsStore } from '@/store/productsStore';
import { useCartStore } from '@/store/cartStore';
import { useLanguageStore } from '@/store/languageStore';

export default function HomeScreen() {
  const router = useRouter();
  const { products, featuredProducts, loading, fetchProducts, fetchFeaturedProducts } =
    useProductsStore();
  const { addItem } = useCartStore();
  const { t, language, isRTL } = useLanguageStore();
  const [search, setSearch] = useState('');

  useEffect(() => {
    fetchFeaturedProducts(language);
    fetchProducts();
  }, [language]);

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
      <Text style={styles.productName}>
        {language === 'ar' ? item.name_ar : item.name_en}
      </Text>
      <Text style={styles.productPrice}>${item.price}</Text>
      <TouchableOpacity
        style={styles.addButton}
        onPress={() => handleAddToCart(item)}
      >
        <Text style={styles.addButtonText}>{t('addToCart')}</Text>
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

  return (
    <View style={[styles.container, isRTL && styles.rtl]}>
      <TextInput
        style={styles.searchInput}
        placeholder={t('search')}
        value={search}
        onChangeText={setSearch}
      />

      {featuredProducts.length > 0 && (
        <>
          <Text style={styles.sectionTitle}>{t('featuredProducts')}</Text>
          <FlatList
            data={featuredProducts}
            renderItem={renderProduct}
            keyExtractor={(item) => item.id.toString()}
            numColumns={2}
            scrollEnabled={false}
            columnWrapperStyle={styles.row}
          />
        </>
      )}

      <Text style={styles.sectionTitle}>{t('allProducts')}</Text>
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
  rtl: {
    direction: 'rtl',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  searchInput: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 10,
    marginBottom: 15,
    borderRadius: 8,
    backgroundColor: '#fff',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
    color: '#333',
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
