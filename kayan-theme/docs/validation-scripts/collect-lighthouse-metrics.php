#!/usr/bin/env php
<?php
/**
 * Phase 1.5 — aggregate Lighthouse JSON exports into a single metrics file.
 * Usage: php collect-lighthouse-metrics.php <dir-with-json>
 */
$dir = $argv[1] ?? __DIR__ . '/../validation-artifacts';
$files = glob( rtrim( $dir, '/' ) . '/lighthouse-*.json' );
$out   = array();

foreach ( $files as $file ) {
	$raw = json_decode( file_get_contents( $file ), true );
	if ( ! is_array( $raw ) ) {
		continue;
	}
	$lr   = isset( $raw['audits'] ) ? $raw : ( $raw['lighthouseResult'] ?? array() );
	$aud  = $lr['audits'] ?? array();
	$cats = $lr['categories'] ?? array();
	$name = basename( $file, '.json' );

	$pick = function ( $id ) use ( $aud ) {
		$a = $aud[ $id ] ?? array();
		return array(
			'display' => $a['displayValue'] ?? null,
			'numeric' => $a['numericValue'] ?? null,
			'score'   => $a['score'] ?? null,
		);
	};

	$out[ $name ] = array(
		'url'          => $lr['finalUrl'] ?? $lr['requestedUrl'] ?? null,
		'fetchTime'    => $lr['fetchTime'] ?? null,
		'performance'  => $cats['performance']['score'] ?? null,
		'seo'          => $cats['seo']['score'] ?? null,
		'best_practices' => $cats['best-practices']['score'] ?? null,
		'accessibility' => $cats['accessibility']['score'] ?? null,
		'lcp'          => $pick( 'largest-contentful-paint' ),
		'cls'          => $pick( 'cumulative-layout-shift' ),
		'inp'          => $pick( 'interaction-to-next-paint' ),
		'tbt'          => $pick( 'total-blocking-time' ),
		'ttfb'         => $pick( 'server-response-time' ),
		'dom_size'     => $pick( 'dom-size' ),
		'unused_css'   => $pick( 'unused-css-rules' ),
		'unused_js'    => $pick( 'unused-javascript' ),
		'fcp'          => $pick( 'first-contentful-paint' ),
		'speed_index'  => $pick( 'speed-index' ),
	);
}

echo json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
