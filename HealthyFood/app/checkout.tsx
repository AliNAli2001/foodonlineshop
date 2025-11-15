import { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  Alert,
  ScrollView,
  Picker,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useCartStore } from '@/store/cartStore';
import { ordersService } from '@/services/orders';
import * as Location from 'expo-location';

export default function CheckoutScreen() {
  const router = useRouter();
  const { items, totalPrice, clearCart } = useCartStore();
  const [deliveryMethod, setDeliveryMethod] = useState('delivery');
  const [address, setAddress] = useState('');
  const [latitude, setLatitude] = useState<number | null>(null);
  const [longitude, setLongitude] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  const handleGetLocation = async () => {
    try {
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Permission Denied', 'Location permission is required');
        return;
      }

      const location = await Location.getCurrentPositionAsync({});
      setLatitude(location.coords.latitude);
      setLongitude(location.coords.longitude);
      Alert.alert('Success', 'Location captured');
    } catch (error) {
      Alert.alert('Error', 'Failed to get location');
    }
  };

  const handlePlaceOrder = async () => {
    if (!address) {
      Alert.alert('Error', 'Please enter delivery address');
      return;
    }

    setLoading(true);
    try {
      const cartData = JSON.stringify(
        items.reduce((acc: any, item) => {
          acc[item.product_id] = { quantity: item.quantity, price: item.price };
          return acc;
        }, {})
      );

      await ordersService.createOrder({
        order_source: 'inside_city',
        delivery_method: deliveryMethod as any,
        address_details: address,
        latitude: latitude || undefined,
        longitude: longitude || undefined,
        cart_data: cartData,
      });

      clearCart();
      Alert.alert('Success', 'Order placed successfully', [
        { text: 'OK', onPress: () => router.replace('/orders') },
      ]);
    } catch (error: any) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to place order');
    } finally {
      setLoading(false);
    }
  };

  if (items.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <Text style={styles.emptyText}>Your cart is empty</Text>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => router.back()}
        >
          <Text style={styles.backButtonText}>Back to Cart</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Order Summary</Text>
        {items.map((item) => (
          <View key={item.product_id} style={styles.orderItem}>
            <Text style={styles.itemName}>{item.name_en}</Text>
            <Text style={styles.itemDetails}>
              {item.quantity} x ${item.price} = ${(item.quantity * item.price).toFixed(2)}
            </Text>
          </View>
        ))}
        <View style={styles.totalRow}>
          <Text style={styles.totalLabel}>Total:</Text>
          <Text style={styles.totalPrice}>${totalPrice.toFixed(2)}</Text>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Delivery Method</Text>
        <Picker
          selectedValue={deliveryMethod}
          onValueChange={setDeliveryMethod}
          style={styles.picker}
        >
          <Picker.Item label="Delivery" value="delivery" />
          <Picker.Item label="Hand Delivered" value="hand_delivered" />
          <Picker.Item label="Shipping" value="shipping" />
        </Picker>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Delivery Address</Text>
        <TextInput
          style={styles.input}
          placeholder="Enter delivery address"
          value={address}
          onChangeText={setAddress}
          multiline
          numberOfLines={3}
        />

        <TouchableOpacity style={styles.locationButton} onPress={handleGetLocation}>
          <Text style={styles.locationButtonText}>📍 Get Current Location</Text>
        </TouchableOpacity>

        {latitude && longitude && (
          <Text style={styles.locationText}>
            Location: {latitude.toFixed(4)}, {longitude.toFixed(4)}
          </Text>
        )}
      </View>

      <TouchableOpacity
        style={[styles.placeOrderButton, loading && styles.buttonDisabled]}
        onPress={handlePlaceOrder}
        disabled={loading}
      >
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.placeOrderButtonText}>Place Order</Text>
        )}
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
    padding: 10,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 18,
    color: '#999',
    marginBottom: 20,
  },
  backButton: {
    backgroundColor: '#2ecc71',
    padding: 12,
    borderRadius: 8,
  },
  backButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  section: {
    backgroundColor: '#fff',
    padding: 15,
    marginBottom: 15,
    borderRadius: 8,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 15,
  },
  orderItem: {
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  itemName: {
    fontWeight: 'bold',
  },
  itemDetails: {
    fontSize: 12,
    color: '#666',
    marginTop: 5,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  totalLabel: {
    fontWeight: 'bold',
  },
  totalPrice: {
    fontWeight: 'bold',
    color: '#2ecc71',
  },
  picker: {
    marginBottom: 15,
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 10,
    borderRadius: 8,
    marginBottom: 15,
    minHeight: 80,
  },
  locationButton: {
    backgroundColor: '#3498db',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 10,
  },
  locationButtonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  locationText: {
    fontSize: 12,
    color: '#666',
    marginTop: 10,
  },
  placeOrderButton: {
    backgroundColor: '#2ecc71',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 30,
  },
  placeOrderButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
});

