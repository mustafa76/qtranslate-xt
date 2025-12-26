<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Mevcut dilin locale'unu döndürür
 */
function qtranxf_localeForCurrentLanguage( string $locale ): string {
    global $q_config;
    $lang = $q_config['language'] ?? $q_config['default_language'];

    if ( isset( $q_config['locale'][ $lang ] ) ) {
        $locale_lang = $q_config['locale'][ $lang ];

        $locales = [
            $locale_lang . '.utf8',
            $locale_lang . '.UTF-8',
            $locale_lang,
            $lang . '_' . strtoupper( $lang ),
        ];

        $windows_locale = qtranxf_default_windows_locale();
        if ( isset( $windows_locale[ $lang ] ) ) {
            $locales[] = $windows_locale[ $lang ];
        }

        @setlocale( LC_TIME, $locales );
    } else {
        $locale_lang = $locale;
    }

    return $locale_lang;
}

/**
 * Çeviri yardımcıları
 */
function qtranxf_useCurrentLanguageIfNotFoundShowEmpty( $content ) {
    global $q_config;
    return qtranxf_use( $q_config['language'], $content, false, true );
}

function qtranxf_useCurrentLanguageIfNotFoundShowAvailable( $content ) {
    global $q_config;
    return qtranxf_use( $q_config['language'], $content, true, false );
}

function qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $content ) {
    global $q_config;
    return qtranxf_use( $q_config['language'], $content, false, false );
}

function qtranxf_useDefaultLanguage( $content ) {
    global $q_config;
    return qtranxf_use( $q_config['default_language'], $content, false, false );
}

function qtranxf_useRawTitle( string $title, string $raw_title = '', string $context = 'save' ): string {
    if ( $context === 'save' ) {
        $raw_title = $raw_title ?: $title;
        $raw_title = qtranxf_useDefaultLanguage( $raw_title );
        $title     = remove_accents( $raw_title );
    }
    return $title;
}

function qtranxf_gettext( $translated ) {
    global $q_config;
    return qtranxf_use( $q_config['language'], $translated, false, false );
}

function qtranxf_gettext_with_context( $translated ) {
    return qtranxf_gettext( $translated );
}

function qtranxf_ngettext( $translated ) {
    return qtranxf_gettext( $translated );
}

if ( ! function_exists( 'qtranxf_getLanguage' ) ) {
    function qtranxf_getLanguage() {
        global $q_config;
        return $q_config['language'];
    }
}

/**
 * EN GÜÇLÜ ÇÖZÜM: Site başlığını zorla çevir (option_blogname ve option_blogdescription)
 */
function qtranxf_force_translate_site_title( $value ) {
    global $q_config;
    if ( is_admin() ) {
        return $value; // Admin tarafında ham hali göster
    }
    return qtranxf_use( $q_config['language'], $value, false, false );
}
add_filter( 'option_blogname', 'qtranxf_force_translate_site_title' );
add_filter( 'option_blogdescription', 'qtranxf_force_translate_site_title' );

/**
 * document_title_parts için güçlü array çevirisi
 */
function qtranxf_document_title_parts_translate( array $parts ): array {
    global $q_config;
    $lang = $q_config['language'];

    // Site adı ve sloganını option'dan tekrar çekip çevir (en güvenilir yol)
    if ( isset( $parts['title'] ) || isset( $parts['tagline'] ) || isset( $parts['site'] ) ) {
        $site_name = get_bloginfo( 'name' );
        $tagline   = get_bloginfo( 'description' );

        if ( isset( $parts['title'] ) ) {
            $parts['title'] = qtranxf_use( $lang, $site_name, false, false );
        }
        if ( isset( $parts['tagline'] ) ) {
            $parts['tagline'] = qtranxf_use( $lang, $tagline, false, false );
        }
        if ( isset( $parts['site'] ) ) {
            $parts['site'] = qtranxf_use( $lang, $site_name, false, false );
        }
    }

    // Diğer parçaları da çevir
    foreach ( $parts as $key => $value ) {
        if ( is_string( $value ) ) {
            $parts[ $key ] = qtranxf_use( $lang, $value, false, false );
        }
    }

    return $parts;
}

/**
 * Ana filtreler
 */
function qtranxf_add_main_filters(): void {
    add_filter( 'the_content', 'qtranxf_useCurrentLanguageIfNotFoundShowAvailable', 100 );
    add_filter( 'the_excerpt', 'qtranxf_useCurrentLanguageIfNotFoundShowAvailable' );
    add_filter( 'the_content_feed', 'qtranxf_useCurrentLanguageIfNotFoundShowAvailable' );
    add_filter( 'the_excerpt_rss', 'qtranxf_useCurrentLanguageIfNotFoundShowAvailable' );

    add_filter( 'pre_get_document_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'document_title_parts', 'qtranxf_document_title_parts_translate', 5, 1 ); // Erken priority
    add_filter( 'document_title_separator', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'wp_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 0 );

    add_filter( 'single_post_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'the_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'bloginfo', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 10, 2 );

    add_filter( 'sanitize_title', 'qtranxf_useRawTitle', 0, 3 );

    add_filter( 'wp_list_categories', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'the_category', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'single_cat_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'single_tag_title', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'get_the_terms', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );

    add_filter( 'comment_moderation_subject', 'qtranxf_useDefaultLanguage' );
    add_filter( 'comment_moderation_text', 'qtranxf_useDefaultLanguage' );
    add_filter( 'comment_notification_text', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'comment_notification_subject', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
    add_filter( 'comment_notification_headers', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );

    add_filter( 'locale', 'qtranxf_localeForCurrentLanguage', 99 );
    add_filter( 'pre_option_rss_language', 'qtranxf_getLanguage' );

    add_filter( 'gettext', 'qtranxf_gettext', 0 );
    add_filter( 'gettext_with_context', 'qtranxf_gettext_with_context', 0 );
    add_filter( 'ngettext', 'qtranxf_ngettext', 0 );

    add_filter( '_wp_post_revision_field_post_title', 'qtranxf_showAllSeparated' );
    add_filter( '_wp_post_revision_field_post_content', 'qtranxf_showAllSeparated' );
    add_filter( '_wp_post_revision_field_post_excerpt', 'qtranxf_showAllSeparated' );

    add_filter( 'wp_trim_words', 'qtranxf_trim_words', 0, 4 );
    add_filter( 'oembed_response_data', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' );
}
