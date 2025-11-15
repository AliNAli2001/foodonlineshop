import { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  ScrollView,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useAuthStore } from '@/store/authStore';
import { profileService } from '@/services/profile';

export default function ProfileScreen() {
  const router = useRouter();
  const { user, logout } = useAuthStore();
  const [profile, setProfile] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      const response = await profileService.getProfile();
      setProfile(response.data);
    } catch (error) {
      console.error('Failed to load profile:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    Alert.alert('Logout', 'Are you sure you want to logout?', [
      { text: 'Cancel', onPress: () => {} },
      {
        text: 'Logout',
        onPress: async () => {
          await logout();
          router.replace('/(auth)/login');
        },
      },
    ]);
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2ecc71" />
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.name}>
          {profile?.first_name} {profile?.last_name}
        </Text>
        <Text style={styles.email}>{profile?.email}</Text>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Contact Information</Text>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Phone:</Text>
          <Text style={styles.value}>{profile?.phone}</Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Email Verified:</Text>
          <Text style={styles.value}>
            {profile?.email_verified ? '✓ Yes' : '✗ No'}
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Phone Verified:</Text>
          <Text style={styles.value}>
            {profile?.phone_verified ? '✓ Yes' : '✗ No'}
          </Text>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Preferences</Text>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Language:</Text>
          <Text style={styles.value}>
            {profile?.language_preference === 'ar' ? 'العربية' : 'English'}
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Text style={styles.label}>Promo Consent:</Text>
          <Text style={styles.value}>
            {profile?.promo_consent ? '✓ Yes' : '✗ No'}
          </Text>
        </View>
      </View>

      <TouchableOpacity style={styles.editButton}>
        <Text style={styles.editButtonText}>Edit Profile</Text>
      </TouchableOpacity>

      <TouchableOpacity style={styles.ordersButton} onPress={() => router.push('/orders')}>
        <Text style={styles.ordersButtonText}>My Orders</Text>
      </TouchableOpacity>

      <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
        <Text style={styles.logoutButtonText}>Logout</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    backgroundColor: '#2ecc71',
    padding: 20,
    alignItems: 'center',
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  email: {
    fontSize: 14,
    color: '#fff',
    marginTop: 5,
  },
  section: {
    backgroundColor: '#fff',
    margin: 10,
    padding: 15,
    borderRadius: 8,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 15,
    color: '#333',
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
  editButton: {
    backgroundColor: '#3498db',
    margin: 10,
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  editButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  ordersButton: {
    backgroundColor: '#9b59b6',
    margin: 10,
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  ordersButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  logoutButton: {
    backgroundColor: '#e74c3c',
    margin: 10,
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 30,
  },
  logoutButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

