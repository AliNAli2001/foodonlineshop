import { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  ScrollView,
} from 'react-native';
import { useRouter, useFocusEffect } from 'expo-router';
import { useAuthStore } from '@/store/authStore';
import { useLanguageStore } from '@/store/languageStore';
import { profileService } from '@/services/profile';

export default function ProfileScreen() {
  const router = useRouter();
  const { user, logout } = useAuthStore();
  const { t, language, setLanguage, isRTL } = useLanguageStore();
  const [profile, setProfile] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const loadProfile = async () => {
    setLoading(true);
    try {
      const response = await profileService.getProfile();
      console.log('Profile API Response:', response); // Debug log
      setProfile(response);
    } catch (error: any) {
      console.error('Failed to load profile:', error?.message || error);
      Alert.alert(t('error'), t('failedToLoadProfile') || 'Could not load profile.');
    } finally {
      setLoading(false);
    }
  };

  // Reload profile every time screen comes into focus
  useFocusEffect(
    useCallback(() => {
      loadProfile();
    }, [])
  );

  const handleLogout = async () => {
    Alert.alert(t('logout'), t('logoutConfirm'), [
      { text: t('cancel'), style: 'cancel' },
      {
        text: t('logout'),
        style: 'destructive',
        onPress: async () => {
          await logout();
          router.replace('/(auth)/login');
        },
      },
    ]);
  };

  const handleLanguageChange = async (lang: 'en' | 'ar') => {
    await setLanguage(lang);
  };

  // Show loading if still fetching or no profile
  if (loading || !profile) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2ecc71" />
        <Text style={{ marginTop: 10, color: '#666' }}>
          {t('loading') || 'Loading profile...'}
        </Text>
      </View>
    );
  }

  return (
    <ScrollView style={[styles.container, isRTL && styles.rtl]}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.name}>
          {profile.first_name} {profile.last_name}
        </Text>
        <Text style={styles.email}>{profile.email}</Text>
      </View>

      {/* Language Selector */}
      <View style={styles.languageSelector}>
        <TouchableOpacity
          style={[
            styles.langButton,
            language === 'en' && styles.langButtonActive,
          ]}
          onPress={() => handleLanguageChange('en')}
        >
          <Text
            style={[
              styles.langButtonText,
              language === 'en' && styles.langButtonTextActive,
            ]}
          >
            English
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[
            styles.langButton,
            language === 'ar' && styles.langButtonActive,
          ]}
          onPress={() => handleLanguageChange('ar')}
        >
          <Text
            style={[
              styles.langButtonText,
              language === 'ar' && styles.langButtonTextActive,
            ]}
          >
            العربية
          </Text>
        </TouchableOpacity>
      </View>

      {/* Contact Information */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>{t('contactInformation') || 'Contact Information'}</Text>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('phone') || 'Phone'}:</Text>
          <Text style={styles.value}>{profile.phone || '-'}</Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('emailVerified') || 'Email Verified'}:</Text>
          <Text style={styles.value}>
            {profile.email_verified === true ? `${t('yes')} ✓` : `${t('no')} ✗`}
          </Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('phoneVerified') || 'Phone Verified'}:</Text>
          <Text style={styles.value}>
            {profile.phone_verified === true ? `${t('yes')} ✓` : `${t('no')} ✗`}
          </Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('address') || 'Address'}:</Text>
          <Text style={styles.value}>
            {profile.address_details || t('notProvided') || 'Not provided'}
          </Text>
        </View>
      </View>

      {/* Preferences */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>{t('preferences') || 'Preferences'}</Text>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('languagePreference') || 'Language'}:</Text>
          <Text style={styles.value}>
            {profile.language_preference === 'ar' ? t('arabic') || 'Arabic' : t('english') || 'English'}
          </Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('promoConsent') || 'Marketing Consent'}:</Text>
          <Text style={styles.value}>
            {profile.promo_consent === true ? `${t('yes')} ✓` : `${t('no')} ✗`}
          </Text>
        </View>
      </View>

      {/* Account Info */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>{t('accountInfo') || 'Account Information'}</Text>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('memberSince') || 'Member Since'}:</Text>
          <Text style={styles.value}>
            {profile.created_at
              ? new Date(profile.created_at).toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
              })
              : '-'}
          </Text>
        </View>

        <View style={styles.infoRow}>
          <Text style={styles.label}>{t('lastUpdated') || 'Last Updated'}:</Text>
          <Text style={styles.value}>
            {profile.updated_at
              ? new Date(profile.updated_at).toLocaleString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
              })
              : '-'}
          </Text>
        </View>
      </View>

      {/* Action Buttons */}
      <TouchableOpacity
        style={styles.editButton}
        onPress={() => router.push('/edit-profile')}
      >
        <Text style={styles.editButtonText}>{t('editProfile') || 'Edit Profile'}</Text>
      </TouchableOpacity>

      <TouchableOpacity
        style={styles.ordersButton}
        onPress={() => router.push('/orders')}
      >
        <Text style={styles.ordersButtonText}>{t('myOrders') || 'My Orders'}</Text>
      </TouchableOpacity>

      <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
        <Text style={styles.logoutButtonText}>{t('logout') || 'Logout'}</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

// Styles
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  rtl: {
    direction: 'rtl',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  header: {
    backgroundColor: '#2ecc71',
    padding: 25,
    alignItems: 'center',
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
  },
  name: {
    fontSize: 26,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 5,
  },
  email: {
    fontSize: 15,
    color: '#fff',
    opacity: 0.9,
  },
  languageSelector: {
    flexDirection: 'row',
    justifyContent: 'center',
    padding: 15,
    gap: 12,
  },
  langButton: {
    paddingHorizontal: 18,
    paddingVertical: 10,
    borderRadius: 25,
    backgroundColor: '#fff',
    borderWidth: 1.5,
    borderColor: '#ddd',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
  },
  langButtonActive: {
    backgroundColor: '#2ecc71',
    borderColor: '#2ecc71',
  },
  langButtonText: {
    fontSize: 13,
    fontWeight: 'bold',
    color: '#666',
  },
  langButtonTextActive: {
    color: '#fff',
  },
  section: {
    backgroundColor: '#fff',
    margin: 12,
    padding: 18,
    borderRadius: 12,
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: 'bold',
    marginBottom: 16,
    color: '#2c3e50',
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 11,
    borderBottomWidth: 1,
    borderBottomColor: '#f0f0f0',
  },
  label: {
    fontWeight: '600',
    color: '#555',
    flex: 1,
  },
  value: {
    color: '#333',
    flex: 1,
    textAlign: 'right',
    fontWeight: '500',
  },
  editButton: {
    backgroundColor: '#3498db',
    marginHorizontal: 12,
    marginTop: 10,
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    elevation: 2,
  },
  editButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  ordersButton: {
    backgroundColor: '#9b59b6',
    marginHorizontal: 12,
    marginTop: 10,
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    elevation: 2,
  },
  ordersButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  logoutButton: {
    backgroundColor: '#e74c3c',
    marginHorizontal: 12,
    marginTop: 10,
    marginBottom: 30,
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    elevation: 2,
  },
  logoutButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});