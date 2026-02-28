import React, { useEffect, useRef } from 'react';
import { View, Text, Animated, StyleSheet, StatusBar } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../theme/colors';

/**
 * Branded splash screen with animated fade-in logo.
 * Auto-navigates to WebView after a brief delay.
 */
export default function SplashScreen({ navigation }) {
    const fadeAnim = useRef(new Animated.Value(0)).current;
    const scaleAnim = useRef(new Animated.Value(0.8)).current;
    const subtitleFade = useRef(new Animated.Value(0)).current;

    useEffect(() => {
        // Animate logo
        Animated.parallel([
            Animated.timing(fadeAnim, {
                toValue: 1,
                duration: 800,
                useNativeDriver: true,
            }),
            Animated.spring(scaleAnim, {
                toValue: 1,
                tension: 50,
                friction: 7,
                useNativeDriver: true,
            }),
        ]).start();

        // Animate subtitle after logo
        setTimeout(() => {
            Animated.timing(subtitleFade, {
                toValue: 1,
                duration: 500,
                useNativeDriver: true,
            }).start();
        }, 600);

        // Navigate to WebView after splash
        const timer = setTimeout(() => {
            navigation.replace('WebView');
        }, 2200);

        return () => clearTimeout(timer);
    }, []);

    return (
        <View style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor={Colors.background} />

            <Animated.View
                style={[
                    styles.logoContainer,
                    {
                        opacity: fadeAnim,
                        transform: [{ scale: scaleAnim }],
                    },
                ]}
            >
                <View style={styles.iconCircle}>
                    <Ionicons name="cellular" size={40} color={Colors.primary} />
                </View>
                <Text style={styles.title}>MIKBILL</Text>
            </Animated.View>

            <Animated.View style={[styles.subtitleContainer, { opacity: subtitleFade }]}>
                <Text style={styles.subtitle}>Mikrotik Billing System</Text>
                <View style={styles.divider} />
                <Text style={styles.version}>v1.0.0</Text>
            </Animated.View>

            <View style={styles.footer}>
                <Text style={styles.footerText}>Powered by Billnesia</Text>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: Colors.background,
        justifyContent: 'center',
        alignItems: 'center',
    },
    logoContainer: {
        alignItems: 'center',
    },
    iconCircle: {
        width: 80,
        height: 80,
        borderRadius: 20,
        backgroundColor: Colors.surface,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 16,
        borderWidth: 1,
        borderColor: Colors.border,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 12,
        elevation: 8,
    },
    title: {
        fontSize: 32,
        fontWeight: '800',
        color: Colors.textPrimary,
        letterSpacing: 4,
    },
    subtitleContainer: {
        alignItems: 'center',
        marginTop: 12,
    },
    subtitle: {
        fontSize: 14,
        color: Colors.textSecondary,
        letterSpacing: 1,
    },
    divider: {
        width: 40,
        height: 2,
        backgroundColor: Colors.primary,
        marginVertical: 16,
        borderRadius: 1,
    },
    version: {
        fontSize: 12,
        color: Colors.textMuted,
    },
    footer: {
        position: 'absolute',
        bottom: 40,
    },
    footerText: {
        fontSize: 12,
        color: Colors.textMuted,
    },
});
