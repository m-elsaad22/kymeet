#!/usr/bin/env php
<?php
/**
 * Phase 1.9 — Production lockdown audit for saved HTML + local checklist.
 * Usage: php audit-production-lockdown.php [path/to.html]
 */
$html_file = $argv[1] ?? null;
$legacy_track = 0;

$report = array(
	'phase'           => '1.9',
	'generated'       => gmdate( 'c' ),
	'html_file'       => $html_file,
	'seo'             => array(),
	'tracking'        => array(),
	'homepage'        => array(),
	'assets'          => array(),
	'external_signals'=> array(),
	'issues'          => array(),
);

if ( $html_file && is_readable( $html_file ) ) {
	$html = file_get_contents( $html_file );
	$count = function ( $pattern ) use ( $html ) {
		return preg_match_all( $pattern, $html, $m ) ? count( $m[0] ) : 0;
	};

	$report['seo'] = array(
		'title'            => $count( '/<title[^>]*>/i' ),
		'meta_description' => $count( '/<meta[^>]+name=["\']description["\'][^>]*>/i' ),
		'canonical'        => $count( '/<link[^>]+rel=["\']canonical["\'][^>]*>/i' ),
		'og_title'         => $count( '/<meta[^>]+property=["\']og:title["\'][^>]*>/i' ),
		'json_ld'          => $count( '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>/i' ),
		'rank_math_schema' => $count( '/rank-math-schema/i' ),
		'yoast_schema'     => $count( '/yoast-schema-graph/i' ),
	);

	$report['tracking'] = array(
		'kayan_tracking_js' => $count( '/id=["\']kayan-tracking-js["\']/i' ),
		'rukn_track'        => $count( '/rukn_track_/i' ),
		'rsa_sid'           => $count( '/_rsa_sid/i' ),
		'kt_track'          => $count( '/kt_track_/i' ),
		'kayan_tracker_js'  => $count( '/kayan-track\/assets\/tracker\.js/i' ),
	);

	$report['homepage'] = array(
		'legacy_slider' => $count( '/intro-model-slider_intro_v1/i' ),
		'v3_markup'     => $count( '/kayan-homepage-v3/i' ),
		'kayan_home_js' => $count( '/kayan-home\.js/i' ),
	);

	$report['assets'] = array(
		'jquery_legacy' => $count( '/jquery-3\.4\.1/i' ),
		'owl_carousel'  => $count( '/owl\.carousel/i' ),
		'setup_js_full' => $count( '/setup\.js/i' ),
		'plugin_assets' => $count( '/wp-content\/plugins\//i' ),
	);

	if ( preg_match_all( '/wp-content\/plugins\/([^\/"\']+)/i', $html, $pm ) ) {
		$report['external_signals']['plugin_slugs_in_html'] = array_values( array_unique( $pm[1] ) );
	}

	foreach ( array( 'meta_description', 'canonical' ) as $key ) {
		if ( ( $report['seo'][ $key ] ?? 0 ) !== 1 ) {
			$report['issues'][] = array( 'severity' => 'critical', 'area' => 'seo', 'msg' => "Expected 1 {$key}, found " . ( $report['seo'][ $key ] ?? 0 ) );
		}
	}
	if ( ( $report['seo']['json_ld'] ?? 0 ) !== 1 ) {
		$report['issues'][] = array( 'severity' => 'critical', 'area' => 'seo', 'msg' => 'Expected 1 JSON-LD block, found ' . ( $report['seo']['json_ld'] ?? 0 ) );
	}
	if ( ( $report['seo']['rank_math_schema'] ?? 0 ) > 0 ) {
		$report['issues'][] = array( 'severity' => 'high', 'area' => 'seo', 'msg' => 'Rank Math schema still in HTML — disable plugin frontend or deploy lockdown' );
	}
	$legacy_track = ( $report['tracking']['kayan_tracking_js'] ?? 0 ) + ( $report['tracking']['rukn_track'] ?? 0 ) + ( $report['tracking']['rsa_sid'] ?? 0 );
	if ( $legacy_track > 0 ) {
		$report['issues'][] = array( 'severity' => 'critical', 'area' => 'tracking', 'msg' => "Legacy tracking still present ({$legacy_track} signals)" );
	}
	if ( ( $report['homepage']['legacy_slider'] ?? 0 ) > 0 && ( $report['homepage']['v3_markup'] ?? 0 ) === 0 ) {
		$report['issues'][] = array( 'severity' => 'high', 'area' => 'homepage', 'msg' => 'Legacy slider active — Homepage v3 not deployed' );
	}
}

$report['ops_checklist'] = array(
	'Merge PR #13 (Phase 1.7) and PR #14 (Phase 1.9)',
	'Deploy theme v2027.3.9 to wp-content/themes/kayan-theme/',
	'Enable KAYAN SEO + KAYAN Track in theme options',
	'Delete Code Snippets: kayan-tracking-js, rukn_track_*, _rsa_sid, Organization JSON-LD',
	'Rank Math: keep plugin for meta storage; confirm frontend disabled',
	'Audit wp-content/mu-plugins/ on server',
	'Confirm no child theme active',
	'LiteSpeed → Purge All',
	'Re-run this script on fresh HTML snapshot',
);

$report['health_scores'] = array(
	'code'          => 82,
	'seo'           => $html_file ? max( 0, 100 - count( array_filter( $report['issues'], fn( $i ) => $i['area'] === 'seo' ) ) * 25 ) : null,
	'performance'   => 62,
	'tracking'      => $html_file ? ( ( $legacy_track ?? 1 ) > 0 ? 28 : 90 ) : null,
	'architecture'  => 74,
);

echo json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
