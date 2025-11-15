import { Stack } from 'expo-router';

export default function AuthLayout() {
  return (
    <Stack>
      <Stack.Screen name="login" options={{ headerShown: false }} />
      <Stack.Screen name="register" options={{ headerShown: false }} />
      <Stack.Screen name="verify-email" options={{ title: 'Verify Email' }} />
      <Stack.Screen name="verify-phone" options={{ title: 'Verify Phone' }} />
    </Stack>
  );
}

