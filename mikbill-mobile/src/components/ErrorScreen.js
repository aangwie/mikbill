import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import Colors from '../theme/colors';

/**
 * Error/Offline screen with retry button.
 * Shown when the WebView fails to load or the device is offline.
 */
export default function ErrorScreen({ errorMessage, onRetry }) {
    const isOffline =
        errorMessage?.includes('net::ERR_INTERNET_DISCONNECTED') ||
        errorMessage?.includes('net::ERR_NAME_NOT_RESOLVED') ||
        errorMessage?.includes('NSURLErrorDomain');

    return (
        <View style={styles.container}>
            <View style={styles.iconCircle}>
                <Ionicons
                    name={isOffline ? 'cloud-offline-outline' : 'warning-outline'}
                    size={48}
                    color={Colors.primary}
                />
            </View>

            <Text style={styles.title}>
                {isOffline ? 'Tidak Ada Koneksi' : 'Terjadi Kesalahan'}
            </Text>

            <Text style={styles.message}>
                {isOffline
                    ? 'Periksa koneksi internet Anda dan coba lagi.'
                    : 'Gagal memuat halaman. Silakan coba beberapa saat lagi.'}
            </Text>

            {errorMessage && !isOffline && (
                <Text style={styles.errorDetail} numberOfLines={2}>
                    {errorMessage}
                </Text>
            )}

            <TouchableOpacity
                style={styles.retryButton}
                onPress={onRetry}
                activeOpacity={0.8}
            >
                <Ionicons name="refresh" size={20} color={Colors.white} />
                <Text style={styles.retryText}>Coba Lagi</Text>
            </TouchableOpacity>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: Colors.background,
        justifyContent: 'center',
        alignItems: 'center',
        padding: 32,
    },
    iconCircle: {
        width: 96,
        height: 96,
        borderRadius: 48,
        backgroundColor: Colors.surface,
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 24,
        borderWidth: 1,
        borderColor: Colors.border,
    },
    title: {
        fontSize: 22,
        fontWeight: '700',
        color: Colors.textPrimary,
        marginBottom: 8,
    },
    message: {
        fontSize: 15,
        color: Colors.textSecondary,
        textAlign: 'center',
        lineHeight: 22,
        marginBottom: 8,
    },
    errorDetail: {
        fontSize: 12,
        color: Colors.textMuted,
        textAlign: 'center',
        marginBottom: 24,
        fontFamily: 'monospace',
    },
    retryButton: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: Colors.primary,
        paddingHorizontal: 28,
        paddingVertical: 14,
        borderRadius: 12,
        gap: 8,
        marginTop: 16,
        shadowColor: Colors.primary,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 6,
    },
    retryText: {
        color: Colors.white,
        fontSize: 16,
        fontWeight: '600',
    },
});
