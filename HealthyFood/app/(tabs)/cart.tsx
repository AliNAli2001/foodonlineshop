import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  TextInput,
} from 'react-native';
import { useRouter, useFocusEffect } from 'expo-router';
import { useCartStore } from '@/store/cartStore';
import { useLanguageStore } from '@/store/languageStore';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { useCallback } from 'react'; // ← useCallback
export default function CartScreen() {
  const router = useRouter();
  const { items, totalPrice, removeItem, updateQuantity } = useCartStore();
  const { t,language, isRTL } = useLanguageStore();
// ← FORCE RE-RENDER EVERY TIME SCREEN IS FOCUSED
  useFocusEffect(
    useCallback(() => {
      // This runs EVERY time the tab is tapped
      console.log('CartScreen focused – language:', language);
      // No need to do anything else – just triggers re-render
    }, [language]) // ← Re-run when language changes
  );
  const renderCartItem = ({ item }: { item: any }) => (
    <View style={[styles.cartItem, isRTL && styles.rtl]}>
      <View style={styles.itemInfo}>
        <Text style={styles.itemName}>
          {isRTL ? item.name_ar : item.name_en}
        </Text>
        <Text style={styles.itemPrice}>${item.price}</Text>
      </View>

      <View style={styles.quantityControl}>
        <TouchableOpacity onPress={() => updateQuantity(item.product_id, item.quantity - 1)}>
          <MaterialCommunityIcons name="minus" size={20} color="#2ecc71" />
        </TouchableOpacity>
        <Text style={styles.quantity}>{item.quantity}</Text>
        <TouchableOpacity onPress={() => updateQuantity(item.product_id, item.quantity + 1)}>
          <MaterialCommunityIcons name="plus" size={20} color="#2ecc71" />
        </TouchableOpacity>
      </View>

      <Text style={styles.subtotal}>${(item.price * item.quantity).toFixed(2)}</Text>

      <TouchableOpacity onPress={() => removeItem(item.product_id)}>
        <MaterialCommunityIcons name="delete" size={20} color="#e74c3c" />
      </TouchableOpacity>
    </View>
  );

  if (items.length === 0) {
    return (
      <View style={[styles.emptyContainer, isRTL && styles.rtl]}>
        <MaterialCommunityIcons name="shopping" size={64} color="#ccc" />
        <Text style={styles.emptyText}>{t('yourCartIsEmpty')}</Text>
        <TouchableOpacity
          style={styles.continueButton}
          onPress={() => router.push('/(tabs)')}
        >
          <Text style={styles.continueButtonText}>{t('continueShopping')}</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={[styles.container, isRTL && styles.rtl]}>
      <FlatList
        data={items}
        renderItem={renderCartItem}
        keyExtractor={(item) => item.product_id.toString()}
      />

      <View style={styles.footer}>
        <View style={styles.totalRow}>
          <Text style={styles.totalLabel}>{t('total')}:</Text>
          <Text style={styles.totalPrice}>${totalPrice.toFixed(2)}</Text>
        </View>

        <TouchableOpacity
          style={styles.checkoutButton}
          onPress={() => router.push('/checkout')}
        >
          <Text style={styles.checkoutButtonText}>{t('proceedToCheckout')}</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  rtl: {
    direction: 'rtl',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 18,
    color: '#999',
    marginTop: 10,
  },
  continueButton: {
    backgroundColor: '#2ecc71',
    padding: 12,
    borderRadius: 8,
    marginTop: 20,
  },
  continueButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  cartItem: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    padding: 15,
    marginBottom: 10,
    marginHorizontal: 10,
    borderRadius: 8,
    alignItems: 'center',
  },
  itemInfo: {
    flex: 1,
  },
  itemName: {
    fontSize: 14,
    fontWeight: 'bold',
  },
  itemPrice: {
    fontSize: 12,
    color: '#2ecc71',
  },
  quantityControl: {
    flexDirection: 'row',
    alignItems: 'center',
    marginHorizontal: 10,
  },
  quantity: {
    marginHorizontal: 10,
    fontWeight: 'bold',
  },
  subtotal: {
    fontWeight: 'bold',
    marginRight: 10,
    minWidth: 50,
    textAlign: 'right',
  },
  footer: {
    backgroundColor: '#fff',
    padding: 15,
    borderTopWidth: 1,
    borderTopColor: '#ddd',
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 15,
  },
  totalLabel: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  totalPrice: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#2ecc71',
  },
  checkoutButton: {
    backgroundColor: '#2ecc71',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  checkoutButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

