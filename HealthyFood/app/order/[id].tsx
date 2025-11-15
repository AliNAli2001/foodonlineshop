import { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  ScrollView,
} from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import { ordersService } from '@/services/orders';

export default function OrderDetailsScreen() {
  const { id } = useLocalSearchParams();
  const [order, setOrder] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (id) {
      loadOrder();
    }
  }, [id]);

  const loadOrder = async () => {
    try {
      const response = await ordersService.getOrder(Number(id));
      setOrder(response.data);
    } catch (error) {
      console.error('Failed to load order:', error);
    } finally {
      setLoading(false);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending':
        return '#f39c12';
      case 'confirmed':
        return '#3498db';
      case 'shipped':
        return '#9b59b6';
      case 'delivered':
        return '#2ecc71';
      case 'canceled':
        return '#e74c3c';
      default:
        return '#95a5a6';
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2ecc71" />
      </View>
    );
  }

  if (!order) {
    return (
      <View style={styles.centerContainer}>
        <Text>Order not found</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.orderId}>Order #{order.id}</Text>
        <View
          style={[
            styles.statusBadge,
            { backgroundColor: getStatusColor(order.status) },
          ]}
        >
          <Text style={styles.statusText}>{order.status.toUpperCase()}</Text>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Order Information</Text>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Date:</Text>
          <Text style={styles.value}>
            {new Date(order.created_at).toLocaleDateString()}
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Total Amount:</Text>
          <Text style={[styles.value, styles.amount]}>${order.total_amount}</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Delivery Method:</Text>
          <Text style={styles.value}>{order.delivery_method}</Text>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Delivery Address</Text>
        <Text style={styles.address}>{order.delivery_address}</Text>
        {order.latitude && order.longitude && (
          <Text style={styles.coordinates}>
            📍 {order.latitude.toFixed(4)}, {order.longitude.toFixed(4)}
          </Text>
        )}
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Items</Text>
        {order.items?.map((item: any) => (
          <View key={item.id} style={styles.itemRow}>
            <View style={styles.itemInfo}>
              <Text style={styles.itemName}>{item.product_name}</Text>
              <Text style={styles.itemDetails}>
                {item.quantity} x ${item.unit_price}
              </Text>
            </View>
            <Text style={styles.itemPrice}>${item.subtotal}</Text>
          </View>
        ))}
      </View>

      {order.delivery && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Delivery Person</Text>
          <View style={styles.infoRow}>
            <Text style={styles.label}>Name:</Text>
            <Text style={styles.value}>{order.delivery.name}</Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={styles.label}>Phone:</Text>
            <Text style={styles.value}>{order.delivery.phone}</Text>
          </View>
        </View>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
    padding: 10,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 8,
    marginBottom: 15,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  orderId: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  statusText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  section: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 8,
    marginBottom: 15,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 15,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  label: {
    fontWeight: 'bold',
    color: '#666',
  },
  value: {
    color: '#333',
  },
  amount: {
    color: '#2ecc71',
    fontWeight: 'bold',
    fontSize: 16,
  },
  address: {
    fontSize: 14,
    color: '#333',
    lineHeight: 20,
    marginBottom: 10,
  },
  coordinates: {
    fontSize: 12,
    color: '#666',
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  itemInfo: {
    flex: 1,
  },
  itemName: {
    fontWeight: 'bold',
  },
  itemDetails: {
    fontSize: 12,
    color: '#666',
    marginTop: 5,
  },
  itemPrice: {
    fontWeight: 'bold',
    color: '#2ecc71',
  },
});

