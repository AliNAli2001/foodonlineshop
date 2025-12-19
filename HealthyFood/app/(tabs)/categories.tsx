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
import { useRouter } from 'expo-router';
import { useCategoriesStore } from '@/store/categoriesStore';
import { useLanguageStore } from '@/store/languageStore';

export default function CategoriesScreen() {
  const router = useRouter();
  const { categories, loading, fetchCategories } = useCategoriesStore();
  const { t, language, isRTL } = useLanguageStore();

  useEffect(() => {
    fetchCategories();
  }, []);

  const renderCategory = ({ item }: { item: any }) => (
    <TouchableOpacity
      style={styles.categoryCard}
      onPress={() => router.push(`/category/${item.id}`)}
    >
      {item.image && (
        <Image source={{ uri: item.image }} style={styles.categoryImage} />
      )}
      <Text style={styles.categoryName}>
        {language === 'ar' ? item.name_ar : item.name_en}
      </Text>
    </TouchableOpacity>
  );

  if (loading && categories.length === 0) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2ecc71" />
      </View>
    );
  }

  return (
    <View style={[styles.container, isRTL && styles.rtl]}>
      <FlatList
        data={categories}
        renderItem={renderCategory}
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
  row: {
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  categoryCard: {
    flex: 1,
    backgroundColor: '#fff',
    borderRadius: 8,
    overflow: 'hidden',
    marginHorizontal: 5,
  },
  categoryImage: {
    width: '100%',
    height: 150,
  },
  categoryName: {
    fontSize: 16,
    fontWeight: 'bold',
    padding: 10,
    textAlign: 'center',
    color: '#333',
  },
});

