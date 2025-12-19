import { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  ScrollView,
  Switch,
  Platform,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useLanguageStore } from '@/store/languageStore';
import { profileService } from '@/services/profile';

// Import the correct Picker
import { Picker } from '@react-native-picker/picker';

export default function EditProfileScreen() {
  const router = useRouter();
  const { t, language, setLanguage, isRTL } = useLanguageStore();
  const [loading, setLoading] = useState(false);
  const [profile, setProfile] = useState<any>(null);
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    language_preference: 'en',
    promo_consent: false,
  });

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      const response = await profileService.getProfile();
      const data = response;
      setFormData({
        first_name: data.first_name || '',
        last_name: data.last_name || '',
        email: data.email || '',
        phone: data.phone || '',
        language_preference: data.language_preference || 'en',
        promo_consent: data.promo_consent || false,
      });
    } catch (error) {
      Alert.alert(t('error'), t('failedToLoadProfile'));
    }
  };

  const handleUpdateProfile = async () => {
    if (!formData.first_name || !formData.last_name || !formData.phone) {
      Alert.alert(t('error'), t('fillAllFields'));
      return;
    }

    setLoading(true);
    try {
      await profileService.updateProfile({
        first_name: formData.first_name,
        last_name: formData.last_name,
        phone: formData.phone,
        language_preference: formData.language_preference,
        promo_consent: formData.promo_consent,
      });

      if (formData.language_preference !== language) {
        await setLanguage(formData.language_preference as 'en' | 'ar');
      }

      Alert.alert(t('success'), t('updateProfile'), [
        { text: t('ok'), onPress: () => router.back() },
      ]);
    } catch (error: any) {
      Alert.alert(t('error'), error.response?.data?.message || t('failedToUpdateProfile'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={[styles.container, isRTL && styles.rtl]}>
      <View style={styles.header}>
        <Text style={styles.title}>{t('editProfile')}</Text>
      </View>

      <View style={styles.form}>
        <View style={styles.formGroup}>
          <Text style={styles.label}>{t('firstName')}</Text>
          <TextInput
            style={styles.input}
            placeholder={t('firstName')}
            value={formData.first_name}
            onChangeText={(text) => setFormData({ ...formData, first_name: text })}
            editable={!loading}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>{t('lastName')}</Text>
          <TextInput
            style={styles.input}
            placeholder={t('lastName')}
            value={formData.last_name}
            onChangeText={(text) => setFormData({ ...formData, last_name: text })}
            editable={!loading}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>{t('email')}</Text>
          <TextInput
            style={[styles.input, styles.disabledInput]}
            placeholder={t('email')}
            value={formData.email}
            editable={false}
          />
          <Text style={styles.helperText}>{t('emailCannotBeChanged') || 'Email cannot be changed'}</Text>
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>{t('phone')}</Text>
          <TextInput
            style={styles.input}
            placeholder={t('phone')}
            value={formData.phone}
            onChangeText={(text) => setFormData({ ...formData, phone: text })}
            keyboardType="phone-pad"
            editable={!loading}
          />
        </View>

        {/* Fixed Picker
        <View style={styles.formGroup}>
          <Text style={styles.label}>{t('language')}</Text>
          <View style={styles.pickerContainer}>
            <Picker
              selectedValue={formData.language_preference}
              onValueChange={(value) =>
                setFormData({ ...formData, language_preference: value })
              }
              enabled={!loading}
              style={styles.picker}
              dropdownIconColor="#666"
            >
              <Picker.Item label={t('english') || 'English'} value="en" />
              <Picker.Item label={t('arabic') || 'العربية'} value="ar" />
            </Picker>
          </View>
        </View> */}

        <View style={styles.switchGroup}>
          <Text style={styles.label}>{t('promoConsent')}</Text>
          <Switch
            value={formData.promo_consent}
            onValueChange={(value) =>
              setFormData({ ...formData, promo_consent: value })
            }
            disabled={loading}
            trackColor={{ false: '#767577', true: '#81c784' }}
            thumbColor={formData.promo_consent ? '#2ecc71' : '#f4f3f4'}
            ios_backgroundColor="#3e3e3e"
          />
        </View>

        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleUpdateProfile}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.buttonText}>{t('updateProfile')}</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.cancelButton}
          onPress={() => router.back()}
          disabled={loading}
        >
          <Text style={styles.cancelButtonText}>{t('cancel')}</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

// Updated Styles (Better Picker on iOS/Android)
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  rtl: {
    direction: 'rtl',
  },
  header: {
    backgroundColor: '#2ecc71',
    padding: 20,
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  form: {
    padding: 20,
  },
  formGroup: {
    marginBottom: 20,
  },
  label: {
    fontSize: 14,
    fontWeight: 'bold',
    marginBottom: 8,
    color: '#333',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 12,
    borderRadius: 8,
    backgroundColor: '#fff',
    fontSize: 14,
  },
  disabledInput: {
    backgroundColor: '#f0f0f0',
    color: '#999',
  },
  helperText: {
    fontSize: 12,
    color: '#999',
    marginTop: 5,
  },
  pickerContainer: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    backgroundColor: '#fff',
    overflow: 'hidden',
    ...Platform.select({
      ios: {
        // iOS needs extra padding
      },
      android: {
        // Android uses native dropdown
      },
    }),
  },
  picker: {
    height: 50,
    width: '100%',
  },
  switchGroup: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
    backgroundColor: '#fff',
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  button: {
    backgroundColor: '#2ecc71',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 10,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  cancelButton: {
    backgroundColor: '#e74c3c',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  cancelButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});