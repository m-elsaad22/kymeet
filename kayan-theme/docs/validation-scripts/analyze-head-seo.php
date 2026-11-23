#!/usr/bin/env php
<?php
/**
 * Phase 1.5 — static <head> SEO duplicate analyzer for saved HTML snapshots.
 * Usage: php analyze-head-seo.php path/to.html
 */
if ( $argc < 2 ) {
	fwrite( STDERR, "Usage: php {$argv[0]} <html-file>\n" );
	exit( 1 );
}

$html = file_get_contents( $argv[1] );
if ( $html === false ) {
	fwrite( STDERR, "Cannot read {$argv[1]}\n" );
	exit( 1 );
}

function count_matches( $html, $pattern ) {
	return preg_match_all( $pattern, $html, $m ) ? count( $m[0] ) : 0;
}

function extract_all( $html, $pattern ) {
	preg_match_all( $pattern, $html, $m );
	return $m[1] ?? array();
}

$checks = array(
	'title'            => count_matches( $html, '/<title[^>]*>/i' ),
	'meta_description' => count_matches( $html, '/<meta[^>]+name=["\']description["\'][^>]*>/i' ),
	'canonical'        => count_matches( $html, '/<link[^>]+rel=["\']canonical["\'][^>]*>/i' ),
	'og_title'         => count_matches( $html, '/<meta[^>]+property=["\']og:title["\'][^>]*>/i' ),
	'og_description'   => count_matches( $html, '/<meta[^>]+property=["\']og:description["\'][^>]*>/i' ),
	'og_url'           => count_matches( $html, '/<meta[^>]+property=["\']og:url["\'][^>]*>/i' ),
	'twitter_card'     => count_matches( $html, '/<meta[^>]+name=["\']twitter:card["\'][^>]*>/i' ),
	'hreflang'         => count_matches( $html, '/<link[^>]+rel=["\']alternate["\'][^>]+hreflang=/i' ),
	'json_ld'          => count_matches( $html, '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>/i' ),
	'breadcrumb_micro' => count_matches( $html, '/itemtype=["\']https?:\/\/schema\.org\/BreadcrumbList["\']/i' ),
	'rank_math'        => count_matches( $html, '/rank-math|rank_math/i' ),
);

$schemas = array();
if ( preg_match_all( '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $blocks ) ) {
	foreach ( $blocks[1] as $raw ) {
		$data = json_decode( trim( $raw ), true );
		if ( ! is_array( $data ) ) {
			$schemas[] = array( 'parse' => 'INVALID_JSON' );
			continue;
		}
		$graph = isset( $data['@graph'] ) ? $data['@graph'] : array( $data );
		foreach ( $graph as $node ) {
			if ( is_array( $node ) && isset( $node['@type'] ) ) {
				$type = $node['@type'];
				if ( is_array( $type ) ) {
					$type = implode( ',', $type );
				}
				$schemas[] = array(
					'@type' => $type,
					'@context' => $data['@context'] ?? ( $node['@context'] ?? '' ),
				);
			}
		}
	}
}

$out = array(
	'file'   => $argv[1],
	'counts' => $checks,
	'schema_types' => $schemas,
	'issues' => array(),
);

foreach ( array( 'title', 'meta_description', 'canonical' ) as $key ) {
	if ( $checks[ $key ] !== 1 ) {
		$out['issues'][] = "Expected exactly 1 {$key}, found {$checks[$key]}";
	}
}
if ( $checks['og_title'] > 1 || $checks['og_description'] > 1 ) {
	$out['issues'][] = 'Duplicate Open Graph tags detected';
}
if ( $checks['breadcrumb_micro'] > 0 && $checks['json_ld'] > 0 ) {
	$out['issues'][] = 'Breadcrumb may be duplicated (microdata + JSON-LD)';
}
if ( $checks['hreflang'] === 1 ) {
	$out['issues'][] = 'Single hreflang without x-default pair (likely invalid)';
}

echo json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n";
