import React, { useState, useRef, useCallback, useEffect } from 'react';
import {
    View,
    StyleSheet,
    BackHandler,
    StatusBar,
    Platform,
    Alert,
} from 'react-native';
import { WebView } from 'react-native-webview';
import * as Network from 'expo-network';
import Colors from '../theme/colors';
import {
    BASE_URL,
    isUrlAllowed,
    INJECTED_SECURITY_JS,
} from '../config/security';
import LoadingOverlay from '../components/LoadingOverlay';
import ErrorScreen from '../components/ErrorScreen';

/**
 * Secure WebView screen that loads the MikBill web app.
 * 
 * Security features:
 * - Domain whitelist enforcement (only pay.billnesia.com)
 * - HTTPS-only enforcement
 * - External link blocking
 * - Back button navigation within WebView
 * - JavaScript injection for popup/context menu prevention
 * - Third-party cookie blocking
 * - File access disabled
 */
export default function WebViewScreen() {
    const webViewRef = useRef(null);
    const [isLoading, setIsLoading] = useState(true);
    const [hasError, setHasError] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');
    const [canGoBack, setCanGoBack] = useState(false);
    const [isConnected, setIsConnected] = useState(true);

    // Check network status on mount
    useEffect(() => {
        checkNetwork();
    }, []);

    const checkNetwork = async () => {
        try {
            const networkState = await Network.getNetworkStateAsync();
            setIsConnected(networkState.isInternetReachable ?? true);
            if (!networkState.isInternetReachable) {
                setHasError(true);
                setErrorMessage('net::ERR_INTERNET_DISCONNECTED');
            }
        } catch (e) {
            // Network check failed, assume connected
            setIsConnected(true);
        }
    };

    // Handle Android back button — navigate WebView history first
    useEffect(() => {
        const backHandler = BackHandler.addEventListener(
            'hardwareBackPress',
            () => {
                if (canGoBack && webViewRef.current) {
                    webViewRef.current.goBack();
                    return true; // Prevent default (app exit)
                }
                // Show exit confirmation dialog
                Alert.alert(
                    'Keluar Aplikasi',
                    'Apakah Anda yakin ingin keluar?',
                    [
                        { text: 'Batal', style: 'cancel' },
                        { text: 'Keluar', style: 'destructive', onPress: () => BackHandler.exitApp() },
                    ],
                    { cancelable: true }
                );
                return true;
            }
        );

        return () => backHandler.remove();
    }, [canGoBack]);

    /**
     * Security: Intercept all navigation requests.
     * Only allow URLs matching the domain whitelist.
     */
    const onShouldStartLoadWithRequest = useCallback((request) => {
        const { url } = request;

        // Always allow the initial load
        if (url === BASE_URL || url === BASE_URL + '/') {
            return true;
        }

        // Check if URL is allowed by security policy
        if (!isUrlAllowed(url)) {
            console.warn(`[WebView] Navigation blocked: ${url}`);
            return false;
        }

        return true;
    }, []);

    /**
     * Handle WebView navigation state changes.
     * Track whether we can go back for the hardware back button.
     */
    const onNavigationStateChange = useCallback((navState) => {
        setCanGoBack(navState.canGoBack);
    }, []);

    /**
     * Handle successful page load.
     */
    const onLoadEnd = useCallback(() => {
        setIsLoading(false);
        setHasError(false);
    }, []);

    /**
     * Handle page load start.
     */
    const onLoadStart = useCallback(() => {
        setIsLoading(true);
    }, []);

    /**
     * Handle WebView errors.
     */
    const onError = useCallback((syntheticEvent) => {
        const { nativeEvent } = syntheticEvent;
        setIsLoading(false);
        setHasError(true);
        setErrorMessage(nativeEvent.description || 'Unknown error');
        console.error(`[WebView] Error: ${nativeEvent.description}`);
    }, []);

    /**
     * Handle HTTP errors (4xx, 5xx).
     */
    const onHttpError = useCallback((syntheticEvent) => {
        const { nativeEvent } = syntheticEvent;
        if (nativeEvent.statusCode >= 500) {
            setHasError(true);
            setErrorMessage(`Server error: ${nativeEvent.statusCode}`);
        }
        console.warn(`[WebView] HTTP ${nativeEvent.statusCode}: ${nativeEvent.url}`);
    }, []);

    /**
     * Handle messages from injected JavaScript.
     */
    const onMessage = useCallback((event) => {
        try {
            const data = JSON.parse(event.nativeEvent.data);
            if (data.type === 'PAGE_LOADED') {
                // Page successfully loaded notification
                console.log(`[WebView] Page loaded: ${data.title}`);
            }
        } catch (e) {
            // Ignore non-JSON messages
        }
    }, []);

    /**
     * Retry loading after error.
     */
    const handleRetry = useCallback(async () => {
        setHasError(false);
        setIsLoading(true);
        setErrorMessage('');

        // Re-check network
        await checkNetwork();

        if (webViewRef.current) {
            webViewRef.current.reload();
        }
    }, []);

    // Show error screen if there's an error
    if (hasError) {
        return (
            <>
                <StatusBar barStyle="light-content" backgroundColor={Colors.background} />
                <ErrorScreen errorMessage={errorMessage} onRetry={handleRetry} />
            </>
        );
    }

    return (
        <View style={styles.container}>
            <StatusBar
                barStyle="light-content"
                backgroundColor={Colors.background}
                translucent={false}
            />

            <WebView
                ref={webViewRef}
                source={{ uri: BASE_URL }}
                style={styles.webview}
                // Security settings
                onShouldStartLoadWithRequest={onShouldStartLoadWithRequest}
                originWhitelist={['https://*']}
                javaScriptEnabled={true}
                domStorageEnabled={true}
                injectedJavaScript={INJECTED_SECURITY_JS}
                // Cookie & storage security
                thirdPartyCookiesEnabled={false}
                sharedCookiesEnabled={false}
                incognito={false}
                // File access security
                allowFileAccess={false}
                allowFileAccessFromFileURLs={false}
                allowUniversalAccessFromFileURLs={false}
                // Prevent mixed content
                mixedContentMode="never"
                // Media
                allowsInlineMediaPlayback={true}
                mediaPlaybackRequiresUserAction={true}
                // Navigation handlers
                onNavigationStateChange={onNavigationStateChange}
                onLoadStart={onLoadStart}
                onLoadEnd={onLoadEnd}
                onError={onError}
                onHttpError={onHttpError}
                onMessage={onMessage}
                // User agent — identify as MikBill mobile app
                applicationNameForUserAgent="MikBillMobile/1.0"
                // Performance
                cacheEnabled={true}
                cacheMode="LOAD_DEFAULT"
                // UI
                startInLoadingState={false}
                scalesPageToFit={true}
                showsHorizontalScrollIndicator={false}
                showsVerticalScrollIndicator={false}
                overScrollMode="never"
                // Text zoom
                textZoom={100}
                // Pull to refresh (Android)
                pullToRefreshEnabled={true}
                // Geolocation
                geolocationEnabled={false}
                // Disable automatic detection of phone numbers
                dataDetectorTypes="none"
            />

            {/* Loading overlay on top of WebView */}
            <LoadingOverlay visible={isLoading} />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: Colors.background,
    },
    webview: {
        flex: 1,
        backgroundColor: Colors.background,
    },
});
