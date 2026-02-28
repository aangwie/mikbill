import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { StatusBar } from 'expo-status-bar';
import * as SplashScreenNative from 'expo-splash-screen';
import SplashScreen from './src/screens/SplashScreen';
import WebViewScreen from './src/screens/WebViewScreen';
import Colors from './src/theme/colors';

// Keep the native splash screen visible while we fetch resources
SplashScreenNative.preventAutoHideAsync()
  .catch(() => { /* ignore error */ });

const Stack = createNativeStackNavigator();

/**
 * MikBill Mobile App
 * 
 * Secure WebView wrapper for the MikBill ISP management system
 * hosted at https://pay.billnesia.com
 * 
 * Flow: SplashScreen → WebViewScreen
 */
export default function App() {
  React.useEffect(() => {
    // Hide the native splash screen after a short delay to allow JS to mount
    const timer = setTimeout(async () => {
      try {
        await SplashScreenNative.hideAsync();
      } catch (e) {
        // ignore
      }
    }, 500);
    return () => clearTimeout(timer);
  }, []);

  return (
    <NavigationContainer
      theme={{
        dark: true,
        colors: {
          primary: Colors.primary,
          background: Colors.background,
          card: Colors.surface,
          text: Colors.textPrimary,
          border: Colors.border,
          notification: Colors.danger,
        },
      }}
    >
      <StatusBar style="light" backgroundColor={Colors.background} />
      <Stack.Navigator
        initialRouteName="Splash"
        screenOptions={{
          headerShown: false,
          animation: 'fade',
          contentStyle: { backgroundColor: Colors.background },
        }}
      >
        <Stack.Screen name="Splash" component={SplashScreen} />
        <Stack.Screen
          name="WebView"
          component={WebViewScreen}
          options={{
            // Prevent going back to splash
            gestureEnabled: false,
            headerBackVisible: false,
          }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
